<?php
require_once 'config.php';

// Fetch all activities
try {
    $stmt = $pdo->query("SELECT * FROM activities");
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Failed to fetch activities: " . $e->getMessage());
}

// Fetch related places for each activity
$related_places = [];
foreach ($activities as $act) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM related_places WHERE activity_id = ?");
        $stmt->execute([$act['id']]);
        $related_places[$act['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Failed to fetch related places for activity ID {$act['id']}: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Things to Do - TravelGuide</title>
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
      background: url('img/nine.jpg') center/cover no-repeat;
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

    .activities {
      max-width: 1200px;
      margin: 3rem auto;
      padding: 0 2rem;
    }

    .activities h2 {
      text-align: center;
      font-size: 2.5rem;
      color: #0077b6;
      margin-bottom: 2rem;
    }

    .activities-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 2rem;
    }

    .activity-card {
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s, box-shadow 0.3s;
    }

    .activity-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
    }

    .activity-card img {
      width: 100%;
      height: 200px;
      object-fit: cover;
    }

    .activity-card-content {
      padding: 1.5rem;
    }

    .activity-card-content h3 {
      font-size: 1.5rem;
      color: #0077b6;
      margin-bottom: 0.5rem;
    }

    .activity-card-content p {
      font-size: 0.9rem;
      color: #555;
      margin-bottom: 1rem;
    }

    .activity-card-content button {
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

    .activity-card-content button:hover {
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
      background: rgba(0, 0, 0, 0.5);
      z-index: 1000;
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      background: #fff;
      border-radius: 10px;
      max-width: 800px;
      width: 90%;
      max-height: 90vh;
      overflow-y: auto;
      position: relative;
      padding: 2rem;
    }

    .modal-content img.main-image {
      width: 100%;
      height: 300px;
      object-fit: cover;
      border-radius: 5px;
      margin-bottom: 1rem;
    }

    .modal-content .close-btn {
      position: absolute;
      top: 1rem;
      right: 1rem;
      font-size: 1.5rem;
      cursor: pointer;
      color: #1a1a1a;
    }

    .modal-content h2 {
      font-size: 2rem;
      color: #0077b6;
      margin-bottom: 1rem;
    }

    .modal-content p {
      font-size: 0.9rem;
      color: #555;
      margin-bottom: 1rem;
    }

    .related-places {
      margin-top: 2rem;
    }

    .related-places h3 {
      font-size: 1.5rem;
      color: #0077b6;
      margin-bottom: 1rem;
    }

    .related-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
    }

    .related-card {
      background: #e5f4fd;
      border-radius: 5px;
      padding: 1rem;
      text-align: center;
    }

    .related-card img {
      width: 100%;
      height: 100px;
      object-fit: cover;
      border-radius: 5px;
      margin-bottom: 0.5rem;
    }

    .related-card h4 {
      font-size: 1rem;
      color: #0077b6;
      margin-bottom: 0.5rem;
    }

    .related-card p {
      font-size: 0.8rem;
      color: #555;
      margin-bottom: 0.5rem;
    }

    .view-map-btn {
      background: #007bff;
      color: #fff;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 5px;
      font-size: 0.8rem;
      font-weight: 600;
      cursor: pointer;
      margin-top: 0.5rem;
      transition: background 0.3s;
    }

    .view-map-btn:hover {
      background: #0056b3;
    }

    .map-link {
      color: #007bff;
      text-decoration: none;
      font-size: 0.8rem;
      font-weight: 600;
      transition: color 0.3s;
    }

    .map-link:hover {
      color: #0056b3;
      text-decoration: underline;
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

      .activity-card img {
        height: 150px;
      }

      .modal-content img.main-image {
        height: 200px;
      }

      .related-card img {
        height: 80px;
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
          <a href="/travelguide/things.php">Things to Do</a>
          <a href="plan_trip.php">Plan Your Tour</a>
         
        </div>
      </div>
      <a href="hotel.php">Hotels</a>
      <a href="cab.php">Cabs</a>
      <a href="about.php">About</a>
      <a href="contact.php">Contact Us</a>
      </div>
    </div>
  </nav>

  <section class="hero">
    <div class="hero-content">
    </div>
  </section>

  <section class="activities">
    <h2>Unforgettable Activities in Sri Lanka</h2>
    <p style="text-align: center; margin-bottom: 2rem;">From wildlife safaris to cultural explorations, discover a range of activities that make your Sri Lankan journey unforgettable. Whether you seek adventure, relaxation, or cultural immersion, there’s something for everyone.</p>
    <div class="activities-grid">
      <?php if (empty($activities)): ?>
        <p style="text-align: center; grid-column: 1 / -1;">No activities available. Please check back later.</p>
      <?php else: ?>
        <?php foreach ($activities as $act): ?>
          <div class="activity-card">
            <img src="<?php echo htmlspecialchars($act['image']); ?>" alt="<?php echo htmlspecialchars($act['title']); ?>" onerror="this.src='img/placeholder.jpg'">
            <div class="activity-card-content">
              <h3><?php echo htmlspecialchars($act['title']); ?></h3>
              <p><?php echo htmlspecialchars(substr($act['description'], 0, 100)) . '...'; ?></p>
              <button class="read-more-btn" data-key="<?php echo htmlspecialchars($act['key']); ?>">Read More</button>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>

  <div id="activity-modal" class="modal">
    <div class="modal-content">
      <span class="close-btn">×</span>
      <img id="modal-image" class="main-image" src="" alt="">
      <h2 id="modal-title"></h2>
      <p id="modal-description"></p>
      <div class="related-places">
        <h3>Related Places</h3>
        <div id="related-grid" class="related-grid"></div>
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
      <a href="/travelguide/place.php">Home</a><br>
      <a href="/travelguide/place.php">Destinations</a><br>
      <a href="/travelguide/things.php">Guides</a><br>
      <a href="#">Hotels</a><br>
      <a href="#">Contact Us</a>
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
    const modal = document.getElementById('activity-modal');
    const closeBtn = document.querySelector('.close-btn');
    const readMoreBtns = document.querySelectorAll('.read-more-btn');

    closeBtn.addEventListener('click', () => {
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
        console.log('Fetching activity with key:', key, 'at', new Date().toLocaleString('en-US', { timeZone: 'Asia/Colombo' }));
        const fetchUrl = './get_activity.php?key=' + encodeURIComponent(key);
        console.log('Fetch URL:', fetchUrl);

        fetch(fetchUrl, { mode: 'cors' })
          .then(response => {
            console.log('HTTP Response Status:', response.status);
            if (!response.ok) {
              throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.text().then(text => ({ status: response.status, text }));
          })
          .then(({ status, text }) => {
            console.log('Raw response text:', text);
            let data;
            try {
              data = JSON.parse(text);
            } catch (e) {
              throw new Error('Invalid JSON response: ' + e.message);
            }
            console.log('Parsed JSON data:', data);
            if (data.error) {
              throw new Error(data.error);
            }
            document.getElementById('modal-title').textContent = data.title;
            document.getElementById('modal-image').src = data.image;
            document.getElementById('modal-description').textContent = data.description;
            const relatedGrid = document.getElementById('related-grid');
            relatedGrid.innerHTML = '';
            data.related.forEach(place => {
              console.log('Related place:', place);
              const card = document.createElement('div');
              card.className = 'related-card';
              card.innerHTML = `
                <img src="${place.image}" alt="${place.title}" onerror="this.src='img/placeholder.jpg'">
                <h4>${place.title}</h4>
                <p>${place.description}</p>
                ${place.map_link ? `<a href="${place.map_link}" target="_blank" class="map-link">View Location</a>` : ''}
              `;
              relatedGrid.appendChild(card);
            });
            modal.style.display = 'flex';
          })
          .catch(error => {
            console.error('Fetch or processing error:', error.message);
            alert('Failed to load activity details: ' + error.message);
          });
      });
    });
  </script>
</body>
</html>