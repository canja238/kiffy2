<?php
require_once 'config.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bitronics - Electronics Store</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    :root {
      --primary: #0f2f4d;
      --primary-light: #1a3d5f;
      --secondary: #d9e6eb;
      --accent: #3a86ff;
      --text: #333;
      --light-text: #777;
      --border: #e0e0e0;
      --error: #e63946;
      --success: #2a9d8f;
      --white: #fff;
      --gray: #f5f5f5;
      --dark-gray: #e0e0e0;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--gray);
      color: var(--text);
      line-height: 1.6;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      margin: 0;
    }
    
    .font-orbitron {
      font-family: 'Orbitron', sans-serif;
    }
    
    header {
      background-color: var(--white);
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
      position: sticky;
      top: 0;
      z-index: 50;
    }
    
    .header-container {
      height: 50px;
      max-width: 100%;
      margin: 0 auto;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 12px 16px;
    }
    
    .search-form {
      display: flex;
      align-items: center;
      border: 1px solid var(--border);
      border-radius: 0.25rem;
      overflow: hidden;
      max-width: 400px;
      width: 100%;
      margin: 0 16px;
    }
    
    .search-input {
      padding: 8px 12px;
      font-size: 14px;
      color: var(--text);
      background-color: transparent;
      border: none;
      outline: none;
      flex-grow: 1;
    }
    
    .search-input::placeholder {
      color: var(--light-text);
    }
    
    .search-button {
      background-color: var(--primary);
      padding: 8px 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      border: none;
      cursor: pointer;
    }
    
    .search-button:hover {
      background-color: var(--primary-light);
    }
    
    .search-button i {
      color: var(--white);
      font-size: 14px;
    }
    
    .cart-button {
      position: relative;
      background-color: var(--primary);
      padding: 8px;
      border-radius: 0.25rem;
      color: var(--white);
      border: none;
      cursor: pointer;
    }
    
    .cart-button:hover {
      background-color: var(--primary-light);
    }
    
    .cart-count {
      position: absolute;
      top: -4px;
      right: -4px;
      background-color: var(--white);
      color: var(--primary);
      font-size: 12px;
      font-weight: bold;
      border-radius: 50%;
      width: 20px;
      height: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .account-dropdown {
      display: flex;
      align-items: center;
      gap: 4px;
      font-size: 14px;
      font-weight: 600;
      color: var(--primary);
      cursor: pointer;
      user-select: none;
      position: relative;
    }
    
    .account-dropdown i.fa-chevron-down {
      font-size: 12px;
      transition: transform 0.2s;
    }
    
    .account-dropdown:hover i.fa-chevron-down {
      transform: rotate(180deg);
    }
    
    .user-button {
      background: none;
      border: none;
      color: var(--primary);
      font-weight: 600;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 4px;
    }
    
    .user-button:hover {
      color: var(--primary-light);
    }
    
    .dropdown-menu {
      position: absolute;
      top: 100%;
      right: 0;
      background-color: var(--white);
      border-radius: 0.25rem;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
      min-width: 160px;
      z-index: 100;
      padding: 8px 0;
      display: none;
    }
    
    .dropdown-menu a {
      display: block;
      padding: 8px 16px;
      color: var(--primary);
      text-decoration: none;
      font-size: 14px;
      font-weight: 500;
    }
    
    .dropdown-menu a:hover {
      background-color: var(--gray);
      color: var(--primary-light);
    }
    
    .account-dropdown:hover .dropdown-menu {
      display: block;
    }
    
    nav {
      background-color: var(--primary);
    }
    
    .nav-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 16px;
    }
    
    .nav-list {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 16px;
      color: var(--white);
      font-size: 14px;
      padding: 8px 0;
      font-weight: 600;
      list-style: none;
      margin: 0;
    }
    
    .nav-list a {
      color: var(--white);
      text-decoration: none;
      padding: 4px 0;
      display: block;
    }
    
    .nav-list a:hover {
      text-decoration: underline;
    }
    
    main {
      flex-grow: 1;
    }
    
    .section-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px 16px;
    }
    
    .section-title {
      font-family: 'Orbitron', sans-serif;
      font-size: 20px;
      color: var(--primary);
      margin-bottom: 16px;
    }
    
    .banner-carousel {
      width: 100%;
      overflow: hidden;
      position: relative;
      border-radius: 0.5rem;
      margin-bottom: 24px;
    }
    
    .banner-track {
      display: flex;
      transition: transform 1s ease-in-out;
    }
    
    .banner-slide {
      min-width: 100%;
      flex-shrink: 0;
    }
    
    .banner-image {
      width: 100%;
      height: auto;
      max-height: 300px;
      object-fit: cover;
      border-radius: 0.5rem;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    
    .carousel-wrapper {
      position: relative;
      overflow: hidden;
      margin: 32px 0 48px;
    }
    
    .carousel-container {
      display: flex;
      gap: 24px;
      background-color: var(--white);
      padding: 16px;
      border-radius: 0.5rem;
      transition: transform 0.3s ease-in-out;
    }
    
    .carousel-slide {
      transition: all 0.2s ease;
      min-width: calc(50% - 12px);
      background-color: var(--white);
      border-radius: 0.5rem;
      padding: 16px;
      display: flex;
      flex-direction: column;
      align-items: center;
      box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
      flex-shrink: 0;
      cursor: pointer;
    }
    
    .carousel-slide:hover {
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
      transform: translateY(-5px);
    }
    
    .carousel-slide img {
      margin-bottom: 12px;
      height: 80px;
      object-fit: contain;
    }
    
    .carousel-slide span {
      font-size: 12px;
      font-weight: 600;
      text-align: center;
      color: var(--primary);
    }
    
    .carousel-button {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background-color: var(--dark-gray);
      border-radius: 50%;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      color: var(--text);
      box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
      border: none;
      cursor: pointer;
      z-index: 10;
    }
    
    .carousel-button:hover {
      background-color: var(--border);
    }
    
    #prevBtn {
      left: -16px;
    }
    
    #nextBtn {
      right: -16px;
    }
    
    footer {
      background-color: var(--white);
      padding: 32px 0;
      border-top: 1px solid var(--border);
    }
    
    .footer-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 16px;
      display: grid;
      grid-template-columns: 1fr;
      gap: 30px;
    }
    
    .footer-logo {
      font-family: 'Orbitron', sans-serif;
      font-size: 24px;
      letter-spacing: 0.1em;
      color: var(--primary);
      margin-bottom: 8px;
    }
    
    .footer-address {
      font-style: normal;
      font-size: 14px;
      color: var(--text);
      margin-bottom: 8px;
    }
    
    .footer-contact {
      font-size: 14px;
      color: var(--text);
      margin-bottom: 4px;
    }
    
    .footer-contact i {
      margin-right: 8px;
    }
    
    .footer-heading {
      font-weight: 600;
      color: var(--primary);
      margin-bottom: 12px;
    }
    
    .footer-links {
      font-size: 14px;
      color: var(--text);
      display: flex;
      flex-direction: column;
      gap: 8px;
      padding: 0;
      list-style: none;
    }
    
    .footer-links a {
      color: inherit;
      text-decoration: none;
      display: flex;
      align-items: center;
    }
    
    .footer-links a:hover {
      text-decoration: underline;
      color: var(--primary);
    }
    
    .footer-links i {
      font-size: 12px;
      margin-right: 8px;
    }
    
    .social-links {
      display: flex;
      gap: 16px;
    }
    
    .social-links a {
      color: var(--primary);
      font-size: 20px;
    }
    
    .social-links a:hover {
      color: var(--primary-light);
    }
    
    @media (min-width: 640px) {
      .carousel-slide {
        min-width: calc(25% - 18px);
      }
      
      .footer-container {
        grid-template-columns: repeat(4, 1fr);
      }
      
      .nav-list {
        justify-content: flex-start;
        gap: 32px;
      }
    }
    
    @media (max-width: 639px) {
      .header-container {
        flex-wrap: wrap;
        height: auto;
        padding: 8px;
      }
      
      .search-form {
        order: 3;
        width: 100%;
        margin: 8px 0;
      }
    }
  </style>
