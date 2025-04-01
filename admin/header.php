<?php 
require_once '../db.php';

// Define a default profile image
$defaultProfileImage = './assets/images/default-profile.png';

// Fetch the user's profile image from the database
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $conn->prepare("SELECT profile_image FROM users WHERE id = :id");
        $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        $userProfileImage = $stmt->fetchColumn();

        // Log if no profile image is found
        if (!$userProfileImage) {
            error_log("No profile image found for user ID: " . $_SESSION['user_id']);
        }

        // Use the default image if no profile image is set
        $userProfileImage = $userProfileImage ?: $defaultProfileImage;
    } catch (PDOException $e) {
        error_log("Error fetching profile image: " . $e->getMessage());
        $userProfileImage = $defaultProfileImage;
    }
} else {
    error_log("User ID not set in session.");
    $userProfileImage = $defaultProfileImage;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../assets/images/favicon.ico" type="image/x-icon" />
    <title>Admin Panel - TUKOLE Business</title>
</head>
<body>
    <header class="bg-gray-900 text-white shadow">
        <div class="container mx-auto">
            <div class="flex items-center justify-between p-4">
                <div class="flex items-center">
                    <a href="index.php" class="font-bold text-xl">
                        TUKOLE <span class="text-orange-500">ADMIN</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <button id="notifications-button" class="text-gray-300 hover:text-white focus:outline-none">
                            <i class="fas fa-bell"></i>
                            <?php if (isset($pending_notifications) && $pending_notifications > 0): ?>
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">
                                    <?php echo $pending_notifications; ?>
                                </span>
                            <?php endif; ?>
                        </button>
                    </div>
                    <div class="relative group">
                        <button class="flex items-center text-gray-300 hover:text-white focus:outline-none">
                            <img 
                                src="<?php echo htmlspecialchars($userProfileImage); ?>" 
                                alt="Profile" 
                                class="w-8 h-8 rounded-full mr-2"
                            />
                            <span class="mr-1">
                                <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin'; ?>
                            </span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div class="absolute right-0 w-48 bg-white rounded-md shadow-lg py-1 z-10 hidden group-hover:block">
                            <a href="../myAccount.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Profile</a>
                            <a href="../login_and_signup/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
</body>
</html>
