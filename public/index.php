<?php
include("../internal/database.php");

$query = $_REQUEST["query"];
if (empty($query)) {
    die("Empty Query");
}

if (strpos($query, "[") === 0 && strpos($query, "]") === strlen($query) - 1) {// JSON Array
    $ips = json_decode($query, true);
} else {// Single IP
    $ips = array($query);
}

if (empty($ips)) {
    die("IP not specified / Invalid JSON");
}
$associative = isset($_REQUEST["associative"]);

$db = db();
$countries = array();
foreach ($ips as $ip) {
    $query = $db->prepare("SELECT country FROM ip2nation WHERE ip < INET_ATON(:ip) ORDER BY ip DESC LIMIT 1");
    $query->bindValue(":ip", $ip);
    $query->execute();
    $result = $query->fetch();
    if ($associative) {
        $countries[$ip] = $result["country"];
    } else {
        $countries[] = $result["country"];
    }
}

header("Access-Control-Allow-Origin: *");
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Request-Headers: X-Requested-With, Accept, Content-Type, Origin");
    exit;
}
header("Content-Type: application/json");
echo json_encode($countries);