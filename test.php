<?php
try {
    $pdo = new PDO("pgsql:host=localhost;port=5432;dbname=postgrado", "postgres", "postgres");
    echo "✅ Conexión exitosa!";
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}