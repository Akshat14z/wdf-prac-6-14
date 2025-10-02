<?php
require_once 'config.php';

// Redirect to dashboard if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Login System - Practice 10</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-blue: #001BB7;
            --secondary-blue: #0046FF;
            --orange: #FF8040;
            --light-gray: #E9E9E9;
            --white: #ffffff;
            --text-dark: #333333;
            --text-light: #666666;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            min-height: 100vh;
        }

        /* Header */
        .header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            color: var(--white);
            font-size: 1.5rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-buttons {
            display: flex;
            gap: 1rem;
        }

        /* Hero Section */
        .hero {
            padding: 4rem 2rem;
            text-align: center;
            max-width: 1200px;
            margin: 0 auto;
            color: var(--white);
        }

        .hero-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            color: var(--orange);
        }

        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .hero .subtitle {
            font-size: 1.2rem;
            margin-bottom: 3rem;
            opacity: 0.9;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.8;
        }

        /* Features Grid */
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .feature {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .feature:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .feature-icon {
            font-size: 2.5rem;
            color: var(--orange);
            margin-bottom: 1rem;
        }

        .feature-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--white);
        }

        .feature-text {
            font-size: 0.9rem;
            opacity: 0.9;
            color: var(--white);
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--orange), #ff6b2b);
            color: var(--white);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 128, 64, 0.4);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--white);
            color: var(--white);
        }

        .btn-outline:hover {
            background: var(--white);
            color: var(--primary-blue);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            color: var(--white);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Demo Info Card */
        .demo-info {
            background: var(--white);
            border-radius: 15px;
            padding: 2rem;
            margin: 3rem auto;
            max-width: 600px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .demo-info h3 {
            color: var(--primary-blue);
            margin-bottom: 1.5rem;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .demo-account {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1rem;
            margin: 0.5rem 0;
            background: var(--light-gray);
            border-radius: 8px;
            font-family: 'Courier New', monospace;
        }

        .demo-account strong {
            color: var(--primary-blue);
            font-weight: 600;
        }

        .demo-account span {
            color: var(--text-light);
            background: var(--white);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        /* Security Features */
        .security-features {
            background: var(--white);
            margin: 4rem 0;
            padding: 3rem 2rem;
            border-radius: 20px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .security-features h2 {
            text-align: center;
            color: var(--primary-blue);
            margin-bottom: 2rem;
            font-size: 2rem;
        }

        .security-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .security-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem;
            border-radius: 10px;
            transition: background-color 0.3s ease;
        }

        .security-item:hover {
            background-color: var(--light-gray);
        }

        .security-icon {
            color: var(--orange);
            font-size: 1.2rem;
            margin-top: 0.2rem;
        }

        .security-text {
            color: var(--text-dark);
            font-size: 0.95rem;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 2rem;
            color: rgba(255, 255, 255, 0.8);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }

            .hero .subtitle {
                font-size: 1rem;
            }

            .features {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1rem;
            }

            .feature {
                padding: 1.5rem;
            }

            .nav {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .nav-buttons {
                flex-wrap: wrap;
                justify-content: center;
            }

            .demo-account {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
            }

            .security-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero, .features, .demo-info, .security-features {
            animation: fadeInUp 0.8s ease-out;
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="logo">
                <i class="fas fa-shield-alt"></i>
                Secure Login System
            </div>
            <div class="nav-buttons">
                <a href="login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </a>
                <a href="register.php" class="btn btn-outline">
                    <i class="fas fa-user-plus"></i>
                    Register
                </a>
            </div>
        </nav>
    </header>

    <main class="hero">
        <div class="hero-icon">
            <i class="fas fa-lock"></i>
        </div>
        <h1>Secure Login System</h1>
        <p class="subtitle">
            Practice 10 - A comprehensive authentication system featuring advanced session management, 
            role-based access control, and enterprise-level security features built with PHP and MySQL.
        </p>

        <div class="features">
            <div class="feature">
                <div class="feature-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="feature-title">User Authentication</div>
                <div class="feature-text">Secure login with password hashing and session management</div>
            </div>
            <div class="feature">
                <div class="feature-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="feature-title">Session Timeout</div>
                <div class="feature-text">Automatic logout after 30 minutes of inactivity</div>
            </div>
            <div class="feature">
                <div class="feature-icon">
                    <i class="fas fa-users-cog"></i>
                </div>
                <div class="feature-title">Role-Based Access</div>
                <div class="feature-text">Admin and user roles with different permission levels</div>
            </div>
            <div class="feature">
                <div class="feature-icon">
                    <i class="fas fa-history"></i>
                </div>
                <div class="feature-title">Activity Tracking</div>
                <div class="feature-text">Complete login history and session monitoring</div>
            </div>
        </div>

        <div style="margin: 2rem 0;">
            <a href="login.php" class="btn btn-primary" style="margin: 0.5rem;">
                <i class="fas fa-arrow-right"></i>
                Access Login System
            </a>
            <a href="dashboard.php" class="btn btn-secondary" style="margin: 0.5rem;">
                <i class="fas fa-tachometer-alt"></i>
                View Dashboard
            </a>
        </div>
    </main>

    <div class="demo-info">
        <h3>
            <i class="fas fa-key"></i>
            Demo Accounts Available
        </h3>
        <div class="demo-account">
            <strong>Administrator:</strong>
            <span>admin / admin123</span>
        </div>
        <div class="demo-account">
            <strong>Standard User:</strong>
            <span>user / user123</span>
        </div>
        <div class="demo-account">
            <strong>Demo Account:</strong>
            <span>demo / demo123</span>
        </div>
    </div>

    <section class="security-features">
        <h2><i class="fas fa-shield-alt"></i> Security Features</h2>
        <div class="security-grid">
            <div class="security-item">
                <div class="security-icon"><i class="fas fa-check-circle"></i></div>
                <div class="security-text">Password hashing with bcrypt algorithm and configurable cost</div>
            </div>
            <div class="security-item">
                <div class="security-icon"><i class="fas fa-check-circle"></i></div>
                <div class="security-text">Session timeout and automatic cleanup of expired sessions</div>
            </div>
            <div class="security-item">
                <div class="security-icon"><i class="fas fa-check-circle"></i></div>
                <div class="security-text">Brute force protection with account lockout mechanism</div>
            </div>
            <div class="security-item">
                <div class="security-icon"><i class="fas fa-check-circle"></i></div>
                <div class="security-text">SQL injection prevention using prepared statements</div>
            </div>
            <div class="security-item">
                <div class="security-icon"><i class="fas fa-check-circle"></i></div>
                <div class="security-text">Session fixation protection and secure session handling</div>
            </div>
            <div class="security-item">
                <div class="security-icon"><i class="fas fa-check-circle"></i></div>
                <div class="security-text">Remember me functionality with secure token management</div>
            </div>
            <div class="security-item">
                <div class="security-icon"><i class="fas fa-check-circle"></i></div>
                <div class="security-text">Role-based access control for different user types</div>
            </div>
            <div class="security-item">
                <div class="security-icon"><i class="fas fa-check-circle"></i></div>
                <div class="security-text">Comprehensive login attempt tracking and monitoring</div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <p>&copy; 2025 Practice 10 - Secure Login System | Built with PHP & MySQL</p>
    </footer>

    <script>
        // Add smooth scroll behavior for any internal links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Add loading animation for buttons
        document.querySelectorAll('.btn').forEach(button => {
            button.addEventListener('click', function() {
                if (!this.classList.contains('loading')) {
                    this.classList.add('loading');
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
                    
                    // Reset after navigation (this won't actually run for external links)
                    setTimeout(() => {
                        this.classList.remove('loading');
                        this.innerHTML = originalText;
                    }, 2000);
                }
            });
        });

        // Add hover effects to feature cards
        document.querySelectorAll('.feature').forEach(feature => {
            feature.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.05)';
            });
            
            feature.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    </script>
</body>
</html>