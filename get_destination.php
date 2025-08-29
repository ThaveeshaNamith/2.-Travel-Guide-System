<?php
header('Content-Type: application/json');
require_once 'config.php';
require_once 'functions.php';

if (!isset($_GET['key'])) {
    echo json_encode(['error' => 'No destination key provided']);
    exit;
}

$key = sanitize($_GET['key']);

try {
    $stmt = $pdo->prepare("SELECT * FROM destinations WHERE `key` = ?");
    $stmt->execute([$key]);
    $destination = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$destination) {
        echo json_encode(['error' => 'Destination not found']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT title, image, description, map FROM nearby_places WHERE destination_id = ?");
    $stmt->execute([$destination['id']]);
    $nearby_places = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'title' => $destination['title'],
        'image' => $destination['image'],
        'description' => $destination['description'],
        'map' => $destination['map'],
        'nearby' => $nearby_places
    ];

    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>