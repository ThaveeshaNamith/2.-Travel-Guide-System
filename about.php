<?php
require 'db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>About Us - TravelGuide</title>
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


    /* Hero Section */
    .hero {
      position: relative;
      height: 50vh;
      background: url('img/ai.jpg') center/cover no-repeat;
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

    /* About Section */
    .about-section {
      padding: 4rem 2rem;
      background: #fff;
      text-align: center;
    }

    .about-section h2 {
      font-size: 2.5rem;
      color: #0077b6;
      margin-bottom: 1rem;
    }

    .about-section p {
      max-width: 800px;
      margin: 0 auto 2rem;
      font-size: 1.1rem;
      color: #546E7A;
      line-height: 1.6;
    }

    .mission-vision {
      display: flex;
      flex-wrap: wrap;
      gap: 2rem;
      justify-content: center;
      max-width: 1200px;
      margin: 0 auto;
    }

    .mission-vision-card {
      flex: 1;
      min-width: 300px;
      background: #e5f4fd;
      border-radius: 10px;
      padding: 2rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s;
    }

    .mission-vision-card:hover {
      transform: translateY(-5px);
    }

    .mission-vision-card h3 {
      font-size: 1.8rem;
      color: #0077b6;
      margin-bottom: 1rem;
    }

    .mission-vision-card p {
      font-size: 1rem;
      color: #1a1a1a;
      line-height: 1.6;
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

      .mission-vision {
        flex-direction: column;
      }

      .team-members {
        grid-template-columns: 1fr;
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
      <a href="about.php" class="always-underline">About</a>
      <a href="contact.php">Contact Us</a>
    </div>
   
    </div>
  </nav>

  <section class="hero">
    <div class="hero-content">
      <h1>About TravelGuide</h1>
      <p>Your trusted companion for discovering the wonders of Sri Lanka.</p>
    </div>
  </section>

  <section class="about-section">
    <h2>Who We Are</h2>
    <p>TravelGuide is your ultimate resource for exploring the vibrant and diverse beauty of Sri Lanka. Founded with a passion for travel and a love for this tropical paradise, we aim to inspire and guide travelers to experience the island’s rich culture, stunning landscapes, and warm hospitality. From ancient ruins to golden beaches, lush tea plantations to thrilling wildlife safaris, we’re here to help you create unforgettable memories.</p>
    <div class="mission-vision">
      <div class="mission-vision-card">
        <h3>Our Mission</h3>
        <p>To empower travelers with comprehensive, reliable, and inspiring resources to explore Sri Lanka’s hidden gems and iconic destinations, ensuring every journey is seamless and memorable.</p>
      </div>
      <div class="mission-vision-card">
        <h3>Our Vision</h3>
        <p>To be the leading travel platform for Sri Lanka, connecting adventurers with authentic experiences, sustainable travel options, and the vibrant spirit of the island.</p>
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
</body>
</html>