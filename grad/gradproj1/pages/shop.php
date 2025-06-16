<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plant Store | Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <script src="https://kit.fontawesome.com/5e15fb3188.js" crossorigin="anonymous"></script>
</head>
<body class="bg-green-50 text-gray-900">
   

    <div class="container mx-auto my-10 px-6">
        <h2 class="text-center text-4xl font-bold text-green-700 mb-8">Shop Plants</h2>
        
        <!-- Search and Filter Section -->
        <div class="flex flex-col md:flex-row justify-center gap-4 mb-8">
            <input type="text" id="search" class="w-full md:w-1/3 p-3 border-2 border-green-300 rounded-lg focus:ring focus:ring-green-400" placeholder="Search plants...">
            <select id="filter" class="w-full md:w-1/3 p-3 border-2 border-green-300 rounded-lg focus:ring focus:ring-green-400">
                <option value="">All Categories</option>
                <option value="indoor">Indoor</option>
                <option value="outdoor">Outdoor</option>
                <option value="succulents">Succulents</option>
            </select>
        </div>
        
        <!-- Plants Grid -->
       <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="plant-list">
    <?php
    include '../config.php';
    $query = "SELECT * FROM items";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($result as $row):
    ?>
    <div class="plant-item bg-white rounded-lg shadow-lg p-4 border border-green-200 transition-transform transform hover:scale-105 hover:shadow-xl flex flex-col"
         data-category="<?php echo strtolower($row['Category']); ?>">
        <!-- Image Container -->
        <div class="image-container">
            <img src="../uploads/<?php echo $row['image']; ?>" alt="<?php echo $row['Name']; ?>" class="w-full h-auto">
        </div>

        <!-- Content Container with Flex-grow -->
        <div class="flex flex-col flex-grow text-center mt-4">
            <h5 class="text-xl font-semibold text-green-800"><?php echo $row['Name']; ?></h5>
            <p class="text-gray-600 flex-grow"><?php echo $row['Description']; ?></p>

            <!-- View Details Button Stays at Bottom -->
            <div class="mt-auto pt-4">
                <a href="shop.php?item=<?php echo $row['id']; ?>" 
                   class="inline-block px-5 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition">
                   View Details
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
</div>
<script>
    // Debugging: Log initial plant categories
    document.querySelectorAll('.plant-item').forEach(item => {
        console.log('Plant Category:', item.getAttribute('data-category'));
    });

    // Filter by category
    document.getElementById('filter')?.addEventListener('change', function() {
        let category = this.value.toLowerCase();
        console.log('Selected Category:', category);

        document.querySelectorAll('.plant-item').forEach(item => {
            let itemCategory = item.getAttribute('data-category')?.toLowerCase() || ''; // Avoid null errors
            console.log('Item Category:', itemCategory);

            item.style.display = (category === '' || itemCategory === category) ? 'flex' : 'none';
        });
    });

    // Live search functionality
    document.getElementById('search')?.addEventListener('input', function() {
        let searchTerm = this.value.toLowerCase();
        console.log('Search Term:', searchTerm);

        document.querySelectorAll('.plant-item').forEach(item => {
            let plantName = item.querySelector('h5')?.textContent.toLowerCase() || ''; // Avoid null errors
            item.style.display = plantName.includes(searchTerm) ? 'flex' : 'none';
        });
    });

    </script>
    
  <!-- Chatbot Box -->
<!-- Chatbot Box -->
<div id="chatbot-box" class="fixed bottom-5 right-5 w-80 bg-white shadow-lg rounded-lg overflow-hidden border border-gray-300 transform scale-0 transition-transform duration-300 origin-bottom-right">
    <div class="bg-green-600 text-white p-3 font-bold flex justify-between items-center">
        Warda AI Assistant
        <button id="chatbot-close" class="hidden text-white text-lg font-bold"><i class="fa-solid fa-x"></i></button>
    </div>
    <div id="chatbot-messages" class="p-3 h-64 overflow-y-auto text-gray-800"></div>
    <div class="p-3 border-t">
        <input type="text" id="chatbot-input" class="w-full p-2 border rounded" placeholder="Ask about plants...">
        <button id="chatbot-send" class="w-full mt-2 bg-green-500 text-white p-2 rounded">Send</button>
    </div>
</div>

<!-- Chatbot Toggle Button -->
<button id="chatbot-toggle" class="fixed bottom-5 right-5 bg-green-600 text-white px-4 py-2 rounded-full shadow-lg transition-transform duration-300 ease-in-out">
    💬 Ask Warda
</button>

<script>
document.getElementById('chatbot-toggle').addEventListener('click', function() {
    let chatbox = document.getElementById('chatbot-box');
    let toggleButton = document.getElementById('chatbot-toggle');
    let closeButton = document.getElementById('chatbot-close');

    if (chatbox.classList.contains('scale-0')) {
        // Show Chatbox and merge button
        chatbox.classList.remove('scale-0');
        toggleButton.classList.add('scale-0');
        setTimeout(() => {
            closeButton.classList.remove('hidden');
        }, 300);
    }
});

document.getElementById('chatbot-close').addEventListener('click', function() {
    let chatbox = document.getElementById('chatbot-box');
    let toggleButton = document.getElementById('chatbot-toggle');
    let closeButton = document.getElementById('chatbot-close');

    chatbox.classList.add('scale-0');
    closeButton.classList.add('hidden');
    setTimeout(() => {
        toggleButton.classList.remove('scale-0');
    }, 300);
});

document.getElementById('chatbot-send').addEventListener('click', function() {
    let input = document.getElementById('chatbot-input');
    let message = input.value.trim();
    
    if (message === '') return;

    let chatMessages = document.getElementById('chatbot-messages');
    chatMessages.innerHTML += `<div class="text-right mb-2"><strong>You:</strong> ${message}</div>`;
    input.value = '';

    fetch('chatbot.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'query=' + encodeURIComponent(message)
    })
    .then(response => response.json())
    .then(data => {
        chatMessages.innerHTML += `<div class="text-left mb-2"><strong>Warda:</strong> ${data.response}</div>`;
        chatMessages.scrollTop = chatMessages.scrollHeight;
    });
});
</script>
</body>
</html>