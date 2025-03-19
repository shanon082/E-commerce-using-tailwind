<?php 
session_start(); 
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>HomePage</title>
  <link rel="stylesheet" href="index.css" />
  <script src="https://kit.fontawesome.com/b27d0ab5e4.js" crossorigin="anonymous"></script>
</head>

<body>
     <?php include 'header.php'; ?>
  <!-- slider images -->
  <section class="slideshow-container">
    <div class="slide fade">
      <img src="./images/electonics.png" alt="Electronics" />
      <div class="caption">
        <h3>Latest Electronics <br />& Gadgets</h3>
        <p>Find the best deals on the newest tech products.</p>
      </div>
    </div>
    <div class="slide fade">
      <img src="./images/clothes.png" alt="Clothes" />
      <div class="caption">
        <h3>Trendy Fashion <br />& Clothing</h3>
        <p>Shop the latest trends at the best prices.</p>
      </div>
    </div>
    <div class="slide fade">
      <img src="./images/agrobased.png" alt="Agro products" />
      <div class="caption">
        <h3>Fresh Agro-Based <br />Products</h3>
        <p>Organic and fresh farm produce delivered to your doorstep.</p>
      </div>
    </div>
    <div class="slide fade">
      <img src="./images/furniture.png" alt="Furniture" />
      <div class="caption">
        <h3>Quality Furniture for <br />Every Home</h3>
        <p>Stylish and durable furniture for your comfort.</p>
      </div>
    </div>
    <!-- <button class="prev" onclick="changeSlide(-1)">&#10094;</button>
      <button class="next" onclick="changeSlide(1)">&#10095;</button> -->
  </section>

  <!-- Featured Products Section -->
  <section class="featured-products">
    <h1>Featured Products</h1>
    <div class="featured-slider">
      <div class="featured-slides">
        <div class="card">
          <a href="product_description.php">
            <img src="./images/cloth.png" alt="Product 1" />
            <h4>Product 1</h4>
            <h5>$100</h5>
            <h6>Review: ⭐⭐⭐⭐⭐</h6>
            <p>High-quality fabric, available in various sizes and colors.</p>
          </a>
          <button class="add-to-cart">Add to Cart</button>
        </div>
        <div class="card">
          <a href="product_description.php?id=2">
            <img src="./images/phone.png" alt="Product 2" />
            <h4>iPhone 15 Pro Max</h4>
            <h5>$200</h5>
            <h6>Review: ⭐⭐⭐⭐⭐</h6>
            <p>Latest model with advanced features and sleek design.</p>
          </a>
          <button class="add-to-cart">Add to Cart</button>
        </div>
        <div class="card">
          <a href="product_description.php?id=3">
            <img src="./images/lap.png" alt="Product 3" />
            <h4>Lenovo ThinkPad T442</h5>
            <h5>$300</h5>
            <h6>Review: ⭐⭐⭐⭐⭐</h6>
            <p>Powerful performance with long battery life and durability.</p>
          </a>
          <button class="add-to-cart">Add to Cart</button>
        </div>
        <div class="card">
          <img src="./images/coofee.png" alt="" />
          <h4>Cofeee 1 acre</h5>
          <h5>negotiable</h5>
          <h6>Review: ⭐⭐⭐⭐⭐</h6>
          <p></p>
          <button class="add-to-cart">Add to Cart</button>
        </div>
        <div class="card">
          <img src="./images/flash.png" alt="" />
          <h4>Flash drives</h4>
          <h5>$2 @</h5>
          <h6>Review: ⭐⭐⭐⭐⭐</h6>
          <p>Latest model with advanced features and sleek design.</p>
          <button class="add-to-cart">Add to Cart</button>
        </div>
      </div>
      <!-- <button class="prev" onclick="prevFeaturedSlide()">&#10094;</button>
        <button class="next" onclick="nextFeaturedSlide()">&#10095;</button> -->
    </div>
  </section>

  <!-- Categories Section -->
  <section class="categories">
    <h1>Shop by Categories</h1>
    <div class="category-cards">
      <div class="category-card">
        <img src="./images/electonics.png" alt="Electronics" />
        <h4>Electronics</h4>
      </div>
      <div class="category-card">
        <img src="./images/clothes.png" alt="Clothing" />
        <h4>Clothing</h4>
      </div>
      <div class="category-card">
        <img src="./images/furniture.png" alt="Furniture" />
        <h4>Furniture</h4>
      </div>
    </div>
  </section>

  <!-- About Us Section -->
  <section class="aboutus-container">
    <img src="images/lap.png" alt="About Us" />
    <div class="info">
      <h3>About Us</h3>
      <h1>We Provide High-Quality Products</h1>
      <h4>
        We ensure that our products meet top-notch quality standards to
        satisfy our customers.
      </h4>
      <p>Hp laptop <span>Brand new</span></p>
      <p>8GB Ram 256GB SSD Dual core....</p>
      <p><span style="text-decoration: line-through;color:grey">$800</span></p>
      <p>$500</p>
      <button type="button">Explore More</button>
    </div>
  </section>

  <!-- Product Details Section -->
  <section class="product-details-container">
    <div class="info">
      <h3>Product Details</h3>
      <h1>Get to Know Our Feature Products</h1>
      <p>
        Explore our best-selling and top-rated products that customers love.
      </p>
    </div>
    <div class="pdt-card">
      <img src="images/agrobased.png" alt="Featured Product" />
      <img src="images/electonics.png" alt="Featured Product" />
    </div>
  </section>

  <!-- Customer Reviews Section -->
  <section class="customer-reviews">
    <h1>What Our Customers Say</h1>
    <div class="review-slider">
      <div class="review-slides">
        <div class="review-card">
          <img src="./images/cust1.png" alt="Customer 1" />
          <p>"Excellent quality products and great customer service!"</p>
          <span>⭐⭐⭐⭐⭐</span>
          <h4>John Doe</h4>
        </div>
        <div class="review-card">
          <img src="./images/cust2.png" alt="Customer 2" />
          <p>"Fast delivery and amazing deals! Will shop again."</p>
          <span>⭐⭐⭐⭐⭐</span>
          <h4>Sarah Smith</h4>
        </div>
        <div class="review-card">
          <img src="./images/cust3.png" alt="Customer 3" />
          <p>"The best shopping experience I have ever had!"</p>
          <span>⭐⭐⭐⭐⭐</span>
          <h4>Michael Johnson</h4>
        </div>
      </div>
    </div>
  </section>

  <?php include 'footer.php'; ?>
  <script src="script.js"></script>
</body>

</html>