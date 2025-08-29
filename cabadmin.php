<?php
require 'db_connect.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Debug: Check received data
    // var_dump($_POST); var_dump($_FILES); exit;

    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $image = isset($_FILES['image']) ? $_FILES['image'] : [];

    if ($name && $description && !empty($image) && $image['size'] > 0) {
        $allowed = ['jpg', 'jpeg'];
        $ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && $image['size'] <= 2 * 1024 * 1024) {
            $filename = uniqid() . '.' . $ext;
            $upload_path = 'uploads/' . $filename;
            if (move_uploaded_file($image['tmp_name'], $upload_path)) {
                $stmt = $pdo->prepare("INSERT INTO cabs (name, description, image, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$name, $description, $upload_path]);
            }
        }
    }

    // Handle edit
    if (isset($_POST['edit_id'])) {
        $id = $_POST['edit_id'];
        $name = isset($_POST['edit_name']) ? trim($_POST['edit_name']) : '';
        $description = isset($_POST['edit_description']) ? trim($_POST['edit_description']) : '';
        $image = isset($_FILES['edit_image']) ? $_FILES['edit_image'] : [];

        $update_fields = ['name' => $name, 'description' => $description];
        if (!empty($image) && $image['size'] > 0) {
            $allowed = ['jpg', 'jpeg'];
            $ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed) && $image['size'] <= 2 * 1024 * 1024) {
                $filename = uniqid() . '.' . $ext;
                $upload_path = 'uploads/' . $filename;
                if (move_uploaded_file($image['tmp_name'], $upload_path)) {
                    $stmt = $pdo->prepare("SELECT image FROM cabs WHERE id = ?");
                    $stmt->execute([$id]);
                    $item = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($item && file_exists($item['image'])) {
                        unlink($item['image']);
                    }
                    $update_fields['image'] = $upload_path;
                }
            }
        }

        $set_clause = implode(', ', array_map(fn($key) => "$key = ?", array_keys($update_fields)));
        $stmt = $pdo->prepare("UPDATE cabs SET $set_clause WHERE id = ?");
        $stmt->execute([...array_values($update_fields), $id]);
    }
}

// Handle delete
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $pdo->prepare("SELECT image FROM cabs WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($item && file_exists($item['image'])) {
        unlink($item['image']);
    }
    $stmt = $pdo->prepare("DELETE FROM cabs WHERE id = ?");
    $stmt->execute([$id]);
}

