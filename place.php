<?php
require_once 'config.php';

// Fetch all destinations
try {
    $stmt = $pdo->query("SELECT * FROM destinations");
    $destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Failed to fetch destinations: " . $e->getMessage());
}

// Fetch nearby places for each destination
$nearby_places = [];
foreach ($destinations as $dest) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM nearby_places WHERE destination_id = ?");
        $stmt->execute([$dest['id']]);
        $nearby_places[$dest['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Failed to fetch nearby places for destination ID {$dest['id']}: " . $e->getMessage());
    }
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
      top: 100%;
      right: 0;
      background-color: #fff;
      min-width: 120px;
      border-radius: 5px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      z-index: 1000;
      transition: opacity 0.2s ease-in-out, transform 0.2s ease-in-out;
      transform: translateY(5px);
      opacity: 0;
    }

    .login-btn:hover .login-dropdown {
      display: block;
      opacity: 1;
      transform: translateY(0);
    }

    .login-dropdown a {
      color: #1a1a1a;
      padding: 0.8rem 1rem;
      display: block;
      font-size: 0.9rem;
      text-decoration: none;
      transition: background 0.2s;
    }

    .login-dropdown a:hover {
      background-color: #e5f4fd;
    }

    .hero {
      background: url('img/cover1.jpg') center/cover no-repeat;
      height: 60vh;
      display: flex;
      justify-content: center;
      align-items: center;
      color: white;
      text-align: center;
      padding: 0 2rem;
    }

    .hero-content h1 {
      font-size: 3rem;
      font-weight: 700;
      margin-bottom: 1rem;
    }

    .hero-content p {
      font-size: 1.2rem;
      font-weight: 300;
      max-width: 600px;
      margin: 0 auto;
    }

    .destinations {
      max-width: 1200px;
      margin: 3rem auto;
      padding: 0 2rem;
    }

    .destinations h2 {
      text-align: center;
      font-size: 2.5rem;
      color: #0077b6;
      margin-bottom: 2rem;
    }

    .destinations-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 2rem;
    }

    .destination-card {
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s, box-shadow 0.3s;
    }

    .destination-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
    }

    .destination-card img {
      width: 100%;
      height: 200px;
      object-fit: cover;
    }

    .destination-card-content {
      padding: 1.5rem;
    }

    .destination-card-content h3 {
      font-size: 1.5rem;
      color: #0077b6;
      margin-bottom: 0.5rem;
    }

    .destination-card-content p {
      font-size: 0.9rem;
      color: #555;
      margin-bottom: 1rem;
    }

    .destination-card-content button {
      background: #f97316;
      color: white;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 5px;
      font-size: 0.9rem;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.3s, transform 0.2s;
    }

    .destination-card-content button:hover {
      background: #e55e00;
      transform: translateY(-2px);
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
      justify-content: center;
      align-items: center;
      animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    .modal-content {
      background: linear-gradient(145deg, #ffffff, #f0f7ff);
      border-radius: 15px;
      max-width: 900px;
      width: 95%;
      max-height: 90vh;
      overflow-y: auto;
      position: relative;
      padding: 2.5rem;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
      transform: scale(0.9);
      animation: modalPop 0.3s ease-out forwards;
    }

    @keyframes modalPop {
      from { transform: scale(0.9); opacity: 0; }
      to { transform: scale(1); opacity: 1; }
    }

    .modal-content img.main-image {
      width: 100%;
      height: 400px;
      object-fit: cover;
      border-radius: 10px;
      margin-bottom: 1.5rem;
      border: 3px solid #e5f4fd;
      transition: transform 0.3s ease;
    }

    .modal-content img.main-image:hover {
      transform: scale(1.02);
    }

    .modal-content .close-btn {
      position: absolute;
      top: 1rem;
      right: 1rem;
      font-size: 2rem;
      cursor: pointer;
      color: #fff;
      background: #f97316;
      width: 40px;
      height: 40px;
      display: flex;
      justify-content: center;
      align-items: center;
      border-radius: 50%;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
      transition: background 0.2s, transform 0.2s;
      z-index: 1001;
    }

    .modal-content .close-btn:hover {
      background: #e55e00;
      transform: scale(1.1);
    }

    .modal-content h2 {
      font-size: 2.5rem;
      color: #0077b6;
      margin-bottom: 1rem;
      font-weight: 700;
      text-transform: capitalize;
      letter-spacing: 0.5px;
    }

    .modal-content p {
      font-size: 1rem;
      color: #333;
      line-height: 1.6;
      margin-bottom: 1.5rem;
      background: #fff;
      padding: 1rem;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .nearby-places {
      margin-top: 2rem;
      padding-top: 1.5rem;
      border-top: 1px solid #e5f4fd;
    }

    .nearby-places h3 {
      font-size: 1.8rem;
      color: #0077b6;
      margin-bottom: 1.5rem;
      font-weight: 600;
      position: relative;
      display: inline-block;
    }

    .nearby-places h3::after {
      content: '';
      position: absolute;
      bottom: -4px;
      left: 0;
      width: 50%;
      height: 3px;
      background: #f97316;
      border-radius: 2px;
    }

    .nearby-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 1.5rem;
    }

    .nearby-card {
      background: #fff;
      border-radius: 10px;
      padding: 1.2rem;
      text-align: center;
      transition: transform 0.3s, box-shadow 0.3s;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .nearby-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
    }

    .nearby-card img {
      width: 100%;
      height: 220px;
      object-fit: cover;
      border-radius: 8px;
      margin-bottom: 0.8rem;
    }

    .nearby-card h4 {
      font-size: 1.1rem;
      color: #0077b6;
      margin-bottom: 0.5rem;
      font-weight: 600;
    }

    .nearby-card p {
      font-size: 0.85rem;
      color: #555;
      line-height: 1.5;
      margin-bottom: 0.5rem;
    }

    .nearby-card a.map-link {
      display: inline-block;
      font-size: 0.85rem;
      color: #f97316;
      text-decoration: none;
      font-weight: 600;
      margin-top: 0.5rem;
      transition: color 0.3s;
    }

    .nearby-card a.map-link:hover {
      color: #e55e00;
      text-decoration: underline;
    }

    .modal-content .map-container {
      position: relative;
      margin-top: 1.5rem;
      margin-bottom: 1.5rem;
    }

    .modal-content iframe {
      width: 100%;
      height: 350px;
      border: none;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .modal-content .map-close-btn {
      display: block;
      margin: 0.5rem auto 0;
      font-size: 1.5rem;
      cursor: pointer;
      color: #fff;
      background: #f97316;
      width: 35px;
      height: 35px;
      display: flex;
      justify-content: center;
      align-items: center;
      border-radius: 50%;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
      transition: background 0.2s, transform 0.2s;
    }

    .modal-content .map-close-btn:hover {
      background: #e55e00;
      transform: scale(1.1);
    }

    footer {
      background: #1f2937;
      color: white;
      padding: 3rem 2rem;
      display: flex;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 2rem;
    }

    .footer-section {
      flex: 1;
      min-width: 200px;
    }

    .footer-section h3 {
      font-size: 1.2rem;
      margin-bottom: 1rem;
    }

    .footer-section p,
    .footer-section a {
      font-size: 0.9rem;
      color: #d1d5db;
      text-decoration: none;
    }

    .footer-section a:hover {
      color: #f97316;
    }

    .footer-bottom {
      background: #111827;
      text-align: center;
      padding: 1rem;
      font-size: 0.8rem;
      color: #d1d5db;
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

      .destination-card img {
        height: 150px;
      }

      .modal-content {
        padding: 1.5rem;
        width: 90%;
      }

      .modal-content img.main-image {
        height: 300px;
      }

      .modal-content iframe {
        height: 250px;
      }

      .nearby-grid {
        grid-template-columns: 1fr;
      }

      .nearby-card img {
        height: 120px;
      }

      .modal-content h2 {
        font-size: 2rem;
      }

      .nearby-places h3 {
        font-size: 1.5rem;
      }

      .nearby-card h4 {
        font-size: 1rem;
      }

      .modal-content .close-btn {
        width: 35px;
        height: 35px;
        font-size: 1.8rem;
      }

      .modal-content .map-close-btn {
        width: 30px;
        height: 30px;
        font-size: 1.2rem;
      }
    }
  </style>
</head>
<body>
  <nav>
    <div class="logo">Travel<span>Guide</span></div>
    <div class="nav-links">
      <a href="/travelguide/home.php">Home</a>
      <div class="dropdown">
        <a href="#" class="always-underline">Guides ▾</a>
        <div class="dropdown-content">
          <a href="/travelguide/place.php">Destinations</a>
          <a href="things.php">Things to Do</a>
          <a href="plan_trip.php">Plan Your Tour</a>
        </div>
      </div>
      <a href="hotel.php">Hotels</a>
      <a href="cab.php">Cabs</a>
      <a href="about.html">About Us</a>
      <a href="contact.php">Contact Us</a>
      
      <div class="login-btn">
        <button><i class="fas fa-user"></i> Login</button>
        <div class="login-dropdown">
          <a href="#">Login</a>
          <a href="#">Register</a>
        </div>
      </div>
    </div>
  </nav>

  <section class="hero">
    <div class="hero-content">
    </div>
  </section>

  <section class="destinations">
    <h2>Travel Destinations</h2>
    <p style="text-align: center; margin-bottom: 2rem;">Dive into the heart of Sri Lanka with our curated list of must-visit destinations. Whether you’re drawn to historic landmarks, scenic landscapes, or vibrant coastal towns, there’s something for every traveler.</p>
    <div class="destinations-grid">
      <?php if (empty($destinations)): ?>
        <p style="text-align: center; grid-column: 1 / -1;">No destinations available. Please check back later.</p>
      <?php else: ?>
        <?php foreach ($destinations as $dest): ?>
          <div class="destination-card">
            <img src="<?php echo htmlspecialchars($dest['image']); ?>" alt="<?php echo htmlspecialchars($dest['title']); ?>" onerror="this.src='img/placeholder.jpg'">
            <div class="destination-card-content">
              <h3><?php echo htmlspecialchars($dest['title']); ?></h3>
              <p><?php echo htmlspecialchars(substr($dest['description'], 0, 100)) . '...'; ?></p>
              <button class="read-more-btn" data-key="<?php echo htmlspecialchars($dest['key']); ?>">Read More</button>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>

  <div id="destination-modal" class="modal">
    <div class="modal-content">
      <span class="close-btn">×</span>
      <img id="modal-image" class="main-image" src="" alt="">
      <h2 id="modal-title"></h2>
      <p id="modal-description"></p>
      <div class="nearby-places">
        <h3>Nearby Places</h3>
        <div id="nearby-grid" class="nearby-grid"></div>
      </div>
      <div class="map-container">
        <iframe id="modal-map" src=""></iframe>
        <span class="map-close-btn">×</span>
      </div>
    </div>
  </div>

  <footer>
    <div class="footer-section">
      <h3>TravelGuide Footnotes</h3>
      <p>Your ultimate companion for exploring Sri Lanka’s wonders.</p>
    </div>
    <div class="footer-section">
      <h3>Quick Links</h3>
      <a href="/travelguide/home.php">Home</a><br>
      <a href="/travelguide/place.php">Destinations</a><br>
      <a href="hotel.php">Hotels</a><br>
      <a href="contact.php">Contact Us</a>
    </div>
    <div class="footer-section">
      <h3>Connect With Us</h3>
      <a href="#"><i class="fab fa-facebook"></i> Facebook</a><br>
      <a href="#"><i class="fab fa-twitter"></i> Twitter</a><br>
      <a href="#"><i class="fab fa-instagram"></i> Instagram</a>
    </div>
    <div class="footer-bottom">
      <p>© 2025 TravelGuide. All rights reserved.</p>
    </div>
  </footer>

  <script>
    const modal = document.getElementById('destination-modal');
    const closeBtn = document.querySelector('.close-btn');
    const mapCloseBtn = document.querySelector('.map-close-btn');
    const readMoreBtns = document.querySelectorAll('.read-more-btn');

    closeBtn.addEventListener('click', () => {
      modal.style.display = 'none';
    });

    mapCloseBtn.addEventListener('click', () => {
      modal.style.display = 'none';
    });

    window.addEventListener('click', (e) => {
      if (e.target === modal) {
        modal.style.display = 'none';
      }
    });

    readMoreBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        const key = btn.getAttribute('data-key');
        fetch('/travelguide/get_destination.php?key=' + encodeURIComponent(key))
          .then(response => response.json())
          .then(data => {
            if (data.error) {
              alert(data.error);
              return;
            }
            document.getElementById('modal-title').textContent = data.title;
            document.getElementById('modal-image').src = data.image;
            document.getElementById('modal-description').textContent = data.description;
            document.getElementById('modal-map').src = data.map;
            const nearbyGrid = document.getElementById('nearby-grid');
            nearbyGrid.innerHTML = '';
            data.nearby.forEach(place => {
              const card = document.createElement('div');
              card.className = 'nearby-card';
              card.innerHTML = `
                <img src="${place.image}" alt="${place.title}" onerror="this.src='img/placeholder.jpg'">
                <h4>${place.title}</h4>
                <p>${place.description}</p>
                ${place.map ? `<a href="${place.map}" target="_blank" class="map-link">View on Map</a>` : ''}
              `;
              nearbyGrid.appendChild(card);
            });
            modal.style.display = 'flex';
          })
          .catch(error => {
            console.error('Error:', error);
            alert('Failed to load destination details.');
          });
      });
    });
  </script>
</body>
</html>