import javax.swing.*;
import java.awt.*;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

/** Ventana de inicio de sesión con roles (admin / cajero) */
public class LoginFrame extends JFrame {
    private final JTextField txtUsuario = new JTextField(15);
    private final JPasswordField txtClave = new JPasswordField(15);

    public LoginFrame() {
        super("Comidas XYZ - Iniciar sesión");
        setDefaultCloseOperation(EXIT_ON_CLOSE);
        setLayout(new GridBagLayout());
        setResizable(false);

        GridBagConstraints c = new GridBagConstraints();
        c.insets = new Insets(6, 8, 6, 8);

        c.gridx = 0; c.gridy = 0; c.anchor = GridBagConstraints.EAST;
        add(new JLabel("Usuario:"), c);
        c.gridx = 1; c.anchor = GridBagConstraints.WEST;
        add(txtUsuario, c);

        c.gridx = 0; c.gridy = 1; c.anchor = GridBagConstraints.EAST;
        add(new JLabel("Contraseña:"), c);
        c.gridx = 1; c.anchor = GridBagConstraints.WEST;
        add(txtClave, c);

        JButton btn = new JButton("Ingresar");
        c.gridx = 0; c.gridy = 2; c.gridwidth = 2; c.anchor = GridBagConstraints.CENTER;
        add(btn, c);

        btn.addActionListener(e -> autenticar());
        getRootPane().setDefaultButton(btn);

        pack();
        setLocationRelativeTo(null);
    }

    private void autenticar() {
        String u = txtUsuario.getText().trim();
        String p = new String(txtClave.getPassword());
        try (Connection con = DB.conectar();
             PreparedStatement ps = con.prepareStatement(
                 "SELECT rol FROM usuarios WHERE usuario = ? AND clave = SHA2(?, 256)")) {
            ps.setString(1, u);
            ps.setString(2, p);
            try (ResultSet rs = ps.executeQuery()) {
                if (rs.next()) {
                    String rol = rs.getString("rol");
                    dispose();
                    if ("admin".equals(rol)) {
                        new AdminFrame().setVisible(true);
                    } else {
                        new FacturacionFrame().setVisible(true);
                    }
                } else {
                    JOptionPane.showMessageDialog(this, "Credenciales incorrectas", "Login", JOptionPane.ERROR_MESSAGE);
                }
            }
        } catch (SQLException ex) {
            JOptionPane.showMessageDialog(this, "Error de conexión:\n" + ex.getMessage(),
                    "Login", JOptionPane.ERROR_MESSAGE);
        }
    }
}
