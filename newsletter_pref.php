<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header('Location: login_and_signup/login.php?redirect=newsletter_pref.php');
    exit;
}

// Get user information
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newsletter = isset($_POST['newsletter']) && $_POST['newsletter'] === 'yes' ? 1 : 0;
    
    try {
        // Update user preferences
        $stmt = $conn->prepare("UPDATE users SET newsletter = :newsletter WHERE id = :id");
        $stmt->bindParam(':newsletter', $newsletter);
        $stmt->bindParam(':id', $_SESSION['user_id']);
        $stmt->execute();
        
        // Set success message
        $success = "Your newsletter preferences have been updated.";
        
        // Update local user data
        $user['newsletter'] = $newsletter;
        
    } catch (Exception $e) {
        $error = "There was a problem updating your preferences. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter Preferences - TUKOLE Business</title>
    <link rel="stylesheet" href="assets/css/tailwind.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/b27d0ab5e4.js" crossorigin="anonymous"></script>
</head>
<body class="bg-gray-100">
    <?php include 'header.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center mb-6">
            <a href="myAccount.php" class="text-blue-500 hover:underline mr-2">
                <i class="fas fa-arrow-left"></i> Back to My Account
            </a>
            <h1 class="text-2xl font-bold">Newsletter Preferences</h1>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 mb-6 rounded relative" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"> <?php echo $error; ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 mb-6 rounded relative" role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline"> <?php echo $success; ?></span>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-lg font-medium">Email Subscription</h2>
            </div>
            
            <form action="newsletter_pref.php" method="post" class="p-6">
                <p class="text-gray-600 mb-6">
                    Subscribe to our newsletter to receive updates on our latest offers, new products, and promotions.
                    You can unsubscribe at any time.
                </p>
                
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input 
                                id="newsletter-yes" 
                                name="newsletter" 
                                type="radio" 
                                value="yes" 
                                <?php echo (isset($user['newsletter']) && $user['newsletter']) ? 'checked' : ''; ?>
                                class="h-4 w-4 text-blue-500 focus:ring-blue-500 border-gray-300"
                            >
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="newsletter-yes" class="font-medium text-gray-700">Yes, I want to receive daily newsletter</label>
                            <p class="text-gray-500">Get the latest deals, product news, and exclusive offers.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input 
                                id="newsletter-no" 
                                name="newsletter" 
                                type="radio" 
                                value="no" 
                                <?php echo (!isset($user['newsletter']) || !$user['newsletter']) ? 'checked' : ''; ?>
                                class="h-4 w-4 text-blue-500 focus:ring-blue-500 border-gray-300"
                            >
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="newsletter-no" class="font-medium text-gray-700">No, I don't want to receive the daily newsletter</label>
                            <p class="text-gray-500">You will still receive important updates related to your account and orders.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start mt-6">
                        <div class="flex items-center h-5">
                            <input 
                                id="terms" 
                                name="terms" 
                                type="checkbox" 
                                required
                                class="h-4 w-4 text-blue-500 focus:ring-blue-500 border-gray-300 rounded"
                            >
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="terms" class="font-medium text-gray-700">I agree to the terms and privacy policy</label>
                            <p class="text-gray-500">By saving these preferences, you agree to our Terms of Service and Privacy Policy.</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6">
                    <button 
                        type="submit" 
                        class="bg-blue-500 border border-transparent rounded-md shadow-sm py-2 px-4 text-sm font-medium text-white hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Save Preferences
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
