<?php
session_start();
require_once 'connection.php';

// Check if the user is logged in, redirect to login page if not
if (!isset($_SESSION['username'])) {
    header("Location: /index.php"); // Adjust the path as per your login page location
    exit;
}

// Fetch transactions for the logged-in user
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = :user_id ORDER BY transaction_date DESC");
$stmt->execute(['user_id' => $user_id]);
$transactions = $stmt->fetchAll();

// Fetch categories for the logged-in user
$stmt_categories = $pdo->prepare("SELECT * FROM categories WHERE user_id = :user_id");
$stmt_categories->execute(['user_id' => $user_id]);
$categories = $stmt_categories->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.7/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Optional: Your custom styles can be added here */
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex flex-col min-h-screen">
        <header class="bg-white dark:bg-zinc-800 shadow-md">
            <div class="container mx-auto px-4 py-2 flex justify-between items-center">
                <h1 class="text-lg font-semibold">Transactions</h1>
            </div>
        </header>
        <main class="container mx-auto px-4 py-8">
            <div class="flex justify-between mb-4">
                <!-- Add Transaction Modal Trigger Button -->
                <button id="addTransactionBtn" class="bg-green-500 text-white px-6 py-3 rounded-lg">Add New Transaction</button>
                <input type="text" id="searchTransactionInput" placeholder="Search Transaction" class="border border-green-500 rounded-lg px-4 py-2">
            </div>
            <table class="table-auto w-full text-center mb-8">
                <thead>
                    <tr class="bg-green-500 text-white">
                        <th class="p-2">Transaction Number</th>
                        <th class="p-2">Date</th>
                        <th class="p-2">Category</th>
                        <th class="p-2">Amount</th>
                        <th class="p-2">Edit</th>
                        <th class="p-2">Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr class="bg-white dark:bg-zinc-700">
                            <td class="p-2"><?php echo htmlspecialchars($transaction['transaction_number']); ?></td>
                            <td class="p-2"><?php echo htmlspecialchars($transaction['transaction_date']); ?></td>
                            <td class="p-2"><?php echo htmlspecialchars($transaction['category']); ?></td>
                            <td class="p-2">$<?php echo number_format($transaction['amount'], 2); ?></td>
                            <td class="p-2"><button class="bg-blue-500 text-white px-4 py-2 rounded edit-btn" data-transaction-id="<?php echo $transaction['id']; ?>">Edit</button></td>
                            <td class="p-2"><button class="bg-red-500 text-white px-4 py-2 rounded delete-btn" data-transaction-id="<?php echo $transaction['id']; ?>">Delete</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
        <div class="bg-white dark:bg-zinc-800 shadow-md py-4">
            <div class="container mx-auto px-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                <button id="dashboardBtn" class="bg-green-500 text-white py-2 px-4 rounded shadow-md">Dashboard</button>
                <button id="transactionBtn" class="bg-green-500 text-white py-2 px-4 rounded shadow-md">Transaction</button>
                <button id="categoriesBtn" class="bg-green-500 text-white py-2 px-4 rounded shadow-md">Categories</button>
                <button id="reportsBtn" class="bg-green-500 text-white py-2 px-4 rounded shadow-md">Reports</button>
                <button id="logoutBtn" class="bg-green-500 text-white py-2 px-4 rounded shadow-md">Logout</button>

            </div>
        </div>
    </div>

    <!-- Modal for Adding and Editing Transaction -->
    <div id="transactionModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden">
        <div class="bg-white p-8 rounded-lg w-96">
            <h2 id="modalTitle" class="text-lg font-semibold mb-4">Add New Transaction</h2>
            <form id="transactionForm">
                <input type="hidden" id="transactionId" name="transactionId">
                <div class="mb-4">
                    <label for="transactionNumber" class="block text-sm font-medium text-gray-700">Transaction Number</label>
                    <input type="number" id="transactionNumber" name="transactionNumber" class="border border-gray-300 rounded-lg px-3 py-2 w-full">
                </div>
                <div class="mb-4">
                    <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                    <input type="date" id="date" name="date" class="border border-gray-300 rounded-lg px-3 py-2 w-full">
                </div>
                <div class="mb-4">
                <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                <select id="category" name="category" class="border border-gray-300 rounded-lg px-3 py-2 w-full">
                <option value="unknown">Unknown</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['category_name']); ?>">
                            <?php echo htmlspecialchars($category['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
                <div class="mb-4">
                    <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
                    <input type="number" id="amount" name="amount" class="border border-gray-300 rounded-lg px-3 py-2 w-full">
                </div>
                <div class="flex justify-end">
                    <button type="submit" id="saveTransactionBtn" class="bg-green-500 text-white px-6 py-2 rounded-lg">Save Transaction</button>
                    <button type="button" id="closeModal" class="bg-red-500 text-white px-6 py-2 rounded-lg ml-4">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <script>
                document.getElementById('dashboardBtn').addEventListener('click', function() {
            window.location.href = "dashboard.php"; // Refreshes the current page
        });

        document.getElementById('transactionBtn').addEventListener('click', function() {
            window.location.href = "transactions.php";
        });

        document.getElementById('categoriesBtn').addEventListener('click', function() {
            window.location.href = "categories.php";
        });

        document.getElementById('reportsBtn').addEventListener('click', function() {
            window.location.href = "#";
        });
        document.getElementById('logoutBtn').addEventListener('click', function() {
            window.location.href = "logout.php";
        });
    // JavaScript to handle modal and form submission
    document.getElementById('addTransactionBtn').addEventListener('click', function() {
        document.getElementById('modalTitle').textContent = 'Add New Transaction';
        document.getElementById('transactionId').value = '';
        document.getElementById('transactionNumber').value = '';
        document.getElementById('date').value = '';
        document.getElementById('category').value = '';
        document.getElementById('amount').value = '';
        document.getElementById('transactionModal').classList.remove('hidden');
    });

    document.getElementById('closeModal').addEventListener('click', function() {
        document.getElementById('transactionModal').classList.add('hidden');
    });

    // Handle form submission for both add and edit
    document.getElementById('transactionForm').addEventListener('submit', function(event) {
        event.preventDefault();
        var transactionId = document.getElementById('transactionId').value;
        var url = transactionId ? '/update-transaction.php' : '/add-transaction.php';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                transactionId: transactionId,
                transactionNumber: document.getElementById('transactionNumber').value,
                date: document.getElementById('date').value,
                category: document.getElementById('category').value,
                amount: document.getElementById('amount').value
            })
        })
        .then(response => {
            if (response.ok) {
                alert('Transaction saved successfully!');
                location.reload(); // Reload the page to fetch updated transactions
            } else {
                throw new Error('Failed to save transaction');
            }
        })
        .catch(error => {
            console.error('Error saving transaction:', error);
            alert('Failed to save transaction');
        });
    });

    // Handle edit button clicks
    document.querySelectorAll('.edit-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            var transactionId = this.getAttribute('data-transaction-id');
            document.getElementById('modalTitle').textContent = 'Edit Transaction';
            document.getElementById('transactionId').value = transactionId;

            // Example: Prefill form fields with existing transaction data for editing
            var transaction = findTransactionById(transactionId); // Replace with actual logic to fetch transaction details
            if (transaction) {
                document.getElementById('transactionNumber').value = transaction.transaction_number;
                document.getElementById('date').value = transaction.transaction_date;
                document.getElementById('category').value = transaction.category;
                document.getElementById('amount').value = transaction.amount;
                document.getElementById('transactionModal').classList.remove('hidden');
            } else {
                alert('Transaction not found!');
            }
        });
    });

    // Handle delete button clicks
    document.querySelectorAll('.delete-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            var transactionId = this.getAttribute('data-transaction-id');
            if (confirm('Are you sure you want to delete this transaction?')) {
                fetch('/delete-transaction.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        transactionId: transactionId
                    })
                })
                .then(response => {
                    if (response.ok) {
                        alert('Transaction deleted successfully!');
                        location.reload(); // Reload the page to fetch updated transactions
                    } else {
                        throw new Error('Failed to delete transaction');
                    }
                })
                .catch(error => {
                    console.error('Error deleting transaction:', error);
                    alert('Failed to delete transaction');
                });
            }
        });
    });

    // Example function to find transaction details by ID (replace with actual logic)
    function findTransactionById(transactionId) {
        // Example: Loop through transactions array to find the specific transaction
        var transactions = <?php echo json_encode($transactions); ?>;
        return transactions.find(transaction => transaction.id == transactionId);
    }
</script>
