<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="./images/logo.png" type="image/x-icon" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="header.css">
</head>

<body>
    <header>
        <nav class="navbar">
            <ul class="nav-links">
                <li class="dropdown"><a class="menu">☰</a>
                    <ul class="dropdown-menu">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="#">Products</a></li>
                        <li><a href="#">About</a></li>
                        <li><a href="#">Contact</a></li>
                        <li><a href="#">Services</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Cart</a></li>
                        <li><a href="#">Account</a></li>
                    </ul>
                </li>
                <li>
                    <h2 class="brand">TUKOLE <span>business</span></h2>
                </li>
            </ul>
            <button class="menu-toggle" onclick="toggleMenu()">☰</button>
            <div class="search-section">
                <div class="sec">
                    <i></i>
                    <input
                        type="search"
                        name="search"
                        id=""
                        placeholder="Search product, brand and category" />
                </div>
                <button type="submit">search</button>
            </div>
            <ul class="nav-links" id="nav-links">
                <li class="dropdown">
                    <?php if (isset($_SESSION['username'])): ?>
                        <a href="#"><img src="./images/account.png" alt="" style="width: 20px;"> Hi, <?php echo $_SESSION['username']; ?> ▼</a>
                        <ul class="dropdown-menu">
                            <li><a href="myAccount.php"><i></i>My Account</a></li>
                            <li><a href="#"><i></i>Orders</a></li>
                            <li><a href="#"><i></i>Inbox</a></li>
                            <li><a href="#"><i></i>Wishlist</a></li>
                            <li><a href="#"><i></i>Vouchers</a></li>
                            <hr>
                            <li><a href="./login_and_signup/logout.php">Logout</a></li>
                        </ul>
                    <?php else: ?>
                        <a href="#"><img src="./images/account.png" alt="" style="width: 20px;"> Account▼</a>
                        <ul class="dropdown-menu">
                            <li><a href="./login_and_signup/signup.php">SignUp</a></li>
                            <li><a href="./login_and_signup/login.php">Login</a></li>
                        </ul>
                    <?php endif; ?>
                </li>
                <li class="dropdown">
                    <a href="#"><img src="./images/help.png" alt="" style="width: 20px;"> Help▼</a>
                    <ul class="dropdown-menu">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Place an Order</a></li>
                        <li><a href="#">Payments Options</a></li>
                        <li><a href="#">Delivery Timelines & Track your order</a></li>
                        <li><a href="#">Returns and Refunds</a></li>
                        <li><a href="#">Warranty</a></li>
                    </ul>
                </li>
                <li><a href="cart.php"><img src="./images/cart.png" alt="" style="width: 20px;"> Cart</a></li>
            </ul>
        </nav>
    </header>
</body>

</html>