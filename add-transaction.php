<?php
session_start();
require_once 'connection.php';

// Check if the user is logged in, redirect to login page if not
if (!isset($_SESSION['username'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit('User not authorized');
}

// Retrieve JSON data from the POST body
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

// Extract transaction details from JSON data
$transactionNumber = isset($input['transactionNumber']) ? htmlspecialchars($input['transactionNumber']) : null;
$date = isset($input['date']) ? htmlspecialchars($input['date']) : null;
$category = isset($input['category']) ? htmlspecialchars($input['category']) : null;
$amount = isset($input['amount']) ? (float) str_replace('$', '', $input['amount']) : null; // Assuming amount comes as string with $

// Validate input (add more validation as needed)
if (!$transactionNumber || !$date || !$category || !$amount) {
    header("HTTP/1.1 400 Bad Request");
    exit('Missing or invalid parameters');
}

// Insert new transaction into database
$user_id = $_SESSION['user_id']; // Get user ID from session
$stmt = $pdo->prepare("INSERT INTO transactions (user_id, transaction_number, transaction_date, category, amount) VALUES (:user_id, :transaction_number, :transaction_date, :category, :amount)");
$result = $stmt->execute([
    'user_id' => $user_id,
    'transaction_number' => $transactionNumber,
    'transaction_date' => $date,
    'category' => $category,
    'amount' => $amount
]);

if ($result) {
    // Respond with success message
    header('Content-Type: application/json');
    echo json_encode(['message' => 'Transaction added successfully']);
} else {
    // Respond with error message
    header("HTTP/1.1 500 Internal Server Error");
    exit('Failed to add transaction');
}
?>
