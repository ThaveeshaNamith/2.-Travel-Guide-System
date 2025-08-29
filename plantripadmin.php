<?php
require 'db_connect.php';

// Initialize form variables
$forms = [
    'trips' => [
        'name' => '',
        'type' => '',
        'duration' => '',
        'region' => '',
        'budget' => '',
        'description' => '',
        'edit_id' => null,
        'errors' => []
    ]
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['trips_submit'])) {
    $forms['trips']['name'] = trim($_POST['name']);
    $forms['trips']['type'] = trim($_POST['type']);
    $forms['trips']['duration'] = trim($_POST['duration']);
    $forms['trips']['region'] = trim($_POST['region']);
    $forms['trips']['budget'] = trim($_POST['budget']);
    $forms['trips']['description'] = trim($_POST['description']);
    $forms['trips']['edit_id'] = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : null;

    // Validation
    if (empty($forms['trips']['name'])) $forms['trips']['errors'][] = "Name is required.";
    if (empty($forms['trips']['type'])) $forms['trips']['errors'][] = "Type is required.";
    if (empty($forms['trips']['duration'])) $forms['trips']['errors'][] = "Duration is required.";
    if (empty($forms['trips']['region'])) $forms['trips']['errors'][] = "Region is required.";
    if (empty($forms['trips']['budget'])) $forms['trips']['errors'][] = "Budget is required.";
    if (empty($forms['trips']['description'])) $forms['trips']['errors'][] = "Description is required.";

    // Handle image upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] != UPLOAD_ERR_NO_FILE) {
        $allowed = ['jpg', 'jpeg'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $forms['trips']['errors'][] = "Only JPG images are allowed.";
        } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $forms['trips']['errors'][] = "Image size must be less than 2MB.";
        } else {
            $image_path = 'uploads/' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
        }
    } elseif ($forms['trips']['edit_id']) {
        $stmt = $pdo->prepare("SELECT image FROM trips WHERE id = ?");
        $stmt->execute([$forms['trips']['edit_id']]);
        $image_path = $stmt->fetchColumn();
    } else {
        $forms['trips']['errors'][] = "Image is required.";
    }

    if (empty($forms['trips']['errors'])) {
        if ($forms['trips']['edit_id']) {
            $stmt = $pdo->prepare("UPDATE trips SET name = ?, type = ?, duration = ?, region = ?, budget = ?, description = ?, image = ? WHERE id = ?");
            $stmt->execute([
                $forms['trips']['name'],
                $forms['trips']['type'],
                $forms['trips']['duration'],
                $forms['trips']['region'],
                $forms['trips']['budget'],
                $forms['trips']['description'],
                $image_path,
                $forms['trips']['edit_id']
            ]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO trips (name, type, duration, region, budget, description, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $forms['trips']['name'],
                $forms['trips']['type'],
                $forms['trips']['duration'],
                $forms['trips']['region'],
                $forms['trips']['budget'],
                $forms['trips']['description'],
                $image_path
            ]);
        }
        header("Location: plantripadmin.php#trips");
        exit;
    }
}

// Handle delete
if (isset($_GET['delete_trips'])) {
    $id = (int)$_GET['delete_trips'];
    $stmt = $pdo->prepare("SELECT image FROM trips WHERE id = ?");
    $stmt->execute([$id]);
    $image = $stmt->fetchColumn();
    if ($image && file_exists($image)) unlink($image);
    $stmt = $pdo->prepare("DELETE FROM trips WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: plantripadmin.php#trips");
    exit;
}

