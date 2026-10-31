import javax.swing.SwingUtilities;
import javax.swing.UIManager;

/** Punto de entrada de la aplicación de escritorio */
public class Main {
    public static void main(String[] args) {
        SwingUtilities.invokeLater(() -> {
            try {
                UIManager.setLookAndFeel(UIManager.getSystemLookAndFeelClassName());
            } catch (Exception ignored) { }
            new LoginFrame().setVisible(true);
        });
    }
}
