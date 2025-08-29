<?php
require 'db_connect.php';

// Fetch trips from database
try {
    $stmt = $pdo->query("SELECT * FROM trips ORDER BY created_at DESC");
    $trips = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching trips: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan Your Trip - TravelGuide</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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

        /* Trip Section */
        .trip-section {
            padding: 4rem 2rem;
            background: #fff;
        }

        .trip-section h2 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #0077b6;
        }

        .trip-section p {
            text-align: center;
            max-width: 800px;
            margin: 0 auto 2rem;
            font-size: 1.1rem;
            color: #546E7A;
            line-height: 1.6;
        }

        .trip-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .trip-card {
            position: relative;
            background: #e5f4fd;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .trip-card:hover {
            transform: translateY(-5px);
        }

        .trip-card .image-container {
            position: relative;
            width: 100%;
            height: 200px;
        }

        .trip-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: opacity 0.3s;
        }

        .trip-card .image-overlay {
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

        .trip-card:hover .image-overlay {
            opacity: 1;
        }

        .trip-card h3 {
            font-size: 1.5rem;
            padding: 1rem;
            color: #0077b6;
        }

        .trip-card p {
            padding: 0 1rem 1rem;
            color: #1a1a1a;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .trip-card .read-more-btn {
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

        .trip-card:hover .read-more-btn {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        .trip-card .read-more-btn:hover {
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

        .modal-content p strong {
            color: #1a1a1a;
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

        /* Footer */
        footer {
            background: #1f2937;
            color: white;
            padding: 1rem 2rem;
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

        /* Animations */
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

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            nav {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .nav-links {
                flex-direction: column;
                width: 100%;
            }

            .nav-links a {
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

            .trip-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }

            .trip-card .read-more-btn {
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
            <a href="home.php">Home</a>
            <div class="dropdown">
                <a href="#">Guides ▾</a>
                <div class="dropdown-content">
                    <a href="place.php">Destinations</a>
                    <a href="things.php">Things to Do</a>
                    <a href="plan_trip.php" class="always-underline">Plan Your Trip</a>
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
    </nav>

    <section class="hero">
        <div class="hero-content">
           
        </div>
    </section>

    <section class="trip-section" id="trips">
        <h2>Plan Your Trip</h2>
        <p>Explore our handpicked trips designed for beach lovers, adventure seekers, cultural explorers, and more.</p>
        <div class="trip-grid">
            <?php foreach ($trips as $trip): ?>
                <div class="trip-card">
                    <div class="image-container">
                        <img src="<?php echo htmlspecialchars($trip['image']); ?>" alt="<?php echo htmlspecialchars($trip['name']); ?>">
                        <div class="image-overlay"></div>
                        <button class="read-more-btn" onclick="openModal('<?php echo htmlspecialchars($trip['image']); ?>', '<?php echo htmlspecialchars(addslashes($trip['name'])); ?>', '<?php echo htmlspecialchars(addslashes($trip['type'])); ?>', '<?php echo htmlspecialchars(addslashes($trip['duration'])); ?>', '<?php echo htmlspecialchars(addslashes($trip['region'])); ?>', '<?php echo htmlspecialchars(addslashes($trip['budget'])); ?>', '<?php echo htmlspecialchars(addslashes($trip['description'])); ?>')">Read More</button>
                    </div>
                    <h3><?php echo htmlspecialchars($trip['name']); ?></h3>
                    <p><?php echo htmlspecialchars(substr($trip['description'], 0, 50)) . (strlen($trip['description']) > 50 ? '...' : ''); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <div class="modal" id="tripModal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">×</span>
            <img id="modalImage" src="" alt="">
            <h2 id="modalName"></h2>
            <p><strong>Type:</strong> <span id="modalType"></span></p>
            <p><strong>Duration:</strong> <span id="modalDuration"></span></p>
            <p><strong>Region:</strong> <span id="modalRegion"></span></p>
            <p><strong>Budget:</strong> <span id="modalBudget"></span></p>
            <p><strong>Details:</strong> <span id="modalDescription"></span></p>
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
                    <li><a href="things.php">Things to Do</a></li>
                    <li><a href="hotel.php">Hotels</a></li>
                    <li><a href="cab.php">Cabs</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Connect</h3>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2025 TravelGuide. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function openModal(image, name, type, duration, region, budget, description) {
            const modal = document.getElementById('tripModal');
            const modalImage = document.getElementById('modalImage');
            const modalName = document.getElementById('modalName');
            const modalType = document.getElementById('modalType');
            const modalDuration = document.getElementById('modalDuration');
            const modalRegion = document.getElementById('modalRegion');
            const modalBudget = document.getElementById('modalBudget');
            const modalDescription = document.getElementById('modalDescription');

            modalImage.src = image;
            modalImage.alt = name;
            modalName.textContent = name;
            modalType.textContent = type;
            modalDuration.textContent = duration;
            modalRegion.textContent = region;
            modalBudget.textContent = budget;
            modalDescription.textContent = description;
            modal.style.display = 'flex';
        }

        function closeModal() {
            const modal = document.getElementById('tripModal');
            modal.style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('tripModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        };
    </script>
</body>
</html>