// Handle edit
if (isset($_GET['edit_trips'])) {
    $forms['trips']['edit_id'] = (int)$_GET['edit_trips'];
    $stmt = $pdo->prepare("SELECT * FROM trips WHERE id = ?");
    $stmt->execute([$forms['trips']['edit_id']]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($record) {
        $forms['trips']['name'] = $record['name'];
        $forms['trips']['type'] = $record['type'];
        $forms['trips']['duration'] = $record['duration'];
        $forms['trips']['region'] = $record['region'];
        $forms['trips']['budget'] = $record['budget'];
        $forms['trips']['description'] = $record['description'];
    }
}

// Fetch data
$stmt = $pdo->query("SELECT * FROM trips ORDER BY created_at DESC");
$forms['trips']['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TravelGuide Plan Trip Admin Panel</title>
    <style>
        /* Reuse CSS from homeadmin.php */
        nav {
            background: rgba(31, 41, 55, 0.8);
            color: white;
            padding: 2rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .logo {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 2rem;
            color: #fff;
        }
        .logo span {
            color: #f97316;
            padding-left: 0.5rem;
        }
        .nav-links {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }
        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background 0.3s, transform 0.2s, text-decoration 0.2s;
        }
        .always-underline {
            text-decoration: underline !important;
        }
        .nav-links a:hover {
            background-color: #f97316;
            transform: translateY(-2px);
            text-decoration: underline;
        }
        .dropdown {
            position: relative;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: #fff;
            min-width: 180px;
            border-radius: 5px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            transition: opacity 0.2s ease-in-out, transform 0.2s ease-in-out;
            transform: translateY(5px);
            opacity: 0;
        }
        .dropdown-content a {
            color: #1a1a1a;
            padding: 1rem 1.5rem;
            display: block;
            font-size: 0.95rem;
            transition: background 0.2s;
        }
        .dropdown-content a:hover {
            background-color: #e5f4fd;
        }
        .dropdown:hover .dropdown-content {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }
        .login-btn {
            position: relative;
        }
        .login-btn button {
            background: #f97316;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 20px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: background 0.3s, transform 0.2s;
        }
        .login-btn button:hover {
            background: #e55e00;
            transform: translateY(-2px);
        }
        .login-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 5px);
            right: 0;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            min-width: 160px;
            z-index: 1000;
            padding: 0.5rem 0;
            transition: opacity 0.2s ease-in-out, transform 0.2s ease-in-out;
            transform: translateY(5px);
            opacity: 0;
        }
        .login-dropdown a {
            color: #1a1a1a;
            padding: 1rem 1.5rem;
            display: block;
            font-size: 0.95rem;
            transition: background 0.2s;
        }
        .login-dropdown a:hover {
            background-color: #e5f4fd;
        }
        .login-btn:hover .login-dropdown {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background: #f8f9fa;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: auto;
        }
        h1 {
            text-align: center;
            font-size: 2em;
            margin-bottom: 20px;
            color: #007bff;
        }
        .section {
            background: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h2 {
            font-size: 1.5em;
            margin-bottom: 15px;
            color: #333;
        }
        .form-section, .table-section {
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        input[type="text"], textarea, input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus, textarea:focus, input[type="file"]:focus {
            border-color: #007bff;
            outline: none;
        }
        textarea {
            height: 100px;
            resize: vertical;
        }
        .buttons {
            margin-top: 10px;
        }
        button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .save-btn {
            background: #28a745;
            color: #fff;
        }
        .save-btn:hover {
            background: #218838;
        }
        .cancel-btn {
            background: #dc3545;
            color: #fff;
        }
        .cancel-btn:hover {
            background: #c82333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #007bff;
            color: #fff;
        }
        img {
            max-width: 100px;
            height: auto;
            border-radius: 4px;
        }
        .actions a {
            color: #007bff;
            margin-right: 10px;
            text-decoration: none;
        }
        .actions a:hover {
            text-decoration: underline;
        }
        .errors {
            color: #dc3545;
            margin-bottom: 15px;
        }
        .errors ul {
            margin: 0;
            padding-left: 20px;
        }
        @media (max-width: 768px) {
            table, th, td {
                display: block;
            }
            th {
                display: none;
            }
            td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <nav>
        <div class="logo">Travel<span>Guide</span></div>
        <div class="nav-links">
            <a href="home.php" class="always-underline">Home</a>
            <div class="dropdown">
                <a href="#">Guides ▾</a>
                <div class="dropdown-content">
                    <a href="placesadmin.php">Destinations</a>
                    <a href="thingsadmin.php">Things to Do</a>
                    <a href="plantripadmin.php">Plan Your Trip</a>
                </div>
            </div>
            <a href="hoteladmin.php">Hotels</a>
            <a href="cabadmin.php">Cabs</a>
            <a href="view_messages.php">Contact Us</a>
        </div>
        <div class="login-btn">
            <button><i class="fas fa-user"></i> Logout</button>
            <div class="login-dropdown">
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1>Plan Trip Admin Panel</h1>

        <!-- Manage Trips -->
        <div class="section" id="trips">
            <h2>Manage Trips</h2>
            <div class="form-section">
                <h3><?php echo $forms['trips']['edit_id'] ? 'Edit' : 'Add'; ?> Trip</h3>
                <?php if ($forms['trips']['errors']): ?>
                    <div class="errors">
                        <ul>
                            <?php foreach ($forms['trips']['errors'] as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="edit_id" value="<?php echo $forms['trips']['edit_id']; ?>">
                    <input type="hidden" name="trips_submit" value="1">
                    <div class="form-group">
                        <label for="trips_name">Name</label>
                        <input type="text" id="trips_name" name="name" value="<?php echo htmlspecialchars($forms['trips']['name']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="trips_type">Type</label>
                        <input type="text" id="trips_type" name="type" value="<?php echo htmlspecialchars($forms['trips']['type']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="trips_duration">Duration</label>
                        <input type="text" id="trips_duration" name="duration" value="<?php echo htmlspecialchars($forms['trips']['duration']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="trips_region">Region</label>
                        <input type="text" id="trips_region" name="region" value="<?php echo htmlspecialchars($forms['trips']['region']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="trips_budget">Budget</label>
                        <input type="text" id="trips_budget" name="budget" value="<?php echo htmlspecialchars($forms['trips']['budget']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="trips_description">Description</label>
                        <textarea id="trips_description" name="description"><?php echo htmlspecialchars($forms['trips']['description']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="trips_image">Upload JPG Image</label>
                        <input type="file" id="trips_image" name="image" accept=".jpg,.jpeg">
                    </div>
                    <div class="buttons">
                        <button type="submit" class="save-btn">Save Trip</button>
                        <button type="button" class="cancel-btn" onclick="window.location='plantripadmin.php#trips'">Cancel</button>
                    </div>
                </form>
            </div>
            <div class="table-section">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Duration</th>
                            <th>Region</th>
                            <th>Budget</th>
                            <th>Description</th>
                            <th>Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($forms['trips']['data'] as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo htmlspecialchars($item['type']); ?></td>
                                <td><?php echo htmlspecialchars($item['duration']); ?></td>
                                <td><?php echo htmlspecialchars($item['region']); ?></td>
                                <td><?php echo htmlspecialchars($item['budget']); ?></td>
                                <td><?php echo htmlspecialchars(substr($item['description'], 0, 100)) . (strlen($item['description']) > 100 ? '...' : ''); ?></td>
                                <td><img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>"></td>
                                <td class="actions">
                                    <a href="?edit_trips=<?php echo $item['id']; ?>#trips">Edit</a>
                                    <a href="?delete_trips=<?php echo $item['id']; ?>#trips" onclick="return confirm('Are you sure?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>