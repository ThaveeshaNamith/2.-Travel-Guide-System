<script type="text/javascript">
        var gk_isXlsx = false;
        var gk_xlsxFileLookup = {};
        var gk_fileData = {};
        function filledCell(cell) {
          return cell !== '' && cell != null;
        }
        function loadFileData(filename) {
        if (gk_isXlsx && gk_xlsxFileLookup[filename]) {
            try {
                var workbook = XLSX.read(gk_fileData[filename], { type: 'base64' });
                var firstSheetName = workbook.SheetNames[0];
                var worksheet = workbook.Sheets[firstSheetName];

                // Convert sheet to JSON to filter blank rows
                var jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1, blankrows: false, defval: '' });
                // Filter out blank rows (rows where all cells are empty, null, or undefined)
                var filteredData = jsonData.filter(row => row.some(filledCell));

                // Heuristic to find the header row by ignoring rows with fewer filled cells than the next row
                var headerRowIndex = filteredData.findIndex((row, index) =>
                  row.filter(filledCell).length >= filteredData[index + 1]?.filter(filledCell).length
                );
                // Fallback
                if (headerRowIndex === -1 || headerRowIndex > 25) {
                  headerRowIndex = 0;
                }

                // Convert filtered JSON back to CSV
                var csv = XLSX.utils.aoa_to_sheet(filteredData.slice(headerRowIndex)); // Create a new sheet from filtered array of arrays
                csv = XLSX.utils.sheet_to_csv(csv, { header: 1 });
                return csv;
            } catch (e) {
                console.error(e);
                return "";
            }
        }
        return gk_fileData[filename] || "";
        }
        </script><?php
require 'db_connect.php';

