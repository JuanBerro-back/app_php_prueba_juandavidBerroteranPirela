import javax.swing.*;
import javax.swing.table.DefaultTableModel;
import java.awt.*;
import java.sql.*;

/** Ventana de administración (rol admin): productos y menús */
public class AdminFrame extends JFrame {
    public AdminFrame() {
        super("Comidas XYZ - Administración (interfaz web)");
        setDefaultCloseOperation(EXIT_ON_CLOSE);
        setSize(760, 540);
        setLocationRelativeTo(null);

        JTabbedPane tabs = new JTabbedPane();
        tabs.addTab("Productos", new ProductosPanel());
        tabs.addTab("Menús", new MenusPanel());
        add(tabs);
    }
}

class ProductosPanel extends JPanel {
    private final DefaultTableModel model =
        new DefaultTableModel(new String[]{"ID", "Código", "Nombre", "Antes IVA", "% IVA", "Después IVA"}, 0);
    private final JTable table = new JTable(model);
    private final JTextField txtNombre = new JTextField(12);
    private final JTextField txtCodigo = new JTextField(9);
    private final JTextField txtCosto = new JTextField(8);
    private final JTextField txtIva = new JTextField(5);

    ProductosPanel() {
        super(new BorderLayout());
        add(new JScrollPane(table), BorderLayout.CENTER);

        JPanel form = new JPanel(new FlowLayout(FlowLayout.LEFT));
        form.add(new JLabel("Nombre:")); form.add(txtNombre);
        form.add(new JLabel("Código:")); form.add(txtCodigo);
        form.add(new JLabel("Costo antes IVA:")); form.add(txtCosto);
        form.add(new JLabel("% IVA:")); form.add(txtIva);
        JButton agregar = new JButton("Agregar");
        JButton eliminar = new JButton("Eliminar");
        form.add(agregar); form.add(eliminar);
        add(form, BorderLayout.NORTH);

        agregar.addActionListener(e -> agregar());
        eliminar.addActionListener(e -> eliminar());
        cargar();
    }

    private void agregar() {
        try {
            double antes = Double.parseDouble(txtCosto.getText());
            double iva = Double.parseDouble(txtIva.getText());
            double despues = antes * (1 + iva / 100); // cálculo del IVA
            try (Connection c = DB.conectar();
                 PreparedStatement ps = c.prepareStatement(
                   "INSERT INTO productos (codigo_unico, nombre, costo_antes_iva, porcentaje_iva, costo_despues_iva) VALUES (?,?,?,?,?)")) {
                ps.setString(1, txtCodigo.getText());
                ps.setString(2, txtNombre.getText());
                ps.setDouble(3, antes);
                ps.setDouble(4, iva);
                ps.setDouble(5, despues);
                ps.executeUpdate();
            }
            cargar();
        } catch (Exception ex) {
            JOptionPane.showMessageDialog(this, "Error: " + ex.getMessage(), "Productos", JOptionPane.ERROR_MESSAGE);
        }
    }

    private void eliminar() {
        int fila = table.getSelectedRow();
        if (fila < 0) return;
        int id = (int) model.getValueAt(fila, 0);
        try (Connection c = DB.conectar();
             PreparedStatement ps = c.prepareStatement("DELETE FROM productos WHERE id_producto = ?")) {
            ps.setInt(1, id);
            ps.executeUpdate();
            cargar();
        } catch (Exception ex) {
            JOptionPane.showMessageDialog(this, "Error: " + ex.getMessage(), "Productos", JOptionPane.ERROR_MESSAGE);
        }
    }

    private void cargar() {
        model.setRowCount(0);
        try (Connection c = DB.conectar();
             Statement st = c.createStatement();
             ResultSet rs = st.executeQuery("SELECT * FROM productos ORDER BY nombre")) {
            while (rs.next()) {
                model.addRow(new Object[]{
                    rs.getInt("id_producto"), rs.getString("codigo_unico"), rs.getString("nombre"),
                    rs.getDouble("costo_antes_iva"), rs.getDouble("porcentaje_iva"),
                    rs.getDouble("costo_despues_iva")});
            }
        } catch (Exception ignored) { }
    }
}

class MenusPanel extends JPanel {
    private final JTextField txtDesc = new JTextField(16);
    private final JComboBox<String> cmbDia = new JComboBox<>(
        new String[]{"Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado", "Domingo"});
    private final DefaultListModel<String> productosModel = new DefaultListModel<>();
    private final JList<String> listaProductos = new JList<>(productosModel);

    MenusPanel() {
        super(new BorderLayout());

        JPanel form = new JPanel(new FlowLayout(FlowLayout.LEFT));
        form.add(new JLabel("Descripción:")); form.add(txtDesc);
        form.add(new JLabel("Día:")); form.add(cmbDia);
        JButton crear = new JButton("Crear menú");
        form.add(crear);
        add(form, BorderLayout.NORTH);

        listaProductos.setSelectionMode(ListSelectionModel.MULTIPLE_INTERVAL_SELECTION);
        listaProductos.setBorder(BorderFactory.createTitledBorder("Seleccione los productos del menú"));
        add(new JScrollPane(listaProductos), BorderLayout.CENTER);

        crear.addActionListener(e -> crearMenu());
        cargarProductos();
    }

    private void cargarProductos() {
        productosModel.clear();
        try (Connection c = DB.conectar();
             Statement st = c.createStatement();
             ResultSet rs = st.executeQuery("SELECT id_producto, nombre FROM productos ORDER BY nombre")) {
            while (rs.next()) {
                productosModel.addElement(rs.getInt("id_producto") + " - " + rs.getString("nombre"));
            }
        } catch (Exception ignored) { }
    }

    private void crearMenu() {
        try (Connection c = DB.conectar()) {
            int idMenu;
            try (PreparedStatement ps = c.prepareStatement(
                    "INSERT INTO menus (descripcion, dia_semana) VALUES (?, ?)", Statement.RETURN_GENERATED_KEYS)) {
                ps.setString(1, txtDesc.getText());
                ps.setString(2, (String) cmbDia.getSelectedItem());
                ps.executeUpdate();
                ResultSet rs = ps.getGeneratedKeys();
                rs.next();
                idMenu = rs.getInt(1);
            }
            int n = 0;
            for (String sel : listaProductos.getSelectedValuesList()) {
                int idProd = Integer.parseInt(sel.split(" - ")[0]);
                try (PreparedStatement ps = c.prepareStatement(
                        "INSERT INTO menu_productos (id_menu, id_producto) VALUES (?, ?)")) {
                    ps.setInt(1, idMenu);
                    ps.setInt(2, idProd);
                    ps.executeUpdate();
                    n++;
                }
            }
            JOptionPane.showMessageDialog(this, "Menú creado con " + n + " producto(s).");
        } catch (Exception ex) {
            JOptionPane.showMessageDialog(this, "Error: " + ex.getMessage(), "Menús", JOptionPane.ERROR_MESSAGE);
        }
    }
}
