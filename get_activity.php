<?php
header('Content-Type: application/json');
require_once 'config.php';

error_log("get_activity.php request: key=" . ($_GET['key'] ?? 'not provided') . " at " . date('Y-m-d H:i:s T'));

if (!isset($_GET['key'])) {
    error_log("Error: No key provided");
    echo json_encode(['error' => 'Activity key is required']);
    exit;
}

$key = filter_var($_GET['key'], FILTER_SANITIZE_STRING);

try {
    $stmt = $pdo->prepare("SELECT * FROM activities WHERE `key` = ?");
    $stmt->execute([$key]);
    $activity = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$activity) {
        error_log("Error: Activity not found for key: $key");
        echo json_encode(['error' => 'Activity not found']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM related_places WHERE activity_id = ?");
    $stmt->execute([$activity['id']]);
    $related = $stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Fetched related places for activity ID {$activity['id']}: " . print_r($related, true));

    $response = [
        'title' => $activity['title'],
        'image' => $activity['image'],
        'description' => $activity['description'],
        'related' => $related
    ];
    error_log("Success: Returning data for key $key");
    echo json_encode($response);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    echo json_encode(['error' => 'Unexpected error: ' . $e->getMessage()]);
}
?>