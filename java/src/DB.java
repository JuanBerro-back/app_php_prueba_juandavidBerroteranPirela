import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

/** Conexión JDBC a la base de datos comidas_xyz */
public class DB {
    private static final String URL =
        "jdbc:mysql://localhost:3306/comidas_xyz?serverTimezone=UTC&useSSL=false&allowPublicKeyRetrieval=true";
    private static final String USER = "root";
    private static final String PASS = "";

    public static Connection conectar() throws SQLException {
        return DriverManager.getConnection(URL, USER, PASS);
    }
}
