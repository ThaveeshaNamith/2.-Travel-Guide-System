```php
   <?php
   header('Content-Type: application/json');
   include 'db_connect.php';

   try {
       $stmt = $pdo->query("SELECT name, category, description, image, link FROM items");
       $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
       echo json_encode($items);
   } catch (PDOException $e) {
       echo json_encode(['error' => 'Failed to fetch items: ' . $e->getMessage()]);
   }
   ?>
   ```