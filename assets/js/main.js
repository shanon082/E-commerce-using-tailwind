document.addEventListener('DOMContentLoaded', function() {
  // Mobile menu toggle
  const mobileMenuButton = document.querySelector('.menu-toggle');
  const navLinks = document.getElementById('nav-links');
  
  if (mobileMenuButton && navLinks) {
    mobileMenuButton.addEventListener('click', function() {
      navLinks.classList.toggle('hidden');
    });
  }
  
  // Dropdown menus
  const dropdowns = document.querySelectorAll('.dropdown');
  
  dropdowns.forEach(dropdown => {
    const dropdownToggle = dropdown.querySelector('a');
    const dropdownMenu = dropdown.querySelector('.dropdown-menu');
    
    if (dropdownToggle && dropdownMenu) {
      dropdownToggle.addEventListener('click', function(e) {
        e.preventDefault();
        dropdownMenu.classList.toggle('show');
      });
    }
  });
  
  // Product image gallery
  const mainImage = document.getElementById('main-product-image');
  const thumbnails = document.querySelectorAll('.product-thumbnail');
  
  if (mainImage && thumbnails.length > 0) {
    thumbnails.forEach(thumbnail => {
      thumbnail.addEventListener('click', function() {
        const newSrc = this.getAttribute('data-image');
        mainImage.setAttribute('src', newSrc);
        
        // Remove active class from all thumbnails
        thumbnails.forEach(thumb => thumb.classList.remove('border-orange-500'));
        
        // Add active class to clicked thumbnail
        this.classList.add('border-orange-500');
      });
    });
  }
  
  // Quantity controls
  const quantityControls = document.querySelectorAll('.quantity');
  
  if (quantityControls.length > 0) {
    quantityControls.forEach(control => {
      const decreaseBtn = control.querySelector('.quantity-decrease');
      const increaseBtn = control.querySelector('.quantity-increase');
      const quantityInput = control.querySelector('.quantity-input');
      
      if (decreaseBtn && increaseBtn && quantityInput) {
        decreaseBtn.addEventListener('click', function() {
          let value = parseInt(quantityInput.value, 10);
          value = isNaN(value) ? 1 : value;
          value = value > 1 ? value - 1 : 1;
          quantityInput.value = value;
          
          // Trigger change event
          const event = new Event('change');
          quantityInput.dispatchEvent(event);
        });
        
        increaseBtn.addEventListener('click', function() {
          let value = parseInt(quantityInput.value, 10);
          value = isNaN(value) ? 1 : value;
          value += 1;
          quantityInput.value = value;
          
          // Trigger change event
          const event = new Event('change');
          quantityInput.dispatchEvent(event);
        });
        
        quantityInput.addEventListener('change', function() {
          // Update cart if needed
          if (this.hasAttribute('data-product-id')) {
            const productId = this.getAttribute('data-product-id');
            updateCartQuantity(productId, this.value);
          }
        });
      }
    });
  }
  
  // Cart quantity update using AJAX
  function updateCartQuantity(productId, quantity) {
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    
    fetch('update_cart.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Update cart total if available
        const cartTotal = document.querySelector('.cart-total-amount');
        if (cartTotal && data.total) {
          cartTotal.textContent = data.total;
        }
        
        // Update item subtotal if available
        const itemSubtotal = document.querySelector(`.item-subtotal-${productId}`);
        if (itemSubtotal && data.itemTotal) {
          itemSubtotal.textContent = data.itemTotal;
        }
        
        showToast('Cart updated successfully', 'success');
      } else {
        showToast('Failed to update cart', 'error');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showToast('An error occurred', 'error');
    });
  }
  
  // Toast notification
  function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
      toast.classList.add('fade-in');
    }, 100);
    
    setTimeout(() => {
      toast.style.opacity = '0';
      setTimeout(() => {
        toast.remove();
      }, 500);
    }, 3000);
  }
  
  // Add to cart buttons
  const addToCartButtons = document.querySelectorAll('.add-to-cart');
  
  if (addToCartButtons.length > 0) {
    addToCartButtons.forEach(button => {
      button.addEventListener('click', function(e) {
        e.preventDefault();
        
        const productId = this.getAttribute('data-product-id');
        const quantity = 1;
        
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('quantity', quantity);
        
        fetch('add_to_cart.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showToast('Product added to cart', 'success');
            
            // Update cart count if available
            const cartCount = document.querySelector('.cart-count');
            if (cartCount) {
              cartCount.textContent = data.cartCount;
            }
          } else {
            showToast(data.message || 'Failed to add product to cart', 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showToast('An error occurred', 'error');
        });
      });
    });
  }
  
  // Slideshow functionality
  let slideIndex = 0;
  const slides = document.querySelectorAll('.slide');
  
  if (slides.length > 0) {
    function showSlides() {
      for (let i = 0; i < slides.length; i++) {
        slides[i].style.display = 'none';
      }
      
      slideIndex++;
      if (slideIndex > slides.length) {
        slideIndex = 1;
      }
      
      slides[slideIndex - 1].style.display = 'block';
      setTimeout(showSlides, 5000); // Change slide every 5 seconds
    }
    
    showSlides();
  }
});
