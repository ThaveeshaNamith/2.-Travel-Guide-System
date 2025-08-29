<?php
require 'db_connect.php';

// Fetch all cab services
$stmt = $pdo->query("SELECT * FROM cabs ORDER BY created_at DESC");
$cabs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Cab Services - TravelGuide</title>
  <meta name="description" content="Book top cab services in Sri Lanka including Kangaroo Cabs, PickMe, Uber, Yogo, TaxiGo, and more for reliable airport transfers, city tours, and island-wide travel." />
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
      overflow-x: hidden;
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
      z-index: 1000;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
      transition: background 0.3s, transform 0.2s;
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

    .hero {
      position: relative;
      height: 70vh;
      background: url('img/tuk2.jpg') center/cover no-repeat;
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
      margin-bottom: 2rem;
    }

    .services-section {
      padding: 4rem 2rem;
      background: #fff;
    }

    .services-section h2 {
      text-align: center;
      font-size: 2.5rem;
      margin-bottom: 1rem;
      color: #0077b6;
    }

    .services-section p {
      text-align: center;
      max-width: 800px;
      margin: 0 auto 2rem;
      font-size: 1.1rem;
      color: #546E7A;
      line-height: 1.6;
    }

    .services-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 2rem;
      max-width: 1200px;
      margin: 0 auto;
    }

    .service-card {
      position: relative;
      background: linear-gradient(135deg, #e5f4fd, #d1e8ff);
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .service-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .service-card .image-container {
      position: relative;
      width: 100%;
      height: 230px;
    }

    .service-card img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: opacity 0.3s ease;
    }

    .service-card .image-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(0deg, rgba(0, 119, 182, 0.5), transparent);
      opacity: 0;
      transition: opacity 0.3s ease;
      z-index: 1;
    }

    .service-card:hover .image-overlay {
      opacity: 1;
    }

    .service-card h3 {
      font-size: 1.5rem;
      padding: 1rem;
      color: #0077b6;
      text-align: center;
    }

    .service-card p {
      display: none; /* Removed description from main interface */
    }

    .service-card .book-now-btn {
      position: absolute;
      bottom: 15px;
      left: 50%;
      transform: translateX(-50%) translateY(100%);
      background: linear-gradient(45deg, #ffd700, #ffca28);
      border: none;
      color: #000;
      padding: 0.8rem 2rem;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      opacity: 0;
      transition: opacity 0.3s ease, transform 0.3s ease;
      border-radius: 25px;
      z-index: 2;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .service-card:hover .book-now-btn {
      opacity: 1;
      transform: translateX(-50%) translateY(0);
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
      overflow-y: auto;
      animation: fadeIn 0.3s ease;
    }

    .modal.active {
      display: flex;
    }

    .modal-content {
      background: linear-gradient(135deg, #ffffff, #f0f8ff);
      border-radius: 15px;
      max-width: 600px;
      width: 90%;
      display: flex;
      flex-direction: column;
      position: relative;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
      padding: 1.5rem;
      animation: slideUp 0.5s ease;
      transform-origin: center;
    }

    .modal-left {
      padding: 0;
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .modal-left h2 {
      font-size: 2rem;
      color: #0077b6;
      text-align: center;
      text-transform: uppercase;
    }

    .modal-left img {
      width: 100%;
      height: 160px;
      object-fit: cover;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .modal-left p {
      font-size: 1rem;
      color: #1a1a1a;
      line-height: 1.6;
      text-align: center;
      padding: 0 1rem;
    }

    .modal-booking-link {
      display: inline-block;
      background: linear-gradient(45deg, #ffd700, #ffca28);
      color: #000;
      padding: 0.8rem 1.5rem;
      text-decoration: none;
      font-size: 1.1rem;
      font-weight: 700;
      border-radius: 25px;
      text-align: center;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
      margin: 0 auto;
      display: block;
      width: fit-content;
    }

    .modal-booking-link:hover {
      transform: scale(1.05);
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
    }

    .modal-close {
      position: absolute;
      top: 15px;
      right: 15px;
      background: linear-gradient(45deg, #f97316, #e55e00);
      color: white;
      border: none;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: 1.5rem;
      transition: transform 0.3s ease, background 0.3s ease;
      z-index: 1001;
    }

    .modal-close:hover {
      transform: rotate(90deg) scale(1.1);
      background: linear-gradient(45deg, #e55e00, #d94600);
    }

    footer {
      background: #1f2937;
      color: white;
      padding: 2rem;
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

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @keyframes slideUp {
      from { transform: translateY(50px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
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

      .nav-links a {
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

      .hero h1 {
        font-size: 2rem;
      }

      .hero p {
        font-size: 1rem;
      }

      .services-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      }

      .service-card .book-now-btn {
        font-size: 0.8rem;
        padding: 0.5rem 1.2rem;
        bottom: 10px;
      }

      .modal-content {
        padding: 1rem;
      }

      .modal-left img {
        height: 130px;
      }

      .modal-close {
        top: 10px;
        right: 10px;
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
        <a href="#" class="always-underline">Guides ▾</a>
        <div class="dropdown-content">
          <a href="home.php#places">Destinations</a>
          <a href="home.php#experiences">Things to Do</a>
          <a href="plan_trip.php">Plan Your Tour</a>
         
        </div>
      </div>
      <a href="hotel.php">Hotels</a>
      <a href="cab.php">Cabs</a>
      <a href="about.php">About</a>
      <a href="contact.php">Contact Us</a>
    </div>
  </nav>

  <section class="hero">
    <div class="hero-content">
    
    </div>
  </section>

  <section class="services-section">
    <h2>Cab Services</h2>
    <p>Choose from Sri Lanka’s top cab providers for reliable airport transfers, city rides, and island-wide tours. Book your ride today!</p>
    <div class="services-grid">
      <?php foreach ($cabs as $cab): ?>
        <div class="service-card" data-service-id="<?php echo $cab['id']; ?>">
          <div class="image-container">
            <img src="<?php echo htmlspecialchars($cab['image']); ?>" alt="<?php echo htmlspecialchars($cab['name']); ?>">
            <div class="image-overlay"></div>
            <button class="book-now-btn" data-name="<?php echo htmlspecialchars($cab['name']); ?>" data-image="<?php echo htmlspecialchars($cab['image']); ?>" data-description="<?php echo htmlspecialchars($cab['description']); ?>" data-booking-link="<?php echo htmlspecialchars($cab['booking_link'] ?? 'https://pickme.lk/'); ?>">Book Now</button>
          </div>
          <h3><?php echo htmlspecialchars($cab['name']); ?></h3>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <div class="modal" id="service-modal">
    <div class="modal-content">
      <button class="modal-close" aria-label="Close service details">×</button>
      <div class="modal-left">
        <h2 id="modal-title"></h2>
        <img id="modal-image" src="" alt="">
        <p id="modal-description"></p>
        <a id="modal-booking-link" href="" class="modal-booking-link" target="_blank" rel="noopener">Book This Service</a>
      </div>
    </div>
  </div>

  <footer>
    <div class="footer-content">
      <div class="footer-section">
        <h3>TravelGuide Footnotes</h3>
        <p>Your ultimate companion for exploring Sri Lanka’s wonders.</p>
      </div>
      <div class="footer-section">
        <h3>Quick Links</h3>
        <ul>
          <li><a href="home.php">Home</a></li>
          <li><a href="home.php#places">Destinations</a></li>
          <li><a href="home.php#experiences">Guides</a></li>
          <li><a href="home.php#accommodations">Hotels</a></li>
          <li><a href="cab.php">Cabs</a></li>
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
    document.addEventListener('DOMContentLoaded', () => {
      const modal = document.getElementById('service-modal');
      const modalContent = document.querySelector('.modal-content');
      const modalImage = document.getElementById('modal-image');
      const modalTitle = document.getElementById('modal-title');
      const modalDescription = document.getElementById('modal-description');
      const modalBookingLink = document.getElementById('modal-booking-link');
      const modalClose = document.querySelector('.modal-close');
      const bookNowButtons = document.querySelectorAll('.book-now-btn');

      if (modal && modalClose && bookNowButtons.length) {
        bookNowButtons.forEach(button => {
          button.addEventListener('click', () => {
            console.log('Opening modal for:', button.dataset.name);
            const name = button.dataset.name;
            const image = button.dataset.image;
            const description = button.dataset.description;
            const bookingLink = button.dataset.bookingLink;

            modalTitle.textContent = name;
            modalImage.src = image;
            modalImage.alt = name;
            modalDescription.textContent = description;
            modalBookingLink.href = bookingLink || '#';
            modalBookingLink.textContent = bookingLink ? 'Book This Service' : 'Booking Not Available';

            modal.style.display = 'flex'; // Ensure display is set
            modal.classList.add('active');
            modalContent.classList.add('active');
            modal.focus();
          });
        });

        modalClose.addEventListener('click', () => {
          console.log('Closing modal');
          modal.classList.remove('active');
          modalContent.classList.remove('active');
          setTimeout(() => {
            modal.style.display = 'none';
          }, 300); // Match animation duration
        });

        modal.addEventListener('click', (e) => {
          if (e.target === modal) {
            console.log('Closing modal by clicking outside');
            modal.classList.remove('active');
            modalContent.classList.remove('active');
            setTimeout(() => {
              modal.style.display = 'none';
            }, 300);
          }
        });

        document.addEventListener('keydown', (e) => {
          if (e.key === 'Escape' && modal.classList.contains('active')) {
            console.log('Closing modal with Escape key');
            modal.classList.remove('active');
            modalContent.classList.remove('active');
            setTimeout(() => {
              modal.style.display = 'none';
            }, 300);
          }
        });
      } else {
        console.error('Modal or related elements not found');
      }
    });
  </script>
</body>
</html>