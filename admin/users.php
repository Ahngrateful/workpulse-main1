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
    <title>Users | WorkPulse</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                    rgba(255, 255, 255, 0) 100%);
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

        /* Main Content Layout */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            margin-bottom: var(--footer-height);
            padding: 1.25rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: calc(100vh - var(--header-height) - var(--footer-height));
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        /* Content Header */
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .content-header h1 {
            font-size: 1.5rem;
            color: var(--text-primary);
            font-weight: 600;
        }

        .add-user-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }

        .add-user-btn:hover {
            background: var(--gradient-end);
        }

        /* Filter Controls */
        .filter-controls {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 1rem;
            margin-bottom: 1rem;
            align-items: center;
        }

        .search-box {
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 0.5rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.875rem;
        }

        .filter-select {
            padding: 0.5rem 2rem 0.5rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.875rem;
            background: white;
            min-width: 120px;
        }

        /* Users Table */
        .users-table-wrapper {
            width: 100%;
            overflow-x: auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .users-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: fixed;
            /* Forces equal column widths */
        }

        .users-table th,
        .users-table td {
            width: 11.11%;
            /* Equal distribution for 9 columns (100/9) */
            padding: 0.75rem;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
            /* Ensures text wrapping */
            overflow-wrap: break-word;
            min-width: 120px;
            /* Minimum width for columns */
        }

        .users-table th {
            background: var(--accent-orange);
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
            position: sticky;
            top: 0;
            z-index: 10;
            white-space: normal;
            /* Allows text wrapping in headers */
        }

        .users-table td {
            font-size: 0.875rem;
            background: white;
            border-bottom: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }

        .users-table td:nth-child(1) {
            /* ID column */
            font-weight: 500;
            font-family: 'SF Mono', monospace;
        }

        .user-link {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .user-link:hover {
            color: var(--accent-orange);
            text-decoration: underline;
        }

        .users-table td:nth-child(2),
        /* Name */
        .users-table td:nth-child(3),
        /* Username */
        .users-table td:nth-child(4) {
            /* Email */
            text-align: left;
            padding-left: 1rem;
        }

        /* Status column styling */
        .users-table td:nth-child(6) {
            white-space: nowrap;
            /* Keep status on one line */
        }

        /* Actions column */
        .users-table td:last-child {
            white-space: nowrap;
            /* Keep actions on one line */
        }

        /* Row hover effect */
        .users-table tr:hover td {
            background: rgba(244, 164, 96, 0.05);
        }

        /* Long text handling */
        .users-table td {
            max-height: 100px;
            line-height: 1.4;
        }

        /* Add ellipsis for very long content */
        .users-table td.truncate {
            max-width: 0;
            /* Forces truncation */
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Tooltip for truncated content */
        .users-table td.truncate:hover {
            position: relative;
        }

        .users-table td.truncate:hover::after {
            content: attr(data-full-text);
            position: absolute;
            left: 0;
            top: 100%;
            background: white;
            padding: 0.5rem;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            z-index: 20;
            white-space: normal;
            max-width: 300px;
            word-wrap: break-word;
        }

        /* Responsive adjustments */
        @media screen and (max-width: 1366px) {

            .users-table th,
            .users-table td {
                padding: 0.625rem;
                font-size: 0.8125rem;
            }
        }

        @media screen and (max-width: 1024px) {
            .users-table {
                min-width: 900px;
                /* Ensures table doesn't get too cramped */
            }

            .users-table th,
            .users-table td {
                min-width: 100px;
            }
        }

        /* Action Buttons in Table */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            cursor: pointer;
            border: none;
        }

        .edit-btn {
            background: #edf2f7;
            color: var(--primary-color);
        }

        .delete-btn {
            background: #fee2e2;
            color: #dc2626;
        }

        /* Pagination Controls */
        .pagination-controls {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .pagination-btn {
            padding: 0.375rem 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            background: white;
            font-size: 0.875rem;
            cursor: pointer;
        }

        .pagination-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        /* Responsive Breakpoints */
        @media screen and (max-width: 1366px) {
            .main-content {
                padding: 1rem;
            }

            .content-header {
                margin-bottom: 1rem;
            }

            .content-header h1 {
                font-size: 1.25rem;
            }

            .filter-controls {
                gap: 0.75rem;
            }

            .users-table th,
            .users-table td {
                padding: 0.625rem 0.75rem;
                font-size: 0.8125rem;
            }

            .action-btn {
                padding: 0.25rem 0.375rem;
                font-size: 0.75rem;
            }
        }

        @media screen and (max-width: 1024px) {
            .filter-controls {
                grid-template-columns: 1fr 1fr;
                gap: 0.75rem;
            }

            .search-box {
                grid-column: 1 / -1;
            }

            .users-table {
                min-width: 700px;
            }
        }

        @media screen and (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 0.75rem;
            }

            .content-header {
                flex-direction: column;
                gap: 0.75rem;
                align-items: stretch;
            }

            .add-user-btn {
                width: 100%;
                justify-content: center;
            }

            .filter-controls {
                grid-template-columns: 1fr;
            }

            .filter-select {
                width: 100%;
            }

            .users-table-wrapper {
                margin: 0 -0.75rem;
                border-radius: 0;
                border-left: none;
                border-right: none;
            }

            .pagination-controls {
                justify-content: center;
            }
        }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-active {
            background: #dcfce7;
            color: #16a34a;
        }

        .status-inactive {
            background: #fee2e2;
            color: #dc2626;
        }

        /* Sort Headers */
        .sort-header {
            cursor: pointer;
            user-select: none;
            position: relative;
        }

        .sort-header:hover {
            background: #f1f5f9;
        }

        .sort-header::after {
            content: '↕';
            position: absolute;
            right: 0.5rem;
            opacity: 0.5;
        }

        .sort-header.sort-asc::after {
            content: '↑';
            opacity: 1;
        }

        .sort-header.sort-desc::after {
            content: '↓';
            opacity: 1;
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

        /* Content Header Styling */
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
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

        /* Add User Button Styling */
        .add-user-btn {
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .add-user-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
        }

        .add-user-btn i {
            transition: transform 0.3s ease;
        }

        .add-user-btn:hover i {
            transform: rotate(90deg);
        }

        /* Filter Controls Styling */
        .filter-controls {
            background: #f8fafc;
            /* Lighter background */
            padding: 1.25rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
            border: 1px solid var(--accent-orange);
            /* Orange border */
        }

        .search-box {
            flex: 1;
            min-width: 250px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 2px solid var(--accent-orange);
            /* Thicker orange border */
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: white;
        }

        .search-box::before {
            content: '\f002';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .search-input:focus {
            background: white;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(244, 164, 96, 0.2);
            /* Soft orange shadow */
            outline: none;
        }

        .filter-select {
            padding: 0.75rem 2rem 0.75rem 1rem;
            border: 2px solid var(--accent-orange);
            /* Thicker orange border */
            border-radius: 8px;
            background: white;
            min-width: 140px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .filter-select:focus {
            background: white;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(244, 164, 96, 0.2);
            /* Soft orange shadow */
            outline: none;
        }

        /* Table Styling */
        .users-table-wrapper {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1),
                0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 2px solid var(--accent-orange);
            /* Thicker border */
        }

        .users-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 1000px;
        }

        .users-table th {
            background: var(--accent-orange);
            /* Orange background */
            padding: 1rem 1.5rem;
            font-weight: 600;
            color: white;
            /* White text */
            text-align: left;
            border-bottom: 3px solid rgba(139, 69, 19, 0.5);
            /* Darker and thicker border */
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .users-table th:hover {
            background: #e5955c;
            /* Slightly darker orange on hover */
        }

        .users-table td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(244, 164, 96, 0.3);
            /* Lighter orange border */
            color: var(--text-primary);
        }

        .users-table tbody tr {
            transition: all 0.3s ease;
        }

        .users-table tbody tr:hover {
            background: #f8fafc;
            transform: translateX(4px);
        }

        /* Status Badge Styling */
        .status-badge {
            padding: 0.375rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
        }

        .status-badge::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }

        .status-active {
            background: rgba(34, 197, 94, 0.1);
            color: #16a34a;
        }

        .status-active::before {
            background: #16a34a;
        }

        .status-inactive {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
        }

        .status-inactive::before {
            background: #dc2626;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            opacity: 0.7;
            transition: all 0.3s ease;
        }

        .users-table tr:hover .action-buttons {
            opacity: 1;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: white;
        }

        .action-btn.edit {
            background: var(--accent-orange);
        }

        .action-btn.delete {
            background: #ef4444;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .action-btn.edit:hover {
            background: #f97316;
        }

        .action-btn.delete:hover {
            background: #dc2626;
        }

        /* Pagination Styling */
        .pagination-controls {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
            padding: 1rem;
        }

        .pagination-btn {
            min-width: 36px;
            height: 36px;
            border-radius: 8px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            background: white;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .pagination-btn:hover:not(:disabled) {
            background: var(--accent-orange);
            color: white;
            border-color: var(--accent-orange);
            transform: translateY(-2px);
        }

        .pagination-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination-ellipsis {
            display: flex;
            align-items: center;
            padding: 0 0.5rem;
            color: var(--text-secondary);
        }

        /* Filter Stats */
        .filter-stats {
            color: var(--text-secondary);
            font-size: 0.875rem;
            margin-top: 1rem;
            text-align: right;
        }

        /* Sort Indicators */
        .sort-header {
            position: relative;
            padding-right: 1.5rem;
        }

        .sort-header::after {
            content: '\f0dc';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            right: 0.5rem;
            color: rgba(255, 255, 255, 0.7);
            /* Lighter white for sort icon */
            opacity: 0.3;
        }

        .sort-header.sorted-asc::after,
        .sort-header.sorted-desc::after {
            color: white;
            /* White for active sort icon */
        }

        /* Loading overlay styles */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Disabled button styles */
        button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        /* Modal base styles */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            overflow-y: auto;
            /* Enable scrolling on the modal overlay */
            padding: 1rem;
        }

        .modal-content {
            background: white;
            border-radius: 8px;
            width: 100%;
            max-width: 800px;
            position: relative;
            margin: auto;
            display: flex;
            flex-direction: column;
            max-height: 90vh;
            /* Maximum height relative to viewport */
        }

        .modal-header {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            background: white;
            z-index: 10;
            border-radius: 8px 8px 0 0;
        }

        .modal-body {
            padding: 1rem;
            overflow-y: auto;
            /* Enable scrolling for modal content */
            flex: 1;
        }

        .modal-footer {
            padding: 1rem;
            border-top: 1px solid var(--border-color);
            position: sticky;
            bottom: 0;
            background: white;
            z-index: 10;
            border-radius: 0 0 8px 8px;
        }

        /* Form step container */
        .form-step {
            padding: 0.5rem;
        }

        /* Responsive styles */
        @media screen and (max-width: 1366px) {
            .modal {
                align-items: flex-start;
                /* Align to top for better scrolling */
                padding: 1rem;
            }

            .modal-content {
                margin: 1rem auto;
                max-height: calc(100vh - 2rem);
                /* Account for padding */
            }

            .modal-body {
                max-height: calc(100vh - 180px);
                /* Account for header and footer */
                padding: 1rem;
            }
        }

        @media screen and (max-height: 768px) {
            .modal {
                padding: 0.5rem;
            }

            .modal-content {
                margin: 0.5rem auto;
                max-height: calc(100vh - 1rem);
            }

            .modal-header {
                padding: 0.75rem 1rem;
            }

            .modal-body {
                padding: 0.75rem;
            }

            .modal-footer {
                padding: 0.75rem 1rem;
            }

            /* Adjust form elements for better fit */
            .form-group {
                margin-bottom: 0.5rem;
            }

            .form-group label {
                margin-bottom: 0.2rem;
            }

            .form-group input,
            .form-group select {
                padding: 0.4rem 0.6rem;
            }

            .step-progress {
                margin-bottom: 1rem;
                padding: 0 0.5rem;
            }
        }

        /* Ensure form feedback doesn't break layout */
        .form-feedback {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 2rem;
            border-radius: 8px;
            width: 90%;
            max-width: 400px;
            z-index: 11;
        }

        /* Animation for loading spinner */
        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            width: 90%;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            max-width: 1000px;
            padding: 1.5rem;
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            color: var(--text-primary);
            font-size: 1.5rem;
        }

        .close-btn {
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-secondary);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
            font-weight: 500;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(244, 164, 96, 0.2);
            outline: none;
        }

        .modal-footer {
            padding: 1rem 0 0;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        .btn-primary,
        .btn-secondary {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            color: white;
            border: none;
        }

        .btn-secondary {
            background: #f1f5f9;
            color: var(--text-primary);
            border: 1px solid #e2e8f0;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
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

        /* Modal Improvements */
        .modal-content {
            max-width: 800px;
            padding: 1rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .form-section {
            background: #f8fafc;
            padding: 1.25rem;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .form-section h3 {
            font-size: 1rem;
            color: #2d3748;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .profile-upload-section {
            text-align: center;
            margin-bottom: 2rem;
        }

        .profile-preview {
            position: relative;
            width: 150px;
            margin: 0 auto;
        }

        .profile-preview img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e2e8f0;
        }

        .upload-btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: #4a5568;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }

        .upload-btn:hover {
            background: #2d3748;
        }

        .hidden {
            display: none;
        }

        .shift-times {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-column {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-group label {
            margin-bottom: 0.25rem;
            color: var(--text-primary);
            font-size: 0.9rem;
        }

        .form-group input,
        .form-group select {
            padding: 0.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.95rem;
        }

        .modal-footer {
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }

        /* Responsive Design */
        @media screen and (max-width: 1024px) {
            .form-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media screen and (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .shift-times {
                grid-template-columns: 1fr;
            }
        }

        @media screen and (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .modal-content {
                padding: 1rem;
                margin: 1rem;
                width: calc(100% - 2rem);
            }
        }

        /* Step Progress Bar */
        .step-progress {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
            padding: 0 1rem;
        }

        .step-progress::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: #e2e8f0;
            z-index: 1;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f1f5f9;
            border: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }

        .step.active .step-number {
            background: var(--gradient-start);
            border-color: var(--gradient-end);
            color: white;
        }

        .step.completed .step-number {
            background: var(--gradient-end);
            border-color: var(--gradient-end);
            color: white;
        }

        .step-label {
            font-size: 0.875rem;
            color: #64748b;
        }

        .step.active .step-label {
            color: var(--text-primary);
            font-weight: 500;
        }

        /* Form Steps */
        .form-step {
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media screen and (max-width: 640px) {
            .step-label {
                font-size: 0.75rem;
            }

            .step-number {
                width: 32px;
                height: 32px;
            }
        }

        /* Two-column layout for Basic Info step */
        .basic-info-grid {
            display: grid;
            grid-template-columns: 250px 1fr;
            /* Reduced from 300px */
            gap: 1.25rem;
            /* Reduced from 2rem */
            align-items: start;
        }

        .profile-column {
            background: #f8fafc;
            padding: 1rem;
            /* Reduced from 1.5rem */
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .profile-upload-section {
            text-align: center;
        }

        .profile-upload-section h3 {
            font-size: 0.9rem;
            /* Reduced from 1rem */
            color: var(--text-primary);
            margin-bottom: 1rem;
            /* Reduced from 1.25rem */
            font-weight: 500;
        }

        .profile-preview {
            position: relative;
            width: 150px;
            /* Reduced from 200px */
            margin: 0 auto 0.75rem;
        }

        .profile-preview img {
            width: 150px;
            /* Reduced from 200px */
            height: 150px;
            /* Reduced from 200px */
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e2e8f0;
            /* Reduced from 3px */
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .upload-btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            /* Reduced from 0.75rem 1.5rem */
            background: var(--primary-color);
            color: white;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 0.75rem;
            /* Reduced from 1rem */
            font-size: 0.85rem;
            /* Reduced from 0.9rem */
            transition: all 0.3s ease;
        }

        .upload-btn:hover {
            background: var(--gradient-end);
            transform: translateY(-1px);
        }

        .upload-hint {
            margin-top: 0.75rem;
            /* Reduced from 1rem */
            font-size: 0.75rem;
            /* Reduced from 0.875rem */
            color: var(--text-secondary);
        }

        .form-column .form-section {
            background: white;
            padding: 1rem;
            /* Reduced from 1.5rem */
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        /* Form groups in the right column */
        .form-group {
            margin-bottom: 0.75rem;
            /* Reduced spacing between form fields */
        }

        .form-group label {
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
            display: block;
        }

        .form-group input {
            padding: 0.5rem 0.75rem;
            /* Reduced padding */
            font-size: 0.85rem;
        }

        /* Responsive breakpoints adjusted for smaller screens */
        @media screen and (max-width: 1366px) {
            .basic-info-grid {
                grid-template-columns: 200px 1fr;
                /* Even smaller for 1366px width */
            }

            .profile-preview,
            .profile-preview img {
                width: 130px;
                height: 130px;
            }
        }

        @media screen and (max-width: 1024px) {
            .basic-info-grid {
                grid-template-columns: 180px 1fr;
                gap: 1rem;
            }
        }

        @media screen and (max-width: 900px) {
            .basic-info-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .profile-column {
                max-width: 400px;
                margin: 0 auto;
            }
        }

        /* Mobile view */
        @media screen and (max-width: 768px) {
            .basic-info-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .profile-column {
                width: 100%;
            }

            .profile-preview {
                width: 120px;
            }

            .profile-preview img {
                width: 120px;
                height: 120px;
            }

            .form-group input {
                font-size: 0.9rem;
                /* Slightly larger for better touch targets */
                padding: 0.625rem;
            }
        }

        /* Add these styles */
        .form-feedback {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .loading-animation {
            width: 80px;
            height: 80px;
            margin: 0 auto 15px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .success-checkmark {
            width: 80px;
            height: 80px;
            margin: 0 auto 15px;
            color: #4CAF50;
        }

        .feedback-message {
            font-size: 1.1rem;
            margin-bottom: 15px;
            color: var(--text-primary);
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .error-message {
            color: #dc2626;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        #deleteConfirmModal .modal-content {
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-100px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
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

        <div class="nav-separator"></div>

        <a href="users.php" class="nav-item active">
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
            <h1>Users Management</h1>
            <button class="add-user-btn" onclick="showAddUserModal()">
                <i class="fas fa-plus"></i> Add User
            </button>
        </div>

        <div class="content">
            <div class="filter-controls">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search users..." class="search-input">
                </div>
                <select id="roleFilter" class="filter-select">
                    <option value="">All Roles</option>
                    <option value="14">Admin</option>
                    <option value="0">User</option>
                </select>
                <select id="statusFilter" class="filter-select">
                    <option value="">All Status</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>

            <div class="filter-stats"></div>

            <div class="users-table-wrapper">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th class="sort-header" data-column="user_id">User ID</th>
                            <th class="sort-header" data-column="name">Name</th>
                            <th class="sort-header" data-column="username">Username</th>
                            <th class="sort-header" data-column="email">Email</th>
                            <th class="sort-header" data-column="role">Role</th>
                            <th class="sort-header" data-column="status">Status</th>
                            <th class="sort-header" data-column="hire_date">Hire Date</th>
                            <th class="sort-header" data-column="company">Company</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <!-- Table content will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <div class="pagination-controls"></div>
        </div>
    </main>


    <!-- Footer -->
    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> WorkPulse. All rights reserved.</p>
    </footer>
</body>
<!-- Add User Modal -->
<div id="addUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New User</h2>
            <span class="close-btn" id="closeAddUserModal">&times;</span>
        </div>
        <div class="modal-body">
            <!-- Step Progress -->
            <div class="step-progress">
                <div class="step active" data-step="1">
                    <div class="step-number">1</div>
                    <span class="step-label">Basic Info</span>
                </div>
                <div class="step" data-step="2">
                    <div class="step-number">2</div>
                    <span class="step-label">Access Info</span>
                </div>
                <div class="step" data-step="3">
                    <div class="step-number">3</div>
                    <span class="step-label">Work Info</span>
                </div>
            </div>

            <form id="addUserForm" action="add_user.php" method="post" enctype="multipart/form-data">
                <!-- Step 1: Basic Information -->
                <div class="form-step" id="step1">
                    <div class="basic-info-grid">
                        <!-- Left Column - Profile Picture -->
                        <div class="profile-column">
                            <div class="profile-upload-section">
                                <h3>Profile Picture</h3>
                                <div class="profile-preview">
                                    <img src="images/avatar.png" alt="Profile Preview" id="profilePreview">
                                    <input type="file" id="profilePicture" name="profile_picture" accept="image/*" class="hidden">
                                    <label for="profilePicture" class="upload-btn">Upload Photo</label>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Form Fields -->
                        <div class="form-column">
                            <div class="form-section">
                                <div class="form-group">
                                    <label for="user_id">User ID</label>
                                    <input type="text" id="user_id" name="user_id" required>
                                </div>
                                <div class="form-group">
                                    <label for="name">Full Name</label>
                                    <input type="text" id="name" name="name" required>
                                </div>
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" id="username" name="username" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" required>
                                </div>
                                <div class="form-group">
                                    <label for="contact">Contact Number</label>
                                    <input type="tel" id="contact" name="contact" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Access Information -->
                <div class="form-step" id="step2" style="display: none;">
                    <div class="form-section">
                        <div class="form-group">
                            <label for="card_number">Card Number</label>
                            <input type="text" id="card_number" name="card_number" required>
                        </div>
                        <div class="form-group">
                            <label for="pin">PIN</label>
                            <input type="password" id="pin" name="pin" required maxlength="8">
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select id="role" name="role" required>
                                <option value="0">User</option>
                                <option value="14">Admin</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Work Information -->
                <div class="form-step" id="step3" style="display: none;">
                    <div class="form-section">
                        <div class="form-group">
                            <label for="company">Company</label>
                            <input type="text" id="company" name="company" required>
                        </div>
                        <div class="form-group">
                            <label for="hire_date">Hire Date</label>
                            <input type="date" id="hire_date" name="hire_date" required>
                        </div>
                        <div class="shift-times">
                            <div class="form-group">
                                <label for="shift_start">Shift Start</label>
                                <select id="shift_start" name="shift_start" required>
                                    <option value="08:00">8:00 AM</option>
                                    <option value="09:00">9:00 AM</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="shift_end">Shift End</label>
                                <select id="shift_end" name="shift_end" required>
                                    <option value="17:00">5:00 PM</option>
                                    <option value="18:00">6:00 PM</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" id="prevStep" style="display: none;">Previous</button>
                    <button type="button" class="btn-primary" id="nextStep">Next</button>
                    <button type="submit" class="btn-primary" id="submitForm" style="display: none;">Submit</button>
                </div>
                <div class="form-feedback">
                    <div class="loading-animation"></div>
                    <div class="success-checkmark" style="display: none;">
                        <i class="fas fa-check-circle fa-5x"></i>
                    </div>
                    <div class="feedback-message"></div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="modal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h2>Confirm Delete</h2>
            <span class="close-btn" id="closeDeleteModal">&times;</span>
        </div>
        <div class="modal-body">
            <p>To confirm deletion, please enter the User ID: <strong id="deleteUserId"></strong></p>
            <div class="form-group">
                <input type="text" id="confirmUserId" placeholder="Enter User ID" class="form-input">
            </div>
            <div class="error-message" id="deleteError" style="color: #dc2626; margin-top: 0.5rem; display: none;"></div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" id="cancelDelete">Cancel</button>
            <button class="btn-primary" id="confirmDelete" style="background: #dc2626;">Delete</button>
        </div>
    </div>
</div>
<!-- Edit User Modal -->
<div id="editUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit User</h2>
            <span class="close-btn" id="closeEditModal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="editUserForm" enctype="multipart/form-data">
                <input type="hidden" id="edit_user_id" name="user_id">

                <div class="form-grid">
                    <!-- Left Column -->
                    <div class="left-column">
                        <div class="profile-upload-section">
                            <h3>Profile Picture</h3>
                            <div class="profile-preview">
                                <img src="" alt="Profile Preview" id="editProfilePreview">
                                <input type="file" id="editProfilePicture" name="profile_picture" accept="image/*" class="hidden">
                                <label for="editProfilePicture" class="upload-btn">Change Photo</label>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="right-column">
                        <div class="form-group">
                            <label for="edit_name">Name</label>
                            <input type="text" id="edit_name" name="name" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_username">Username</label>
                            <input type="text" id="edit_username" name="username" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_email">Email</label>
                            <input type="email" id="edit_email" name="email" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_contact">Contact</label>
                            <input type="text" id="edit_contact" name="contact" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_password">New Password (leave blank to keep current)</label>
                            <input type="password" id="edit_password" name="password">
                        </div>

                        <div class="form-group">
                            <label for="edit_role">Role</label>
                            <select id="edit_role" name="role" required>
                                <option value="14">Admin</option>
                                <option value="0">User</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="edit_status">Status</label>
                            <select id="edit_status" name="status" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="edit_company">Company</label>
                            <input type="text" id="edit_company" name="company" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_hire_date">Hire Date</label>
                            <input type="date" id="edit_hire_date" name="hire_date" required>
                        </div>

                        <div class="form-group">
                            <label for="edit_shift_start">Shift Start</label>
                            <select id="edit_shift_start" name="shift_start" required>
                                <option>8:00 AM</option>
                                <option>9:00 AM</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="edit_shift_end">Shift End</label>
                            <select id="edit_shift_end" name="shift_end" required>
                                <option>5:00 PM</option>
                                <option>6:00 PM</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="error-message" id="editError" style="color: #dc2626; margin-top: 0.5rem; display: none;"></div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" id="cancelEdit">Cancel</button>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    let currentPage = 1;
    const perPage = 10;
    let currentSearch = '';
    let currentRole = '';
    let currentStatus = '';
    let currentSort = 'name';
    let currentSortOrder = 'ASC';
    let userIdToDelete = null;

    function fetchUsers(page = 1) {
        const params = new URLSearchParams({
            page: page,
            per_page: perPage,
            search: currentSearch,
            role: currentRole,
            status: currentStatus,
            sort_by: currentSort,
            sort_order: currentSortOrder
        });

        $('#usersTableBody').html('<tr><td colspan="9" class="text-center">Loading...</td></tr>');

        $.ajax({
            url: `../api/users.php?${params.toString()}`,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (!response.data || response.data.length === 0) {
                    $('#usersTableBody').html('<tr><td colspan="9" class="text-center">No users found</td></tr>');
                    return;
                }

                let tableContent = '';
                // Update the fetchUsers function's tableContent generation
                response.data.forEach(user => {
                    const statusClass = user.status === 'Active' ? 'status-active' : 'status-inactive';
                    tableContent += `
                        <tr>
                            <td><a href="user_profile.php?id=${user.user_id}" class="user-link">${user.user_id || '-'}</a></td>
                            <td><a href="user_profile.php?id=${user.user_id}" class="user-link">${user.name || '-'}</a></td>
                            <td>${user.username || '-'}</td>
                            <td>${user.email || '-'}</td>
                            <td>${user.role || '-'}</td>
                            <td><span class="status-badge ${statusClass}">${user.status || 'Unknown'}</span></td>
                            <td>${user.hire_date || '-'}</td>
                            <td>${user.company || '-'}</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn edit" onclick="editUser('${user.user_id}')" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn delete" onclick="deleteUser('${user.user_id}')" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                });
                $('#usersTableBody').html(tableContent);
                updatePagination(response.current_page, response.total_pages);
                updateFilterStats(response.total);
            },
            error: function(xhr, status, error) {
                console.error('Error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText
                });

                let errorMessage = 'Error loading users';
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMessage = response.message;
                    }
                } catch (e) {
                    // If response isn't JSON, use the raw response text
                    if (xhr.responseText) {
                        errorMessage = xhr.responseText;
                    }
                }

                $('#usersTableBody').html(`<tr><td colspan="9" class="text-center">${errorMessage}</td></tr>`);
            }
        });
    }

    function updatePagination(currentPage, totalPages) {
        let paginationHtml = `
                <button class="pagination-btn" onclick="changePage(1)" ${currentPage === 1 ? 'disabled' : ''}>
                    <i class="fas fa-angle-double-left"></i>
                </button>
                <button class="pagination-btn" onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
                    <i class="fas fa-angle-left"></i>
                </button>
            `;

        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                paginationHtml += `
                        <button class="pagination-btn ${i === currentPage ? 'active' : ''}" 
                                onclick="changePage(${i})">${i}</button>
                    `;
            } else if (i === currentPage - 3 || i === currentPage + 3) {
                paginationHtml += '<span class="pagination-ellipsis">...</span>';
            }
        }

        paginationHtml += `
                <button class="pagination-btn" onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>
                    <i class="fas fa-angle-right"></i>
                </button>
                <button class="pagination-btn" onclick="changePage(${totalPages})" ${currentPage === totalPages ? 'disabled' : ''}>
                    <i class="fas fa-angle-double-right"></i>
                </button>
            `;

        $('.pagination-controls').html(paginationHtml);
    }

    function changePage(page) {
        currentPage = page;
        fetchUsers(page);
    }

    function updateFilterStats(total) {
        $('.filter-stats').html(`Showing ${total} user${total !== 1 ? 's' : ''}`);
    }

    // Search handler with debounce
    let searchTimeout;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentSearch = $(this).val();
            currentPage = 1;
            fetchUsers(1);
        }, 300);
    });

    // Filter handlers
    $('#roleFilter').on('change', function() {
        currentRole = $(this).val();
        currentPage = 1;
        fetchUsers(1);
    });

    $('#statusFilter').on('change', function() {
        currentStatus = $(this).val();
        currentPage = 1;
        fetchUsers(1);
    });

    // Sort handler
    $('.sort-header').on('click', function() {
        const column = $(this).data('column');
        if (currentSort === column) {
            currentSortOrder = currentSortOrder === 'ASC' ? 'DESC' : 'ASC';
        } else {
            currentSort = column;
            currentSortOrder = 'ASC';
        }
        currentPage = 1;
        fetchUsers(1);

        // Update sort indicators
        $('.sort-header').removeClass('sorted-asc sorted-desc');
        $(this).addClass(currentSortOrder === 'ASC' ? 'sorted-asc' : 'sorted-desc');
    });

    // Initial load
    $(document).ready(function() {
        fetchUsers(1);
    });

    function editUser(userId) {
        // Show loading state
        const editModal = document.getElementById('editUserModal');
        editModal.style.display = 'block';

        // Fetch user data
        $.ajax({
            url: '../api/get_user.php',
            method: 'GET',
            data: {
                user_id: userId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const user = response.data;

                    // Populate form fields
                    document.getElementById('edit_user_id').value = user.user_id;
                    document.getElementById('edit_name').value = user.name;
                    document.getElementById('edit_username').value = user.username;
                    document.getElementById('edit_email').value = user.email;
                    document.getElementById('edit_role').value = user.role;
                    document.getElementById('edit_status').value = user.status;
                    document.getElementById('edit_hire_date').value = user.hire_date;
                    document.getElementById('edit_company').value = user.company;
                } else {
                    document.getElementById('editError').textContent = response.message || 'Failed to load user data';
                    document.getElementById('editError').style.display = 'block';
                }
            },
            error: function(xhr) {
                document.getElementById('editError').textContent = 'Error loading user data';
                document.getElementById('editError').style.display = 'block';
            }
        });
    }

    function deleteUser(userId) {
        // Show delete confirmation modal
        document.getElementById('deleteConfirmModal').style.display = 'block';
        document.getElementById('deleteUserId').textContent = userId;
        userIdToDelete = userId;
    }

    let currentStep = 1;

    function updateStepVisibility() {
        // Hide all steps
        document.querySelectorAll('.form-step').forEach(step => {
            step.style.display = 'none';
        });

        // Show current step
        document.getElementById(`step${currentStep}`).style.display = 'block';

        // Update buttons
        const prevBtn = document.getElementById('prevStep');
        const nextBtn = document.getElementById('nextStep');
        const submitBtn = document.getElementById('submitForm');

        prevBtn.style.display = currentStep === 1 ? 'none' : 'block';
        nextBtn.style.display = currentStep === 3 ? 'none' : 'block';
        submitBtn.style.display = currentStep === 3 ? 'block' : 'none';

        // Update step indicators
        document.querySelectorAll('.step').forEach(step => {
            const stepNum = parseInt(step.dataset.step);
            if (stepNum === currentStep) {
                step.classList.add('active');
            } else if (stepNum < currentStep) {
                step.classList.add('completed');
            } else {
                step.classList.remove('active', 'completed');
            }
        });
    }

    // Profile picture preview
    document.getElementById('profilePicture').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profilePreview').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });

    // Step navigation
    document.getElementById('nextStep').addEventListener('click', () => {
        if (validateStep(currentStep)) {
            currentStep++;
            updateStepVisibility();
        }
    });

    document.getElementById('prevStep').addEventListener('click', () => {
        currentStep--;
        updateStepVisibility();
    });

    // Modal controls
    function showAddUserModal() {
        document.getElementById('addUserModal').style.display = 'block';
        currentStep = 1;
        updateStepVisibility();
    }

    document.getElementById('closeAddUserModal').addEventListener('click', function() {
        document.getElementById('addUserModal').style.display = 'none';
        document.getElementById('addUserForm').reset();
        document.getElementById('profilePreview').src = 'images/avatar.png';
    });

    // Form validation
    function validateStep(step) {
        const requiredFields = document.querySelectorAll(`#step${step} [required]`);
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('error');
                isValid = false;
            } else {
                field.classList.remove('error');
            }
        });

        if (!isValid) {
            showNotification('Error', 'Please fill in all required fields.', 'error');
        }

        return isValid;
    }

    // Initialize when page loads
    document.addEventListener('DOMContentLoaded', function() {
        updateStepVisibility();
    });

    // Add this to your form submission handling
    $('#addUserForm').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const formContent = form.find('.form-step');
        const formFeedback = form.find('.form-feedback');
        const loadingAnimation = formFeedback.find('.loading-animation');
        const successCheckmark = formFeedback.find('.success-checkmark');
        const feedbackMessage = formFeedback.find('.feedback-message');
        const modalFooter = form.find('.modal-footer'); // Get the modal footer containing the buttons

        // Hide form content, buttons, and show loading
        formContent.hide();
        modalFooter.hide(); // Hide all buttons in the footer
        formFeedback.show();
        loadingAnimation.show();
        successCheckmark.hide();
        feedbackMessage.text('Processing your request...');

        const formData = new FormData(this);

        $.ajax({
            url: 'add_user.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                setTimeout(() => {
                    if (response.success) {
                        // Show success state
                        loadingAnimation.hide();
                        successCheckmark.show().css('animation', 'fadeIn 0.5s');
                        feedbackMessage.text('User added successfully!');

                        // Redirect after showing success state
                        setTimeout(() => {
                            window.location.href = 'users.php';
                        }, 1500);
                    } else {
                        // Show error state
                        formContent.show();
                        modalFooter.show(); // Show the buttons again
                        formFeedback.hide();
                        feedbackMessage.text(response.message || 'Error adding user');
                    }
                }, 2000); // Processing simulation delay
            },
            error: function(xhr, status, error) {
                // Show error state
                formContent.show();
                modalFooter.show(); // Show the buttons again
                formFeedback.hide();
                alert('Error adding user. Please try again.');
            }
        });
    });

    // Add this to reset the form state when modal is closed
    document.getElementById('closeAddUserModal').addEventListener('click', function() {
        const form = $('#addUserForm');
        form.find('.form-step').show();
        form.find('.modal-footer').show(); // Show the buttons
        form.find('.form-feedback').hide();
        form[0].reset();
        document.getElementById('profilePreview').src = 'images/avatar.png';
    });

    // Add these event handlers
    document.getElementById('cancelDelete').addEventListener('click', function() {
        document.getElementById('deleteConfirmModal').style.display = 'none';
        document.getElementById('confirmUserId').value = '';
        document.getElementById('deleteError').style.display = 'none';
    });

    document.getElementById('closeDeleteModal').addEventListener('click', function() {
        document.getElementById('deleteConfirmModal').style.display = 'none';
        document.getElementById('confirmUserId').value = '';
        document.getElementById('deleteError').style.display = 'none';
    });

    document.getElementById('confirmDelete').addEventListener('click', function() {
        const confirmInput = document.getElementById('confirmUserId');
        const errorDiv = document.getElementById('deleteError');

        if (confirmInput.value !== userIdToDelete) {
            errorDiv.textContent = 'User ID does not match';
            errorDiv.style.display = 'block';
            return;
        }

        // Disable the delete button and show loading state
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';

        $.ajax({
            url: '../api/delete_user.php',
            method: 'POST',
            data: {
                user_id: userIdToDelete
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Close modal and refresh table
                    document.getElementById('deleteConfirmModal').style.display = 'none';
                    fetchUsers(currentPage);
                } else {
                    errorDiv.textContent = response.message || 'Failed to delete user';
                    errorDiv.style.display = 'block';
                }
            },
            error: function(xhr) {
                errorDiv.textContent = 'Error occurred while deleting user';
                errorDiv.style.display = 'block';
            },
            complete: function() {
                // Reset button state
                const confirmButton = document.getElementById('confirmDelete');
                confirmButton.disabled = false;
                confirmButton.innerHTML = 'Delete';
            }
        });
    });

    // Add event listeners for the edit modal
    document.getElementById('closeEditModal').addEventListener('click', function() {
        document.getElementById('editUserModal').style.display = 'none';
        document.getElementById('editError').style.display = 'none';
    });

    document.getElementById('cancelEdit').addEventListener('click', function() {
        document.getElementById('editUserModal').style.display = 'none';
        document.getElementById('editError').style.display = 'none';
    });

    // Profile picture preview functionality
    document.getElementById('editProfilePicture').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('editProfilePreview').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    function editUser(userId) {
        const editModal = document.getElementById('editUserModal');
        editModal.style.display = 'block';

        // Fetch user data
        $.ajax({
            url: '../api/get_user.php',
            method: 'GET',
            data: {
                user_id: userId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const user = response.data;

                    // Populate form fields
                    document.getElementById('edit_user_id').value = user.user_id;
                    document.getElementById('edit_name').value = user.name;
                    document.getElementById('edit_username').value = user.username;
                    document.getElementById('edit_email').value = user.email;
                    document.getElementById('edit_contact').value = user.contact;
                    document.getElementById('edit_role').value = user.role;
                    document.getElementById('edit_status').value = user.status;
                    document.getElementById('edit_company').value = user.company;
                    document.getElementById('edit_hire_date').value = user.hire_date;
                    document.getElementById('edit_shift_start').value = user.shift_start;
                    document.getElementById('edit_shift_end').value = user.shift_end;

                    // Set profile picture preview
                    const profilePreview = document.getElementById('editProfilePreview');
                    profilePreview.src = user.profile_picture ? user.profile_picture : '../images/avatar.png';
                } else {
                    document.getElementById('editError').textContent = response.message || 'Failed to load user data';
                    document.getElementById('editError').style.display = 'block';
                }
            },
            error: function(xhr) {
                document.getElementById('editError').textContent = 'Error loading user data';
                document.getElementById('editError').style.display = 'block';
            }
        });
    }

    // Handle form submission
    document.getElementById('editUserForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const form = this;
        const submitButton = form.querySelector('button[type="submit"]');
        const errorDiv = document.getElementById('editError');

        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        const formData = new FormData(form);

        $.ajax({
            url: '../api/update_user.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // Parse the response if it's a string
                const result = typeof response === 'string' ? JSON.parse(response) : response;

                if (result.success) {
                    document.getElementById('editUserModal').style.display = 'none';
                    fetchUsers(currentPage); // Refresh the table
                    alert('User updated successfully'); // Optional: show success message
                } else {
                    errorDiv.textContent = result.message || 'Failed to update user';
                    errorDiv.style.display = 'block';
                }
            },
            error: function(xhr) {
                let errorMessage = 'Error occurred while updating user';
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMessage = response.message || errorMessage;
                } catch (e) {
                    // If response isn't JSON, use default message
                }
                errorDiv.textContent = errorMessage;
                errorDiv.style.display = 'block';
            },
            complete: function() {
                submitButton.disabled = false;
                submitButton.innerHTML = 'Save Changes';
            }
        });
    });
</script>

</html>
<!-- Uncomment this line if you want to use jQuery -->
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