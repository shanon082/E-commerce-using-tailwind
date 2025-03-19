<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Address</title>
    <link rel="stylesheet" href="account.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="account">
        <div class="account-card">
            <div class="head">
                <h1>Edit Address</h1>
                <a href="myAccount.php"><<< Back</a>
            </div><hr>
            <form action="address_form_processing.php" method="post" class="form-grid">
                <div class="form-group">
                    <label for="firstname">First Name</label>
                    <input type="text" name="firstname" id="firstname"  placeholder="Enter your firstname" required>
                </div>
                <div class="form-group">
                    <label for="lastname">Last Name</label>
                    <input type="text" name="lastname" id="lastname"  placeholder="Enter your lastname" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" name="phone" id="phone" placeholder="Enter your phonenumber"  required>
                </div>
                <div class="form-group">
                    <label for="additional-phone">Additional Phone</label>
                    <input type="tel" name="additional-phone" id="additional-phone" placeholder="Enter any additional phone (optional)" >
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" name="address" id="address"  placeholder="Enter your Address" required>
                </div>
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" name="city" id="city"  placeholder="Enter your City" required>
                </div>
                <div class="form-group">
                    <label for="region">Region</label>
                    <input type="text" name="region" id="region" placeholder="Enter your region"  required>
                </div>
                <div class="form-group">
                    <label for="postal-code">Postal Code</label>
                    <input type="text" name="postal-code" id="postal-code" placeholder="Enter your Postal code"  required>
                </div>
                <button type="submit">Save</button>
            </form>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>