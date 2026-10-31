import javax.swing.*;
import javax.swing.table.DefaultTableModel;
import java.awt.*;
import java.sql.*;

/** Ventana de facturación (rol cajero): cliente + platos + totales */
public class FacturacionFrame extends JFrame {
    private final JComboBox<String> cmbTipo = new JComboBox<>(new String[]{"CC", "TI", "CE", "NIT", "Pasaporte"});
    private final JTextField txtNumero = new JTextField(12);
    private final JTextField txtNombre = new JTextField(16);
    private final JTextField txtCelular = new JTextField(10);

    private final DefaultTableModel model = new DefaultTableModel(
        new String[]{"Producto", "Valor unitario", "Cantidad", "Valor total"}, 0) {
        @Override public boolean isCellEditable(int r, int c) { return c == 2; }
    };
    private final JTable table = new JTable(model);

    private final JLabel lblSubtotal = new JLabel("0.00");
    private final JLabel lblImpuesto = new JLabel("0.00");
    private final JLabel lblTotal = new JLabel("0.00");
    private boolean recalculando = false;

    public FacturacionFrame() {
        super("Comidas XYZ - Facturación (interfaz de escritorio)");
        setDefaultCloseOperation(EXIT_ON_CLOSE);
        setSize(800, 580);
        setLocationRelativeTo(null);
        setLayout(new BorderLayout());

        JPanel cliente = new JPanel(new FlowLayout(FlowLayout.LEFT));
        cliente.setBorder(BorderFactory.createTitledBorder("Datos del cliente"));
        cliente.add(new JLabel("Tipo:")); cliente.add(cmbTipo);
        cliente.add(new JLabel("Nº ID:")); cliente.add(txtNumero);
        cliente.add(new JLabel("Nombre:")); cliente.add(txtNombre);
        cliente.add(new JLabel("Celular:")); cliente.add(txtCelular);
        add(cliente, BorderLayout.NORTH);

        add(new JScrollPane(table), BorderLayout.CENTER);

        JPanel pie = new JPanel(new FlowLayout(FlowLayout.RIGHT));
        pie.add(new JLabel("Subtotal:")); pie.add(lblSubtotal);
        pie.add(new JLabel("  Impuesto (8%):")); pie.add(lblImpuesto);
        pie.add(new JLabel("  Total a pagar:")); pie.add(lblTotal);
        JButton generar = new JButton("Generar factura");
        pie.add(generar);
        add(pie, BorderLayout.SOUTH);

        generar.addActionListener(e -> generarFactura());
        model.addTableModelListener(e -> calcularTotales());
        cargarProductos();
        calcularTotales();
    }

    private void cargarProductos() {
        model.setRowCount(0);
        try (Connection c = DB.conectar();
             Statement st = c.createStatement();
             ResultSet rs = st.executeQuery("SELECT nombre, costo_despues_iva FROM productos ORDER BY nombre")) {
            while (rs.next()) {
                model.addRow(new Object[]{rs.getString("nombre"), rs.getDouble("costo_despues_iva"), 0, 0.0});
            }
        } catch (Exception ignored) { }
    }

    private void calcularTotales() {
        if (recalculando) return;
        recalculando = true;
        double subtotal = 0;
        for (int i = 0; i < model.getRowCount(); i++) {
            double unit = (double) model.getValueAt(i, 1);
            int cant = Integer.parseInt(String.valueOf(model.getValueAt(i, 2)));
            double total = unit * cant;
            model.setValueAt(total, i, 3);
            subtotal += total;
        }
        double imp = subtotal * 0.08; // impuesto al consumo 8%
        lblSubtotal.setText(String.format("%.2f", subtotal));
        lblImpuesto.setText(String.format("%.2f", imp));
        lblTotal.setText(String.format("%.2f", subtotal + imp));
        recalculando = false;
    }

