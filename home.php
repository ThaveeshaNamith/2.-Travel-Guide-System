<?php
require 'db_connect.php';

// Handle search
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$places = $experiences = $accommodations = [];

if ($search_query) {
    $stmt = $pdo->prepare("SELECT * FROM places WHERE name LIKE ? ORDER BY created_at DESC LIMIT 6");
    $stmt->execute(["%$search_query%"]);
    $places = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT * FROM experiences WHERE name LIKE ? ORDER BY created_at DESC LIMIT 6");
    $stmt->execute(["%$search_query%"]);
    $experiences = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT * FROM accommodations WHERE name LIKE ? ORDER BY created_at DESC LIMIT 9");
    $stmt->execute(["%$search_query%"]);
    $accommodations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->query("SELECT * FROM places ORDER BY created_at DESC LIMIT 6");
    $places = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT * FROM experiences ORDER BY created_at DESC LIMIT 6");
    $experiences = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT * FROM accommodations ORDER BY created_at DESC LIMIT 9");
    $accommodations = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>TravelGuide</title>
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
      height: 70vh;
      background: url('img/homecover.jpg') center/cover no-repeat;
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
      font-size: 3rem;
      font-weight: 700;
      margin-bottom: 1rem;
    }

    .hero p {
      font-size: 1.2rem;
      margin-bottom: 2rem;
    }

    /* Welcome Section */
    .welcome-section {
      display: flex;
      flex-wrap: wrap;
      padding: 4rem 2%;
      align-items: center;
      justify-content: center;
      gap: 2rem;
    }

    .welcome-section img {
      max-width: 100%;
      width: 700px;
      height: auto;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s;
    }

    .welcome-section img:hover {
      transform: scale(1.02);
    }

    .welcome-text {
      max-width: 600px;
      padding: 2rem;
    }

    .welcome-text h2 {
      font-size: 2.5rem;
      color: #263238;
      margin-bottom: 1rem;
    }

    .welcome-text h2 span {
      color: #8BC34A;
    }

    .welcome-text p {
      margin: 1.2rem 0;
      font-size: 1.1rem;
      color: #546E7A;
      line-height: 1.6;
    }

    /* Featured Destinations */
    .featured {
      padding: 4rem 2rem;
      background: #fff;
    }

    .featured h2 {
      text-align: center;
      font-size: 2.5rem;
      margin-bottom: 1rem;
      color: #0077b6;
    }

    .featured p {
      text-align: center;
      max-width: 800px;
      margin: 0 auto 2rem;
      font-size: 1.1rem;
      color: #546E7A;
      line-height: 1.6;
    }

    .destinations {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 2rem;
      max-width: 1200px;
      margin: 0 auto;
    }

    .destination-card {
      position: relative;
      background: #e5f4fd;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s;
    }

    .destination-card:hover {
      transform: translateY(-5px);
    }

    .destination-card .image-container {
      position: relative;
      width: 100%;
      height: 200px;
    }

    .destination-card img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: opacity 0.3s;
    }

    .destination-card .image-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 119, 182, 0.3);
      opacity: 0;
      transition: opacity 0.3s;
      z-index: 1;
    }

    .destination-card:hover .image-overlay {
      opacity: 1;
    }

    .destination-card h3 {
      font-size: 1.5rem;
      padding: 1rem;
      color: #0077b6;
    }

    .destination-card p {
      padding: 0 1rem 1rem;
      color: #1a1a1a;
      font-size: 0.9rem;
      line-height: 1.5;
    }

    .destination-card .read-more-btn {
      position: absolute;
      bottom: 15px;
      left: 50%;
      transform: translateX(-50%) translateY(100%);
      background-color: #8BC34A;
      border: none;
      color: white;
      padding: 0.6rem 1.5rem;
      font-size: 0.9rem;
      font-weight: 600;
      cursor: pointer;
      opacity: 0;
      transition: opacity 0.3s, transform 0.3s;
      border-radius: 5px;
      z-index: 2;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .destination-card:hover .read-more-btn {
      opacity: 1;
      transform: translateX(-50%) translateY(0);
    }

    .destination-card .read-more-btn:hover {
      background-color: #689f38;
    }

    .find-more-btn-container {
      text-align: center;
      margin-top: 2rem;
    }

    .find-more-btn {
      background-color: #f97316;
      border: none;
      color: white;
      padding: 1rem 2.5rem;
      font-size: 1.1rem;
      font-weight: 600;
      border-radius: 5px;
      cursor: pointer;
      opacity: 0;
      transform: translateY(20px);
      animation: slideUp 0.5s ease-out forwards;
      transition: background 0.3s, transform 0.2s;
    }

    .find-more-btn:hover {
      background-color: #e55e00;
      transform: translateY(-2px);
    }

    /* Popular Attractions Section */
    .popular-attractions {
      padding: 4rem 2rem;
      background: #f8fafc;
    }

    .popular-attractions h2 {
      text-align: center;
      font-size: 2.5rem;
      margin-bottom: 1rem;
      color: #0077b6;
    }

    .popular-attractions p {
      text-align: center;
      max-width: 800px;
      margin: 0 auto 2rem;
      font-size: 1.1rem;
      color: #546E7A;
      line-height: 1.6;
    }

    .attractions {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 2rem;
      max-width: 1200px;
      margin: 0 auto;
    }

    .attraction-card {
      position: relative;
      background: #e5f4fd;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s;
    }

    .attraction-card:hover {
      transform: translateY(-5px);
    }

    .attraction-card .image-container {
      position: relative;
      width: 100%;
      height: 200px;
    }

    .attraction-card img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: opacity 0.3s;
    }

    .attraction-card .image-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 119, 182, 0.3);
      opacity: 0;
      transition: opacity 0.3s;
      z-index: 1;
    }

    .attraction-card:hover .image-overlay {
      opacity: 1;
    }

    .attraction-card h3 {
      font-size: 1.5rem;
      padding: 1rem;
      color: #0077b6;
    }

    .attraction-card p {
      padding: 0 1rem 1rem;
      color: #1a1a1a;
      font-size: 0.9rem;
      line-height: 1.5;
    }

    .attraction-card .read-more-btn {
      position: absolute;
      bottom: 15px;
      left: 50%;
      transform: translateX(-50%) translateY(100%);
      background-color: #8BC34A;
      border: none;
      color: white;
      padding: 0.6rem 1.5rem;
      font-size: 0.9rem;
      font-weight: 600;
      cursor: pointer;
      opacity: 0;
      transition: opacity 0.3s, transform 0.3s;
      border-radius: 5px;
      z-index: 2;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .attraction-card:hover .read-more-btn {
      opacity: 1;
      transform: translateX(-50%) translateY(0);
    }

    .attraction-card .read-more-btn:hover {
      background-color: #689f38;
    }

    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 1000;
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      background-color: #fff;
      border-radius: 10px;
      padding: 2rem;
      max-width: 600px;
      width: 90%;
      max-height: 80vh;
      overflow-y: auto;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
      position: relative;
      animation: modalFadeIn 0.3s ease-out;
    }

    .modal-content img {
      width: 100%;
      height: auto;
      max-height: 300px;
      object-fit: cover;
      border-radius: 5px;
      margin-bottom: 1rem;
    }

    .modal-content p {
      font-size: 1rem;
      color: #1a1a1a;
      line-height: 1.6;
    }

    .close-btn {
      position: absolute;
      top: 1rem;
      right: 1rem;
      font-size: 1.5rem;
      color: #546E7A;
      cursor: pointer;
      transition: color 0.3s;
    }

    .close-btn:hover {
      color: #f97316;
    }

    @keyframes modalFadeIn {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes slideUp {
      to {
        opacity: 1;
        transform: translateY(0);
      }
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

      .nav-links, .search-login {
        flex-direction: column;
        align-items: flex-start;
        width: 100%;
      }

      .nav-links a,
      .search-container,
      .login-btn button {
        width: 100%;
        text-align: center;
      }

      .search-login input[type="text"] {
        border-radius: 20px;
      }

      .search-btn {
        border-radius: 20px;
      }

      .search-results {
        position: static;
        width: 100%;
        max-height: 300px;
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

      .welcome-section {
        flex-direction: column;
        text-align: center;
      }

      .destinations,
      .attractions {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      }

      .destination-card .read-more-btn,
      .attraction-card .read-more-btn {
        font-size: 0.8rem;
        padding: 0.5rem 1.2rem;
        bottom: 10px;
      }

      .modal-content {
        width: 95%;
        padding: 1.5rem;
      }

      .modal-content img {
        max-height: 200px;
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
      <a href="home.php" class="always-underline">Home</a>
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
      <a href="contact.php">Contact Us</a>
    </div>
    
      <div class="login-btn">
        <button><i class="fas fa-user"></i> Login</button>
        <div class="login-dropdown">
          <a href="login.php">Login</a>
          <a href="register.php">Register</a>
        </div>
      </div>
    </div>
  </nav>

  <section class="hero">
    <div class="hero-content">
    </div>
  </section>

  <section class="welcome-section">
   <img src="img/well.jpg" alt="Sri Lanka">
    <div class="welcome-text">
      <h2>Welcome to <span>Sri Lanka</span></h2>
      <p>Uncover a destination where every moment feels like magic. Explore ancient ruins steeped in history, stroll along golden beaches kissed by the sun, and trek through misty hills blanketed in lush tea plantations. Dive into turquoise waters alive with marine wonders, embark on exhilarating wildlife safaris, and savor the bold, authentic flavors of local cuisine. Whether you’re seeking adventure, serenity, or cultural immersion, this tropical paradise offers it all. Start planning your journey today and discover breathtaking beauty, unforgettable experiences, and memories that will last a lifetime.</p>
    </div>
  </section>

  <section class="featured" id="places">
    <h2>Top Sri Lankan Destinations</h2>
    <p>Uncover a destination where every moment feels like magic. Explore ancient ruins steeped in history, stroll along golden beaches kissed by the sun, and trek through misty hills blanketed in lush tea plantations. Dive into turquoise waters alive with marine wonders, embark on exhilarating wildlife safaris, and savor the bold, authentic flavors of local cuisine. Whether you’re seeking adventure, serenity, or cultural immersion, this tropical paradise offers it all.</p>
    <div class="destinations">
      <?php foreach ($places as $place): ?>
        <div class="destination-card">
          <div class="image-container">
            <img src="<?php echo htmlspecialchars($place['image']); ?>" alt="<?php echo htmlspecialchars($place['name']); ?>">
            <div class="image-overlay"></div>
            <button class="read-more-btn" onclick="openModal('<?php echo htmlspecialchars($place['image']); ?>', '<?php echo htmlspecialchars(addslashes($place['description'])); ?>')">Read More</button>
          </div>
          <h3><?php echo htmlspecialchars($place['name']); ?></h3>
          <p><?php echo htmlspecialchars(substr($place['description'], 0, 50)) . (strlen($place['description']) > 50 ? '...' : ''); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="find-more-btn-container">
      <a href="place.php" class="find-more-btn">See All The Destinations</a>
    </div>
  </section>

  <section class="popular-attractions" id="experiences">
    <h2>Things To Do In Sri Lanka</h2>
    <p>Experience the best of Sri Lanka with unforgettable activities that cater to every traveler. Explore ancient UNESCO World Heritage sites, relax on sun-soaked beaches, and trek through lush tea plantations. Go whale watching, embark on thrilling wildlife safaris, and dive into vibrant coral reefs. Discover bustling local markets, indulge in authentic Sri Lankan cuisine, and soak in the rich cultural heritage through festivals and traditional performances. Whether it’s adventure, relaxation, or cultural discovery, Sri Lanka promises endless experiences to make your trip truly unforgettable. Start planning your adventure today!</p>
    <div class="attractions">
      <?php foreach ($experiences as $experience): ?>
        <div class="attraction-card">
          <div class="image-container">
            <img src="<?php echo htmlspecialchars($experience['image']); ?>" alt="<?php echo htmlspecialchars($experience['name']); ?>">
            <div class="image-overlay"></div>
            <button class="read-more-btn" onclick="openModal('<?php echo htmlspecialchars($experience['image']); ?>', '<?php echo htmlspecialchars(addslashes($experience['description'])); ?>')">Read More</button>
          </div>
          <h3><?php echo htmlspecialchars($experience['name']); ?></h3>
          <p><?php echo htmlspecialchars(substr($experience['description'], 0, 50)) . (strlen($experience['description']) > 50 ? '...' : ''); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="find-more-btn-container">
      <a href="things.php" class="find-more-btn">Find All The Things To Do</a>
    </div>
  </section>

  <section class="popular-attractions" id="accommodations">
    <h2>Hotels And Cab Services</h2>
    <p>Experience the best of Sri Lanka with top-rated hotels and reliable cab services that make your journey seamless and comfortable. Stay in everything from luxury resorts to cozy budget-friendly stays, all offering warm hospitality and modern amenities. Navigate the island with ease using trusted cab services—perfect for airport transfers, day tours, and city travel. Whether you're exploring ancient cities, relaxing on the coast, or heading into the hill country, your accommodation and transport are covered. Enjoy convenience, safety, and comfort every step of the way. Start planning your hassle-free travel experience today!</p>
    <div class="attractions">
      <?php foreach ($accommodations as $accommodation): ?>
        <div class="attraction-card">
          <div class="image-container">
            <img src="<?php echo htmlspecialchars($accommodation['image']); ?>" alt="<?php echo htmlspecialchars($accommodation['name']); ?>">
            <div class="image-overlay"></div>
            <button class="read-more-btn" onclick="openModal('<?php echo htmlspecialchars($accommodation['image']); ?>', '<?php echo htmlspecialchars(addslashes($accommodation['description'])); ?>')">Read More</button>
          </div>
          <h3><?php echo htmlspecialchars($accommodation['name']); ?></h3>
          <p><?php echo htmlspecialchars(substr($accommodation['description'], 0, 50)) . (strlen($accommodation['description']) > 50 ? '...' : ''); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="find-more-btn-container">
      <a href="hotel.php" class="find-more-btn">Find All The Hotels</a>
      <a href="cab.php" class="find-more-btn">Find All The Cabs</a>
    </div>
  </section>

  <!-- Modal -->
  <div class="modal" id="descriptionModal">
    <div class="modal-content">
      <span class="close-btn" onclick="closeModal()">×</span>
      <img id="modalImage" src="" alt="Modal Image">
      <p id="modalDescription"></p>
    </div>
  </div>

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
          <li><a href="contact">Contact Us</a></li>
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

  <script>
    function openModal(image, description) {
      const modal = document.getElementById('descriptionModal');
      const modalImage = document.getElementById('modalImage');
      const modalDescription = document.getElementById('modalDescription');
      modalImage.src = image;
      modalDescription.textContent = description;
      modal.style.display = 'flex';
    }

    function closeModal() {
      const modal = document.getElementById('descriptionModal');
      modal.style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
      const modal = document.getElementById('descriptionModal');
      if (event.target == modal) {
        modal.style.display = 'none';
      }
    }

    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    const searchContainer = document.querySelector('.search-container');

    function displayResults(results, category) {
      const resultItem = document.createElement('div');
      resultItem.classList.add('search-result-item');
      resultItem.innerHTML = `
        <img src="${results.image}" alt="${results.name}">
        <div>
          <h3>${results.name}</h3>
          <p>${results.description.substring(0, 50)}${results.description.length > 50 ? '...' : ''}</p>
          <span>${category}</span>
        </div>
      `;
      searchResults.appendChild(resultItem);
    }

    searchInput.addEventListener('input', () => {
      const query = searchInput.value.trim();
      if (query.length > 0) {
        searchResults.innerHTML = '';
        searchResults.classList.add('show');
        <?php foreach (['places' => 'Destination', 'experiences' => 'Activity', 'accommodations' => 'Hotel/Cab'] as $table => $category): ?>
          <?php foreach ($$table as $item): ?>
            if ("<?php echo htmlspecialchars($item['name']); ?>".toLowerCase().includes(query.toLowerCase()) ||
                "<?php echo htmlspecialchars($item['description']); ?>".toLowerCase().includes(query.toLowerCase())) {
              displayResults({
                name: "<?php echo htmlspecialchars($item['name']); ?>",
                description: "<?php echo htmlspecialchars($item['description']); ?>",
                image: "<?php echo htmlspecialchars($item['image']); ?>"
              }, "<?php echo $category; ?>");
            }
          <?php endforeach; ?>
        <?php endforeach; ?>
        if (!searchResults.hasChildNodes()) {
          searchResults.innerHTML = '<div class="search-result-item"><p>No results found</p></div>';
        }
      } else {
        searchResults.classList.remove('show');
        searchResults.innerHTML = '';
      }
    });

    document.addEventListener('click', (e) => {
      if (!searchContainer.contains(e.target)) {
        setTimeout(() => {
          searchResults.classList.remove('show');
          searchResults.innerHTML = '';
        }, 200);
      }
    });

    searchResults.addEventListener('click', (e) => {
      e.stopPropagation();
    });
  </script>
</body>
</html>