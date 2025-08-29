<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start output buffering
ob_start();

require_once 'config.php';
require_once 'functions.php';

$message = '';

// Log incoming requests for debugging
error_log("Request URI: " . $_SERVER['REQUEST_URI']);
error_log("Query String: " . $_SERVER['QUERY_STRING']);
error_log("GET Parameters: " . print_r($_GET, true));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Starting POST request processing");

    $key = isset($_POST['key']) ? sanitize($_POST['key']) : (isset($_POST['original_key']) ? sanitize($_POST['original_key']) : '');
    $title = isset($_POST['title']) ? sanitize($_POST['title']) : '';
    $map = isset($_POST['map']) ? sanitize($_POST['map']) : '';
    $description = isset($_POST['description']) ? sanitize($_POST['description']) : '';
    $editing_id = isset($_POST['editing_id']) ? (int)$_POST['editing_id'] : null;

    error_log("POST Data: " . print_r($_POST, true));
    error_log("Files: " . print_r($_FILES, true));
    error_log("Editing ID: " . $editing_id);

    // Validate inputs
    if ($editing_id) {
        // For editing, key is optional (use original_key), but title, map, and description are required
        if (empty($title) || empty($map) || empty($description)) {
            $message = 'Please fill in all required fields (Title, Map, Description).';
            error_log("Validation failed: Missing required fields for editing");
        }
    } else {
        // For new destinations, all fields including key are required
        if (empty($key) || empty($title) || empty($map) || empty($description)) {
            $message = 'Please fill in all required fields (Key, Title, Map, Description).';
            error_log("Validation failed: Missing required fields for new destination");
        }
    }

    if (!$message) {
        // Handle destination image
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            error_log("Uploading new destination image");
            $upload = uploadFile($_FILES['image'], $editing_id ?? uniqid(), 'destination');
            if (isset($upload['error'])) {
                $message = 'Destination image upload failed: ' . $upload['error'];
                error_log("Destination image upload failed: " . $upload['error']);
            } else {
                $image_path = $upload['path'];
                error_log("Destination image uploaded: " . $image_path);
            }
        } elseif ($editing_id) {
            error_log("Reusing existing destination image for ID: " . $editing_id);
            $stmt = $pdo->prepare("SELECT image FROM destinations WHERE id = ?");
            $stmt->execute([$editing_id]);
            $image_path = $stmt->fetchColumn();
            if (!$image_path) {
                $message = 'Existing image not found for this destination in the database.';
                error_log("Existing destination image not found in database");
            } elseif (!file_exists($image_path)) {
                $message = 'The image file for this destination no longer exists on the server.';
                error_log("Destination image file does not exist: " . $image_path);
            }
        } else {
            $message = 'Please upload a JPG image for the destination.';
            error_log("No destination image provided for new destination");
        }

        if (!$message) {
            try {
                // Save or update destination
                if ($editing_id) {
                    error_log("Updating destination with ID: " . $editing_id);
                    $stmt = $pdo->prepare("UPDATE destinations SET `key` = ?, title = ?, image = ?, map = ?, description = ? WHERE id = ?");
                    $stmt->execute([$key, $title, $image_path, $map, $description, $editing_id]);
                    error_log("Destination updated successfully");

                    error_log("Deleting existing nearby places for destination ID: " . $editing_id);
                    $pdo->prepare("DELETE FROM nearby_places WHERE destination_id = ?")->execute([$editing_id]);
                    error_log("Existing nearby places deleted");
                } else {
                    error_log("Inserting new destination");
                    $stmt = $pdo->prepare("INSERT INTO destinations (`key`, title, image, map, description) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$key, $title, $image_path, $map, $description]);
                    $editing_id = $pdo->lastInsertId();
                    error_log("New destination inserted with ID: " . $editing_id);
                }

                // Handle nearby places
                if (isset($_POST['nearby-title']) && !empty($_POST['nearby-title'])) {
                    error_log("Processing nearby places");
                    foreach ($_POST['nearby-title'] as $index => $nearby_title) {
                        $nearby_title = sanitize($nearby_title);
                        $nearby_description = sanitize($_POST['nearby-description'][$index] ?? '');
                        $nearby_map = sanitize($_POST['nearby-map'][$index] ?? ''); // New map link field
                        $nearby_image_path = '';

                        error_log("Nearby Place [$index]: Title=$nearby_title, Description=$nearby_description, Map=$nearby_map");

                        // Check if a new image is uploaded
                        if (isset($_FILES['nearby-image']['name'][$index]) && $_FILES['nearby-image']['error'][$index] === UPLOAD_ERR_OK) {
                            error_log("Uploading image for nearby place #$index");
                            $file = [
                                'name' => $_FILES['nearby-image']['name'][$index],
                                'type' => $_FILES['nearby-image']['type'][$index],
                                'tmp_name' => $_FILES['nearby-image']['tmp_name'][$index],
                                'error' => $_FILES['nearby-image']['error'][$index],
                                'size' => $_FILES['nearby-image']['size'][$index]
                            ];
                            $upload = uploadFile($file, $editing_id . '_nearby_' . $index, 'nearby');
                            if (isset($upload['error'])) {
                                $message = "Nearby place image upload failed for place #$index: " . $upload['error'];
                                error_log("Nearby place image upload failed: " . $upload['error']);
                                break;
                            } else {
                                $nearby_image_path = $upload['path'];
                                error_log("Nearby place image uploaded: " . $nearby_image_path);
                            }
                        } elseif (isset($_POST['existing-nearby-image'][$index])) {
                            $nearby_image_path = sanitize($_POST['existing-nearby-image'][$index]);
                            error_log("Using existing image for nearby place #$index: " . $nearby_image_path);
                        } else {
                            $message = "Please upload a JPG image for nearby place #$index.";
                            error_log("Validation failed: Missing image for nearby place #$index");
                            break;
                        }

                        // Validate nearby place fields
                        if (empty($nearby_title)) {
                            $message = "Please provide a title for nearby place #$index.";
                            error_log("Validation failed: Missing title for nearby place #$index");
                            break;
                        }
                        if (empty($nearby_description)) {
                            $message = "Please provide a description for nearby place #$index.";
                            error_log("Validation failed: Missing description for nearby place #$index");
                            break;
                        }

                        // Insert nearby place with map link
                        error_log("Inserting nearby place #$index");
                        $stmt = $pdo->prepare("INSERT INTO nearby_places (destination_id, title, image, description, map) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$editing_id, $nearby_title, $nearby_image_path, $nearby_description, $nearby_map]);
                        error_log("Nearby place #$index inserted successfully");
                    }
                } else {
                    error_log("No nearby places provided");
                }

                if (!$message) {
                    $message = 'Destination saved successfully!';
                    error_log("Destination saved successfully");
                    ob_end_clean();
                    header("Location: placesadmin.php");
                    exit;
                }
            } catch (PDOException $e) {
                $message = 'Database error: ' . $e->getMessage();
                error_log("Database error: " . $e->getMessage());
            }
        }
    }
}