    private void generarFactura() {
        calcularTotales();
        double subtotal = 0;
        for (int i = 0; i < model.getRowCount(); i++) {
            subtotal += (double) model.getValueAt(i, 3);
        }
        double imp = subtotal * 0.08;
        double total = subtotal + imp;

        try (Connection c = DB.conectar()) {
            int idCliente;
            try (PreparedStatement ps = c.prepareStatement("SELECT id_cliente FROM clientes WHERE numero_id = ?")) {
                ps.setString(1, txtNumero.getText());
                ResultSet rs = ps.executeQuery();
                if (rs.next()) {
                    idCliente = rs.getInt(1);
                    try (PreparedStatement up = c.prepareStatement(
                            "UPDATE clientes SET nombre=?, celular=?, tipo_id=? WHERE id_cliente=?")) {
                        up.setString(1, txtNombre.getText());
                        up.setString(2, txtCelular.getText());
                        up.setString(3, (String) cmbTipo.getSelectedItem());
                        up.setInt(4, idCliente);
                        up.executeUpdate();
                    }
                } else {
                    try (PreparedStatement ins = c.prepareStatement(
                            "INSERT INTO clientes (tipo_id, numero_id, nombre, celular) VALUES (?,?,?,?)",
                            Statement.RETURN_GENERATED_KEYS)) {
                        ins.setString(1, (String) cmbTipo.getSelectedItem());
                        ins.setString(2, txtNumero.getText());
                        ins.setString(3, txtNombre.getText());
                        ins.setString(4, txtCelular.getText());
                        ins.executeUpdate();
                        ResultSet rs2 = ins.getGeneratedKeys();
                        rs2.next();
                        idCliente = rs2.getInt(1);
                    }
                }
            }

            int idFactura;
            try (PreparedStatement ps = c.prepareStatement(
                    "INSERT INTO facturas (id_cliente, subtotal, impoconsumo_8, total_pagar) VALUES (?,?,?,?)",
                    Statement.RETURN_GENERATED_KEYS)) {
                ps.setInt(1, idCliente);
                ps.setDouble(2, subtotal);
                ps.setDouble(3, imp);
                ps.setDouble(4, total);
                ps.executeUpdate();
                ResultSet rs = ps.getGeneratedKeys();
                rs.next();
                idFactura = rs.getInt(1);
            }

            StringBuilder detalle = new StringBuilder();
            detalle.append("FACTURA Nº ").append(idFactura).append("\n");
            detalle.append("Cliente: ").append(txtNombre.getText())
                  .append(" (").append(cmbTipo.getSelectedItem()).append(" ").append(txtNumero.getText()).append(")\n\n");

            try (PreparedStatement ps = c.prepareStatement(
                    "INSERT INTO factura_detalles (id_factura, id_producto, cantidad, valor_unitario, valor_total) VALUES (?,?,?,?,?)")) {
                for (int i = 0; i < model.getRowCount(); i++) {
                    int cant = Integer.parseInt(String.valueOf(model.getValueAt(i, 2)));
                    if (cant <= 0) continue;
                    String nombre = (String) model.getValueAt(i, 0);
                    double unit = (double) model.getValueAt(i, 1);
                    double tot = unit * cant;
                    ps.setInt(1, idFactura);
                    ps.setInt(2, buscarProductoId(c, nombre));
                    ps.setInt(3, cant);
                    ps.setDouble(4, unit);
                    ps.setDouble(5, tot);
                    ps.executeUpdate();
                    detalle.append(String.format("%-18s x%d  = %.2f%n", nombre, cant, tot));
                }
            }
            detalle.append("\nSubtotal: ").append(String.format("%.2f", subtotal))
                  .append("\nImpuesto al consumo (8%): ").append(String.format("%.2f", imp))
                  .append("\nTOTAL A PAGAR: ").append(String.format("%.2f", total));

            JOptionPane.showMessageDialog(this, detalle.toString(), "Factura", JOptionPane.INFORMATION_MESSAGE);
        } catch (Exception ex) {
            JOptionPane.showMessageDialog(this, "Error: " + ex.getMessage(), "Facturación", JOptionPane.ERROR_MESSAGE);
        }
    }

    private int buscarProductoId(Connection c, String nombre) throws SQLException {
        try (PreparedStatement ps = c.prepareStatement("SELECT id_producto FROM productos WHERE nombre = ?")) {
            ps.setString(1, nombre);
            ResultSet rs = ps.executeQuery();
            return rs.next() ? rs.getInt(1) : 0;
        }
    }
}
