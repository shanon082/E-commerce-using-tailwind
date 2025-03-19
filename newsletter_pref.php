<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!checkUserSession()) {
    header("Location: login_and_signup/login.php");
    exit;
}

// Get user data
$userData = getUserData($conn);

// Initialize variables
$subscribed = false;

// Check if the user has newsletter preferences saved
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userData['id']]);
$user = $stmt->fetch();

if ($user && isset($user['newsletter_subscribed'])) {
    $subscribed = $user['newsletter_subscribed'] == 1;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $wantsNewsletter = isset($_POST['newsletter']) && $_POST['newsletter'] == 'yes' ? 1 : 0;
    $agreeToTerms = isset($_POST['terms']);
    
    // Validate
    $errors = [];
    
    if (!$agreeToTerms) {
        $errors[] = 'You must agree to the terms and conditions.';
    }
    
    // If no errors, save preferences
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE users SET newsletter_subscribed = ? WHERE id = ?");
        $stmt->execute([$wantsNewsletter, $userData['id']]);
        
        // Refresh subscribed status
        $subscribed = $wantsNewsletter == 1;
        
        // Set success message
        $success = "Your newsletter preferences have been updated successfully!";
    }
}
?>

<?php include 'header.php'; ?>

<!-- Breadcrumb -->
<div class="bg-gray-100 py-3">
    <div class="container mx-auto px-4">
        <nav class="flex text-sm">
            <a href="index.php" class="text-gray-600 hover:text-jumia-orange">Home</a>
            <span class="mx-2 text-gray-400">/</span>
            <a href="myAccount.php" class="text-gray-600 hover:text-jumia-orange">My Account</a>
            <span class="mx-2 text-gray-400">/</span>
            <span class="text-gray-800 font-medium">Newsletter Preferences</span>
        </nav>
    </div>
</div>

<!-- Newsletter Preferences Section -->
<section class="py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                    <h2 class="font-semibold text-lg text-gray-800">Newsletter Preferences</h2>
                    <a href="myAccount.php" class="text-jumia-orange hover:underline text-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                </div>
                
                <div class="p-6">
                    <?php if (!empty($errors)): ?>
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
                            <ul class="list-disc pl-4">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($success)): ?>
                        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-6">
                            <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>
                    
                    <p class="text-gray-700 mb-6">
                        Choose your email communication preferences to stay informed about our latest products, promotions, and offers.
                    </p>
                    
                    <form action="newsletter_pref.php" method="post">
                        <div class="mb-6">
                            <h3 class="font-medium text-gray-800 mb-3">Define your preference</h3>
                            
                            <div class="space-y-3">
                                <div class="flex items-center">
                                    <input type="radio" name="newsletter" id="yes" value="yes" 
                                           <?= $subscribed ? 'checked' : '' ?> 
                                           class="rounded-full text-jumia-orange focus:ring-jumia-orange">
                                    <label for="yes" class="ml-2 text-gray-700">I want to receive daily newsletter</label>
                                </div>
                                
                                <div class="flex items-center">
                                    <input type="radio" name="newsletter" id="no" value="no" 
                                           <?= !$subscribed ? 'checked' : '' ?> 
                                           class="rounded-full text-jumia-orange focus:ring-jumia-orange">
                                    <label for="no" class="ml-2 text-gray-700">I don't want to receive daily newsletter</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <div class="flex items-center">
                                <input type="checkbox" name="terms" id="terms" 
                                       class="rounded text-jumia-orange focus:ring-jumia-orange">
                                <label for="terms" class="ml-2 text-gray-700">
                                    I agree to the <a href="#" class="text-jumia-orange hover:underline">terms and conditions</a>
                                </label>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="bg-jumia-orange text-white px-6 py-2 rounded-md font-semibold hover:bg-orange-600 transition-colors">
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
