<?php
include 'config.php'; // Include the PDO connection
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plant Store | Home</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/styles.css">
    
    
    <style>
        .image-container {
            width: 100%;
            height: 256px; /* Adjust as needed */
            overflow: hidden;
            border-radius: 0.5rem; /* Match your rounded-lg */
        }
        
        .image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Ensures the image covers the container */
            object-position: center; /* Centers the image */
        }
    </style>
</head>
<body class="bg-green-50 text-gray-900">
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <header class="relative w-full h-screen flex items-center justify-center bg-cover bg-center" style="background-image: url('assets/images/hero.jpg');">
        <div class="absolute inset-0 bg-black bg-opacity-40"></div>
        <div class="relative text-center text-white z-10">
            <h1 class="text-5xl font-bold">Welcome to Your Green Haven</h1>
            <p class="mt-4 text-lg">Discover the best plants for your home and garden</p>
            <a href="pages/shop.php" class="mt-6 inline-block px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">Shop Now</a>
        </div>
    </header>

    <!-- Featured Plants -->
    <section class="container mx-auto my-10 px-6">
        <h2 class="text-center text-4xl font-bold text-green-700 mb-8">Featured Plants</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
                try {
                    // Fetch featured items using PDO
                    $query = "SELECT * FROM items WHERE featured = 1 LIMIT 6";
                    $stmt = $conn->prepare($query);
                    $stmt->execute();
                    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Loop through the items and display them
                    foreach ($items as $row):
            ?>
            <div class="bg-white rounded-lg shadow-lg p-4 border border-green-200 transition-transform transform hover:scale-105 hover:shadow-xl">
                <!-- Image Container -->
                <div class="image-container">
                    <img src="./uploads/<?php echo $row['image']; ?>" alt="<?php echo $row['Name']; ?>">
                </div>
                <div class="text-center mt-4">
                    <h5 class="text-xl font-semibold text-green-800"><?php echo $row['Name']; ?></h5>
                    <p class="text-gray-600"><?php echo $row['Description']; ?></p>
                    <a href="pages/shop.php?item=<?php echo $row['id']; ?>" 
                       class="mt-3 inline-block px-5 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                       View Details
                    </a>
                </div>
            </div>

            <?php
                    endforeach;
                } catch (PDOException $e) {
                    echo "Error fetching items: " . $e->getMessage();
                }
            ?>
        </div>
    </section>
</body>
</html>