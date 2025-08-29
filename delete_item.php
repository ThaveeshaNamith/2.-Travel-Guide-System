```php
<?php
header('Content-Type: application/json');
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';

    try {
        // Get the image path to delete the file
        $stmt = $pdo->prepare("SELECT image FROM items WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($item && !empty($item['image']) && file_exists($item['image'])) {
            unlink($item['image']);
        }

        // Delete the item
        $stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Failed to delete item: ' . $e->getMessage()]);
    }
}
?>
```