// Fetch all cab services
$stmt = $pdo->query("SELECT * FROM cabs ORDER BY created_at DESC");
$cabs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>TravelGuide Cab Admin Dashboard</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background: #f4f7fa;
      color: #333;
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

   

    .admin-section {
      padding: 4rem 2rem;
      background: #fff;
    }

    .admin-section h2 {
      text-align: center;
      font-size: 2.5rem;
      margin-bottom: 1rem;
      color: #0077b6;
    }

    .admin-section p {
      text-align: center;
      max-width: 800px;
      margin: 0 auto 2rem;
      font-size: 1.1rem;
      color: #546E7A;
      line-height: 1.6;
    }

    .admin-form {
      max-width: 600px;
      margin: 0 auto 2rem;
      background: #e5f4fd;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .admin-form input[type="text"],
    .admin-form textarea,
    .admin-form input[type="file"] {
      width: 100%;
      padding: 0.8rem;
      margin: 0.5rem 0;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 1rem;
    }

    .admin-form textarea {
      height: 100px;
      resize: vertical;
    }

    .admin-form button {
      background-color: #8BC34A;
      border: none;
      color: white;
      padding: 0.8rem 2rem;
      font-size: 1rem;
      font-weight: 600;
      border-radius: 5px;
      cursor: pointer;
      transition: background 0.3s, transform 0.2s;
      width: 100%;
    }

    .admin-form button:hover {
      background-color: #689f38;
      transform: translateY(-2px);
    }

    .cab-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 2rem;
      max-width: 1200px;
      margin: 0 auto;
    }

    .cab-card {
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s;
    }

    .cab-card:hover {
      transform: translateY(-5px);
    }

    .cab-card img {
      width: 100%;
      height: 200px;
      object-fit: cover;
    }

    .cab-card h3 {
      font-size: 1.5rem;
      padding: 1rem;
      color: #0077b6;
    }

    .cab-card p {
      padding: 0 1rem 1rem;
      font-size: 0.9rem;
      color: #546E7A;
    }

    .item-actions {
      padding: 0 1rem 1rem;
      display: flex;
      gap: 1rem;
    }

    .item-actions button {
      background-color: #f97316;
      border: none;
      color: white;
      padding: 0.6rem 1.5rem;
      font-size: 0.9rem;
      font-weight: 600;
      border-radius: 5px;
      cursor: pointer;
      transition: background 0.3s;
    }

    .item-actions button:hover {
      background-color: #e55e00;
    }

    .item-actions button.delete {
      background-color: #ef4444;
    }

    .item-actions button.delete:hover {
      background-color: #dc2626;
    }

    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      z-index: 1000;
      align-items: center;
      justify-content: center;
    }

    .modal-content {
      background: #fff;
      padding: 2rem;
      border-radius: 10px;
      max-width: 500px;
      width: 90%;
      position: relative;
      animation: slideIn 0.3s ease;
    }

    .modal-content h2 {
      margin-bottom: 1rem;
      color: #0077b6;
    }

    .close {
      position: absolute;
      top: 15px;
      right: 20px;
      font-size: 1.8em;
      cursor: pointer;
      color: #333;
    }

    .close:hover {
      color: #f97316;
    }

    .modal-content input,
    .modal-content textarea,
    .modal-content button {
      width: 100%;
      padding: 0.8rem;
      margin: 0.5rem 0;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 1rem;
    }

    .modal-content textarea {
      height: 100px;
      resize: vertical;
    }

    .modal-content button {
      background-color: #8BC34A;
      border: none;
      color: white;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.3s;
    }

    .modal-content button:hover {
      background-color: #689f38;
    }

    @keyframes slideIn {
      from { transform: translateY(-50px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }

    

    @media (max-width: 768px) {
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

      .dropdown-content,
      .login-dropdown {
        position: static;
        width: 100%;
        box-shadow: none;
        transform: none;
        opacity: 1;
      }

      .hero-content h1 {
        font-size: 2rem;
      }

      .hero-content p {
        font-size: 1rem;
      }

      .cab-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <nav>
    <div class="logo">Travel<span>Guide</span></div>
    <div class="nav-links">
      <a href="homeadmin.php">Home</a>
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

  

  <section class="admin-section">
    <h2>Manage Cab Services</h2>
    <p>Control the list of cab services displayed on the public cab services page.</p>
    <div class="admin-form">
      <form action="cabadmin.php" method="POST" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Cab Service Name" required>
        <textarea name="description" placeholder="Description" required></textarea>
        <input type="file" name="image" accept=".jpg,.jpeg" required>
        <button type="submit">Add Cab Service</button>
      </form>
    </div>
    <div class="cab-grid">
      <?php foreach ($cabs as $cab): ?>
        <div class="cab-card">
          <img src="<?php echo htmlspecialchars($cab['image'] ?? ''); ?>" alt="<?php echo htmlspecialchars($cab['name'] ?? ''); ?>">
          <h3><?php echo htmlspecialchars($cab['name'] ?? ''); ?></h3>
          <p><?php echo htmlspecialchars(substr($cab['description'] ?? '', 0, 50)) . (strlen($cab['description'] ?? '') > 50 ? '...' : ''); ?></p>
          <div class="item-actions">
            <button onclick="openEditModal(<?php echo $cab['id']; ?>, '<?php echo htmlspecialchars($cab['name'] ?? ''); ?>', '<?php echo htmlspecialchars($cab['description'] ?? ''); ?>')">Edit</button>
            <button class="delete" onclick="if(confirm('Delete this cab service?')) window.location.href='cabadmin.php?delete_id=<?php echo $cab['id']; ?>'">Delete</button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <div id="editModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeEditModal()">×</span>
      <h2>Edit Cab Service</h2>
      <form id="editForm" action="cabadmin.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="edit_id" id="edit_id">
        <input type="text" name="edit_name" id="edit_name" placeholder="Cab Service Name" required>
        <textarea name="edit_description" id="edit_description" placeholder="Description" required></textarea>
        <input type="file" name="edit_image" accept=".jpg,.jpeg">
        <button type="submit">Save Changes</button>
      </form>
    </div>
  </div>

 

  <script>
    function openEditModal(id, name, description) {
      document.getElementById('edit_id').value = id;
      document.getElementById('edit_name').value = name;
      document.getElementById('edit_description').value = description;
      document.getElementById('editModal').style.display = 'flex';
    }

    function closeEditModal() {
      document.getElementById('editModal').style.display = 'none';
    }
  </script>
</body>
</html>