// Handle deletion
if (isset($_GET['delete'])) {
    error_log("Processing DELETE for ID: " . $_GET['delete']);
    try {
        $id = (int)$_GET['delete'];
        $stmt = $pdo->prepare("SELECT image FROM destinations WHERE id = ?");
        $stmt->execute([$id]);
        $destination = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$destination) {
            $message = 'Destination not found.';
            error_log("Destination not found for deletion");
        } else {
            $image = $destination['image'];
            if ($image && file_exists($image)) {
                unlink($image);
                error_log("Deleted destination image: " . $image);
            }
            $stmt = $pdo->prepare("SELECT image FROM nearby_places WHERE destination_id = ?");
            $stmt->execute([$id]);
            foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $nearby_image) {
                if ($nearby_image && file_exists($nearby_image)) {
                    unlink($nearby_image);
                    error_log("Deleted nearby image: " . $nearby_image);
                }
            }
            $pdo->prepare("DELETE FROM destinations WHERE id = ?")->execute([$id]);
            $message = 'Destination deleted successfully!';
            error_log("Destination deleted successfully");
            ob_end_clean();
            header("Location: placesadmin.php");
            exit;
        }
    } catch (PDOException $e) {
        $message = 'Deletion failed: ' . $e->getMessage();
        error_log("Deletion failed: " . $e->getMessage());
    }
}

