<?php
// Database connection
$servername = "localhost";
$username = "root"; // Update with your DB username
$password = ""; // Update with your DB password
$dbname = "travelers";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch all hotels
$stmt = $conn->query("SELECT * FROM hotels ORDER BY location, hotel_name");
$hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group hotels by location
$hotelsByLocation = [
    'Colombo' => [],
    'Galle' => [],
    'Kandy' => []
];
foreach ($hotels as $hotel) {
    $hotelsByLocation[$hotel['location']][] = $hotel;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Hotels - TravelGuide</title>
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

        .hero {
            position: relative;
            height: 70vh;
            background: url('img/hcover.jpg') center/cover no-repeat;
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

        .featured, .popular-attractions {
            padding: 4rem 2rem;
            background: #fff;
        }

        .popular-attractions {
            background: #f8fafc;
        }

        .featured h2, .popular-attractions h2 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #0077b6;
        }

        .featured p, .popular-attractions p {
            text-align: center;
            max-width: 800px;
            margin: 0 auto 2rem;
            font-size: 1.1rem;
            color: #546E7A;
            line-height: 1.6;
        }

        .destinations, .attractions {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .destination-card, .attraction-card {
            position: relative;
            background: #e5f4fd;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .destination-card:hover, .attraction-card:hover {
            transform: translateY(-5px);
        }

        .destination-card .image-container, .attraction-card .image-container {
            position: relative;
            width: 100%;
            height: 200px;
        }

        .destination-card img, .attraction-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: opacity 0.3s;
        }

        .destination-card .image-overlay, .attraction-card .image-overlay {
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

        .destination-card:hover .image-overlay, .attraction-card:hover .image-overlay {
            opacity: 1;
        }

        .destination-card h3, .attraction-card h3 {
            font-size: 1.5rem;
            padding: 1rem;
            color: #0077b6;
        }

        .destination-card p, .attraction-card p {
            padding: 0 1rem 1rem;
            color: #1a1a1a;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .destination-card .read-more-btn, .attraction-card .read-more-btn {
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

        .destination-card:hover .read-more-btn, .attraction-card:hover .read-more-btn {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        .destination-card .read-more-btn:hover, .attraction-card .read-more-btn:hover {
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

        #hotelModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            font-family: 'Poppins', sans-serif;
        }

        #hotelModal .modal-content {
            background: #fff;
            padding: 2rem;
            border-radius: 10px;
            max-width: 600px;
            width: 90%;
            position: relative;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        #closeModal {
            position: absolute;
            top: 10px;
            right: 15px;
            cursor: pointer;
            font-size: 1.5rem;
            color: #1a1a1a;
            transition: color 0.3s;
        }

        #closeModal:hover {
            color: #f97316;
        }

        #modalImage {
            width: 100%;
            height: auto;
            max-height: 300px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        #modalTitle {
            font-size: 1.8rem;
            color: #0077b6;
            margin-bottom: 1rem;
        }

        #modalDescription {
            font-size: 1rem;
            color: #546E7A;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        #modalLink {
            display: inline-block;
            background-color: #f97316;
            color: white;
            padding: 0.8rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s, transform 0.2s;
        }

        #modalLink:hover {
            background-color: #e55e00;
            transform: translateY(-2px);
        }

        @keyframes slideUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
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

            .destinations, .attractions {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }

            .destination-card .read-more-btn, .attraction-card .read-more-btn {
                font-size: 0.8rem;
                padding: 0.5rem 1.2rem;
                bottom: 10px;
            }

            .footer-content {
                flex-direction: column;
                align-items: center;
            }

            .footer-section {
                text-align: center;
            }

            #hotelModal .modal-content {
                padding: 1.5rem;
                max-width: 90%;
            }

            #modalTitle {
                font-size: 1.5rem;
            }

            #modalDescription {
                font-size: 0.9rem;
            }

            #modalLink {
                padding: 0.6rem 1.2rem;
                font-size: 0.9rem;
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
    </nav>

    <section class="hero">
        <div class="hero-content">
            
        </div>
    </section>

    <section class="featured">
        <h2>Best Hotels around Colombo</h2>
        <p>Uncover a destination where every moment feels like magic. Explore ancient ruins steeped in history, stroll along golden beaches kissed by the sun, and trek through misty hills blanketed in lush tea plantations. Dive into turquoise waters alive with marine wonders, embark on exhilarating wildlife safaris, and savor the bold, authentic flavors of local cuisine.</p>
        <div class="destinations">
            <?php foreach ($hotelsByLocation['Colombo'] as $hotel): ?>
                <div class="destination-card">
                    <div class="image-container">
                        <img src="<?php echo htmlspecialchars($hotel['image'] ?: 'img/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($hotel['hotel_name']); ?>">
                        <div class="image-overlay"></div>
                        <button class="read-more-btn" data-hotel="<?php echo htmlspecialchars($hotel['id']); ?>">Read More</button>
                    </div>
                    <h3><?php echo htmlspecialchars($hotel['hotel_name']); ?></h3>
                    <p><?php echo htmlspecialchars($hotel['description']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="find-more-btn-container">
            <a href="hotels.php" class="find-more-btn">Find All The Hotels</a>
        </div>
    </section>

    <section class="popular-attractions">
        <h2>Best Hotels around Galle</h2>
        <p>Experience the best of Sri Lanka with unforgettable stays that cater to every traveler. Explore ancient UNESCO World Heritage sites, relax on sun-soaked beaches, and dive into vibrant coral reefs. Discover colonial charm, indulge in authentic Sri Lankan cuisine, and soak in the rich cultural heritage through a perfect hotel stay.</p>
        <div class="attractions">
            <?php foreach ($hotelsByLocation['Galle'] as $hotel): ?>
                <div class="attraction-card">
                    <div class="image-container">
                        <img src="<?php echo htmlspecialchars($hotel['image'] ?: 'img/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($hotel['hotel_name']); ?>">
                        <div class="image-overlay"></div>
                        <button class="read-more-btn" data-hotel="<?php echo htmlspecialchars($hotel['id']); ?>">Read More</button>
                    </div>
                    <h3><?php echo htmlspecialchars($hotel['hotel_name']); ?></h3>
                    <p><?php echo htmlspecialchars($hotel['description']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="find-more-btn-container">
            <a href="hotels.php" class="find-more-btn">Find All The Hotels</a>
        </div>
    </section>

    <section class="popular-attractions">
        <h2>Best Hotels around Kandy</h2>
        <p>Experience the best of Sri Lanka with top-rated hotels that make your journey seamless and comfortable. Stay in everything from luxury resorts to cozy budget-friendly stays, all offering warm hospitality and modern amenities. Navigate the island with ease and enjoy the cultural heart of Sri Lanka in Kandy.</p>
        <div class="attractions">
            <?php foreach ($hotelsByLocation['Kandy'] as $hotel): ?>
                <div class="attraction-card">
                    <div class="image-container">
                        <img src="<?php echo htmlspecialchars($hotel['image'] ?: 'img/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($hotel['hotel_name']); ?>">
                        <div class="image-overlay"></div>
                        <button class="read-more-btn" data-hotel="<?php echo htmlspecialchars($hotel['id']); ?>">Read More</button>
                    </div>
                    <h3><?php echo htmlspecialchars($hotel['hotel_name']); ?></h3>
                    <p><?php echo htmlspecialchars($hotel['description']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="find-more-btn-container">
            <a href="hotels.php" class="find-more-btn">Find All The Hotels</a>
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
                    <li><a href="hotel.php">Hotels</a></li>
                     <li><a href="cab.php">Cabs</a></li>
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

    <div id="hotelModal">
        <div class="modal-content">
            <span id="closeModal">×</span>
            <img id="modalImage" src="" alt="Hotel Image">
            <h2 id="modalTitle"></h2>
            <p id="modalDescription"></p>
            <a id="modalLink" href="" target="_blank">Visit Hotel Website</a>
        </div>
    </div>

    <script>
        const hotelData = <?php echo json_encode($hotels, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('hotelModal');

            document.querySelectorAll('.read-more-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const hotelId = this.getAttribute('data-hotel');
                    const hotel = hotelData.find(h => h.id == hotelId);

                    if (hotel) {
                        document.getElementById('modalImage').src = hotel.image || 'img/placeholder.jpg';
                        document.getElementById('modalTitle').textContent = hotel.hotel_name;
                        document.getElementById('modalDescription').textContent = hotel.description;
                        document.getElementById('modalLink').href = hotel.website_link;
                        modal.style.display = 'flex';
                    }
                });
            });

            document.getElementById('closeModal').addEventListener('click', function() {
                modal.style.display = 'none';
            });

            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>