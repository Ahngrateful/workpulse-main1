<?php
session_start();
require_once '../db.php';

// Check if user is logged in and has user role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 14) {
    header('Location: ../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Dashboard | WorkPulse</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Import professional fonts */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        :root {
            --primary-color: #8B4513;
            /* Terracotta brown from the logo */
            --dark-bg: #2C3639;
            /* Deep charcoal */
            --card-bg: #ffffff;
            --text-primary: #2d3748;
            --text-secondary: #718096;
            --accent-orange: #F4A460;
            /* Soft orange from logo */
            --accent-green: #8B8B6E;
            /* Muted sage green from logo */
            --gradient-start: #F4A460;
            /* Warm orange gradient */
            --gradient-end: #8B4513;
            /* Terracotta end */
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
            --header-height: 60px;
            --footer-height: 30px;
            /* Reduced from 40px to 30px */
            --icon-size: 20px;

            /* Typography variables */
            --font-primary: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            --font-size-xs: 0.75rem;
            /* 12px */
            --font-size-sm: 0.875rem;
            /* 14px */
            --font-size-base: 1rem;
            /* 16px */
            --font-size-lg: 1.125rem;
            /* 18px */
            --font-size-xl: 1.25rem;
            /* 20px */
            --font-size-2xl: 1.5rem;
            /* 24px */
            --font-size-3xl: 1.875rem;
            /* 30px */

            /* Font weights */
            --font-light: 300;
            --font-regular: 400;
            --font-medium: 500;
            --font-semibold: 600;
            --font-bold: 700;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: var(--font-primary);
        }

        body {
            background: #f8fafc;
            color: var(--text-primary);
            font-size: var(--font-size-base);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Header Styles */
        .header {
            height: var(--header-height);
            background: linear-gradient(to right, var(--gradient-start), var(--gradient-end));
            border-bottom: 1px solid #e2e8f0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .brand h1 {
            font-size: var(--font-size-xl);
            font-weight: var(--font-semibold);
            color: #ffffff;
            letter-spacing: -0.025em;
        }

        .logo {
            width: 35px;
            height: 35px;
        }

        .sidebar-toggle {
            cursor: pointer;
            padding: 0.5rem;
            color: #ffffff;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-menu span {
            font-size: var(--font-size-sm);
            font-weight: var(--font-medium);
            color: #ffffff;
        }

        .user-menu .nav-item {
            padding: 0.5rem;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        /* Sidebar Styles */
        .sidebar {
            background: var(--dark-bg);
            width: var(--sidebar-width);
            position: fixed;
            left: 0;
            top: var(--header-height);
            bottom: 0;
            transition: all 0.3s ease;
            overflow-x: hidden;
            overflow-y: auto;
            z-index: 50;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .nav-item {
            font-size: var(--font-size-sm);
            font-weight: var(--font-medium);
            letter-spacing: 0.01em;
            padding: 0.875rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #a0aec0;
            text-decoration: none;
            transition: all 0.3s ease;
            margin: 0.25rem 0.75rem;
            border-radius: 8px;
            white-space: nowrap;
        }

        .nav-item i {
            font-size: var(--icon-size);
            min-width: var(--icon-size);
            text-align: center;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .nav-item.active {
            background: var(--accent-orange);
            color: #fff;
        }

        .sidebar.collapsed .nav-item {
            padding: 0.875rem;
            justify-content: center;
            margin: 0.25rem auto;
            width: calc(var(--sidebar-collapsed-width) - 20px);
        }

        .sidebar.collapsed .nav-item span {
            display: none;
        }

        /* Footer Styles */
        .footer {
            height: var(--footer-height);
            background: var(--card-bg);
            border-top: 1px solid #e2e8f0;
            color: var(--text-secondary);
            position: fixed;
            bottom: 0;
            left: var(--sidebar-width);
            /* Start from sidebar width */
            right: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: var(--font-size-xs);
            font-weight: var(--font-regular);
            padding: 0 1rem;
            transition: all 0.3s ease;
            /* Add transition for smooth sidebar toggle */
        }

        /* Add this class for when sidebar is collapsed */
        .footer.expanded {
            left: var(--sidebar-collapsed-width);
        }

        /* Update main content margin to match new footer height */
        .main-content {
            margin-bottom: var(--footer-height);
        }

        /* Responsive Design */
        @media screen and (min-width: 1200px) {
            .stats-container {
                grid-template-columns: repeat(3, 1fr);
            }

            .main-content {
                padding: 2rem;
            }
        }

        @media screen and (min-width: 768px) and (max-width: 1199px) {
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }

            .sidebar {
                width: var(--sidebar-collapsed-width);
            }

            .main-content {
                margin-left: var(--sidebar-collapsed-width);
            }

            .footer {
                left: var(--sidebar-collapsed-width);
            }

            .sidebar .nav-item span {
                display: none;
            }

            .sidebar .nav-item {
                padding: 0.875rem;
                justify-content: center;
            }
        }

        @media screen and (max-width: 767px) {
            .stats-container {
                grid-template-columns: 1fr;
            }

            .sidebar {
                transform: translateX(-100%);
                width: var(--sidebar-width);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .footer {
                left: 0;
            }

            .header {
                padding: 0 1rem;
            }

            .brand h1 {
                font-size: var(--font-size-base);
            }

            .recent-attendance {
                overflow-x: auto;
            }

            .recent-attendance table {
                min-width: 600px;
            }
        }

        /* Mobile menu toggle */
        .mobile-menu-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 49;
        }

        .mobile-menu-overlay.show {
            display: block;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="brand">
            <div class="sidebar-toggle">
                <i class="fas fa-bars"></i>
            </div>
            <img src="../assets/logo.png" alt="Logo" class="logo">
            <h1>WorkPulse</h1>
        </div>
        <div class="user-menu">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="profile.php" class="nav-item">
                <i class="fas fa-user"></i>
            </a>
        </div>
    </header>

    <!-- Sidebar -->
    <nav class="sidebar">
        <a href="dashboard.php" class="nav-item">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="attendance.php" class="nav-item">
            <i class="fas fa-clock"></i>
            <span>Attendance</span>
        </a>

        <a href="users.php" class="nav-item">
            <i class="fas fa-user"></i>
            <span>Users</span>
        </a>
        <a href="reports.php" class="nav-item">
            <i class="fas fa-file-alt"></i>
            <span>Reports</span>
        </a>
        <a href="settings.php" class="nav-item">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </a>
        <a href="../logout.php" class="nav-item">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </nav>

    <!-- Main Content -->
    <main class="main-content">

    </main>



    <!-- Footer -->
    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> WorkPulse. All rights reserved.</p>
    </footer>
</body>

</html>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        const footer = document.querySelector('.footer');

        // Add overlay div to body
        const overlay = document.createElement('div');
        overlay.className = 'mobile-menu-overlay';
        document.body.appendChild(overlay);

        sidebarToggle.addEventListener('click', function() {
            if (window.innerWidth <= 767) {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            } else {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
                footer.classList.toggle('expanded');
            }
        });

        // Close sidebar when clicking overlay
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 767) {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            }
        });

        // Add this new code for handling active state
        const navItems = document.querySelectorAll('.sidebar .nav-item');

        navItems.forEach(item => {
            item.addEventListener('click', function() {
                // Remove active class from all items
                navItems.forEach(nav => nav.classList.remove('active'));

                // Add active class to clicked item
                this.classList.add('active');

                // Store the active path in localStorage
                localStorage.setItem('activePath', this.getAttribute('href'));
            });
        });

        // Check and set active state on page load
        const currentPath = window.location.pathname;
        const activePath = localStorage.getItem('activePath');

        navItems.forEach(item => {
            const href = item.getAttribute('href');
            if (currentPath.endsWith(href) || (activePath && activePath === href)) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    });
</script>