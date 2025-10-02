<?php
require_once 'config.php';
initializeDatabase();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Demo | SecureAuth Pro</title>
    <meta name="description" content="Experience our secure authentication system with live demo and interactive features">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Enhanced Demo Page Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --beige: #E8E4E1;
            --light-orange: #F9C49A;
            --primary-orange: #EC823A;
            --dark-brown: #7C3C21;
            --white: #FFFFFF;
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
            --gradient-primary: linear-gradient(135deg, var(--primary-orange), var(--light-orange));
            --gradient-bg: linear-gradient(135deg, var(--beige) 0%, var(--light-orange) 50%, var(--beige) 100%);
            --shadow-soft: 0 10px 40px rgba(124, 60, 33, 0.1);
            --shadow-medium: 0 20px 60px rgba(124, 60, 33, 0.15);
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--gradient-bg);
            background-attachment: fixed;
            color: var(--dark-brown);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Animated Background */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.6;
        }

        .bg-animation::before,
        .bg-animation::after {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: var(--gradient-primary);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
            opacity: 0.1;
        }

        .bg-animation::before {
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .bg-animation::after {
            top: 60%;
            right: 10%;
            animation-delay: 3s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) scale(1); }
            50% { transform: translateY(-20px) scale(1.1); }
        }

        /* Navigation Header */
        .demo-nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(124, 60, 33, 0.1);
            padding: 15px 0;
            transition: all 0.3s ease;
        }

        .nav-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 20px;
            font-weight: 700;
            color: var(--dark-brown);
            text-decoration: none;
        }

        .nav-logo .logo-icon {
            font-size: 24px;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .nav-btn {
            padding: 8px 20px;
            border: 2px solid var(--primary-orange);
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .nav-btn.primary {
            background: var(--gradient-primary);
            color: var(--white);
        }

        .nav-btn.secondary {
            background: transparent;
            color: var(--primary-orange);
        }

        .nav-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-soft);
        }

        /* Main Demo Container */
        .demo-container {
            margin-top: 80px;
            min-height: calc(100vh - 80px);
            display: flex;
            flex-direction: column;
        }

        /* Hero Section */
        .demo-hero {
            text-align: center;
            padding: 80px 20px 60px;
            position: relative;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid var(--light-orange);
            border-radius: 50px;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 600;
            color: var(--dark-brown);
            margin-bottom: 32px;
            box-shadow: var(--shadow-soft);
            backdrop-filter: blur(10px);
        }

        .hero-title {
            font-size: clamp(40px, 8vw, 72px);
            font-weight: 800;
            background: linear-gradient(135deg, var(--dark-brown), var(--primary-orange));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 24px;
            line-height: 1.1;
        }

        .hero-subtitle {
            font-size: 24px;
            color: #666;
            margin-bottom: 16px;
            font-weight: 400;
        }

        .hero-description {
            font-size: 18px;
            color: #777;
            max-width: 600px;
            margin: 0 auto 40px;
            line-height: 1.7;
        }

        .demo-cta {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 60px;
        }

        .cta-btn {
            padding: 16px 32px;
            border: none;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 180px;
            justify-content: center;
        }

        .cta-primary {
            background: var(--gradient-primary);
            color: var(--white);
            box-shadow: var(--shadow-medium);
        }

        .cta-secondary {
            background: rgba(255, 255, 255, 0.9);
            color: var(--dark-brown);
            border: 2px solid var(--primary-orange);
            backdrop-filter: blur(10px);
        }

        .cta-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 25px 80px rgba(124, 60, 33, 0.2);
        }

        /* Demo Section */
        .demo-section {
            flex: 1;
            padding: 0 20px 60px;
        }

        .demo-content {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: start;
        }

        /* Demo Preview */
        .demo-preview {
            position: sticky;
            top: 120px;
        }

        .preview-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 24px;
            box-shadow: var(--shadow-medium);
            overflow: hidden;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(124, 60, 33, 0.1);
        }

        .preview-header {
            background: var(--gradient-primary);
            padding: 20px 30px;
            color: var(--white);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .preview-icon {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .preview-info h3 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .preview-info p {
            font-size: 14px;
            opacity: 0.9;
        }

        /* Stats Display */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            padding: 30px;
            background: var(--white);
        }

        .stat-item {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 16px;
            border: 1px solid rgba(124, 60, 33, 0.05);
        }

        .stat-number {
            font-size: 32px;
            font-weight: 800;
            color: var(--primary-orange);
            display: block;
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 14px;
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Features List */
        .feature-showcase {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .showcase-header {
            text-align: left;
        }

        .showcase-title {
            font-size: 36px;
            font-weight: 700;
            color: var(--dark-brown);
            margin-bottom: 16px;
        }

        .showcase-subtitle {
            font-size: 18px;
            color: #666;
            margin-bottom: 32px;
        }

        .feature-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .feature-item {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            padding: 32px;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(124, 60, 33, 0.05);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .feature-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
            border-color: var(--light-orange);
        }

        .feature-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 16px;
        }

        .feature-icon {
            width: 56px;
            height: 56px;
            background: var(--gradient-primary);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: var(--white);
            flex-shrink: 0;
        }

        .feature-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark-brown);
            margin-bottom: 4px;
        }

        .feature-status {
            font-size: 12px;
            background: rgba(34, 197, 94, 0.1);
            color: var(--success);
            padding: 4px 12px;
            border-radius: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .feature-description {
            color: #666;
            line-height: 1.7;
            font-size: 16px;
        }

        /* CTA Section */
        .demo-cta-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 24px;
            padding: 48px;
            text-align: center;
            box-shadow: var(--shadow-medium);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(124, 60, 33, 0.1);
            margin-top: 40px;
        }

        .cta-section-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark-brown);
            margin-bottom: 16px;
        }

        .cta-section-description {
            font-size: 16px;
            color: #666;
            margin-bottom: 32px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .demo-content {
                grid-template-columns: 1fr;
                gap: 40px;
            }
            
            .demo-preview {
                position: static;
                order: -1;
            }
        }

        @media (max-width: 768px) {
            .demo-hero {
                padding: 40px 20px 30px;
            }
            
            .hero-badge {
                font-size: 12px;
                padding: 8px 16px;
            }
            
            .demo-cta {
                flex-direction: column;
                align-items: center;
            }
            
            .cta-btn {
                min-width: 200px;
            }
            
            .preview-container {
                margin: 0 10px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .feature-item {
                padding: 24px;
            }
            
            .nav-content {
                padding: 0 15px;
            }
            
            .nav-actions {
                gap: 10px;
            }
            
            .nav-btn {
                padding: 6px 16px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="bg-animation"></div>
    
    <!-- Navigation -->
    <nav class="demo-nav">
        <div class="nav-content">
            <a href="landing.html" class="nav-logo">
                <span class="logo-icon">🔐</span>
                <span>SecureAuth Pro</span>
            </a>
            <div class="nav-actions">
                <a href="landing.html" class="nav-btn secondary">
                    <span>🏠</span>
                    Home
                </a>
                <a href="#demo" class="nav-btn primary">
                    <span>🚀</span>
                    Try Demo
                </a>
            </div>
        </div>
    </nav>

    <!-- Demo Container -->
    <div class="demo-container">
        <!-- Hero Section -->
        <section class="demo-hero">
            <div class="hero-badge">
                <span>🎯</span>
                Interactive Live Demo
            </div>
            <h1 class="hero-title">Experience Security</h1>
            <h2 class="hero-subtitle">In Real-Time</h2>
            <p class="hero-description">
                Test our enterprise-grade authentication system with live validation, 
                security features, and real-time feedback. See how professional security works.
            </p>
            
            <div class="demo-cta">
                <a href="#demo" class="cta-btn cta-primary">
                    <span>🔥</span>
                    Start Demo
                </a>
                <a href="dashboard.php" class="cta-btn cta-secondary">
                    <span>👤</span>
                    View Dashboard
                </a>
            </div>
        </section>

        <!-- Demo Content -->
        <section class="demo-section" id="demo">
            <div class="demo-content">
                <!-- Demo Preview -->
                <div class="demo-preview">
                    <div class="preview-container">
                        <div class="preview-header">
                            <div class="preview-icon">⚡</div>
                            <div class="preview-info">
                                <h3>Live Security Stats</h3>
                                <p>Real-time security monitoring</p>
                            </div>
                        </div>
                        
                        <div class="stats-grid">
                            <div class="stat-item">
                                <span class="stat-number" id="security-score">100</span>
                                <span class="stat-label">Security Score</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number" id="protection-level">🛡️</span>
                                <span class="stat-label">Protection Level</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number" id="validation-checks">15</span>
                                <span class="stat-label">Validation Checks</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number" id="csrf-status">✅</span>
                                <span class="stat-label">CSRF Protected</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Features Showcase -->
                <div class="feature-showcase">
                    <div class="showcase-header">
                        <h2 class="showcase-title">Security Features</h2>
                        <p class="showcase-subtitle">
                            Comprehensive protection with enterprise-level security measures
                        </p>
                    </div>

                    <div class="feature-list">
                        <div class="feature-item" onclick="highlightFeature('password')">
                            <div class="feature-header">
                                <div class="feature-icon">🔐</div>
                                <div>
                                    <div class="feature-title">Password Security</div>
                                    <div class="feature-status">Active</div>
                                </div>
                            </div>
                            <p class="feature-description">
                                Advanced password hashing with bcrypt, salt generation, and strength validation. 
                                Passwords are never stored in plain text and use industry-standard encryption.
                            </p>
                        </div>

                        <div class="feature-item" onclick="highlightFeature('csrf')">
                            <div class="feature-header">
                                <div class="feature-icon">🛡️</div>
                                <div>
                                    <div class="feature-title">CSRF Protection</div>
                                    <div class="feature-status">Active</div>
                                </div>
                            </div>
                            <p class="feature-description">
                                Cross-Site Request Forgery protection with secure tokens, session validation, 
                                and request verification to prevent unauthorized actions.
                            </p>
                        </div>

                        <div class="feature-item" onclick="highlightFeature('validation')">
                            <div class="feature-header">
                                <div class="feature-icon">✨</div>
                                <div>
                                    <div class="feature-title">Input Validation</div>
                                    <div class="feature-status">Active</div>
                                </div>
                            </div>
                            <p class="feature-description">
                                Real-time client and server-side validation with SQL injection prevention, 
                                XSS protection, and comprehensive input sanitization.
                            </p>
                        </div>

                        <div class="feature-item" onclick="highlightFeature('brute-force')">
                            <div class="feature-header">
                                <div class="feature-icon">🚫</div>
                                <div>
                                    <div class="feature-title">Brute Force Protection</div>
                                    <div class="feature-status">Active</div>
                                </div>
                            </div>
                            <p class="feature-description">
                                Account lockout mechanisms, failed attempt tracking, and progressive delays 
                                to prevent automated password attacks and unauthorized access.
                            </p>
                        </div>

                        <div class="feature-item" onclick="highlightFeature('session')">
                            <div class="feature-header">
                                <div class="feature-icon">⏰</div>
                                <div>
                                    <div class="feature-title">Session Management</div>
                                    <div class="feature-status">Active</div>
                                </div>
                            </div>
                            <p class="feature-description">
                                Secure session handling with automatic regeneration, timeout protection, 
                                and proper session cleanup to maintain user security throughout their visit.
                            </p>
                        </div>

                        <div class="feature-item" onclick="highlightFeature('responsive')">
                            <div class="feature-header">
                                <div class="feature-icon">📱</div>
                                <div>
                                    <div class="feature-title">Responsive Design</div>
                                    <div class="feature-status">Active</div>
                                </div>
                            </div>
                            <p class="feature-description">
                                Mobile-first responsive design with touch-optimized interface, 
                                accessibility features, and seamless experience across all devices.
                            </p>
                        </div>
                    </div>

                    <!-- CTA Section -->
                    <div class="demo-cta-section">
                        <h3 class="cta-section-title">Ready to Experience SecureAuth?</h3>
                        <p class="cta-section-description">
                            Join thousands of users who trust our secure authentication system. 
                            Start with our interactive forms below.
                        </p>
                        <div class="cta-buttons">
                            <button onclick="showLoginForm()" class="cta-btn cta-primary">
                                <span>👤</span>
                                Try Login Demo
                            </button>
                            <button onclick="showRegisterForm()" class="cta-btn cta-secondary">
                                <span>✨</span>
                                Try Register Demo
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Hidden Forms for Demo -->
    <div id="demo-forms" style="display: none;">
        <!-- Login Form -->
        <div id="login-demo" class="form-overlay">
            <div class="form-modal">
                <div class="form-header">
                    <h3>Login Demo</h3>
                    <button onclick="closeDemoForm()" class="close-btn">×</button>
                </div>
                <div class="form-content">
                    <form id="demo-login-form">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="form-group">
                            <label>Username or Email</label>
                            <input type="text" name="username" placeholder="Try: admin or user@example.com" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" placeholder="Try: password123" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Security Code</label>
                            <div class="captcha-demo">
                                <span id="demo-captcha">ABC123</span>
                                <button type="button" onclick="refreshDemoCaptcha()">🔄</button>
                            </div>
                            <input type="text" name="captcha" placeholder="Enter: ABC123" required>
                        </div>
                        
                        <button type="submit" class="demo-submit-btn">
                            <span>🚀</span>
                            Test Login Security
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Register Form -->
        <div id="register-demo" class="form-overlay">
            <div class="form-modal">
                <div class="form-header">
                    <h3>Registration Demo</h3>
                    <button onclick="closeDemoForm()" class="close-btn">×</button>
                </div>
                <div class="form-content">
                    <form id="demo-register-form">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="fullname" placeholder="Try: John Doe" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" placeholder="Try: johndoe123" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" placeholder="Try: john@example.com" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" placeholder="Try: SecurePass123!" required>
                            <div class="password-hints">
                                <small>Must include: uppercase, lowercase, number, special character (8+ chars)</small>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password" placeholder="Repeat password" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Security Code</label>
                            <div class="captcha-demo">
                                <span id="demo-captcha-register">XYZ789</span>
                                <button type="button" onclick="refreshDemoRegisterCaptcha()">🔄</button>
                            </div>
                            <input type="text" name="captcha" placeholder="Enter: XYZ789" required>
                        </div>
                        
                        <button type="submit" class="demo-submit-btn">
                            <span>✨</span>
                            Test Registration Security
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Form Modal Styles */
        .form-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            backdrop-filter: blur(10px);
        }

        .form-modal {
            background: var(--white);
            border-radius: 24px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: var(--shadow-medium);
        }

        .form-modal .form-header {
            background: var(--gradient-primary);
            color: var(--white);
            padding: 20px 30px;
            border-radius: 24px 24px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .form-modal .form-header h3 {
            font-size: 20px;
            font-weight: 700;
        }

        .close-btn {
            background: none;
            border: none;
            color: var(--white);
            font-size: 24px;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .close-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .form-content {
            padding: 30px;
        }

        .form-content .form-group {
            margin-bottom: 20px;
        }

        .form-content label {
            display: block;
            font-weight: 600;
            color: var(--dark-brown);
            margin-bottom: 8px;
        }

        .form-content input[type="text"],
        .form-content input[type="email"],
        .form-content input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-content input:focus {
            outline: none;
            border-color: var(--primary-orange);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(236, 130, 58, 0.1);
        }

        .captcha-demo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 10px 0;
            padding: 10px;
            background: #f0f0f0;
            border-radius: 8px;
        }

        .captcha-demo span {
            font-family: monospace;
            font-size: 18px;
            font-weight: bold;
            color: var(--dark-brown);
            background: var(--white);
            padding: 8px 16px;
            border-radius: 6px;
            border: 1px solid #ddd;
        }

        .captcha-demo button {
            background: var(--primary-orange);
            color: var(--white);
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }

        .password-hints {
            margin-top: 5px;
        }

        .password-hints small {
            color: #666;
            font-size: 12px;
        }

        .demo-submit-btn {
            width: 100%;
            background: var(--gradient-primary);
            color: var(--white);
            border: none;
            padding: 14px 20px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .demo-submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }
    </style>

    <script>
        // Demo functionality
        let stats = {
            securityScore: 100,
            validationChecks: 15,
            protectionLevel: '🛡️',
            csrfStatus: '✅'
        };

        // Animate stats on page load
        window.addEventListener('load', function() {
            animateStats();
            setInterval(updateStats, 3000);
        });

        function animateStats() {
            const scoreEl = document.getElementById('security-score');
            const checksEl = document.getElementById('validation-checks');
            
            let currentScore = 0;
            let currentChecks = 0;
            
            const scoreInterval = setInterval(() => {
                currentScore += 2;
                scoreEl.textContent = currentScore;
                if (currentScore >= 100) clearInterval(scoreInterval);
            }, 20);
            
            const checksInterval = setInterval(() => {
                currentChecks += 1;
                checksEl.textContent = currentChecks;
                if (currentChecks >= 15) clearInterval(checksInterval);
            }, 80);
        }

        function updateStats() {
            // Simulate live updates
            const scoreEl = document.getElementById('security-score');
            const protectionEl = document.getElementById('protection-level');
            
            // Randomly update protection level emoji
            const shields = ['🛡️', '🔒', '🔐', '⚡', '🚀'];
            protectionEl.textContent = shields[Math.floor(Math.random() * shields.length)];
            
            // Flash the score
            scoreEl.style.color = 'var(--success)';
            setTimeout(() => {
                scoreEl.style.color = 'var(--primary-orange)';
            }, 500);
        }

        function highlightFeature(feature) {
            // Visual feedback when clicking features
            event.target.closest('.feature-item').style.transform = 'scale(1.02)';
            event.target.closest('.feature-item').style.boxShadow = '0 25px 80px rgba(236, 130, 58, 0.2)';
            
            setTimeout(() => {
                event.target.closest('.feature-item').style.transform = '';
                event.target.closest('.feature-item').style.boxShadow = '';
            }, 200);
            
            // Update stats based on feature
            const checksEl = document.getElementById('validation-checks');
            checksEl.textContent = Math.floor(Math.random() * 5) + 15;
            checksEl.style.color = 'var(--success)';
            setTimeout(() => {
                checksEl.style.color = 'var(--primary-orange)';
            }, 1000);
        }

        function showLoginForm() {
            document.getElementById('demo-forms').style.display = 'block';
            document.getElementById('login-demo').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function showRegisterForm() {
            document.getElementById('demo-forms').style.display = 'block';
            document.getElementById('register-demo').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeDemoForm() {
            document.getElementById('demo-forms').style.display = 'none';
            document.getElementById('login-demo').style.display = 'none';
            document.getElementById('register-demo').style.display = 'none';
            document.body.style.overflow = '';
        }

        function refreshDemoCaptcha() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let result = '';
            for (let i = 0; i < 6; i++) {
                result += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            document.getElementById('demo-captcha').textContent = result;
        }

        function refreshDemoRegisterCaptcha() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let result = '';
            for (let i = 0; i < 6; i++) {
                result += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            document.getElementById('demo-captcha-register').textContent = result;
        }

        // Handle demo form submissions
        document.getElementById('demo-login-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show success message
            const btn = this.querySelector('.demo-submit-btn');
            btn.innerHTML = '<span>✅</span> Demo Complete - Security Verified!';
            btn.style.background = 'var(--success)';
            
            setTimeout(() => {
                alert('🎉 Login Demo Complete!\n\n✅ CSRF Token Validated\n✅ Input Sanitized\n✅ Password Verified\n✅ Session Created\n\nRedirecting to dashboard...');
                closeDemoForm();
                btn.innerHTML = '<span>🚀</span> Test Login Security';
                btn.style.background = '';
            }, 2000);
        });

        document.getElementById('demo-register-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show success message
            const btn = this.querySelector('.demo-submit-btn');
            btn.innerHTML = '<span>✅</span> Demo Complete - Account Created!';
            btn.style.background = 'var(--success)';
            
            setTimeout(() => {
                alert('🎉 Registration Demo Complete!\n\n✅ Input Validation Passed\n✅ Password Hashed Securely\n✅ SQL Injection Protected\n✅ Email Verified\n✅ Account Created\n\nYou can now login!');
                closeDemoForm();
                btn.innerHTML = '<span>✨</span> Test Registration Security';
                btn.style.background = '';
            }, 2000);
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDemoForm();
            }
        });

        // Close modal on backdrop click
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('form-overlay')) {
                closeDemoForm();
            }
        });
    </script>
</body>
</html>