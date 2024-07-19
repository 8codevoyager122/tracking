<?php
session_start();
require_once 'connection.php'; // Adjust the path as per your file structure

// Check if the user is logged in, redirect to login page if not
if (!isset($_SESSION['username'])) {
    header("Location: /login.php"); // Adjust the path as per your login page location
    exit;
}

// Initialize variables
$Income = isset($_SESSION['Income']) ? floatval($_SESSION['Income']) : 0; // Manual income set by the user
$user_id = $_SESSION['user_id'];
$currentMonth = date('m');
$currentYear = date('Y');

// Get the start and end dates from the form submission if available
$startDate = isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-01');
$endDate = isset($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-t');

try {
    // Calculate total income from transactions
    $stmtIncome = $pdo->prepare("SELECT SUM(amount) AS total_income FROM transactions WHERE user_id = :user_id AND MONTH(transaction_date) = :month AND YEAR(transaction_date) = :year AND category = 'income'");
    $stmtIncome->execute(['user_id' => $user_id, 'month' => $currentMonth, 'year' => $currentYear]);
    $totalIncome = $stmtIncome->fetchColumn();
    $totalIncome = $totalIncome ? $totalIncome : 0;

    // Calculate total expenses from transactions
    $stmtExpenses = $pdo->prepare("SELECT SUM(amount) AS total_expenses FROM transactions WHERE user_id = :user_id AND MONTH(transaction_date) = :month AND YEAR(transaction_date) = :year AND category = 'expense'");
    $stmtExpenses->execute(['user_id' => $user_id, 'month' => $currentMonth, 'year' => $currentYear]);
    $totalExpenses = $stmtExpenses->fetchColumn();
    $totalExpenses = $totalExpenses ? $totalExpenses : 0;

    // Calculate total outcome (sum of transactions)
    $stmtOutcome = $pdo->prepare("SELECT SUM(amount) AS total_outcome FROM transactions WHERE user_id = :user_id AND MONTH(transaction_date) = :month AND YEAR(transaction_date) = :year");
    $stmtOutcome->execute(['user_id' => $user_id, 'month' => $currentMonth, 'year' => $currentYear]);
    $totalOutcome = $stmtOutcome->fetchColumn();
    $totalOutcome = $totalOutcome ? $totalOutcome : 0;

    // Add manual income to the total income
    $totalIncome += $Income;

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage(); // Display any SQL errors
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
                    <div class="absolute top-0 right-0 p-4">
                        <button id="addIncomeBtn" class="bg-green-500 text-white px-4 py-2 rounded">Add Income</button>
                    </div>
                    <div class="h-40 md:h-56 overflow-hidden">
                        <h1 id="totalIncome" class="text-5xl justify-center items-center text-zinc-600 mx-16 my-16 text-gray-500 font-bold">$<?php echo number_format($totalIncome, 2); ?></h1>
                    </div>
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-green-600 dark:text-green-300">Income</h3>
                        <p class="text-sm text-zinc-600">Total income for the month: $<?php echo number_format($totalIncome, 2); ?></p>
                    </div>
                </div>

                <!-- Total Outcome -->
                <div class="relative bg-red-100 dark:bg-red-800 rounded-lg shadow-md overflow-hidden">
                    <div class="h-40 md:h-56 overflow-hidden">
                        <h1 id="totalOutcome" class="text-5xl justify-center items-center text-zinc-600 mx-16 my-16 text-gray-500 font-bold">$<?php echo number_format($totalOutcome, 2); ?></h1>
                    </div>
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-red-600 dark:text-red-300">Outcome</h3>
                        <p class="text-sm text-zinc-600">Total expenses for the month: $<?php echo number_format($totalOutcome, 2); ?></p>
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
                window.location.href = "reports.php";
            });

            document.getElementById('logoutButton').addEventListener('click', function() {
                window.location.href = "logout.php";
            });

// Handle Add Manual Income button click
document.getElementById('addIncomeBtn').addEventListener('click', function() {
    const Income = prompt('Enter income amount:');
    if (Income) {
        document.getElementById('totalIncome').innerText = '$' + parseFloat(Income).toFixed(2);

        // Make the AJAX request to update the server-side data
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                Income: Income
            }),
        })
     .then(response => response.json())
     .then(data => {
            if (data.error) {
                alert(data.error);
            } else {
                alert(data.message);
            }
        })
     .catch((error) => {
            console.error('Error:', error);
        });
    }
});
        </script>
    </div>
</body>
</html>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['Income'])) {
        $Income = floatval($data['Income']);
        $_SESSION['Income'] = $Income;

        // Recalculate the total income and expenses with the new manual income
        try {
            $stmtIncome = $pdo->prepare("SELECT SUM(amount) AS total_income FROM transactions WHERE user_id = :user_id AND MONTH(transaction_date) = :month AND YEAR(transaction_date) = :year AND category = 'income'");
            $stmtIncome->execute(['user_id' => $user_id, 'month' => $currentMonth, 'year' => $currentYear]);
            $totalIncome = $stmtIncome->fetchColumn();
            $totalIncome = $totalIncome ? $totalIncome : 0;
            $totalIncome += $Income;

            // Recalculate total expenses
            $stmtExpenses = $pdo->prepare("SELECT SUM(amount) AS total_expenses FROM transactions WHERE user_id = :user_id AND MONTH(transaction_date) = :month AND YEAR(transaction_date) = :year AND category = 'expense'");
            $stmtExpenses->execute(['user_id' => $user_id, 'month' => $currentMonth, 'year' => $currentYear]);
            $totalExpenses = $stmtExpenses->fetchColumn();
            $totalExpenses = $totalExpenses ? $totalExpenses : 0;

            // Calculate total outcome (sum of transactions)
            $stmtOutcome = $pdo->prepare("SELECT SUM(amount) AS total_outcome FROM transactions WHERE user_id = :user_id AND MONTH(transaction_date) = :month AND YEAR(transaction_date) = :year");
            $stmtOutcome->execute(['user_id' => $user_id, 'month' => $currentMonth, 'year' => $currentYear]);
            $totalOutcome = $stmtOutcome->fetchColumn();
            $totalOutcome = $totalOutcome ? $totalOutcome : 0;
            $totalOutcome += $Income;

            echo json_encode(['message' => 'Income updated successfully', 'totalIncome' => $totalIncome, 'totalOutcome' => $totalOutcome]);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['error' => 'Invalid request']);
    }
    exit;
}
?>
