<?php
$user = "postgres.pnmkwubyuzqqjtvkulvj";
$pass = "Sipuhjarak1";

$dsn1 = "pgsql:host=aws-0-ap-southeast-1.pooler.supabase.com;port=6543;dbname=postgres";
try {
    echo "1. Connecting to pooler on port 6543...\n";
    $pdo = new PDO($dsn1, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "   SUCCESS!\n\n";
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n\n";
}

$dsn2 = "pgsql:host=aws-0-ap-southeast-1.pooler.supabase.com;port=5432;dbname=postgres";
try {
    echo "2. Connecting to pooler on port 5432...\n";
    $pdo = new PDO($dsn2, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "   SUCCESS!\n\n";
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n\n";
}
