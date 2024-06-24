<?php
session_start();
require_once 'connection.php'; // Include your database connection script

// Check if the user is logged in, redirect to login page if not
if (!isset($_SESSION['username'])) {
    header("Location: /login.php"); // Adjust the path as per your login page location
    exit;
}

// Calculate total income for the current month
$user_id = $_SESSION['user_id'];
$currentMonth = date('m');
$currentYear = date('Y');

$stmt = $pdo->prepare("SELECT SUM(amount) AS total_income FROM transactions WHERE user_id = :user_id");
$stmt->execute(['user_id' => $user_id]);
$totalIncome = $stmt->fetchColumn();

// If total income is NULL (no transactions), set it to 0
$totalIncome = $totalIncome ? $totalIncome : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags and title -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Optional: Add your custom styles here */
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900">
    <div class="flex flex-col min-h-screen">
        <!-- Header section -->
        <header class="bg-white dark:bg-zinc-800 shadow-md">
            <div class="container mx-auto px-4 py-2 flex justify-between items-center">
                <h1 class="text-lg font-semibold">Dashboard</h1>
            </div>
        </header>
        
        <!-- Main content section -->
        <main class="container mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Total Income -->
                <div class="relative bg-green-100 dark:bg-green-800 rounded-lg shadow-md overflow-hidden">
                    <div class="h-40 md:h-56 overflow-hidden">
                        <h1 class="text-5xl justify-center items-center text-zinc-600 mx-16 my-16 text-gray-500 font-bold">$<?php echo number_format($totalIncome, 2); ?></h1>
                    </div>
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-green-600 dark:text-green-300">Income</h3>
                        <p class="text-sm text-zinc-600">Total income for the month: $<?php echo number_format($totalIncome, 2); ?></p>
                    </div>
                </div>

                <!-- Total Outcome -->
                <div class="relative bg-red-100 dark:bg-red-800 rounded-lg shadow-md overflow-hidden">
                    <div class="h-40 md:h-56 overflow-hidden">
                        <img src="https://placehold.co/400" alt="Outcome" class="w-full h-full object-cover" />
                    </div>
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-red-600 dark:text-red-300">Outcome</h3>
                        <p class="text-sm text-zinc-600">Total outcome for the month</p>
                    </div>
                </div>
            </div>
        </main>
        
        <!-- Navigation buttons -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <button id="dashboardButton" class="bg-green-500 text-white py-2 px-4 rounded shadow-md">Dashboard</button>
            <button id="transactionButton" class="bg-green-500 text-white py-2 px-4 rounded shadow-md">Transaction</button>
            <button id="categoriesButton" class="bg-green-500 text-white py-2 px-4 rounded shadow-md">Categories</button>
            <button id="reportsButton" class="bg-green-500 text-white py-2 px-4 rounded shadow-md">Reports</button>
            <button id="logoutButton" class="bg-green-500 text-white py-2 px-4 rounded shadow-md">Logout</button>
        </div>
    </div>

    <!-- JavaScript to handle button clicks -->
    <script>
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
            window.location.href = "#";
        });
        document.getElementById('logoutButton').addEventListener('click', function() {
            window.location.href = "logout.php";
        });
    </script>
</body>
</html>
