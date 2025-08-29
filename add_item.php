```php
<?php
header('Content-Type: application/json');
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $category = $_POST['category'] ?? '';
    $description = $_POST['description'] ?? '';
    $link = $_POST['link'] ?? '';

    // Handle image upload
    $imagePath = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = $_FILES['image'];
        $allowedTypes = ['image/jpeg', 'image/jpg'];
        if (in_array($image['type'], $allowedTypes)) {
            $uploadDir = 'img/';
            $imageName = uniqid() . '_' . basename($image['name']);
            $imagePath = $uploadDir . $imageName;
            if (!move_uploaded_file($image['tmp_name'], $imagePath)) {
                echo json_encode(['error' => 'Failed to upload image']);
                exit;
            }
        } else {
            echo json_encode(['error' => 'Only JPG/JPEG images are allowed']);
            exit;
        }
    } else {
        echo json_encode(['error' => 'Image upload failed']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO items (name, category, description, image, link) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $category, $description, $imagePath, $link]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Failed to add item: ' . $e->getMessage()]);
    }
}
?>
```