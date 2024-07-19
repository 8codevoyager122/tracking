<?php
session_start(); // Ensure the session is started

// Include your database connection or any necessary files
require_once 'connection.php';
// Check if the user is logged in, redirect to login page if not
if (!isset($_SESSION['username'])) {
    header("Location: /login.php"); // Adjust the path as per your login page location
    exit;
}

// Function to retrieve the username for the logged-in user
function getUsername($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = :user_id");
    $stmt->execute(['user_id' => $userId]);
    $user = $stmt->fetch();
    return $user ? $user['username'] : 'Unknown';
}

// Function to retrieve the user's time zone
function getUserTimeZone($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT time_zone FROM users WHERE id = :user_id");
    $stmt->execute(['user_id' => $userId]);
    $user = $stmt->fetch();
    return $user ? $user['time_zone'] : 'UTC'; // Default to 'UTC' if not set
}

// Function to generate a report for the logged-in user with a specific date range
function generateReport($startDate, $endDate) {
    global $pdo; // Assuming $pdo is your database connection object
    $userId = $_SESSION['user_id']; // Assuming user ID is stored in session
    $username = getUsername($userId);

    // Set the default time zone to the user's time zone
    date_default_timezone_set($userTimeZone);
    
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = :user_id AND transaction_date BETWEEN :start_date AND :end_date ORDER BY transaction_date DESC");
    $stmt->execute([
        'user_id' => $userId,
        'start_date' => $startDate,
        'end_date' => $endDate
    ]);
    $transactions = $stmt->fetchAll();

    // Example report generation logic without User ID
    $report = "Transaction Report for User: " . $username . " from " . $startDate . " to " . $endDate . " Generated at " . date('Y-m-d H:i:s') . "\n\n";
    foreach ($transactions as $transaction) {
        $report .= "Transaction Number: " . $transaction['transaction_number'] . ", Date: " . $transaction['transaction_date'] . ", Amount: $" . number_format($transaction['amount'], 2) . "\n";
    }

    return $report;
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Get the date range from the POST request
        $startDate = $_POST['start_date'];
        $endDate = $_POST['end_date'];

        // Generate the report content
        $reportContent = generateReport($startDate, $endDate);

        if ($_POST['action'] === 'generate') {
            // Output the report (display on the website)
            echo '<div class="container mx-auto px-4 py-8">';
            echo '<div class="bg-white dark:bg-zinc-800 shadow-md px-4 py-4 mb-4">';
            echo nl2br(htmlspecialchars($reportContent)); // Display report on the page
            echo '</div>';
            echo '<div class="flex justify-center mb-4">';
            echo '<a href="reports.php" class="report-button back-button">Back to Reports</a>'; // Add a back button
            echo '</div>';
            echo '</div>';
        } elseif ($_POST['action'] === 'download') {
            $filename = 'transaction_report_' . date('Y_m_d_H_i_s') . '.txt';
            
          if ($_POST['action'] === 'download') {
    // Ensure the correct timezone is set
    date_default_timezone_set(getUserTimeZone($_SESSION['user_id']));
    
    $filename = 'transaction_report_' . date('Y_m_d_H_i_s') . '.txt';

    // Force download the report
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo $reportContent;
}


        }
        exit; // End script execution after handling the action
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.7/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Custom styles */
        .report-button {
            background-color: #48bb78; /* Tailwind green-500 */
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem; /* rounded-lg */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 0.5rem;
        }
        .report-button:hover {
            background-color: #38a169; /* Tailwind green-600 */
        }
        .input-small {
            max-width: 200px; /* Adjust width as needed */
        }
        .input-group {
            display: flex;
            gap: 1rem; /* Space between input fields */
        }
        .button-large {
            padding: 0.5rem 1rem;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex flex-col min-h-screen">
        <header class="bg-white dark:bg-zinc-800 shadow-md">
            <div class="container mx-auto px-4 py-2 flex justify-between items-center">
                <h1 class="text-lg font-semibold">Reports</h1>
            </div>
        </header>
        <main class="container mx-auto px-4 py-8">
            <div class="flex flex-col items-center mb-8">
                <!-- Combined Form for generating and downloading reports -->
                <form action="reports.php" method="post">
                    <div class="input-group mb-4">
                        <div class="flex flex-col">
                            <label for="start_date" class="mb-2 font-semibold">Start Date:</label>
                            <input type="date" id="start_date" name="start_date" required class="border border-gray-300 p-2 rounded input-small">
                        </div>
                        <div class="flex flex-col">
                            <label for="end_date" class="mb-2 font-semibold">End Date:</label>
                            <input type="date" id="end_date" name="end_date" required class="border border-gray-300 p-2 rounded input-small">
                        </div>
                    </div>
                    <button type="submit" name="action" value="generate" class="report-button">Generate Report</button>
                    <button type="submit" name="action" value="download" class="report-button">Download Report</button>
                </form>
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
        // JavaScript to handle button clicks
        document.getElementById('dashboardButton').addEventListener('click', function() {
            window.location.href = "dashboard.php";
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
