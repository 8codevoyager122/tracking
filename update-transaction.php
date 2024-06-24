<?php
session_start();
require_once 'connection.php';

// Validate if user is logged in
if (!isset($_SESSION['username'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

// Read and decode JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['transactionId'])) {
    header("HTTP/1.1 400 Bad Request");
    exit();
}

$user_id = $_SESSION['user_id'];
$transactionId = $data['transactionId'];
$transactionNumber = $data['transactionNumber'];
$date = $data['date'];
$category = $data['category'];
$amount = $data['amount'];

// Update transaction in database
$stmt = $pdo->prepare("UPDATE transactions SET transaction_number = :transaction_number, transaction_date = :transaction_date, category = :category, amount = :amount WHERE id = :transaction_id AND user_id = :user_id");
$result = $stmt->execute([
    'transaction_number' => $transactionNumber,
    'transaction_date' => $date,
    'category' => $category,
    'amount' => $amount,
    'transaction_id' => $transactionId,
    'user_id' => $user_id
]);

if ($result) {
    http_response_code(200);
    echo json_encode(['message' => 'Transaction updated successfully']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update transaction']);
}
?>
