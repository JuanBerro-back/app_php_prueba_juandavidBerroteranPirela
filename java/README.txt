VERSIÓN DE ESCRITORIO EN JAVA (Swing + JDBC)
===========================================

Requisitos
----------
- JDK 8 o superior (probado con JDK 21).
- MySQL con la base comidas_xyz importada (ver comidas_xyz.sql y usuarios.sql).
- Conector MySQL (mysql-connector-j-x.x.x.jar) en el classpath al ejecutar.

Compilar
--------
  javac -encoding UTF-8 -d bin src/*.java

Ejecutar (con el conector en el classpath)
------------------------------------------
  java -cp "bin;mysql-connector-j-8.4.0.jar" Main

Archivos
--------
- DB.java             Conexión JDBC a comidas_xyz.
- Main.java           Punto de entrada.
- LoginFrame.java     Login con roles (admin / cajero).
- AdminFrame.java     Ventana admin: pestañas Productos y Menús.
- FacturacionFrame.java  Ventana cajero: cliente + platos + subtotal, 8% y total.

Usuarios de prueba
------------------
  admin   / admin123   -> abre la ventana de administración.
  cajero  / cajero123  -> abre la ventana de facturación.

Capturas de pantalla
--------------------
Las imágenes de las ventanas están en la carpeta screenshots/.
