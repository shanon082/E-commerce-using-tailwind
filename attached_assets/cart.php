<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
    <link rel="stylesheet" href="cart_page.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="contain">
        <?php if (isset($_SESSION['username'])): ?>
            <section>
                <div class="cart_card">                
                    <div class="cart">
                        <h1>Cart (3)</h1><hr>
                        <div class="cart-item">
                            <img src="./images/cloth.png" alt="Product 1" />
                            <div class="cart-item-details">
                                <h4>Product 1</h4>
                                <p>Price: $100</p>
                                <div class="quantity">
                                    <button>-</button>
                                    <span>1</span>
                                    <button>+</button>
                                </div>
                                <button>Remove</button>
                            </div>
                        </div>
                        <hr>
                        <div class="cart-item">
                            <img src="./images/cloth.png" alt="Product 1" />
                            <div class="cart-item-details">
                                <h4>Product 2</h4>
                                <p>Price: $100</p>
                                <div class="quantity">
                                    <button>-</button>
                                    <span>1</span>
                                    <button>+</button>
                                </div>
                                <button>Remove</button>
                            </div>
                        </div>
                        <hr>
                        <div class="cart-item">
                            <img src="./images/cloth.png" alt="Product 1" />
                            <div class="cart-item-details">
                                <h4>Product 3</h4>
                                <p>Price: $100</p>
                                <div class="quantity">
                                    <button>-</button>
                                    <span>1</span>
                                    <button>+</button>
                                </div>
                                <button>Remove</button>
                            </div>
                        </div>
                        <hr>
                    </div>
                    <div class="cart-total">
                        <h3>Total: <span>$300</span></h3>
                        <a href="check-out.php">Checkout</a>
                    </div>
                </div>
            </section>

            <section>
                <div class="top-selling-items">
                    <h1>Top Selling Items</h1>
                    <div class="top-selling-items-cards">
                        <div class="card">
                            <img src="./images/cloth.png" alt="Product 1" />
                            <h4>Product 1</h4>
                            <p>Price: $100</p>
                        </div>
                        <div class="card">
                            <img src="./images/phone.png" alt="Product 2" />
                            <h4>Product 2</h4>
                            <p>Price: $200</p>
                        </div>
                        <div class="card">
                            <img src="./images/lap.png" alt="Product 3" />
                            <h4>Product 3</h4>
                            <p>Price: $300</p>
                        </div>
                    </div>
                </div>
            </section>
        <?php else: ?>
            <div class="login-prompt">
                <div class="card">
                    <h2>Please <a href="./login_and_signup/login.php">log in</a> to continue shopping.</h2>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php include 'footer.php'; ?>
</body>

</html>