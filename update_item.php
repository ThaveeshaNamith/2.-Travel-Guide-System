```php
<?php
header('Content-Type: application/json');
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'] ?? '';
    $category = $_POST['category'] ?? '';
    $description = $_POST['description'] ?? '';
    $link = $_POST['link'] ?? '';

    // Handle image upload (optional)
    $imagePath = $_POST['existing_image'] ?? '';
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
            // Delete old image if it exists
            if (!empty($_POST['existing_image']) && file_exists($_POST['existing_image'])) {
                unlink($_POST['existing_image']);
            }
        } else {
            echo json_encode(['error' => 'Only JPG/JPEG images are allowed']);
            exit;
        }
    }

    try {
        $stmt = $pdo->prepare("UPDATE items SET name = ?, category = ?, description = ?, image = ?, link = ? WHERE id = ?");
        $stmt->execute([$name, $category, $description, $imagePath, $link, $id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Failed to update item: ' . $e->getMessage()]);
    }
}
?>
```