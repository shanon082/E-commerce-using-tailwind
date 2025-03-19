<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header('Location: login_and_signup/login.php?redirect=edit_address.php');
    exit;
}

// Initialize variables
$addressId = null;
$address = [
    'address' => '',
    'city' => '',
    'region' => '',
    'postal_code' => '',
    'country' => 'Uganda',
    'is_default' => false
];

// Check if editing existing address
if (isset($_GET['id'])) {
    $addressId = $_GET['id'];
    
    // Get address details
    $stmt = $conn->prepare("SELECT * FROM addresses WHERE id = :id AND user_id = :user_id");
    $stmt->bindParam(':id', $addressId);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $addressData = $stmt->fetch();
    
    if ($addressData) {
        $address = $addressData;
    } else {
        // Redirect if address not found or doesn't belong to user
        header('Location: myAccount.php');
        exit;
    }
}

// Get user information
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $addressLine = $_POST['address'];
    $city = $_POST['city'];
    $region = $_POST['region'];
    $postalCode = $_POST['postal_code'];
    $country = $_POST['country'];
    $isDefault = isset($_POST['is_default']) ? 1 : 0;
    
    // Validate input
    $errors = [];
    
    if (empty($addressLine)) {
        $errors[] = "Address is required";
    }
    
    if (empty($city)) {
        $errors[] = "City is required";
    }
    
    if (empty($region)) {
        $errors[] = "Region is required";
    }
    
    if (empty($errors)) {
        try {
            // Begin transaction
            $conn->beginTransaction();
            
            // If setting as default, unset other default addresses
            if ($isDefault) {
                $stmt = $conn->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = :user_id");
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $stmt->execute();
            }
            
            if ($addressId) {
                // Update existing address
                $stmt = $conn->prepare("UPDATE addresses SET 
                                       address = :address, 
                                       city = :city, 
                                       region = :region, 
                                       postal_code = :postal_code, 
                                       country = :country, 
                                       is_default = :is_default 
                                       WHERE id = :id AND user_id = :user_id");
                
                $stmt->bindParam(':id', $addressId);
            } else {
                // Check if this is the first address (make it default)
                $stmt = $conn->prepare("SELECT COUNT(*) FROM addresses WHERE user_id = :user_id");
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $stmt->execute();
                $addressCount = $stmt->fetchColumn();
                
                if ($addressCount === 0) {
                    $isDefault = 1;
                }
                
                // Insert new address
                $stmt = $conn->prepare("INSERT INTO addresses (
                                       user_id, address, city, region, postal_code, country, is_default
                                       ) VALUES (
                                       :user_id, :address, :city, :region, :postal_code, :country, :is_default
                                       )");
            }
            
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->bindParam(':address', $addressLine);
            $stmt->bindParam(':city', $city);
            $stmt->bindParam(':region', $region);
            $stmt->bindParam(':postal_code', $postalCode);
            $stmt->bindParam(':country', $country);
            $stmt->bindParam(':is_default', $isDefault);
            $stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            // Redirect to my account page
            header('Location: myAccount.php');
            exit;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollBack();
            $error = "There was a problem saving your address. Please try again.";
        }
    }
}

// Set page title based on action
$pageTitle = $addressId ? 'Edit Address' : 'Add New Address';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - TUKOLE Business</title>
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
            <h1 class="text-2xl font-bold"><?php echo $pageTitle; ?></h1>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 mb-6 rounded relative" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"> <?php echo $error; ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 mb-6 rounded relative" role="alert">
                <strong class="font-bold">Please fix the following errors:</strong>
                <ul class="mt-2 list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-lg font-medium">Address Details</h2>
            </div>
            
            <form action="<?php echo $addressId ? "edit_address.php?id=$addressId" : 'edit_address.php'; ?>" method="post" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700">Address Line</label>
                        <input 
                            type="text" 
                            id="address" 
                            name="address" 
                            value="<?php echo htmlspecialchars($address['address']); ?>" 
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                            required
                        >
                        <p class="mt-1 text-sm text-gray-500">Street address, apartment, suite, unit, building, etc.</p>
                    </div>
                    
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                        <input 
                            type="text" 
                            id="city" 
                            name="city" 
                            value="<?php echo htmlspecialchars($address['city']); ?>" 
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                            required
                        >
                    </div>
                    
                    <div>
                        <label for="region" class="block text-sm font-medium text-gray-700">Region/State</label>
                        <input 
                            type="text" 
                            id="region" 
                            name="region" 
                            value="<?php echo htmlspecialchars($address['region']); ?>" 
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                            required
                        >
                    </div>
                    
                    <div>
                        <label for="postal_code" class="block text-sm font-medium text-gray-700">Postal Code</label>
                        <input 
                            type="text" 
                            id="postal_code" 
                            name="postal_code" 
                            value="<?php echo htmlspecialchars($address['postal_code']); ?>" 
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                        >
                    </div>
                    
                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700">Country</label>
                        <select 
                            id="country" 
                            name="country" 
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                        >
                            <option value="Uganda" <?php echo $address['country'] === 'Uganda' ? 'selected' : ''; ?>>Uganda</option>
                            <option value="Kenya" <?php echo $address['country'] === 'Kenya' ? 'selected' : ''; ?>>Kenya</option>
                            <option value="Tanzania" <?php echo $address['country'] === 'Tanzania' ? 'selected' : ''; ?>>Tanzania</option>
                            <option value="Rwanda" <?php echo $address['country'] === 'Rwanda' ? 'selected' : ''; ?>>Rwanda</option>
                        </select>
                    </div>
                    
                    <div class="md:col-span-2">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input 
                                    id="is_default" 
                                    name="is_default" 
                                    type="checkbox" 
                                    <?php echo $address['is_default'] ? 'checked' : ''; ?> 
                                    class="h-4 w-4 text-orange-500 focus:ring-orange-500 border-gray-300 rounded"
                                >
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="is_default" class="font-medium text-gray-700">Set as default address</label>
                                <p class="text-gray-500">This address will be used as the default for shipping and billing.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end">
                    <a 
                        href="myAccount.php" 
                        class="bg-white border border-gray-300 rounded-md shadow-sm py-2 px-4 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
                    >
                        Cancel
                    </a>
                    <button 
                        type="submit" 
                        class="ml-3 bg-orange-500 border border-transparent rounded-md shadow-sm py-2 px-4 text-sm font-medium text-white hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
                    >
                        Save Address
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
