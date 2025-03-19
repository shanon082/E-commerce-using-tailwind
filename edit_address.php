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
$address = null;
$isNewAddress = true;

// Check if we're editing an existing address
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $addressId = (int)$_GET['id'];
    
    // Fetch address if it belongs to the user
    $stmt = $conn->prepare("SELECT * FROM addresses WHERE id = ? AND user_id = ?");
    $stmt->execute([$addressId, $userData['id']]);
    $address = $stmt->fetch();
    
    if ($address) {
        $isNewAddress = false;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $alternatePhone = trim($_POST['alternate_phone'] ?? '');
    $addressText = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $region = trim($_POST['region'] ?? '');
    $postalCode = trim($_POST['postal_code'] ?? '');
    $isDefault = isset($_POST['is_default']) ? 1 : 0;

    // Validate input
    $errors = [];
    
    if (empty($firstName)) {
        $errors[] = 'First name is required';
    }
    
    if (empty($lastName)) {
        $errors[] = 'Last name is required';
    }
    
    if (empty($phone)) {
        $errors[] = 'Phone number is required';
    }
    
    if (empty($addressText)) {
        $errors[] = 'Address is required';
    }
    
    if (empty($city)) {
        $errors[] = 'City is required';
    }
    
    if (empty($region)) {
        $errors[] = 'Region is required';
    }
    
    if (empty($postalCode)) {
        $errors[] = 'Postal code is required';
    }

    // If no errors, proceed with saving
    if (empty($errors)) {
        // If this is set as default, update all other addresses to non-default
        if ($isDefault) {
            $stmt = $conn->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
            $stmt->execute([$userData['id']]);
        }

        // Insert or update address
        if ($isNewAddress) {
            // If first address, make it default
            $countStmt = $conn->prepare("SELECT COUNT(*) FROM addresses WHERE user_id = ?");
            $countStmt->execute([$userData['id']]);
            $addressCount = $countStmt->fetchColumn();
            
            if ($addressCount == 0) {
                $isDefault = 1;
            }
            
            $stmt = $conn->prepare("INSERT INTO addresses (user_id, first_name, last_name, phone, alternate_phone, address, city, region, postal_code, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $userData['id'],
                $firstName,
                $lastName,
                $phone,
                $alternatePhone,
                $addressText,
                $city,
                $region,
                $postalCode,
                $isDefault
            ]);
        } else {
            $stmt = $conn->prepare("UPDATE addresses SET first_name = ?, last_name = ?, phone = ?, alternate_phone = ?, address = ?, city = ?, region = ?, postal_code = ?, is_default = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([
                $firstName,
                $lastName,
                $phone,
                $alternatePhone,
                $addressText,
                $city,
                $region,
                $postalCode,
                $isDefault,
                $address['id'],
                $userData['id']
            ]);
        }

        // Redirect back to account page
        header("Location: myAccount.php");
        exit;
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
            <span class="text-gray-800 font-medium"><?= $isNewAddress ? 'Add Address' : 'Edit Address' ?></span>
        </nav>
    </div>
</div>

<!-- Edit Address Section -->
<section class="py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                    <h2 class="font-semibold text-lg text-gray-800"><?= $isNewAddress ? 'Add New Address' : 'Edit Address' ?></h2>
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
                    
                    <form action="<?= $isNewAddress ? 'edit_address.php' : 'edit_address.php?id=' . $address['id'] ?>" method="post">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name*</label>
                                <input type="text" name="first_name" id="first_name" 
                                       value="<?= isset($address['first_name']) ? htmlspecialchars($address['first_name']) : '' ?>" 
                                       class="input-field" required>
                            </div>
                            
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name*</label>
                                <input type="text" name="last_name" id="last_name" 
                                       value="<?= isset($address['last_name']) ? htmlspecialchars($address['last_name']) : '' ?>" 
                                       class="input-field" required>
                            </div>
                            
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone*</label>
                                <input type="tel" name="phone" id="phone" 
                                       value="<?= isset($address['phone']) ? htmlspecialchars($address['phone']) : '' ?>" 
                                       class="input-field" required>
                            </div>
                            
                            <div>
                                <label for="alternate_phone" class="block text-sm font-medium text-gray-700 mb-2">Additional Phone (optional)</label>
                                <input type="tel" name="alternate_phone" id="alternate_phone" 
                                       value="<?= isset($address['alternate_phone']) ? htmlspecialchars($address['alternate_phone']) : '' ?>" 
                                       class="input-field">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Address*</label>
                                <input type="text" name="address" id="address" 
                                       value="<?= isset($address['address']) ? htmlspecialchars($address['address']) : '' ?>" 
                                       class="input-field" required>
                            </div>
                            
                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-700 mb-2">City*</label>
                                <input type="text" name="city" id="city" 
                                       value="<?= isset($address['city']) ? htmlspecialchars($address['city']) : '' ?>" 
                                       class="input-field" required>
                            </div>
                            
                            <div>
                                <label for="region" class="block text-sm font-medium text-gray-700 mb-2">Region*</label>
                                <input type="text" name="region" id="region" 
                                       value="<?= isset($address['region']) ? htmlspecialchars($address['region']) : '' ?>" 
                                       class="input-field" required>
                            </div>
                            
                            <div>
                                <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-2">Postal Code*</label>
                                <input type="text" name="postal_code" id="postal_code" 
                                       value="<?= isset($address['postal_code']) ? htmlspecialchars($address['postal_code']) : '' ?>" 
                                       class="input-field" required>
                            </div>
                            
                            <div class="md:col-span-2">
                                <div class="flex items-center">
                                    <input type="checkbox" name="is_default" id="is_default" 
                                           <?= (!$isNewAddress && $address['is_default']) || $isNewAddress ? 'checked' : '' ?> 
                                           class="rounded text-jumia-orange focus:ring-jumia-orange">
                                    <label for="is_default" class="ml-2 text-sm text-gray-700">Set as default shipping address</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 flex items-center justify-end">
                            <a href="myAccount.php" class="text-gray-600 hover:text-gray-800 mr-4">Cancel</a>
                            <button type="submit" class="bg-jumia-orange text-white px-6 py-2 rounded-md font-semibold hover:bg-orange-600 transition-colors">
                                Save Address
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
