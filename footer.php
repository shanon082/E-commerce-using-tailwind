</main>

<!-- Newsletter section -->
<section class="bg-gray-100 py-10 border-t border-gray-200">
    <div class="container mx-auto px-4">
        <div class="max-w-xl mx-auto text-center">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Sign Up for Updates & Newsletter</h2>
            <form class="flex flex-col sm:flex-row gap-2">
                <input type="email" placeholder="Enter your Email" 
                      class="flex-1 px-4 py-3 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500" />
                <button type="submit" class="bg-jumia-orange text-white font-semibold px-6 py-3 rounded-md hover:bg-orange-600 transition-colors">
                    Subscribe Now
                </button>
            </form>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-white pt-10 border-t border-gray-200">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Customer Service -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Customer Service</h3>
                <ul class="space-y-2">
                    <li><a href="#" class="text-gray-600 hover:text-jumia-orange transition-colors">Help Center</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-jumia-orange transition-colors">Returns</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-jumia-orange transition-colors">Shipping</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-jumia-orange transition-colors">Track Order</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-jumia-orange transition-colors">Contact Us</a></li>
                </ul>
            </div>
            
            <!-- About Us -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">About Us</h3>
                <ul class="space-y-2">
                    <li><a href="#" class="text-gray-600 hover:text-jumia-orange transition-colors">Our Story</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-jumia-orange transition-colors">Careers</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-jumia-orange transition-colors">Press</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-jumia-orange transition-colors">Blog</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-jumia-orange transition-colors">Sustainability</a></li>
                </ul>
            </div>
            
            <!-- Payment Methods -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Payment Methods</h3>
                <ul class="space-y-2">
                    <li><a href="#" class="text-gray-600 hover:text-jumia-orange transition-colors">Credit Cards</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-jumia-orange transition-colors">Debit Cards</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-jumia-orange transition-colors">Mobile Money</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-jumia-orange transition-colors">Bank Transfer</a></li>
                    <li><a href="#" class="text-gray-600 hover:text-jumia-orange transition-colors">Pay on Delivery</a></li>
                </ul>
            </div>
            
            <!-- Follow Us -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Follow Us</h3>
                <div class="flex space-x-4 mb-4">
                    <a href="#" class="text-gray-600 hover:text-jumia-orange transition-colors">
                        <i class="fab fa-facebook-f text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-600 hover:text-jumia-orange transition-colors">
                        <i class="fab fa-twitter text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-600 hover:text-jumia-orange transition-colors">
                        <i class="fab fa-instagram text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-600 hover:text-jumia-orange transition-colors">
                        <i class="fab fa-youtube text-xl"></i>
                    </a>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Download Our App</h3>
                <div class="flex space-x-2">
                    <a href="#" class="block bg-black text-white rounded px-2 py-2 text-xs flex items-center">
                        <i class="fab fa-apple text-2xl mr-1"></i>
                        <div>
                            <div class="text-xs">Download on the</div>
                            <div class="font-semibold">App Store</div>
                        </div>
                    </a>
                    <a href="#" class="block bg-black text-white rounded px-2 py-2 text-xs flex items-center">
                        <i class="fab fa-google-play text-2xl mr-1"></i>
                        <div>
                            <div class="text-xs">GET IT ON</div>
                            <div class="font-semibold">Google Play</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="mt-10 pt-6 pb-6 border-t border-gray-200 text-center text-gray-600 text-sm">
            <p>&copy; <?= date('Y') ?> Jumia Clone. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Javascript -->
<script src="assets/js/main.js"></script>
</body>
</html>
