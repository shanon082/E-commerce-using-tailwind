<?php
session_start();
// require_once 'db.php';

// // Fetch product details based on the product ID passed in the URL
// $product_id = isset($_GET['id']) ? $_GET['id'] : 0;
// $sql = "SELECT * FROM products WHERE id = :id";
// $stmt = $conn->prepare($sql);
// $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
// $stmt->execute();
// $product = $stmt->fetch(PDO::FETCH_ASSOC);

// if (!$product) {
//     echo "Product not found.";
//     exit;
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Product Description</title>
    <link rel="stylesheet" href="product_description.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="wrapper">
        <div class="product-details">
            <!-- <h1><?php //echo htmlspecialchars($product['name']); ?></h1>
            <img src="<?php //echo htmlspecialchars($product['image']); ?>" alt="<? //php// echo htmlspecialchars($product['name']); ?>">
            <p><?php //echo htmlspecialchars($product['description']); ?></p>
            <h3>Price: $<?php //echo htmlspecialchars($product['price']); ?></h3> -->
            <div class="image">
                <img src="./images/phone.png" alt="" srcset="">
            </div>
            <div class="info">
                <h1>title</h1>
                <h6><a href="#">Brand| more products of this brand</a></h6>
                <h3>Price: $100</h3>
                <h6>Rating: ⭐⭐⭐⭐⭐</h6>
                <button>Add to Cart</button>
            </div>        
        </div>
        <div class="product-description">
            <h2>Description</h2><hr>
            <h4>Product name</h4>
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit </p>
        </div>
        <div class="product-specification">
            <h2>Specifications</h2><hr>
            <p><strong>Brand: </strong>Brand name</p>
            <p><strong>Model: </strong>Mode name</p>
            <p><strong>Color: </strong>Color name</p>
            <p><strong>Size: </strong>large</p>
            <p><strong>Weigt:</strong> 0.21 <span>g</span></p>
        </div>
        <div class="similar-items">
            <h1>Top Selling Items</h1><hr>
            <div class="similar-items-cards">
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
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
