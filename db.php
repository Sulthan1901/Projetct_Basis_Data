<?php
$host = 'aws-0-ap-southeast-1.pooler.supabase.com';
$port = '6543'; // Default PostgreSQL port
$dbname = 'postgres';
$user = 'postgres.egtqolpljwoyjkuqgawi';
$password = 'shinichi4869';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>