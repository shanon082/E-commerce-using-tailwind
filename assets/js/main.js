/**
 * TUKOLE E-commerce - Main JavaScript
 */

// Define global variables
let currentSlide = 0;
let slides = document.querySelectorAll('.slide');
let timeout = null;

// DOM Ready event
document.addEventListener('DOMContentLoaded', function() {
  initSlideshow();
  setupMobileMenu();
  initQuantityInputs();
  setupAddToCartButtons();
});

/**
 * Initialize the slideshow component
 */
function initSlideshow() {
  slides = document.querySelectorAll('.slide');
  
  if (slides.length > 0) {
    // Show the first slide
    showSlide(0);
    
    // Auto-rotate slides every 5 seconds
    startSlideTimer();
  }
}

/**
 * Show a specific slide by index
 * @param {number} index - The index of the slide to show
 */
function showSlide(index) {
  // Reset the timer
  if (timeout) {
    clearTimeout(timeout);
  }
  
  // Handle edge cases
  if (index >= slides.length) {
    index = 0;
  } else if (index < 0) {
    index = slides.length - 1;
  }
  
  // Hide all slides
  for (let i = 0; i < slides.length; i++) {
    slides[i].classList.add('hidden');
  }
  
  // Show the selected slide
  slides[index].classList.remove('hidden');
  currentSlide = index;
  
  // Restart the timer
  startSlideTimer();
}

/**
 * Change to the next or previous slide
 * @param {number} n - The direction to move (1 = next, -1 = previous)
 */
function changeSlide(n) {
  showSlide(currentSlide + n);
}

/**
 * Start the automatic slideshow timer
 */
function startSlideTimer() {
  timeout = setTimeout(() => {
    changeSlide(1);
  }, 5000);
}

/**
 * Setup mobile menu functionality
 */
function setupMobileMenu() {
  const mobileMenuButton = document.getElementById('mobile-menu-button');
  const mobileMenu = document.getElementById('mobile-menu');
  
  if (mobileMenuButton && mobileMenu) {
    mobileMenuButton.addEventListener('click', function() {
      mobileMenu.classList.toggle('hidden');
    });
  }
}

/**
 * Initialize quantity inputs with increment/decrement buttons
 */
function initQuantityInputs() {
  // This is handled by inline event handlers in the HTML
}

/**
 * Add a product to the cart
 * @param {number} productId - The ID of the product to add
 * @param {number} quantity - The quantity to add (default: 1)
 */
function addToCart(productId, quantity = 1) {
  // Create a request to add item to cart
  fetch('add_to_cart.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: `product_id=${productId}&quantity=${quantity}`
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showToast('Product added to cart');
      
      // Update cart count in header if available
      const cartCountElement = document.querySelector('.cart-count');
      if (cartCountElement) {
        cartCountElement.textContent = data.cartCount;
        
        if (data.cartCount > 0) {
          cartCountElement.classList.remove('hidden');
        }
      }
    } else {
      showToast(data.message || 'Failed to add product to cart');
    }
  })
  .catch(error => {
    console.error('Error adding to cart:', error);
    showToast('An error occurred. Please try again.');
  });
}

/**
 * Setup the Add to Cart buttons
 */
function setupAddToCartButtons() {
  document.querySelectorAll('[data-add-to-cart]').forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      const productId = this.getAttribute('data-product-id');
      addToCart(productId);
    });
  });
}

/**
 * Display a toast notification
 * @param {string} message - The message to display
 * @param {number} duration - How long to show the toast (in ms)
 */
function showToast(message, duration = 3000) {
  // Remove existing toast if present
  const existingToast = document.querySelector('.toast');
  if (existingToast) {
    existingToast.remove();
  }
  
  // Create new toast
  const toast = document.createElement('div');
  toast.className = 'toast';
  toast.textContent = message;
  
  // Add to document
  document.body.appendChild(toast);
  
  // Trigger animation
  setTimeout(() => {
    toast.classList.add('show');
  }, 10);
  
  // Auto remove
  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => {
      toast.remove();
    }, 300);
  }, duration);
}

/**
 * Increment quantity input
 * @param {string} inputId - The ID of the input element
 * @param {number} max - The maximum allowed value
 */
function incrementQuantity(inputId, max = null) {
  const input = document.getElementById(inputId);
  const currentValue = parseInt(input.value);
  
  if (max === null || currentValue < max) {
    input.value = currentValue + 1;
  }
}

/**
 * Decrement quantity input
 * @param {string} inputId - The ID of the input element
 */
function decrementQuantity(inputId) {
  const input = document.getElementById(inputId);
  const currentValue = parseInt(input.value);
  
  if (currentValue > 1) {
    input.value = currentValue - 1;
  }
}
