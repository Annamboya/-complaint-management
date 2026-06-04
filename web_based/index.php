<?php include "includes/header.php"; ?>

<!-- HERO SLIDESHOW -->
<section class="hero-slideshow">
    <div class="slides">
        <div class="slide active" style="background-image: url('assets/images/slider1.jpg');"></div>
        <div class="slide" style="background-image: url('assets/images/slider2.jpg');"></div>
        <div class="slide" style="background-image: url('assets/images/slider3.jpg');"></div>
    </div>

    <div class="hero-content">
        <div class="hero-text-wrapper">
            <span class="badge-top">Official University System</span>
            <h1>Maseno Complaint Management Platform</h1>
            <p>A secure, transparent, and intelligent system designed to improve complaint handling, accountability, and institutional efficiency.</p>
            <div class="hero-buttons">
                <a href="login.php" class="btn primary">Login</a>
                <a href="register.php" class="btn secondary">Get Started</a>
            </div>
        </div>
    </div>
</section>

<!-- ABOUT -->
<section class="about" id="about">
    <div class="container narrow">
        <h2>About the Platform</h2>
        <p>
            The Maseno Web-Based Complaint Management System (WCMS) replaces manual processes 
            with a structured digital workflow. It enables students and staff to submit, 
            track, and resolve complaints efficiently while ensuring transparency and 
            institutional accountability.
        </p>
    </div>
</section>

<!-- FEATURES -->
<section class="features" id="features">
    <div class="container">
        <h2>Core Features</h2>
        <div class="feature-grid">
            <div class="feature-card">
                <div class="icon">📩</div>
                <h4>Online Submission</h4>
                <p>Submit complaints securely anytime from any device.</p>
            </div>
            <div class="feature-card">
                <div class="icon">📊</div>
                <h4>Real-Time Tracking</h4>
                <p>Monitor progress from submission to final resolution.</p>
            </div>
            <div class="feature-card">
                <div class="icon">👨‍💼</div>
                <h4>Admin Control Panel</h4>
                <p>Manage, assign and monitor complaints with ease.</p>
            </div>
            <div class="feature-card">
                <div class="icon">🔔</div>
                <h4>Automated Alerts</h4>
                <p>Instant email notifications for status updates.</p>
            </div>
            <div class="feature-card">
                <div class="icon">🔐</div>
                <h4>Secure Infrastructure</h4>
                <p>Role-based access and protected database storage.</p>
            </div>
            <div class="feature-card">
                <div class="icon">📈</div>
                <h4>Analytics & Reports</h4>
                <p>Data-driven insights for institutional improvement.</p>
            </div>
        </div>
    </div>
</section>

<!-- PROCESS -->
<section class="process" id="process">
    <div class="container narrow">
        <h2>How It Works</h2>
        <div class="steps">
            <div class="step">1. Register Account</div>
            <div class="step">2. Submit Complaint</div>
            <div class="step">3. Admin Assigns Case</div>
            <div class="step">4. Staff Resolves Issue</div>
            <div class="step">5. User Receives Feedback</div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta" id="cta">
    <div class="container">
        <h2>Strengthen Transparency & Accountability</h2>
        <p>Empower your institution with a modern digital solution.</p>
        <a href="register.php" class="btn primary large">Create Account</a>
    </div>
</section>

<?php include "includes/footer.php"; ?>

<!-- JS Slideshow + Hero Animation -->
<script>
let slides = document.querySelectorAll('.hero-slideshow .slide');
let heroText = document.querySelector('.hero-text-wrapper');
let current = 0;

function showSlide(index){
    slides.forEach((slide) => slide.classList.remove('active'));
    slides[index].classList.add('active');

    // Hero fly-in animation
    heroText.style.opacity = 0;
    heroText.style.animation = 'none';
    void heroText.offsetWidth; // force reflow
    heroText.style.animation = 'flyIn 1s ease-out forwards';
}

function nextSlide(){
    current = (current + 1) % slides.length;
    showSlide(current);
}

// auto-slide every 5s
setInterval(nextSlide, 5000);
</script>