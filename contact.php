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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $errors = [];

    // Basic validation
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email is required.';
    }
    if (empty($message)) {
        $errors[] = 'Message is required.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, message, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$name, $email, $message]);
            $success = 'Your message has been sent successfully!';
        } catch (PDOException $e) {
            $errors[] = 'An error occurred while sending your message. Please try again later.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Contact Us - TravelGuide</title>
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

    /* Hero Section */
    .hero {
      position: relative;
      height: 50vh;
      background: url('img/welcome.jpg') center/cover no-repeat;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      color: white;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
    }

    .hero-content {
      max-width: 700px;
      padding: 1rem;
    }

    .hero h1 {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 1rem;
    }

    .hero p {
      font-size: 1.1rem;
      margin-bottom: 1.5rem;
    }

    /* Contact Section */
    .contact-section {
      padding: 4rem 2rem;
      background: #fff;
      display: flex;
      flex-wrap: wrap;
      gap: 2rem;
      justify-content: center;
      max-width: 1200px;
      margin: 0 auto;
    }

    .contact-form {
      flex: 1;
      min-width: 300px;
      background: #e5f4fd;
      border-radius: 10px;
      padding: 2rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .contact-form h2 {
      font-size: 2rem;
      color: #0077b6;
      margin-bottom: 1rem;
    }

    .contact-form p {
      font-size: 1rem;
      color: #546E7A;
      margin-bottom: 1.5rem;
    }

    .contact-form form {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .contact-form input,
    .contact-form textarea {
      padding: 0.8rem;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 1rem;
      font-family: 'Poppins', sans-serif;
      width: 100%;
    }

    .contact-form textarea {
      resize: vertical;
      min-height: 100px;
    }

    .contact-form button {
      background-color: #f97316;
      border: none;
      color: white;
      padding: 0.8rem 1.5rem;
      font-size: 1rem;
      font-weight: 600;
      border-radius: 5px;
      cursor: pointer;
      transition: background 0.3s, transform 0.2s;
    }

    .contact-form button:hover {
      background-color: #e55e00;
      transform: translateY(-2px);
    }

    .contact-form .error {
      color: #e55e00;
      font-size: 0.9rem;
    }

    .contact-form .success {
      color: #8BC34A;
      font-size: 0.9rem;
    }

    .contact-info {
      flex: 1;
      min-width: 300px;
      background: #e5f4fd;
      border-radius: 10px;
      padding: 2rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .contact-info h2 {
      font-size: 2rem;
      color: #0077b6;
      margin-bottom: 1rem;
    }

    .contact-info p {
      font-size: 1rem;
      color: #1a1a1a;
      line-height: 1.6;
      margin-bottom: 1rem;
    }

    .contact-info .info-item {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 1rem;
    }

    .contact-info .info-item i {
      color: #f97316;
      font-size: 1.2rem;
    }

    .contact-info .social-icons {
      display: flex;
      gap: 1rem;
      margin-top: 1rem;
    }

    .contact-info .social-icons a {
      color: #1a1a1a;
      font-size: 1.5rem;
      transition: color 0.3s;
    }

    .contact-info .social-icons a:hover {
      color: #f97316;
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

      .hero h1 {
        font-size: 2rem;
      }

      .hero p {
        font-size: 1rem;
      }

      .contact-section {
        flex-direction: column;
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
      <a href="home.php">Home</a>
      <div class="dropdown">
        <a href="#">Guides ▾</a>
        <div class="dropdown-content">
          <a href="place.php">Destinations</a>
          <a href="things.php">Things to Do</a>
          <a href="plan_trip.php">Plan Your Trip</a>
        </div>
      </div>
      <a href="hotel.php">Hotels</a>
      <a href="cab.php">Cabs</a>
      <a href="about.php">About</a>
      <a href="contact.php" class="always-underline">Contact Us</a>
    </div>
    <div class="login-btn">
      <button><i class="fas fa-user"></i> Login</button>
      <div class="login-dropdown">
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
      </div>
    </div>
  </nav>

  <section class="hero">
    <div class="hero-content">
    </div>
  </section>

  <section class="contact-section">
    <div class="contact-form">
      <h2>Send Us a Message</h2>
      <p>Fill out the form below, and we’ll get back to you as soon as possible.</p>
      <?php if (!empty($errors)): ?>
        <div class="error">
          <?php foreach ($errors as $error): ?>
            <p><?php echo htmlspecialchars($error); ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <?php if (isset($success)): ?>
        <div class="success">
          <p><?php echo htmlspecialchars($success); ?></p>
        </div>
      <?php endif; ?>
      <form method="POST" action="contact.php">
        <input type="text" name="name" placeholder="Your Name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
        <input type="email" name="email" placeholder="Your Email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
        <textarea name="message" placeholder="Your Message" required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
        <button type="submit">Send Message</button>
      </form>
    </div>
    <div class="contact-info">
      <h2>Contact Information</h2>
      <p>Reach out to us directly or connect with us on social media.</p>
      <div class="info-item">
        <i class="fas fa-phone"></i>
        <p>+94 11 123 4567</p>
      </div>
      <div class="info-item">
        <i class="fas fa-envelope"></i>
        <p>info@travelguide.lk</p>
      </div>
      <div class="info-item">
        <i class="fas fa-map-marker-alt"></i>
        <p>123 Travel Lane, Colombo, Sri Lanka</p>
      </div>
      <div class="social-icons">
        <a href="#"><i class="fab fa-facebook-f"></i></a>
        <a href="#"><i class="fab fa-twitter"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
      </div>
    </div>
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
          <li><a href="home.php">Home</a></li>
          <li><a href="place.php">Destinations</a></li>
          <li><a href="cab.php">Cabs</a></li>
          <li><a href="hotel.php">Hotels</a></li>
          <li><a href="contact.php">Contact Us</a></li>
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