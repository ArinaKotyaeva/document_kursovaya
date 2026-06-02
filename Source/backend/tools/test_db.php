<?php
try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=document_kursovaya', 'postgres', 'postgres', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "OK\n";
} catch (Throwable $e) {
    echo get_class($e) . ': ' . $e->getMessage() . "\n";
    if ($e->getPrevious()) {
        echo 'Previous: ' . $e->getPrevious()->getMessage() . "\n";
    }
}
