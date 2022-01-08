<?php
require_once "pdo.php";

session_start();

header('Content-Type: application/json; charset=utf-8');

$term = $_GET['term'];
error_log("Looking up typehead term=".$term);
$stmt = $pdo->prepare('SELECT name FROM Institution 
     WHERE name LIKE :prefix');
$stmt->execute(array(':prefix' => $term."%"));

$retval = array();
while( $row = $stmt->fetch(PDO::FETCH_ASSOC)){
    $retval[] = $row['name'];
}

echo json_encode($retval, JSON_PRETTY_PRINT);

?>