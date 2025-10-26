<?php
// generar_password.php
// Copia este archivo en C:\xampp\htdocs\electromisiones_xampp\
// Accede a: http://localhost/electromisiones_xampp/generar_password.php
// ELIMINA ESTE ARCHIVO DESPUÉS DE USARLO

echo "<h1>Generador de Hashes de Contraseñas</h1>";
echo "<p>Estos son los hashes correctos para las contraseñas del sistema:</p>";

echo "<hr>";

// Admin - admin123
$password_admin = 'admin123';
$hash_admin = password_hash($password_admin, PASSWORD_DEFAULT);
echo "<h3>Usuario: admin</h3>";
echo "<p>Contraseña: <strong>$password_admin</strong></p>";
echo "<p>Hash: <code>$hash_admin</code></p>";
echo "<p>SQL: <code>UPDATE usuarios SET password_hash = '$hash_admin' WHERE username = 'admin';</code></p>";

echo "<hr>";

// Vendedor - vendedor123
$password_vendedor = 'vendedor123';
$hash_vendedor = password_hash($password_vendedor, PASSWORD_DEFAULT);
echo "<h3>Usuario: vendedor</h3>";
echo "<p>Contraseña: <strong>$password_vendedor</strong></p>";
echo "<p>Hash: <code>$hash_vendedor</code></p>";
echo "<p>SQL: <code>UPDATE usuarios SET password_hash = '$hash_vendedor' WHERE username = 'vendedor';</code></p>";

echo "<hr>";

// Visor - visor123
$password_visor = 'visor123';
$hash_visor = password_hash($password_visor, PASSWORD_DEFAULT);
echo "<h3>Usuario: visor</h3>";
echo "<p>Contraseña: <strong>$password_visor</strong></p>";
echo "<p>Hash: <code>$hash_visor</code></p>";
echo "<p>SQL: <code>UPDATE usuarios SET password_hash = '$hash_visor' WHERE username = 'visor';</code></p>";

echo "<hr>";
echo "<h2>SQL Completo para Copiar:</h2>";
echo "<textarea style='width:100%; height:200px; font-family:monospace;'>";
echo "USE electromisiones_db;\n\n";
echo "UPDATE usuarios SET password_hash = '$hash_admin' WHERE username = 'admin';\n";
echo "UPDATE usuarios SET password_hash = '$hash_vendedor' WHERE username = 'vendedor';\n";
echo "UPDATE usuarios SET password_hash = '$hash_visor' WHERE username = 'visor';\n";
echo "</textarea>";

echo "<hr>";
echo "<h3>Verificación:</h3>";

// Verificar que funcionan
if (password_verify($password_admin, $hash_admin)) {
    echo "<p style='color:green;'>✅ Hash de admin es CORRECTO</p>";
} else {
    echo "<p style='color:red;'>❌ Hash de admin tiene problemas</p>";
}

if (password_verify($password_vendedor, $hash_vendedor)) {
    echo "<p style='color:green;'>✅ Hash de vendedor es CORRECTO</p>";
} else {
    echo "<p style='color:red;'>❌ Hash de vendedor tiene problemas</p>";
}

if (password_verify($password_visor, $hash_visor)) {
    echo "<p style='color:green;'>✅ Hash de visor es CORRECTO</p>";
} else {
    echo "<p style='color:red;'>❌ Hash de visor tiene problemas</p>";
}

echo "<hr>";
echo "<p><strong>⚠️ IMPORTANTE: Elimina este archivo después de usarlo por seguridad</strong></p>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 1000px;
    margin: 50px auto;
    padding: 20px;
    background: #f5f5f5;
}
h1, h2, h3 {
    color: #333;
}
code {
    background: #e9ecef;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 12px;
    display: block;
    margin: 10px 0;
    word-wrap: break-word;
}
hr {
    margin: 30px 0;
    border: none;
    border-top: 2px solid #ddd;
}
textarea {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
}
</style>