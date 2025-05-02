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

        /* Enhanced Sidebar Styles */
        :root {
            --primary-color: #8B4513;
            --dark-bg: #2C3639;
            --card-bg: #ffffff;
            --text-primary: #2d3748;
            --text-secondary: #718096;
            --accent-orange: #F4A460;
            --accent-green: #8B8B6E;
            --gradient-start: #F4A460;
            --gradient-end: #8B4513;
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
            --header-height: 60px;
            --footer-height: 30px;
            --icon-size: 20px;
        }

        .sidebar {
            background: linear-gradient(180deg, var(--dark-bg) 0%, #1a2327 100%);
            width: var(--sidebar-width);
            position: fixed;
            left: 0;
            top: var(--header-height);
            bottom: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-x: hidden;
            overflow-y: auto;
            z-index: 50;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
        }

        .sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        .nav-item {
            font-size: var(--font-size-sm);
            font-weight: 500;
            letter-spacing: 0.01em;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.2s ease;
            margin: 0.25rem 1rem;
            border-radius: 8px;
            position: relative;
            overflow: hidden;
        }

        .nav-item i {
            font-size: var(--icon-size);
            min-width: var(--icon-size);
            text-align: center;
            transition: transform 0.2s ease;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            transform: translateX(4px);
        }

        .nav-item:hover i {
            transform: scale(1.1);
        }

        .nav-item.active {
            background: var(--accent-orange);
            color: #fff;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(244, 164, 96, 0.3);
        }

        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: #fff;
            border-radius: 0 2px 2px 0;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar.collapsed .nav-item {
            padding: 0.75rem;
            justify-content: center;
            margin: 0.25rem auto;
            width: calc(var(--sidebar-collapsed-width) - 20px);
        }

        .sidebar.collapsed .nav-item span {
            display: none;
        }

        .sidebar.collapsed .nav-item:hover {
            transform: scale(1.1);
        }

        .nav-separator {
            height: 1px;
            background: linear-gradient(90deg, 
                rgba(255, 255, 255, 0) 0%, 
                rgba(255, 255, 255, 0.1) 50%, 
                rgba(255, 255, 255, 0) 100%
            );
            margin: 0.75rem 1rem;
        }

        .nav-item.logout {
            margin-top: auto;
            color: #ff7675;
        }

        .nav-item.logout:hover {
            background: rgba(255, 118, 117, 0.1);
            color: #ff5252;
        }

        /* Adjust main content for new sidebar */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            margin-bottom: var(--footer-height);
            padding: 2rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        .content-header {
            margin-bottom: 2rem;
            width: 100%;
        }

        .content-header h1 {
            font-size: 1.875rem;
            color: var(--text-primary);
            font-weight: 600;
            position: relative;
            padding-left: 1rem;
        }

        .content-header h1::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 24px;
            background: linear-gradient(to bottom, var(--gradient-start), var(--gradient-end));
            border-radius: 2px;
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            gap: 1.5rem;
            margin-bottom: 2rem;
            width: 100%;
        }

        .stat-card {
            background: var(--card-bg);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 1.5rem;
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-card i {
            font-size: 2rem;
            color: var(--accent-orange);
            background: rgba(244, 164, 96, 0.1);
            padding: 1rem;
            border-radius: 10px;
        }

        .stat-info h3 {
            color: var(--text-secondary);
            font-size: var(--font-size-sm);
            font-weight: var(--font-medium);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .stat-info p {
            color: var(--text-primary);
            font-size: var(--font-size-2xl);
            font-weight: var(--font-semibold);
            letter-spacing: -0.025em;
            line-height: 1.2;
        }

        /* Recent Attendance Cards */
        .attendance-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .current-date {
            font-size: var(--font-size-base);
            color: var(--text-secondary);
            font-weight: var(--font-medium);
            background: var(--card-bg);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .recent-attendance {
            background: var(--card-bg);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-top: 2rem;
        }

        .recent-attendance h2 {
            color: var(--text-primary);
            font-size: var(--font-size-xl);
            font-weight: var(--font-semibold);
            margin-bottom: 1.5rem;
        }

        .attendance-cards {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            margin-top: 2rem;
        }

        .attendance-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07),
                0 1px 3px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(226, 232, 240, 0.7);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .attendance-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to right, var(--gradient-start), var(--gradient-end));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .attendance-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.1),
                0 2px 4px rgba(0, 0, 0, 0.08);
        }

        .attendance-card:hover::before {
            opacity: 1;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .user-avatar {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: var(--font-semibold);
            font-size: 1.125rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }

        .attendance-card:hover .user-avatar {
            transform: scale(1.05);
        }

        .user-details {
            flex-grow: 1;
        }

        .user-details h3 {
            color: var(--text-primary);
            font-size: 1.125rem;
            font-weight: var(--font-semibold);
            margin-bottom: 0.375rem;
            line-height: 1.2;
        }

        .company {
            color: var(--text-secondary);
            font-size: var(--font-size-sm);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .company::before {
            content: '';
            display: inline-block;
            width: 6px;
            height: 6px;
            background-color: var(--accent-orange);
            border-radius: 50%;
        }

        .attendance-details {
            background: rgba(244, 164, 96, 0.05);
            padding: 1rem;
            border-radius: 12px;
            margin-top: 0.5rem;
        }

        .attendance-type {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.7);
        }

        .attendance-type .fas {
            font-size: 1.25rem;
            width: 24px;
            text-align: center;
        }

        .type-label {
            font-weight: var(--font-medium);
            color: var(--text-primary);
        }

        .time {
            margin-left: auto;
            color: var(--text-secondary);
            font-size: var(--font-size-sm);
            font-weight: var(--font-medium);
            background: rgba(244, 164, 96, 0.1);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
        }

        /* Enhanced Attendance Type Icons */
        .check-in .fas {
            color: #4CAF50;
            text-shadow: 0 2px 4px rgba(76, 175, 80, 0.2);
        }

        .check-out .fas {
            color: #F44336;
            text-shadow: 0 2px 4px rgba(244, 67, 54, 0.2);
        }

        .break-in .fas,
        .break-out .fas {
            color: #FF9800;
            text-shadow: 0 2px 4px rgba(255, 152, 0, 0.2);
        }

        .overtime-in .fas,
        .overtime-out .fas {
            color: #2196F3;
            text-shadow: 0 2px 4px rgba(33, 150, 243, 0.2);
        }

        /* Status Indicators */
        .attendance-type.check-in {
            background-color: rgba(76, 175, 80, 0.05);
        }

        .attendance-type.check-out {
            background-color: rgba(244, 67, 54, 0.05);
        }

        .attendance-type.break-in,
        .attendance-type.break-out {
            background-color: rgba(255, 152, 0, 0.05);
        }

        .attendance-type.overtime-in,
        .attendance-type.overtime-out {
            background-color: rgba(33, 150, 243, 0.05);
        }

        /* Responsive Adjustments */
        @media screen and (max-width: 1400px) {
            .attendance-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media screen and (max-width: 768px) {
            .attendance-cards {
                grid-template-columns: 1fr;
            }

            .attendance-card {
                padding: 1.25rem;
            }

            .user-avatar {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }
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
        <a href="dashboard.php" class="nav-item active">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="attendance.php" class="nav-item">
            <i class="fas fa-clock"></i>
            <span>Attendance</span>
        </a>
        
        <div class="nav-separator"></div>
        
        <a href="users.php" class="nav-item">
            <i class="fas fa-users"></i>
            <span>Users</span>
        </a>
        <a href="reports.php" class="nav-item">
            <i class="fas fa-chart-bar"></i>
            <span>Reports</span>
        </a>
        
        <div class="nav-separator"></div>
        
        <a href="settings.php" class="nav-item">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </a>
        <a href="../logout.php" class="nav-item logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="content-header">
            <h1>Dashboard</h1>
        </div>
        <div class="content">
            <!-- Statistics Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <div class="stat-info">
                        <h3>Total Users</h3>
                        <p>25</p>
                    </div>
                </div>

                <div class="stat-card">
                    <i class="fas fa-user-check"></i>
                    <div class="stat-info">
                        <h3>Present Today</h3>
                        <p>18</p>
                    </div>
                </div>

                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <div class="stat-info">
                        <h3>On Time</h3>
                        <p>92%</p>
                    </div>
                </div>
            </div>

            <!-- Recent Attendance Section -->
            <div class="recent-attendance">
                <div class="attendance-header">
                    <h2>Recent Attendance</h2>
                    <div class="current-date">
                        <?php echo date('F d, Y'); ?>
                    </div>
                </div>
                <div class="attendance-cards">
                    <!-- Cards will be populated by JavaScript -->
                </div>
            </div>
        </div>
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
<script>
    function fetchDashboardData() {
        $.ajax({
            url: '../api/dashboard.php',
            method: 'GET',
            success: function(data) {
                let cardsHtml = '';
                data.forEach(function(record) {
                    const iconClass = getAttendanceIconClass(record.type);
                    const typeClass = record.type.toLowerCase().replace(/\s+/g, '-');

                    cardsHtml += `
                        <div class="attendance-card">
                            <div class="user-info">
                                <div class="user-avatar" title="${record.name}">${record.initials}</div>
                                <div class="user-details">
                                    <h3>${record.name}</h3>
                                    <span class="company">${record.company}</span>
                                </div>
                            </div>
                            <div class="attendance-details">
                                <div class="attendance-type ${typeClass}">
                                    <i class="fas ${iconClass}"></i>
                                    <span class="type-label">${record.type}</span>
                                    <span class="time">${record.time}</span>
                                </div>
                            </div>
                        </div>
                    `;
                });
                $('.attendance-cards').html(cardsHtml);
            },
            error: function(xhr, status, error) {
                console.error('Error fetching dashboard data:', error);
                $('.attendance-cards').html('<div class="error-message">Failed to load attendance data</div>');
            }
        });
    }

    function getAttendanceIconClass(type) {
        switch (type) {
            case 'Check In':
                return 'fa-sign-in-alt';
            case 'Check Out':
                return 'fa-sign-out-alt';
            case 'Break In':
                return 'fa-coffee';
            case 'Break Out':
                return 'fa-walking';
            case 'Overtime In':
                return 'fa-clock';
            case 'Overtime Out':
                return 'fa-home';
            default:
                return 'fa-clock';
        }
    }

    // Fetch initially
    fetchDashboardData();

    // Refresh every 30 seconds
    setInterval(fetchDashboardData, 2000);
</script>
