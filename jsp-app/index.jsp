<%@ page contentType="text/html; charset=UTF-8" pageEncoding="UTF-8" %>
<%!
    String escapeHtml(String value) {
        if (value == null) return "";
        return value.replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;")
            .replace("\"", "&quot;").replace("'", "&#x27;");
    }
%>
<%
    if (session.getAttribute("contactos") == null) {
        session.setAttribute("contactos", new java.util.ArrayList<java.util.Map<String, String>>());
    }
%>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Registro de contacto - JSP</title><link rel="stylesheet" href="styles.css"></head>
<body><main class="container">
<h1>Registro de contacto</h1><p class="badge">JSP / Tomcat :8081</p>
<form action="registro.jsp" method="post">
    <label for="nombre">Nombre completo</label><input id="nombre" name="nombre" maxlength="80" required>
    <label for="email">Correo electrónico</label><input id="email" name="email" type="email" maxlength="120" required>
    <label for="telefono">Teléfono</label><input id="telefono" name="telefono" maxlength="20" required>
    <label for="mensaje">Mensaje</label><textarea id="mensaje" name="mensaje" maxlength="500" required></textarea>
    <button type="submit">Guardar contacto</button>
</form>
<h2>Contactos de esta sesión</h2>
<%
    java.util.List<java.util.Map<String, String>> contactos =
        (java.util.List<java.util.Map<String, String>>) session.getAttribute("contactos");
    if (contactos.isEmpty()) {
%><p>No hay contactos registrados.</p><%
    } else {
%><table><thead><tr><th>Nombre</th><th>Correo</th><th>Teléfono</th><th>Mensaje</th></tr></thead><tbody><%
        for (java.util.Map<String, String> contacto : contactos) {
%><tr><td><%= escapeHtml(contacto.get("nombre")) %></td>
<td><%= escapeHtml(contacto.get("email")) %></td>
<td><%= escapeHtml(contacto.get("telefono")) %></td>
<td><%= escapeHtml(contacto.get("mensaje")) %></td></tr><%
        }
%></tbody></table><%
    }
%>
</main></body></html>
