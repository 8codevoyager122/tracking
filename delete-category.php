<?php
session_start();
require_once 'connection.php';

// Check if the user is logged in, redirect to login page if not
if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Delete category
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['categoryId'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $category_id = $data['categoryId'];

    // Check if the category belongs to the logged-in user
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = :category_id AND user_id = :user_id");
    $stmt->execute(['category_id' => $category_id, 'user_id' => $user_id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$category) {
        http_response_code(404);
        echo json_encode(['error' => 'Category not found']);
        exit;
    }

    // Delete category from database
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :category_id");
    $result = $stmt->execute(['category_id' => $category_id]);

    if ($result) {
        http_response_code(200);
        echo json_encode(['message' => 'Category deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete category']);
    }

    exit;
}

// Handle invalid method
http_response_code(405);
echo json_encode(['error' => 'Method Not Allowed']);
