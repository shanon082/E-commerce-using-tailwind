<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="checkout.css">
</head>

<body>
    <?php include "header.php"; ?>
    <div class="wrapper">
        <h1>Select your delivery method</h1>
        <div class="customer-details">
            <div class="customer-address">
                <div class="head">
                    <h2>1. Customer Address</h2><hr>
                    <button>Edit</button>
                </div>
                <div class="address">
                    <p>John</p>
                    <p>busega</p>
                    <p>23304</p>
                    <p>uganda</p>
                    <p>Phone: 0771950092</p>
                </div>
            </div>
            <div class="delivery-method">
                <h2>2. Delivery Method</h2><hr>
                <div class="method">
                    <div class="method-card">
                        <h3>Standard Delivery</h3>
                        <p>3-5 days</p>
                        <p>Free</p>
                        <button>Select</button>
                    </div>
                    <div class="method-card">
                        <h3>Express Delivery</h3>
                        <p>1-2 days</p>
                        <p>$10</p>
                        <button>Select</button>
                    </div>
                </div>
            </div>
            <button class="confirm-delivery">Confirm delivery details</button>
        </div>
        <div class="order-summary">
            <h2>Order Summary</h2><hr>
            <div class="summary">
                <div class="summary-item">
                    <p>Product 1</p>
                    <p>$100</p>
                </div>
                <div class="summary-item">
                    <p>Product 2</p>
                    <p>$200</p>
                </div>
                <div class="summary-item">
                    <p>Product 3</p>
                    <p>$300</p>
                </div>
                <div class="summary-item">
                    <p>Delivery</p>
                    <p>Free</p>
                </div>
                <div class="summary-item total">
                    <p>Total</p>
                    <p>$600</p>
                </div>
            </div>
            <button class="confirm-order">Confirm Order</button>
        </div>
    </div>
    <?php include "footer.php"; ?>
</body>

</html>