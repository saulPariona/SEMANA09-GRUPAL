<%@ page contentType="text/html; charset=UTF-8" pageEncoding="UTF-8" import="jakarta.servlet.http.HttpServletResponse" %>
<%!
    String escapeHtml(String value) {
        if (value == null) return "";
        return value.replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;")
            .replace("\"", "&quot;").replace("'", "&#x27;");
    }
%>
<%
    if (!"POST".equalsIgnoreCase(request.getMethod())) {
        response.sendError(HttpServletResponse.SC_METHOD_NOT_ALLOWED, "Use POST para registrar un contacto.");
        return;
    }
    request.setCharacterEncoding("UTF-8");
    String nombre = request.getParameter("nombre") == null ? "" : request.getParameter("nombre").trim();
    String email = request.getParameter("email") == null ? "" : request.getParameter("email").trim();
    String telefono = request.getParameter("telefono") == null ? "" : request.getParameter("telefono").trim();
    String mensaje = request.getParameter("mensaje") == null ? "" : request.getParameter("mensaje").trim();
    java.util.List<String> errores = new java.util.ArrayList<String>();
    if (nombre.length() < 2 || nombre.length() > 80) errores.add("El nombre debe tener entre 2 y 80 caracteres.");
    if (!email.matches("^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$") || email.length() > 120) errores.add("El correo electrónico no es válido.");
    if (!telefono.matches("^[0-9+()\\-\\s]{7,20}$")) errores.add("El teléfono no es válido.");
    if (mensaje.isEmpty() || mensaje.length() > 500) errores.add("El mensaje es obligatorio y no debe superar 500 caracteres.");
    if (!errores.isEmpty()) {
        response.setStatus(HttpServletResponse.SC_UNPROCESSABLE_ENTITY);
%><!doctype html><html lang="es"><head><meta charset="utf-8"><title>Datos inválidos</title><link rel="stylesheet" href="styles.css"></head>
<body><main class="container"><h1>Datos inválidos</h1><ul class="error"><%
        for (String error : errores) {
%><li><%= escapeHtml(error) %></li><%
        }
%></ul><p><a href="index.jsp">Volver al formulario</a></p></main></body></html><%
        return;
    }
    int id;
    try {
        Class.forName("com.mysql.cj.jdbc.Driver");
        try (java.sql.Connection conexion = java.sql.DriverManager.getConnection(
                "jdbc:mysql://127.0.0.1:3306/registro_contactos?useUnicode=true&characterEncoding=UTF-8&serverTimezone=UTC",
                "root", "");
             java.sql.PreparedStatement consulta = conexion.prepareStatement(
                "INSERT INTO contactos (nombre, email, telefono, mensaje) VALUES (?, ?, ?, ?)",
                java.sql.Statement.RETURN_GENERATED_KEYS)) {
            consulta.setString(1, nombre);
            consulta.setString(2, email);
            consulta.setString(3, telefono);
            consulta.setString(4, mensaje);
            consulta.executeUpdate();
            try (java.sql.ResultSet claves = consulta.getGeneratedKeys()) {
                if (!claves.next()) {
                    response.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR,
                        "No se pudo obtener el identificador del contacto.");
                    return;
                }
                id = claves.getInt(1);
            }
        }
    } catch (java.sql.SQLException | ClassNotFoundException ex) {
        response.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR,
            "No fue posible guardar el contacto en la base de datos.");
        return;
    }
    java.util.List<java.util.Map<String, String>> contactos =
        (java.util.List<java.util.Map<String, String>>) session.getAttribute("contactos");
    if (contactos == null) {
        contactos = new java.util.ArrayList<java.util.Map<String, String>>();
        session.setAttribute("contactos", contactos);
    }
    java.util.Map<String, String> contacto = new java.util.LinkedHashMap<String, String>();
    contacto.put("id", String.valueOf(id));
    contacto.put("nombre", nombre); contacto.put("email", email);
    contacto.put("telefono", telefono); contacto.put("mensaje", mensaje);
    contactos.add(contacto);
    response.sendRedirect("index.jsp");
%>