// Fetch all destinations
try {
    $stmt = $pdo->query("SELECT * FROM destinations");
    $destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = 'Failed to fetch destinations: ' . $e->getMessage();
    error_log("Failed to fetch destinations: " . $e->getMessage());
    $destinations = [];
}

// Fetch editing data
$editing = null;
if (isset($_GET['edit'])) {
    error_log("Processing EDIT for ID: " . $_GET['edit']);
    try {
        $id = (int)$_GET['edit'];
        $stmt = $pdo->prepare("SELECT * FROM destinations WHERE id = ?");
        $stmt->execute([$id]);
        $editing = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($editing) {
            $stmt = $pdo->prepare("SELECT * FROM nearby_places WHERE destination_id = ?");
            $stmt->execute([$id]);
            $editing['nearby'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Fetched editing data for ID: " . $id);
        } else {
            $message = 'Destination not found for editing.';
            error_log("Destination not found for editing: ID " . $id);
        }
    } catch (PDOException $e) {
        $message = 'Failed to fetch destination for editing: ' . $e->getMessage();
        error_log("Failed to fetch destination for editing: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard - TravelGuide</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: #e5f4fd;
      color: #1a1a1a;
    }

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

    .container {
      max-width: 1200px;
      margin: 2rem auto;
      padding: 0 2rem;
    }

    h1 {
      text-align: center;
      font-size: 2.5rem;
      color: #0077b6;
      margin-bottom: 2rem;
    }

    .message {
      text-align: center;
      padding: 1rem;
      margin-bottom: 1rem;
      border-radius: 5px;
      color: white;
    }

    .message.success {
      background: #8BC34A;
    }

    .message.error {
      background: #d32f2f;
    }

    .admin-form {
      background: #fff;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      margin-bottom: 2rem;
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-group label {
      display: block;
      font-size: 1rem;
      font-weight: 600;
      color: #0077b6;
      margin-bottom: 0.5rem;
    }

    .form-group input,
    .form-group textarea {
      width: 100%;
      padding: 0.8rem;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 0.9rem;
    }

    .form-group input[type="file"] {
      padding: 0.4rem;
    }

    .form-group textarea {
      resize: vertical;
      min-height: 100px;
    }

    .nearby-places-form {
      margin-top: 1rem;
      padding: 1rem;
      background: #e5f4fd;
      border-radius: 5px;
    }

    .nearby-place-item {
      display: flex;
      gap: 1rem;
      margin-bottom: 1rem;
      align-items: flex-end;
    }

    .nearby-place-item input {
      flex: 1;
    }

    .nearby-place-item button {
      background: #f97316;
      color: white;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 5px;
      cursor: pointer;
      transition: background 0.3s;
    }

    .nearby-place-item button:hover {
      background: #e55e00;
    }

    .form-buttons {
      display: flex;
      gap: 1rem;
    }

    .form-buttons button {
      background: #8BC34A;
      color: white;
      border: none;
      padding: 0.8rem 1.5rem;
      border-radius: 5px;
      font-size: 0.9rem;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.3s, transform 0.2s;
    }

    .form-buttons button:hover {
      background: #7cb342;
      transform: translateY(-2px);
    }

    .form-buttons .cancel-btn {
      background: #f97316;
    }

    .form-buttons .cancel-btn:hover {
      background: #e55e00;
    }

    .destinations-table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .destinations-table th,
    .destinations-table td {
      padding: 1rem;
      text-align: left;
      border-bottom: 1px solid #e5f4fd;
    }

    .destinations-table th {
      background: #0077b6;
      color: white;
      font-weight: 600;
    }

    .destinations-table td img {
      width: 100px;
      height: 60px;
      object-fit: cover;
      border-radius: 5px;
    }

    .destinations-table .actions {
      display: flex;
      gap: 0.5rem;
    }

    .destinations-table .actions a {
      background: #f97316;
      color: white;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 5px;
      text-decoration: none;
      transition: background 0.3s;
    }

    .destinations-table .actions a:hover {
      background: #e55e00;
    }

    .destinations-table .actions a.delete-btn {
      background: #d32f2f;
    }

    .destinations-table .actions a.delete-btn:hover {
      background: #b71c1c;
    }

    @media screen and (max-width: 768px) {
      nav {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
      }

      .nav-links {
        flex-direction: column;
        align-items: flex-start;
        width: 100%;
      }

      .nav-links a,
      .login-btn button {
        width: 100%;
        text-align: center;
      }

      .dropdown-content {
        position: static;
        width: 100%;
        box-shadow: none;
        transform: none;
        opacity: 1;
      }

      .admin-form,
      .destinations-table {
        padding: 1rem;
      }

      .nearby-place-item {
        flex-direction: column;
      }

      .form-buttons {
        flex-direction: column;
      }

      .destinations-table th,
      .destinations-table td {
        font-size: 0.8rem;
        padding: 0.8rem;
      }

      .destinations-table td img {
        width: 80px;
        height: 50px;
      }
    }
  </style>
</head>
<body>
  <nav>
    <div class="logo">Travel<span>Guide</span></div>
    <div class="nav-links">
      <a href="homeadmin.php" class="always-underline">Home</a>
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
      <div class="login-btn">
        <button><i class="fas fa-user"></i> Logout</button>
        <div class="login-dropdown">
          <a href="logout.php">Logout</a>
        </div>
      </div>
    </div>
  </nav>

  <div class="container">
    <h1>Admin Dashboard - Manage Destinations</h1>
    <?php if ($message): ?>
      <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>
    <div class="admin-form">
      <h2><?php echo $editing ? 'Edit Destination: ' . htmlspecialchars($editing['title']) : 'Add New Destination'; ?></h2>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="editing_id" value="<?php echo $editing ? $editing['id'] : ''; ?>">
        <input type="hidden" name="original_key" value="<?php echo $editing ? htmlspecialchars($editing['key']) : ''; ?>">
        <div class="form-group">
          <label for="key">Destination Key (e.g., kandy)</label>
          <input type="text" id="key" name="key" value="<?php echo $editing ? htmlspecialchars($editing['key']) : ''; ?>" <?php echo $editing ? 'disabled' : 'required'; ?> />
        </div>
        <div class="form-group">
          <label for="title">Title</label>
          <input type="text" id="title" name="title" value="<?php echo $editing ? htmlspecialchars($editing['title']) : ''; ?>" required />
        </div>
        <div class="form-group">
          <label for="image">Image (JPG only)</label>
          <input type="file" id="image" name="image" accept=".jpg" <?php echo $editing ? '' : 'required'; ?> />
          <?php if ($editing && $editing['image']): ?>
            <img src="<?php echo htmlspecialchars($editing['image']); ?>" alt="Current Image" style="width: 100px; height: 60px; object-fit: cover; margin-top: 0.5rem;" />
          <?php endif; ?>
        </div>
        <div class="form-group">
          <label for="map">Map Embed URL</label>
          <input type="text" id="map" name="map" value="<?php echo $editing ? htmlspecialchars($editing['map']) : ''; ?>" required />
        </div>
        <div class="form-group">
          <label for="description">Description</label>
          <textarea id="description" name="description" required><?php echo $editing ? htmlspecialchars($editing['description']) : ''; ?></textarea>
        </div>
        <div class="nearby-places-form">
          <h3>Nearby Places</h3>
          <div id="nearby-places-list">
            <?php if ($editing && $editing['nearby']): foreach ($editing['nearby'] as $index => $place): ?>
              <div class="nearby-place-item">
                <div class="form-group">
                  <label>Nearby Place Title</label>
                  <input type="text" name="nearby-title[]" value="<?php echo htmlspecialchars($place['title']); ?>" required />
                </div>
                <div class="form-group">
                  <label>Nearby Place Image (JPG only)</label>
                  <input type="file" name="nearby-image[]" accept=".jpg" />
                  <input type="hidden" name="existing-nearby-image[]" value="<?php echo htmlspecialchars($place['image']); ?>" />
                  <img src="<?php echo htmlspecialchars($place['image']); ?>" alt="Current Image" style="width: 50px; height: 30px; object-fit: cover; margin-top: 0.5rem;" />
                </div>
                <div class="form-group">
                  <label>Nearby Place Description</label>
                  <input type="text" name="nearby-description[]" value="<?php echo htmlspecialchars($place['description']); ?>" required />
                </div>
                <div class="form-group">
                  <label>Nearby Place Map Link (Optional)</label>
                  <input type="text" name="nearby-map[]" value="<?php echo htmlspecialchars($place['map'] ?? ''); ?>" />
                </div>
                <button type="button" class="remove-nearby-place">Remove</button>
              </div>
            <?php endforeach; endif; ?>
          </div>
          <button type="button" id="add-nearby-place">Add Nearby Place</button>
        </div>
        <div class="form-buttons">
          <button type="submit">Save Destination</button>
          <a href="placesadmin.php" class="cancel-btn">Cancel</a>
        </div>
      </form>
    </div>

    <table class="destinations-table">
      <thead>
        <tr>
          <th>Key</th>
          <th>Title</th>
          <th>Image</th>
          <th>Description</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($destinations)): ?>
          <tr>
            <td colspan="5" style="text-align: center;">No destinations found. Add a new destination above.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($destinations as $dest): ?>
            <tr>
              <td><?php echo htmlspecialchars($dest['key']); ?></td>
              <td><?php echo htmlspecialchars($dest['title']); ?></td>
              <td><img src="<?php echo htmlspecialchars($dest['image']); ?>" alt="<?php echo htmlspecialchars($dest['title']); ?>" onerror="this.src='img/placeholder.jpg'"></td>
              <td><?php echo htmlspecialchars(substr($dest['description'], 0, 50)) . '...'; ?></td>
              <td class="actions">
                <a href="placesadmin.php?edit=<?php echo $dest['id']; ?>">Edit</a>
                <a href="placesadmin.php?delete=<?php echo $dest['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete <?php echo htmlspecialchars($dest['title']); ?>?');">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <script>
    const nearbyPlacesList = document.getElementById('nearby-places-list');
    const addNearbyPlaceBtn = document.getElementById('add-nearby-place');

    function addNearbyPlaceInputs() {
      const nearbyPlaceItem = document.createElement('div');
      nearbyPlaceItem.classList.add('nearby-place-item');
      nearbyPlaceItem.innerHTML = `
        <div class="form-group">
          <label>Nearby Place Title</label>
          <input type="text" name="nearby-title[]" required />
        </div>
        <div class="form-group">
          <label>Nearby Place Image (JPG only)</label>
          <input type="file" name="nearby-image[]" accept=".jpg" required />
        </div>
        <div class="form-group">
          <label>Nearby Place Description</label>
          <input type="text" name="nearby-description[]" required />
        </div>
        <div class="form-group">
          <label>Nearby Place Map Link (Optional)</label>
          <input type="text" name="nearby-map[]" />
        </div>
        <button type="button" class="remove-nearby-place">Remove</button>
      `;
      nearbyPlacesList.appendChild(nearbyPlaceItem);
      nearbyPlaceItem.querySelector('.remove-nearby-place').addEventListener('click', () => {
        nearbyPlaceItem.remove();
      });
    }

    addNearbyPlaceBtn.addEventListener('click', addNearbyPlaceInputs);

    document.querySelectorAll('.remove-nearby-place').forEach(btn => {
      btn.addEventListener('click', () => {
        btn.closest('.nearby-place-item').remove();
      });
    });
  </script>
</body>
</html>
<?php
// Flush output buffer
ob_end_flush();
?>