</head>
<body>
  <!-- Header -->
  <header>
    <div class="header-container">
      <h1 class="font-orbitron">BITRONICS</h1>

      <form class="search-form" action="products.php" method="get">
        <input class="search-input" 
               placeholder="Search for Products" 
               type="text"
               name="search"
               aria-label="Search products"/>
        <button class="search-button" aria-label="Search">
          <i class="fas fa-search"></i>
        </button>
      </form>
      
      <div style="display: flex; align-items: center; gap: 16px;">
        <button class="cart-button" aria-label="Cart" onclick="window.location.href='cart.php'">
          <i class="fas fa-shopping-bag"></i>
          <span class="cart-count">0</span>
        </button>
        
        <?php if (isset($_SESSION['user_id'])): ?>
          <div class="account-dropdown">
            <button class="user-button">
              <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
              <i class="fas fa-chevron-down"></i>
            </button>
            <div class="dropdown-menu">
              <a href="account.php"><i class="fas fa-user-circle"></i> My Account</a>
              <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="admin/"><i class="fas fa-cog"></i> Admin Panel</a>
              <?php endif; ?>
              <a href="orders.php"><i class="fas fa-clipboard-list"></i> My Orders</a>
              <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
          </div>
        <?php else: ?>
          <a href="login.php" style="text-decoration: none; color: var(--primary); font-weight: 600;">LOGIN</a>
          <span style="color: var(--primary);">|</span>
          <a href="signup.php" style="text-decoration: none; color: var(--primary); font-weight: 600;">SIGN UP</a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Navigation bar -->
    <nav>
      <div class="nav-container">
        <ul class="nav-list">
          <li><a href="index.php">HOME</a></li>
          <li><a href="products.php">PRODUCTS</a></li>
          <li><a href="#">BRANDS</a></li>
          <li><a href="#">TECHNICAL SUPPORT</a></li>
          <li><a href="#">ABOUT US</a></li>
        </ul>
      </div>
    </nav>
  </header>

  <!-- Main Content -->
  <main>
    <!-- Banner Carousel -->
    <section class="section-container">
      <div class="banner-carousel">
        <div class="banner-track">
          <!-- Slides will be added by JavaScript -->
        </div>
      </div>
    </section>

    <!-- Product carousel -->
    <section class="section-container">
      <h2 class="section-title">Featured Categories</h2>
      
      <div class="carousel-wrapper">
        <div class="carousel-container">
          <!-- These will be populated by JavaScript -->
        </div>
        
        <button id="prevBtn" aria-label="Previous category" class="carousel-button">
          <i class="fas fa-chevron-left"></i>
        </button>

        <button id="nextBtn" aria-label="Next category" class="carousel-button">
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer>
    <div class="footer-container">
      <div>
        <h2 class="footer-logo">BITRONICS</h2>
        <address class="footer-address">
          Quezon Avenue, Cotabato City
        </address>
        <p class="footer-contact">
          <i class="fas fa-phone-alt"></i>+639998893894
        </p>
        <p class="footer-contact">
          <i class="fas fa-envelope"></i>sales@bitronics-electronics.com
        </p>
      </div>

      <div>
        <h3 class="footer-heading">Company</h3>
        <ul class="footer-links">
          <li><a href="#">
            <i class="fas fa-map-marker-alt"></i>Store Locations
          </a></li>
          <li><a href="#">
            <i class="fas fa-star"></i>Reviews
          </a></li>
          <li><a href="#">
            <i class="fas fa-info-circle"></i>About Us
          </a></li>
        </ul>
      </div>
      
      <div>
        <h3 class="footer-heading">Links</h3>
        <ul class="footer-links">
          <li><a href="#" target="_blank">
            <i class="fas fa-external-link-alt"></i>Shopee Official Store
          </a></li>
          <li><a href="#" target="_blank">
            <i class="fas fa-external-link-alt"></i>Lazada Official Store
          </a></li>
        </ul>
      </div>
      
      <div>
        <h3 class="footer-heading">Follow Us</h3>
        <div class="social-links">
          <a href="#"><i class="fab fa-facebook-f"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
        </div>
      </div>
    </div>
  </footer>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize cart count
      updateCartCount();
      
      // Banner Carousel
      const bannerImages = [
        "https://storage.googleapis.com/a1aa/image/db6ef11d-075c-4c73-408c-8ba8fef43b73.jpg",
        "https://storage.googleapis.com/a1aa/image/cabda94a-a6f2-4bb4-3103-f0b0773f2e35.jpg",
        "https://storage.googleapis.com/a1aa/image/7cf3778f-3b25-494a-8b4b-25822dfb9016.jpg"
      ];

      const bannerTrack = document.querySelector('.banner-track');
      
      // Initialize banner carousel
      function initBannerCarousel() {
        bannerTrack.innerHTML = '';
        
        // Create slides
        bannerImages.forEach((image, index) => {
          const slide = document.createElement('div');
          slide.className = 'banner-slide';
          slide.innerHTML = `
            <img alt="Promotional banner ${index + 1}" 
                 class="banner-image" 
                 src="${image}" 
                 loading="${index === 0 ? 'eager' : 'lazy'}"/>
          `;
          bannerTrack.appendChild(slide);
        });
        
        // Start auto-sliding the banners
        startBannerCarousel();
      }
      
      // Function to start auto-sliding
      let currentBannerIndex = 0;
      function startBannerCarousel() {
        setInterval(() => {
          currentBannerIndex = (currentBannerIndex + 1) % bannerImages.length;
          bannerTrack.style.transform = `translateX(-${currentBannerIndex * 100}%)`;
        }, 3000); // Change every 3 seconds
      }

      // Initialize the banner carousel
      initBannerCarousel();

      // Product categories data
      const categories = [
        {
          name: "MEMORY",
          image: "https://storage.googleapis.com/a1aa/image/db6ef11d-075c-4c73-408c-8ba8fef43b73.jpg",
          alt: "Memory module with RGB lighting on top"
        },
        {
          name: "SOLID STATE DRIVES",
          image: "https://storage.googleapis.com/a1aa/image/cabda94a-a6f2-4bb4-3103-f0b0773f2e35.jpg",
          alt: "Black rectangular solid state drive SSD"
        },
        {
          name: "POWER SUPPLY",
          image: "https://storage.googleapis.com/a1aa/image/85e6fa37-e408-4bda-6ffc-e7bdcffd11ae.jpg",
          alt: "Black power supply unit with fan"
        },
        {
          name: "PC CASE",
          image: "https://storage.googleapis.com/a1aa/image/7cf3778f-3b25-494a-8b4b-25822dfb9016.jpg",
          alt: "Black and white PC case with glass side panel"
        },
        {
          name: "MOTHERBOARDS",
          image: "https://storage.googleapis.com/a1aa/image/db6ef11d-075c-4c73-408c-8ba8fef43b73.jpg",
          alt: "Computer motherboard"
        },
        {
          name: "GRAPHICS CARDS",
          image: "https://storage.googleapis.com/a1aa/image/cabda94a-a6f2-4bb4-3103-f0b0773f2e35.jpg",
          alt: "Graphics card"
        }
      ];

      // DOM elements
      const carouselContainer = document.querySelector('.carousel-container');
      const prevBtn = document.getElementById('prevBtn');
      const nextBtn = document.getElementById('nextBtn');

      // Initialize carousel with infinite loop
      function initCarousel() {
        // Clear existing slides
        carouselContainer.innerHTML = '';
        
        // Create slides from categories data
        categories.forEach(category => {
          const slide = document.createElement('div');
          slide.className = 'carousel-slide';
          slide.innerHTML = `
            <img alt="${category.alt}" src="${category.image}" loading="lazy"/>
            <span>${category.name}</span>
          `;
          carouselContainer.appendChild(slide);
        });
        
        // Clone first few slides to end for infinite loop
        const visibleSlides = getVisibleSlidesCount();
        for (let i = 0; i < visibleSlides; i++) {
          const clone = carouselContainer.children[i].cloneNode(true);
          carouselContainer.appendChild(clone);
        }
        
        // Clone last few slides to beginning for infinite loop
        for (let i = categories.length - 1; i >= categories.length - visibleSlides; i--) {
          const clone = carouselContainer.children[i].cloneNode(true);
          carouselContainer.insertBefore(clone, carouselContainer.firstChild);
        }
        
        // Initialize carousel state
        currentIndex = visibleSlides;
        updateCarousel();
      }

      // Carousel state
      let currentIndex = 0;
      let visibleSlides = getVisibleSlidesCount();

      // Get number of visible slides based on screen size
      function getVisibleSlidesCount() {
        return window.innerWidth >= 640 ? 4 : 2;
      }

      // Update carousel position
      function updateCarousel() {
        const slideWidth = document.querySelector('.carousel-slide').offsetWidth + 24; // width + gap
        const offset = -currentIndex * slideWidth;
        carouselContainer.style.transform = `translateX(${offset}px)`;
      }

      // Handle transition end for infinite loop
      function handleCarouselTransitionEnd() {
        const totalSlides = categories.length + 2 * getVisibleSlidesCount();
        
        // If we're at the beginning clones, jump to real slides
        if (currentIndex <= 0) {
          currentIndex = categories.length;
          carouselContainer.style.transition = 'none';
          updateCarousel();
          // Force reflow
          void carouselContainer.offsetWidth;
          carouselContainer.style.transition = 'transform 0.3s ease-in-out';
        }
        // If we're at the end clones, jump to real slides
        else if (currentIndex >= categories.length + getVisibleSlidesCount()) {
          currentIndex = getVisibleSlidesCount();
          carouselContainer.style.transition = 'none';
          updateCarousel();
          // Force reflow
          void carouselContainer.offsetWidth;
          carouselContainer.style.transition = 'transform 0.3s ease-in-out';
        }
      }

      // Event listeners
      nextBtn.addEventListener('click', () => {
        currentIndex++;
        updateCarousel();
      });

      prevBtn.addEventListener('click', () => {
        currentIndex--;
        updateCarousel();
      });

      // Handle window resize
      window.addEventListener('resize', () => {
        const newVisibleSlides = getVisibleSlidesCount();
        if (newVisibleSlides !== visibleSlides) {
          visibleSlides = newVisibleSlides;
          initCarousel(); // Reinitialize carousel on resize
        }
      });

      // Add transition end listener
      carouselContainer.addEventListener('transitionend', handleCarouselTransitionEnd);

      // Initialize the carousel
      initCarousel();

      // Add click handlers to slides
      document.addEventListener('click', function(e) {
        if (e.target.closest('.carousel-slide')) {
          const slide = e.target.closest('.carousel-slide');
          const categoryName = slide.querySelector('span').textContent;
          window.location.href = `products.php?category=${encodeURIComponent(categoryName)}`;
        }
      });
    });
    
    // Function to update cart count
    function updateCartCount() {
      fetch('get_cart_count.php')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const cartCountElements = document.querySelectorAll('.cart-count');
            cartCountElements.forEach(element => {
              element.textContent = data.count;
            });
          }
        });
    }
  </script>
</body>
</html>