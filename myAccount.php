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

// Get user addresses
$addresses = getUserAddresses($conn, $userData['id']);
$defaultAddress = getDefaultAddress($conn, $userData['id']);

?>

<?php include 'header.php'; ?>

<!-- Breadcrumb -->
<div class="bg-gray-100 py-3">
    <div class="container mx-auto px-4">
        <nav class="flex text-sm">
            <a href="index.php" class="text-gray-600 hover:text-jumia-orange">Home</a>
            <span class="mx-2 text-gray-400">/</span>
            <span class="text-gray-800 font-medium">My Account</span>
        </nav>
    </div>
</div>

<!-- Account Overview Section -->
<section class="py-8">
    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Account Overview</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Account Details Card -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="font-semibold text-lg text-gray-800">Account Details</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <p class="text-gray-700"><span class="font-medium">Username:</span> <?= htmlspecialchars($userData['username']) ?></p>
                        <p class="text-gray-700"><span class="font-medium">Email:</span> <?= htmlspecialchars($userData['email']) ?></p>
                        <p class="text-gray-700"><span class="font-medium">Phone:</span> <?= htmlspecialchars($userData['phone']) ?></p>
                    </div>
                    <div class="mt-6">
                        <a href="#" class="text-jumia-orange hover:underline">Change Password</a>
                    </div>
                </div>
            </div>

            <!-- Address Book Card -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                    <h2 class="font-semibold text-lg text-gray-800">Address Book</h2>
                    <a href="edit_address.php" class="text-jumia-orange hover:underline text-sm">Edit</a>
                </div>
                <div class="p-6">
                    <?php if ($defaultAddress): ?>
                        <div class="space-y-2">
                            <p class="font-medium text-gray-800"><?= htmlspecialchars($defaultAddress['first_name'] . ' ' . $defaultAddress['last_name']) ?></p>
                            <p class="text-gray-700"><?= htmlspecialchars($defaultAddress['address']) ?></p>
                            <p class="text-gray-700"><?= htmlspecialchars($defaultAddress['city'] . ', ' . $defaultAddress['region'] . ' ' . $defaultAddress['postal_code']) ?></p>
                            <p class="text-gray-700">Phone: <?= htmlspecialchars($defaultAddress['phone']) ?></p>
                            <?php if ($defaultAddress['alternate_phone']): ?>
                                <p class="text-gray-700">Another Phone: <?= htmlspecialchars($defaultAddress['alternate_phone']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-600">No address saved yet.</p>
                        <div class="mt-4">
                            <a href="edit_address.php" class="bg-jumia-orange text-white px-4 py-2 rounded-md hover:bg-orange-600 transition-colors inline-block">Add Address</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Store Credit Card -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="font-semibold text-lg text-gray-800">Store Credit</h2>
                </div>
                <div class="p-6">
                    <p class="font-medium text-gray-800 mb-2">Current Balance: $0.00</p>
                    <p class="text-gray-600 text-sm">
                        Store credit is a payment method that can be used for future purchases. 
                        Store credit is issued when you return an item, and can be used to purchase 
                        anything on the site.
                    </p>
                    <div class="mt-4">
                        <a href="#" class="text-jumia-orange hover:underline">Learn more</a>
                    </div>
                </div>
            </div>

            <!-- Newsletter Preferences Card -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="font-semibold text-lg text-gray-800">Newsletter Preferences</h2>
                </div>
                <div class="p-6">
                    <p class="text-gray-600 mb-4">
                        Manage your email communications to stay updated with the latest news and offers.
                    </p>
                    <a href="newsletter_pref.php" class="text-jumia-orange hover:underline">Edit newsletter preferences</a>
                </div>
            </div>

            <!-- Recent Orders Card -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                    <h2 class="font-semibold text-lg text-gray-800">Recent Orders</h2>
                    <a href="#" class="text-jumia-orange hover:underline text-sm">View All</a>
                </div>
                <div class="p-6">
                    <!-- Placeholder for recent orders -->
                    <p class="text-gray-600">You haven't placed any orders yet.</p>
                </div>
            </div>

            <!-- Wishlist Card -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                    <h2 class="font-semibold text-lg text-gray-800">Wishlist</h2>
                    <a href="#" class="text-jumia-orange hover:underline text-sm">View All</a>
                </div>
                <div class="p-6">
                    <!-- Placeholder for wishlist -->
                    <p class="text-gray-600">Your wishlist is empty.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
