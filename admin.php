<?php
require 'db_connect.php';

// Initialize variables
$name = $description = $category = '';
$errors = [];
$edit_id = null;

// Handle form submission (Add/Edit)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $category = $_POST['category'];
    $description = trim($_POST['description']);
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : null;

    // Validation
    if (empty($name)) $errors[] = "Name is required.";
    if (empty($description)) $errors[] = "Description is required.";
    if (!in_array($category, ['Hotel', 'Cab Service'])) $errors[] = "Invalid category.";
    
    // Handle image upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
        $allowed = ['jpg', 'jpeg'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $errors[] = "Only JPG images are allowed.";
        } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $errors[] = "Image size must be less than 2MB.";
        } else {
            $image_path = 'uploads/' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
        }
    } elseif ($edit_id) {
        // If editing and no new image, keep existing image
        $stmt = $pdo->prepare("SELECT image FROM hotels_cabs WHERE id = ?");
        $stmt->execute([$edit_id]);
        $image_path = $stmt->fetchColumn();
    } else {
        $errors[] = "Image is required.";
    }

    if (empty($errors)) {
        if ($edit_id) {
            // Update existing record
            $stmt = $pdo->prepare("UPDATE hotels_cabs SET name = ?, category = ?, description = ?, image = ? WHERE id = ?");
            $stmt->execute([$name, $category, $description, $image_path, $edit_id]);
        } else {
            // Insert new record
            $stmt = $pdo->prepare("INSERT INTO hotels_cabs (name, category, description, image) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $category, $description, $image_path]);
        }
        header("Location: admin.php");
        exit;
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT image FROM hotels_cabs WHERE id = ?");
    $stmt->execute([$id]);
    $image = $stmt->fetchColumn();
    if ($image && file_exists($image)) unlink($image);
    $stmt = $pdo->prepare("DELETE FROM hotels_cabs WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin.php");
    exit;
}

// Handle edit (populate form)
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM hotels_cabs WHERE id = ?");
    $stmt->execute([$edit_id]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($record) {
        $name = $record['name'];
        $category = $record['category'];
        $description = $record['description'];
    }
}

// Fetch all records
$stmt = $pdo->query("SELECT * FROM hotels_cabs ORDER BY created_at DESC");
$hotels_cabs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TravelGuide Admin Panel</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f4f4f4; }
        .container { max-width: 1200px; margin: auto; }
        h1 { text-align: center; color: #333; }
        .form-section, .table-section { background: #fff; padding: 20px; margin-bottom: 20px; border-radius: 8px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], textarea, select, input[type="file"] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        textarea { height: 100px; resize: vertical; }
        .buttons { margin-top: 10px; }
        button { padding: 10px 20px; margin-right: 10px; border: none; border-radius: 4px; cursor: pointer; }
        .save-btn { background: #28a745; color: #fff; }
        .cancel-btn { background: #dc3545; color: #fff; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #007bff; color: #fff; }
        img { max-width: 100px; height: auto; }
        .actions a { margin-right: 10px; color: #007bff; text-decoration: none; }
        .errors { color: #dc3545; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>TravelGuide Admin Panel - Manage Hotels & Cabs</h1>

        <!-- Add/Edit Form -->
        <div class="form-section">
            <h2><?php echo $edit_id ? 'Edit' : 'Add'; ?> Hotel/Cab</h2>
            <?php if ($errors): ?>
                <div class="errors">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="edit_id" value="<?php echo $edit_id; ?>">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>">
                </div>
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="Hotel" <?php echo $category == 'Hotel' ? 'selected' : ''; ?>>Hotel</option>
                        <option value="Cab Service" <?php echo $category == 'Cab Service' ? 'selected' : ''; ?>>Cab Service</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"><?php echo htmlspecialchars($description); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="image">Upload JPG Image</label>
                    <input type="file" id="image" name="image" accept=".jpg,.jpeg">
                </div>
                <div class="buttons">
                    <button type="submit" class="save-btn">Save</button>
                    <button type="button" class="cancel-btn" onclick="window.location='admin.php'">Cancel</button>
                </div>
            </form>
        </div>

        <!-- Hotels & Cabs List -->
        <div class="table-section">
            <h2>Hotels & Cabs List</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($hotels_cabs as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo htmlspecialchars($item['category']); ?></td>
                            <td><?php echo htmlspecialchars(substr($item['description'], 0, 100)) . (strlen($item['description']) > 100 ? '...' : ''); ?></td>
                            <td><img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>"></td>
                            <td class="actions">
                                <a href="?edit=<?php echo $item['id']; ?>">Edit</a>
                                <a href="?delete=<?php echo $item['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>