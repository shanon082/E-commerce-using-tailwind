<!-- PHP backend integration will handle:
1. Newsletter subscription functionality
2. Dynamic link generation for site pages
3. Copyright year automation
4. Social media integration
5. Payment methods display based on availability
-->

<footer class="bg-gray-800 text-white">
    <!-- Newsletter Subscription -->
    <div class="bg-gray-700 py-8">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <div class="mb-4 md:mb-0">
                    <h3 class="text-xl font-semibold mb-1">Subscribe to our Newsletter</h3>
                    <p class="text-gray-300">Get the latest deals and special offers</p>
                </div>
                <div class="w-full md:w-1/2 lg:w-1/3">
                    <form action="newsletter_pref.php" method="post" class="flex">
                        <input 
                            type="email" 
                            name="email" 
                            placeholder="Your email address" 
                            class="w-full px-4 py-2 rounded-l-md focus:outline-none text-gray-900"
                            required
                        />
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 px-4 py-2 rounded-r-md transition">
                            Subscribe
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Footer -->
    <div class="container mx-auto py-10 px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- About Company -->
            <div>
                <h3 class="text-lg font-semibold mb-4">TUKOLE Business</h3>
                <p class="text-gray-400 mb-4">Your trusted online marketplace for quality products at competitive prices.</p>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-white transition transform hover:scale-110">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition transform hover:scale-110">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition transform hover:scale-110">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition transform hover:scale-110">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>

            <!-- Customer Service -->
            <div>
                <h3 class="text-lg font-semibold mb-4">Customer Service</h3>
                <ul class="space-y-2">
                    <li><a href="#" class="text-gray-400 hover:text-white transition">Help Center</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition">Returns & Refunds</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition">Shipping Information</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition">Track Your Order</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition">Contact Us</a></li>
                </ul>
            </div>

            <!-- About Us -->
            <div>
                <h3 class="text-lg font-semibold mb-4">About Us</h3>
                <ul class="space-y-2">
                    <li><a href="#" class="text-gray-400 hover:text-white transition">Our Story</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition">Careers</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition">Terms & Conditions</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition">Privacy Policy</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition">Become a Seller</a></li>
                </ul>
            </div>

            <!-- Payment & Delivery -->
            <div>
                <h3 class="text-lg font-semibold mb-4">Payment & Delivery</h3>
                <ul class="space-y-2">
                    <li><a href="#" class="text-gray-400 hover:text-white transition">Payment Methods</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition">Delivery Options</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition">Money-Back Guarantee</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition">International Shipping</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Payment Methods -->
    <div class="border-t border-gray-700 py-6">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <p class="text-gray-400">Payment Methods:</p>
                    <div class="flex space-x-4 mt-2">
                        <i class="fab fa-cc-visa text-2xl text-gray-400"></i>
                        <i class="fab fa-cc-mastercard text-2xl text-gray-400"></i>
                        <i class="fab fa-cc-paypal text-2xl text-gray-400"></i>
                        <i class="fab fa-cc-apple-pay text-2xl text-gray-400"></i>
                        <i class="fas fa-money-bill-wave text-2xl text-gray-400"></i>
                    </div>
                </div>
                <div>
                    <p class="text-gray-400">Download Our App:</p>
                    <div class="flex space-x-4 mt-2">
                        <a href="#" class="text-gray-400 hover:text-white transition">
                            <i class="fab fa-android text-2xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition">
                            <i class="fab fa-apple text-2xl"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Copyright -->
    <div class="bg-gray-900 py-4">
        <div class="container mx-auto px-4 text-center text-gray-400 text-sm">
            <p>&copy; <?php echo date('Y'); ?> TUKOLE Business. All rights reserved.</p>
        </div>
    </div>
</footer>