// Fetch messages from the database
try {
    $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching messages: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>View Contact Messages - TravelGuide</title>
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

    /* Navbar */
    nav {
      background: rgba(31, 41, 55, 0.8);
      color: white;
      padding: 1rem 2rem;
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

    .logout-btn {
      position: relative;
    }

    .logout-btn button {
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

    .logout-btn button:hover {
      background: #e55e00;
      transform: translateY(-2px);
    }

    .logout-dropdown {
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

    .logout-dropdown a {
      color: #1a1a1a;
      padding: 1rem 1.5rem;
      display: block;
      font-size: 0.95rem;
      transition: background 0.2s;
    }

    .logout-dropdown a:hover {
      background-color: #e5f4fd;
    }

    .logout-btn:hover .logout-dropdown {
      display: block;
      opacity: 1;
      transform: translateY(0);
    }

    /* Messages Section */
    .messages-section {
      padding: 4rem 2rem;
      background: #fff;
      max-width: 1200px;
      margin: 0 auto;
    }

    .messages-section h2 {
      font-size: 2.5rem;
      color: #0077b6;
      text-align: center;
      margin-bottom: 1rem;
    }

    .messages-section p {
      font-size: 1.1rem;
      color: #546E7A;
      text-align: center;
      margin-bottom: 2rem;
    }

    .messages-table {
      width: 100%;
      border-collapse: collapse;
      background: #e5f4fd;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }

    .messages-table th,
    .messages-table td {
      padding: 1rem;
      text-align: left;
      border-bottom: 1px solid #ccc;
    }

    .messages-table th {
      background: #0077b6;
      color: white;
      font-weight: 600;
    }

    .messages-table td {
      color: #1a1a1a;
    }

    .messages-table tr:hover {
      background: #d1e7f5;
    }

    .messages-table .message-content {
      max-width: 300px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .error {
      color: #e55e00;
      font-size: 1rem;
      text-align: center;
      margin-bottom: 2rem;
    }

    /* Footer */
    footer {
      background: #1f2937;
      color: white;
      padding: 2rem 2rem;
      text-align: center;
    }

    .footer-content {
      max-width: 1200px;
      margin: 0 auto;
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      gap: 2rem;
    }

    .footer-section {
      flex: 1;
      min-width: 200px;
    }

    .footer-section h3 {
      font-size: 1.5rem;
      margin-bottom: 1rem;
      color: #f97316;
    }

    .footer-section ul {
      list-style: none;
    }

    .footer-section ul li {
      margin: 0.5rem 0;
    }

    .footer-section ul li a {
      color: #e5f4fd;
      text-decoration: none;
      transition: color 0.3s;
    }

    .footer-section ul li a:hover {
      color: #f97316;
    }

    .social-icons {
      display: flex;
      justify-content: center;
      gap: 1rem;
      margin-top: 1rem;
    }

    .social-icons a {
      color: white;
      font-size: 1.5rem;
      transition: color 0.3s;
    }

    .social-icons a:hover {
      color: #f97316;
    }

    .footer-bottom {
      margin-top: 1.5rem;
      font-size: 0.9rem;
      color: #e5f4fd;
    }

    /* Responsive Design */
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

      .nav-links a {
        width: 100%;
        text-align: center;
      }

      .logout-btn button {
        width: 100%;
        text-align: center;
      }

      .dropdown-content,
      .logout-dropdown {
        position: static;
        width: 100%;
        box-shadow: none;
        transform: none;
        opacity: 1;
      }

      .messages-table {
        font-size: 0.9rem;
      }

      .messages-table th,
      .messages-table td {
        padding: 0.8rem;
      }

      .messages-table .message-content {
        max-width: 150px;
      }

      .footer-content {
        flex-direction: column;
        align-items: center;
      }

      .footer-section {
        text-align: center;
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
          <a href="#">Plan Your Trip</a>
        </div>
      </div>
      <a href="hoteladmin.php">Hotels</a>
      <a href="cabadmin.php">Cabs</a>
      <a href="about.php">About</a>
      <a href="view_messages.php" class="always-underline">Messages</a>
    </div>
    <div class="logout-btn">
      <button><i class="fas fa-sign-out-alt"></i> Logout</button>
      <div class="logout-dropdown">
        <a href="logout.php">Logout</a>
      </div>
    </div>
  </nav>

  <section class="messages-section">
    <h2>Contact Messages</h2>
    <p>View and manage inquiries sent through the Contact Us form.</p>
    <?php if (isset($error)): ?>
      <div class="error">
        <p><?php echo htmlspecialchars($error); ?></p>
      </div>
    <?php endif; ?>
    <?php if (empty($messages)): ?>
      <p>No messages found.</p>
    <?php else: ?>
      <table class="messages-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Message</th>
            <th>Received</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($messages as $message): ?>
            <tr>
              <td><?php echo htmlspecialchars($message['id']); ?></td>
              <td><?php echo htmlspecialchars($message['name']); ?></td>
              <td><?php echo htmlspecialchars($message['email']); ?></td>
              <td class="message-content"><?php echo htmlspecialchars($message['message']); ?></td>
              <td><?php echo htmlspecialchars($message['created_at']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </section>

  <footer>
    <div class="footer-content">
      <div class="footer-section">
        <h3>TravelGuide</h3>
        <p>Your ultimate companion for exploring Sri Lanka’s wonders.</p>
      </div>
      <div class="footer-section">
        <h3>Quick Links</h3>
        <ul>
          <li><a href="homeadmin.php">Home</a></li>
          <li><a href="placesadmin.php">Destinations</a></li>
          <li><a href="cabadmin.php">Cabs</a></li>
          <li><a href="hoteladmin.php">Hotels</a></li>
          <li><a href="view_messages.php">Messages</a></li>
        </ul>
      </div>
    </div>
    <div class="social-icons">
      <a href="#"><i class="fab fa-facebook-f"></i></a>
      <a href="#"><i class="fab fa-twitter"></i></a>
      <a href="#"><i class="fab fa-instagram"></i></a>
    </div>
    <div class="footer-bottom">
      <p>© 2025 TravelGuide. All rights reserved.</p>
    </div>
  </footer>
</body>
</html>