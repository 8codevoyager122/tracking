<?php
session_start();
require_once 'connection.php';

// Check if the user is logged in, redirect to login page if not
if (!isset($_SESSION['username'])) {
    header("Location: /index.php"); // Adjust the path as per your login page location
    exit;
}

// Add new category
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['category'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $category = $data['category'];

    // Insert category into database
    $stmt = $pdo->prepare("INSERT INTO categories (user_id, category_name) VALUES (:user_id, :category)");
    $result = $stmt->execute(['user_id' => $user_id, 'category' => $category]);

    if ($result) {
        http_response_code(200);
        echo json_encode(['message' => 'Category added successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to add category']);
    }

    exit;
}

// Fetch categories for the logged-in user
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM categories WHERE user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <style>
        /* Optional: Add your custom styles here */
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900">
    <div class="flex flex-col min-h-screen">
        <header class="bg-white dark:bg-zinc-800 shadow-md">
            <div class="container mx-auto px-4 py-2 flex justify-between items-center">
                <h1 class="text-lg font-semibold">Categories</h1>
            </div>
        </header>
        <main class="container mx-auto px-4 py-8">
            <div class="flex justify-between mb-4">
                <input type="text" id="categoryInput" placeholder="Enter Category" class="border border-green-500 rounded-lg px-4 py-2">
                <button id="addCategoryBtn" class="bg-green-500 text-white px-6 py-3 rounded-lg">Add New Category</button>
            </div>
            <div id="categoryTable">
                <?php foreach ($categories as $category): ?>
                    <div class="bg-white dark:bg-zinc-700 flex justify-between items-center p-4 mb-2">
                        <span><?php echo htmlspecialchars($category['category_name']); ?></span>
                        <div>
                            <button class="deleteCategoryBtn bg-red-500 text-white px-4 py-2 rounded" data-category-id="<?php echo $category['id']; ?>">Delete</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
        <div class="bg-white dark:bg-zinc-800 shadow-md py-4">
            <div class="container mx-auto px-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                <button id="dashboardButton" class="bg-green-500 text-white py-2 px-4 rounded shadow-md">Dashboard</button>
                <button id="transactionButton" class="bg-green-500 text-white py-2 px-4 rounded shadow-md">Transaction</button>
                <button id="categoriesButton" class="bg-green-500 text-white py-2 px-4 rounded shadow-md">Categories</button>
                <button id="reportsButton" class="bg-green-500 text-white py-2 px-4 rounded shadow-md">Reports</button>
                <button id="logoutButton" class="bg-green-500 text-white py-2 px-4 rounded shadow-md">Logout</button>

            </div>
        </div>
    </div>

    <script>
        // Function to add a new category
        function addCategory(category) {
            fetch('categories.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ category })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to add category');
                }
                console.log('Category added successfully:', category);
                // Reload categories after adding (update the UI directly)
                const categoryTable = document.getElementById('categoryTable');
                categoryTable.innerHTML += `
                    <div class="bg-white dark:bg-zinc-700 flex justify-between items-center p-4 mb-2">
                        <span>${category}</span>
                        <div>
                            <button class="deleteCategoryBtn bg-red-500 text-white px-4 py-2 rounded">Delete</button>
                        </div>
                    </div>
                `;
            })
            .catch(error => console.error('Error adding category:', error));
        }

        // Add New Category button click event
        document.getElementById('addCategoryBtn').addEventListener('click', function() {
            const newCategory = document.getElementById('categoryInput').value.trim();
            if (newCategory !== '') {
                addCategory(newCategory);
                document.getElementById('categoryInput').value = ''; // Clear input after adding
            } else {
                console.error('Empty category name.');
            }
        });

        // Navigation buttons
        document.getElementById('dashboardButton').addEventListener('click', function() {
            window.location.href = "dashboard.php"; // Refreshes the current page
        });

        document.getElementById('transactionButton').addEventListener('click', function() {
            window.location.href = "transactions.php";
        });

        document.getElementById('categoriesButton').addEventListener('click', function() {
            window.location.href = "categories.php";
        });
        document.getElementById('logoutButton').addEventListener('click', function() {
            window.location.href = "logout.php";
        });

        document.getElementById('reportsButton').addEventListener('click', function() {
            window.location.href = "#";
        });

        // Example of handling delete button (event delegation)
        document.getElementById('categoryTable').addEventListener('click', function(event) {
            if (event.target.classList.contains('deleteCategoryBtn')) {
                const categoryId = event.target.getAttribute('data-category-id');
                if (confirm('Are you sure you want to delete this category?')) {
                    deleteCategory(categoryId);
                }
            }
        });

        // Function to delete a category
        function deleteCategory(categoryId) {
            fetch('delete-category.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ categoryId })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to delete category');
                }
                console.log('Category deleted successfully:', categoryId);
                // Reload categories after deletion (update the UI directly)
                const categoryToDelete = document.querySelector(`[data-category-id="${categoryId}"]`).closest('.flex');
                categoryToDelete.remove();
            })
            .catch(error => console.error('Error deleting category:', error));
        }
    </script>
</body>
</html>
