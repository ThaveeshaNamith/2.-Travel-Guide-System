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

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_hotel']) || isset($_POST['edit_hotel'])) {
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        $hotel_name = $_POST['hotel_name'];
        $location = $_POST['location'];
        $description = $_POST['description'];
        $website_link = $_POST['website_link'];
        $image = isset($_POST['existing_image']) ? $_POST['existing_image'] : '';

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "Uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $image = $target_dir . basename($_FILES["image"]["name"]);
            move_uploaded_file($_FILES["image"]["tmp_name"], $image);
        }

        if (isset($_POST['add_hotel'])) {
            $stmt = $conn->prepare("INSERT INTO hotels (hotel_name, location, description, image, website_link) VALUES (:hotel_name, :location, :description, :image, :website_link)");
            $stmt->execute([
                'hotel_name' => $hotel_name,
                'location' => $location,
                'description' => $description,
                'image' => $image,
                'website_link' => $website_link
            ]);
        } elseif (isset($_POST['edit_hotel'])) {
            $stmt = $conn->prepare("UPDATE hotels SET hotel_name = :hotel_name, location = :location, description = :description, image = :image, website_link = :website_link WHERE id = :id");
            $stmt->execute([
                'id' => $id,
                'hotel_name' => $hotel_name,
                'location' => $location,
                'description' => $description,
                'image' => $image,
                'website_link' => $website_link
            ]);
        }
    } elseif (isset($_POST['delete_hotel'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM hotels WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
}

// Fetch all hotels
$stmt = $conn->query("SELECT * FROM hotels");
$hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TravelGuide Admin Dashboard</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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

        .dashboard-container {
            padding: 4rem 2rem;
            background: #fff;
            min-height: calc(100vh - 60px);
        }

        .dashboard-container h1 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #0077b6;
        }

        .dashboard-container p {
            text-align: center;
            max-width: 800px;
            margin: 0 auto 2rem;
            font-size: 1.1rem;
            color: #546E7A;
            line-height: 1.6;
        }

        .district-section {
            margin-bottom: 3rem;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        .district-section h2 {
            font-size: 2rem;
            color: #0077b6;
            margin-bottom: 1rem;
        }

        .add-hotel-btn {
            display: block;
            margin: 1rem 0;
            background-color: #f97316;
            border: none;
            color: white;
            padding: 0.8rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }

        .add-hotel-btn:hover {
            background-color: #e55e00;
            transform: translateY(-2px);
        }

        .table-container {
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #e5f4fd;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 1rem;
            text-align: left;
            font-size: 0.9rem;
        }

        th {
            background: #0077b6;
            color: white;
            font-weight: 600;
        }

        tr:nth-child(even) {
            background: #f8fafc;
        }

        .action-btn {
            background-color: #8BC34A;
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
            font-weight: 600;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 0.5rem;
            transition: background 0.3s;
        }

        .action-btn.edit:hover {
            background-color: #689f38;
        }

        .action-btn.delete {
            background-color: #dc3545;
        }

        .action-btn.delete:hover {
            background-color: #c82333;
        }

        .table-image {
            width: 50px;
            height: auto;
            border-radius: 5px;
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

        #hotelModal h2 {
            font-size: 1.8rem;
            color: #0077b6;
            margin-bottom: 1rem;
            text-align: center;
        }

        #hotelModal .form-group {
            margin-bottom: 1rem;
        }

        #hotelModal label {
            display: block;
            font-size: 0.9rem;
            color: #546E7A;
            margin-bottom: 0.5rem;
        }

        #hotelModal input,
        #hotelModal textarea,
        #hotelModal select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 0.9rem;
            font-family: 'Poppins', sans-serif;
        }

        #hotelModal input[type="file"] {
            padding: 0.3rem;
        }

        #hotelModal textarea {
            resize: vertical;
            min-height: 100px;
        }

        #imagePreview {
            max-width: 100%;
            max-height: 200px;
            margin-top: 0.5rem;
            border-radius: 5px;
            display: none;
        }

        #hotelModal .submit-btn {
            background-color: #f97316;
            border: none;
            color: white;
            padding: 0.8rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 5px;
            cursor: pointer;
            display: block;
            margin: 1rem auto 0;
            transition: background 0.3s, transform 0.2s;
        }

        #hotelModal .submit-btn:hover {
            background-color: #e55e00;
            transform: translateY(-2px);
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
            <a href="view_messages.php">Contact Us</a>
            <div class="login-btn">
                <button><i class="fas fa-user"></i> Logout</button>
                <div class="login-dropdown">
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <section class="dashboard-container">
        <h1>Hotel Management Dashboard</h1>
        <p>Manage hotel listings for Colombo, Galle, and Kandy. Add, edit, or delete hotels to keep the TravelGuide website updated.</p>

        <div class="district-section" id="colombo">
            <h2>Best Hotels in Colombo</h2>
            <button class="add-hotel-btn" onclick="openModal('add', 'Colombo')">Add New Hotel</button>
            <div class="table-container">
                <table class="table" id="colomboTable">
                    <thead>
                        <tr>
                            <th>Hotel Name</th>
                            <th>Location</th>
                            <th>Description</th>
                            <th>Image</th>
                            <th>Website Link</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="colomboTableBody"></tbody>
                </table>
            </div>
        </div>

        <div class="district-section" id="galle">
            <h2>Best Hotels in Galle</h2>
            <button class="add-hotel-btn" onclick="openModal('add', 'Galle')">Add New Hotel</button>
            <div class="table-container">
                <table class="table" id="galleTable">
                    <thead>
                        <tr>
                            <th>Hotel Name</th>
                            <th>Location</th>
                            <th>Description</th>
                            <th>Image</th>
                            <th>Website Link</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="galleTableBody"></tbody>
                </table>
            </div>
        </div>

        <div class="district-section" id="kandy">
            <h2>Best Hotels in Kandy</h2>
            <button class="add-hotel-btn" onclick="openModal('add', 'Kandy')">Add New Hotel</button>
            <div class="table-container">
                <table class="table" id="kandyTable">
                    <thead>
                        <tr>
                            <th>Hotel Name</th>
                            <th>Location</th>
                            <th>Description</th>
                            <th>Image</th>
                            <th>Website Link</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="kandyTableBody"></tbody>
                </table>
            </div>
        </div>
    </section>

    <div id="hotelModal">
        <div class="modal-content">
            <span id="closeModal" onclick="closeModal()">×</span>
            <h2 id="modalTitle">Add Hotel</h2>
            <form id="hotelForm" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" id="hotelId">
                <input type="hidden" name="existing_image" id="existingImage">
                <div class="form-group">
                    <label for="hotelName">Hotel</label>
                    <input type="text" id="hotelName" name="hotel_name" required>
                </div>
                <div class="form-group">
                    <label for="hotelLocation">Location</label>
                    <select id="hotelLocation" name="location" required>
                        <option value="">Select District</option>
                        <option value="Colombo">Colombo</option>
                        <option value="Galle">Galle</option>
                        <option value="Kandy">Kandy</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="hotelDescription">Description</label>
                    <textarea id="hotelDescription" name="description" required></textarea>
                </div>
                <div class="form-group">
                    <label for="hotelImage">Choose Image</label>
                    <input type="file" id="hotelImage" name="image" accept="image/*">
                    <img id="imagePreview" src="" alt="Image Preview">
                </div>
                <div class="form-group">
                    <label for="hotelWebsite">Website Link</label>
                    <input type="url" id="hotelWebsite" name="website_link" placeholder="e.g., https://www.example.com" required>
                </div>
                <button type="submit" class="submit-btn" id="submitBtn" name="add_hotel">Add Hotel</button>
            </form>
        </div>
    </div>

 

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        let hotels = <?php echo json_encode($hotels); ?>;
        let editHotelId = null;

        function renderTable(district) {
            const tableBody = document.getElementById(district.toLowerCase() + 'TableBody');
            tableBody.innerHTML = '';
            hotels.filter(h => h.location === district).forEach(hotel => {
                const row = document.createElement('tr');
                const imageCell = hotel.image ? `<img src="${hotel.image}" class="table-image" alt="${hotel.hotel_name}">` : `<span>No Image</span>`;
                row.innerHTML = `
                    <td>${hotel.hotel_name}</td>
                    <td>${hotel.location}</td>
                    <td>${hotel.description}</td>
                    <td>${imageCell}</td>
                    <td><a href="${hotel.website_link}" target="_blank">${hotel.website_link}</a></td>
                    <td>
                        <button class="action-btn edit" onclick="openModal('edit', ${hotel.id})">Edit</button>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="${hotel.id}">
                            <input type="hidden" name="delete_hotel" value="1">
                            <button type="submit" class="action-btn delete" onclick="return confirm('Are you sure you want to delete this hotel?')">Delete</button>
                        </form>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        }

        function renderAllTables() {
            ['Colombo', 'Galle', 'Kandy'].forEach(district => renderTable(district));
        }

        function openModal(mode, param) {
            const modal = document.getElementById('hotelModal');
            const modalTitle = document.getElementById('modalTitle');
            const submitBtn = document.getElementById('submitBtn');
            const form = document.getElementById('hotelForm');
            const imagePreview = document.getElementById('imagePreview');
            const locationSelect = document.getElementById('hotelLocation');

            if (mode === 'add') {
                modalTitle.textContent = 'Add Hotel';
                submitBtn.textContent = 'Add Hotel';
                submitBtn.name = 'add_hotel';
                form.reset();
                imagePreview.style.display = 'none';
                imagePreview.src = '';
                locationSelect.value = param || '';
                document.getElementById('hotelId').value = '';
                document.getElementById('existingImage').value = '';
                editHotelId = null;
            } else {
                modalTitle.textContent = 'Edit Hotel';
                submitBtn.textContent = 'Save Changes';
                submitBtn.name = 'edit_hotel';
                const hotel = hotels.find(h => h.id == param);
                document.getElementById('hotelId').value = hotel.id;
                document.getElementById('hotelName').value = hotel.hotel_name;
                document.getElementById('hotelLocation').value = hotel.location;
                document.getElementById('hotelDescription').value = hotel.description;
                document.getElementById('existingImage').value = hotel.image;
                document.getElementById('hotelWebsite').value = hotel.website_link;
                imagePreview.src = hotel.image;
                imagePreview.style.display = hotel.image ? 'block' : 'none';
                editHotelId = param;
            }

            modal.style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('hotelModal').style.display = 'none';
            document.getElementById('hotelForm').reset();
            document.getElementById('imagePreview').style.display = 'none';
            document.getElementById('imagePreview').src = '';
            editHotelId = null;
        }

        document.getElementById('hotelImage').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const imagePreview = document.getElementById('imagePreview');
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                imagePreview.style.display = 'none';
                imagePreview.src = '';
            }
        });

        document.getElementById('hotelModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        document.addEventListener('DOMContentLoaded', renderAllTables);
    </script>
</body>
</html>