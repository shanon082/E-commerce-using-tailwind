<?php session_start();?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account</title>
    <link rel="stylesheet" href="account.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="account">
        <div class="account-card">
            <h1>Account Overview</h1>
            <div class="account-info">
                <div class="card-details">
                    <h1>Account Details</h1><hr>
                    <div class="info">
                        <p>Username: <?php echo $_SESSION['username']; ?></p>
                        <p>Email: <?php echo $_SESSION['email']; ?></p>
                        <p>Phone: <?php echo $_SESSION['phone']; ?></p>
                    </div>
                </div>
                <div class="card-details">
                    <div class="head">
                    <h1>Address Book</h1>
                    <a href="edit_address.php">Edit</a>
                    </div>                  
                    <hr>
                    <div class="add-info">
                        <p>Username: <?php echo $_SESSION['username']; ?></p>
                        <p>Phone: <?php echo $_SESSION['phone']; ?></p>
                        <p>Another Phone: 0786975622</p>
                        <p>Address: arapai soroti</p>
                    </div>
                </div>
                <div class="card-details">
                    <h1>Store Credit</h1><hr>
                    <div class="credit">
                        <p>Current Balance: $0.00</p>
                        <p>Store credit is a payment method that can be used for future purchases. Store credit is issued when you return an item, and can be used to purchase anything on the site.</p>
                        <a href="#">Learn more</a>
                    </div>
                </div>
                <div class="card-details">
                    <h1>Newsletter Preferences</h1><hr>
                    <div class="pref">
                        <p>Manage your email communications to stay updated with the latest news and offers.</p>
                        <a href="newsletter_pref.php">Edit newsletter preferences</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php';?>
</body>
</html>