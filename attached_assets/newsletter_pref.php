<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="account.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="account">
        <div class="account-card">
            <div class="head">
                <h1>Newsletter Preferences</h1>
                <a href="myAccount.php"><<< Back</a>
            </div><hr>
            <form action="newsletter_pref_processing.php" method="post" class="form-grid">
                <div class="">
                    <h4>Define your preference</h4>
                    <div class="radio-btn">
                        <input type="radio" name="newsletter" id="yes" value="yes">
                        <label for="yes">I want to receive daily newsletter</label>
                    </div>
                    <div class="radio-btn">
                        <input type="radio" name="newsletter" id="no" value="no">
                        <label for="no">I don't want to receive daily newsletter</label>
                    </div>
                    <div class="check">
                        <input type="checkbox" name="terms" id="terms">
                        <label for="terms">I agree to the terms and conditions</label>
                    </div>
                </div>
                <button type="submit">Save</button>
            </form>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>