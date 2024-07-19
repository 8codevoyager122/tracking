<?php
session_start();
require_once 'connection.php';

// Check if the user is logged in, redirect to login page if not
if (!isset($_SESSION['username'])) {
    header("Location: /login.php"); // Adjust the path as per your login page location
    exit;
}

// Handle form submissions (Add, Edit, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Add new category
    if (isset($data['action']) && $data['action'] === 'addCategory') {
        if (!isset($data['category'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid request']);
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $category = $data['category'];

        // Check if category already exists for the user
        $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM categories WHERE user_id = :user_id AND category_name = :category");
        $stmt->execute(['user_id' => $user_id, 'category' => $category]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Category already exists']);
            exit;
        }

        // Insert category into database
        $stmt = $pdo->prepare("INSERT INTO categories (user_id, category_name) VALUES (:user_id, :category)");
        $result = $stmt->execute(['user_id' => $user_id, 'category' => $category]);

        if ($result) {
            http_response_code(200);
            echo json_encode(['message' => 'Category added successfully', 'categoryId' => $pdo->lastInsertId()]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to add category']);
        }

        exit;
    }

    // Update category
    if (isset($data['action']) && $data['action'] === 'editCategory') {
        if (!isset($data['categoryId']) || !isset($data['category'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid request']);
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $category_id = $data['categoryId'];
        $new_category_name = trim($data['category']);

        if (empty($new_category_name)) {
            http_response_code(400);
            echo json_encode(['error' => 'Category name cannot be empty']);
            exit;
        }

        // Check if category already exists for the user
        $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM categories WHERE user_id = :user_id AND category_name = :category AND id != :category_id");
        $stmt->execute(['user_id' => $user_id, 'category' => $new_category_name, 'category_id' => $category_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Category already exists']);
            exit;
        }

        // Update category in database
        $stmt = $pdo->prepare("UPDATE categories SET category_name = :category WHERE id = :category_id AND user_id = :user_id");
        $result = $stmt->execute(['category' => $new_category_name, 'category_id' => $category_id, 'user_id' => $user_id]);

        if ($result) {
            http_response_code(200);
            echo json_encode(['message' => 'Category updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update category']);
        }

        exit;
    }

    // Delete category
    if (isset($data['action']) && $data['action'] === 'deleteCategory') {
        if (!isset($data['categoryId'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid request']);
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $category_id = $data['categoryId'];

        // Delete category from database
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :category_id AND user_id = :user_id");
        $result = $stmt->execute(['category_id' => $category_id, 'user_id' => $user_id]);

        if ($result) {
            http_response_code(200);
            echo json_encode(['message' => 'Category deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete category']);
        }

        exit;
    }
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
                <input type="text" id="categoryInput" placeholder="Search Category" class="border border-green-500 rounded-lg px-4 py-2">
                <button id="addCategoryBtn" class="bg-green-500 text-white px-6 py-3 rounded-lg">Add New Category</button>
            </div>
            <div id="categoryTable">
                <?php foreach ($categories as $category): ?>
                    <div class="category-item bg-white dark:bg-zinc-700 flex justify-between items-center p-4 mb-2">
                        <span><?php echo htmlspecialchars($category['category_name']); ?></span>
                        <div>
                            <button class="editCategoryBtn bg-blue-500 text-white px-4 py-2 rounded" data-category-id="<?php echo $category['id']; ?>">Edit</button>
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

    <!-- Edit Category Modal -->
    <div id="editCategoryModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white p-8 rounded-lg max-w-md">
            <h2 class="text-lg font-semibold mb-4">Edit Category</h2>
            <input type="hidden" id="editCategoryId">
            <input type="text" id="editCategoryInput" placeholder="Enter Category Name" class="border border-gray-300 rounded-lg px-4 py-2 mb-4">
            <div class="flex justify-end">
                <button id="updateCategoryBtn" class="bg-green-500 text-white px-4 py-2 rounded mr-2">Update</button>
                <button id="cancelEditCategoryBtn" class="bg-gray-300 text-gray-800 px-4 py-2 rounded">Cancel</button>
            </div>
        </div>
    </div>

    <script>
// Function to add a new category
function addCategory(category) {
    // Check if the category already exists
    const categories = document.querySelectorAll('.category-item span');
    const existingCategories = Array.prototype.map.call(categories, function(item) {
        return item.textContent.toLowerCase();
    });

    if (existingCategories.includes(category.toLowerCase())) {
        alert('Category already exists');
        return;
    }

    fetch('categories.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'addCategory',
            category: category
        }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
        } else {
            alert(data.message);
            // Refresh the page to show the new category
            location.reload();
        }
    })
    .catch((error) => {
        console.error('Error:', error);
    });
}

    // Function to update a category
    function updateCategory(categoryId, category) {
        fetch('categories.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'editCategory',
                categoryId: categoryId,
                category: category
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
            } else {
                alert(data.message);
                // Refresh the page to show the updated category
                location.reload();
            }
        })
        .catch((error) => {
            console.error('Error:', error);
        });
    }

    // Function to delete a category
    function deleteCategory(categoryId) {
        fetch('categories.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'deleteCategory',
                categoryId: categoryId
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
            } else {
                alert(data.message);
                // Refresh the page to show the remaining categories
                location.reload();
            }
        })
        .catch((error) => {
            console.error('Error:', error);
        });
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', () => {
        const addCategoryBtn = document.getElementById('addCategoryBtn');
        const categoryInput = document.getElementById('categoryInput');

        addCategoryBtn.addEventListener('click', () => {
            const category = prompt('Enter category name:');
            if (category) {
                addCategory(category);
            }
        });

        categoryInput.addEventListener('keypress', (event) => {
            if (event.key === 'Enter') {
                const category = categoryInput.value.trim();
                if (category) {
                    addCategory(category);
                }
            }
        });

        document.querySelectorAll('.editCategoryBtn').forEach(button => {
            button.addEventListener('click', () => {
                const categoryId = button.getAttribute('data-category-id');
                const currentCategory = button.closest('.category-item').querySelector('span').textContent;
                document.getElementById('editCategoryId').value = categoryId;
                document.getElementById('editCategoryInput').value = currentCategory;
                document.getElementById('editCategoryModal').classList.remove('hidden');
            });
        });

        document.querySelectorAll('.deleteCategoryBtn').forEach(button => {
            button.addEventListener('click', () => {
                const categoryId = button.getAttribute('data-category-id');
                if (confirm('Are you sure you want to delete this category?')) {
                    deleteCategory(categoryId);
                }
            });
        });

        document.getElementById('updateCategoryBtn').addEventListener('click', () => {
            const categoryId = document.getElementById('editCategoryId').value;
            const category = document.getElementById('editCategoryInput').value;
            if (category) {
                updateCategory(categoryId, category);
                document.getElementById('editCategoryModal').classList.add('hidden');
            } else {
                alert('Category name cannot be empty');
            }
        });

        document.getElementById('cancelEditCategoryBtn').addEventListener('click', () => {
            document.getElementById('editCategoryModal').classList.add('hidden');
        });

        document.getElementById('editCategoryInput').addEventListener('keypress', (event) => {
            if (event.key === 'Enter') {
                const categoryId = document.getElementById('editCategoryId').value;
                const category = document.getElementById('editCategoryInput').value;
                if (category) {
                    updateCategory(categoryId, category);
                    document.getElementById('editCategoryModal').classList.add('hidden');
                } else {
                    alert('Category name cannot be empty');
                }
            }
        });

        // Category search functionality
        const categoryTable = document.getElementById('categoryTable');
        categoryInput.addEventListener('input', () => {
            const filter = categoryInput.value.toLowerCase();
            const categories = categoryTable.querySelectorAll('.category-item');
            categories.forEach(category => {
                const categoryName = category.querySelector('span').textContent.toLowerCase();
                if (categoryName.includes(filter)) {
                    category.style.display = '';
                } else {
                    category.style.display = 'none';
                }
            });
        });
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

    document.getElementById('reportsButton').addEventListener('click', function() {
        window.location.href = "reports.php";
    });

    document.getElementById('logoutButton').addEventListener('click', function() {
        window.location.href = "logout.php";
    });
</script>

</body>
</html>
