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
    <title>Attendance | WorkPulse</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Import professional fonts */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

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

            /* Typography variables */
            --font-primary: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            --font-size-xs: 0.75rem;
            --font-size-sm: 0.875rem;
            --font-size-base: 1rem;
            --font-size-lg: 1.125rem;
            --font-size-xl: 1.25rem;
            --font-size-2xl: 1.5rem;
            --font-size-3xl: 1.875rem;

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

        /* Separator between nav groups */
        .nav-separator {
            height: 1px;
            background: linear-gradient(90deg, 
                rgba(255, 255, 255, 0) 0%, 
                rgba(255, 255, 255, 0.1) 50%, 
                rgba(255, 255, 255, 0) 100%
            );
            margin: 0.75rem 1rem;
        }

        /* Special styling for logout item */
        .nav-item.logout {
            margin-top: auto;
            color: #ff7675;
        }

        .nav-item.logout:hover {
            background: rgba(255, 118, 117, 0.1);
            color: #ff5252;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            margin-bottom: var(--footer-height);
            padding: 2rem;
            transition: all 0.3s ease;
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        .content-header {
            margin-bottom: 2rem;
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

        /* Attendance Management Specific Styles */
        .attendance-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .control-group {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .date-filter {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .date-input-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .date-input-group label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .date-input {
            padding: 0.625rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.875rem;
            color: var(--text-primary);
            background: var(--bg-primary);
            transition: all 0.3s ease;
            min-width: 140px;
        }

        .date-input:hover {
            border-color: var(--accent-orange);
        }

        .date-input:focus {
            outline: none;
            border-color: var(--accent-orange);
            box-shadow: 0 0 0 3px rgba(244, 164, 96, 0.2);
            transform: translateY(-1px);
        }

        /* Calendar icon styling */
        .date-input::-webkit-calendar-picker-indicator {
            cursor: pointer;
            opacity: 0.6;
            transition: all 0.2s ease;
        }

        .date-input::-webkit-calendar-picker-indicator:hover {
            opacity: 1;
        }

        /* Dark mode support for date inputs */
        @media (prefers-color-scheme: dark) {
            .date-input {
                background: rgba(255, 255, 255, 0.05);
                color: var(--text-primary);
            }
            
            .date-input::-webkit-calendar-picker-indicator {
                filter: invert(1);
            }
        }

        /* Responsive adjustments */
        @media screen and (max-width: 768px) {
            .attendance-controls {
                flex-direction: column;
                align-items: stretch;
            }

            .control-group {
                flex-wrap: wrap;
            }

            .date-filter {
                flex-wrap: wrap;
                gap: 1rem;
            }

            .date-input-group {
                flex: 1;
                min-width: 140px;
            }
        }

        /* Control Button Base Styles */
        .control-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1rem;
            border-radius: 8px;
            border: none;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        /* Ripple effect */
        .control-btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.5);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
        }

        .control-btn:active::after {
            animation: ripple 0.6s ease-out;
        }

        .control-btn i {
            font-size: 1rem;
        }

        .control-btn span {
            line-height: 1;
        }

        /* Button variants with enhanced interactions */
        .control-btn.primary,
        .control-btn[data-action="filter"],
        .control-btn[data-action="export"] {
            background: linear-gradient(135deg, var(--accent-orange), var(--primary-color));
            color: white;
            box-shadow: 0 2px 4px rgba(244, 164, 96, 0.2);
        }

        .control-btn.primary:hover,
        .control-btn[data-action="filter"]:hover,
        .control-btn[data-action="export"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(244, 164, 96, 0.3);
            background: linear-gradient(135deg, var(--primary-color), var(--accent-orange));
        }

        .control-btn.primary:active,
        .control-btn[data-action="filter"]:active,
        .control-btn[data-action="export"]:active {
            transform: translateY(1px);
            box-shadow: 0 2px 4px rgba(244, 164, 96, 0.2);
        }

        /* Icon styles for all primary buttons */
        .control-btn.primary i,
        .control-btn[data-action="filter"] i,
        .control-btn[data-action="export"] i {
            font-size: 1rem;
            transition: transform 0.3s ease;
        }

        .control-btn.primary:hover i,
        .control-btn[data-action="filter"]:hover i,
        .control-btn[data-action="export"]:hover i {
            animation: pulse 1s infinite;
        }

        /* Specific hover animations for each button type */
        .control-btn[data-action="sync"]:hover i {
            animation: rotate-hover 1s infinite linear;
        }

        .control-btn[data-action="export"]:hover i {
            animation: bounce 0.6s ease infinite;
        }

        .control-btn[data-action="filter"]:hover i {
            animation: shake 0.6s ease infinite;
        }

        /* Loading state */
        .control-btn.loading {
            opacity: 0.8;
            cursor: wait;
        }

        .control-btn.loading i {
            animation: fa-spin 1s infinite linear !important;
        }

        /* Animations */
        @keyframes ripple {
            0% {
                transform: scale(0, 0);
                opacity: 0.5;
            }
            100% {
                transform: scale(100, 100);
                opacity: 0;
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
            }
        }

        @keyframes rotate-hover {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(180deg);
            }
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-3px);
            }
        }

        @keyframes shake {
            0%, 100% {
                transform: rotate(0deg);
            }
            25% {
                transform: rotate(-10deg);
            }
            75% {
                transform: rotate(10deg);
            }
        }

        /* Loading animation */
        .control-btn .fa-spin {
            animation: fa-spin 1s infinite linear;
        }

        @keyframes fa-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Date filter styling */
        .date-filter {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: var(--bg-secondary);
            padding: 0.5rem;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .date-input-group {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .date-input-group label {
            font-size: 0.75rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        /* Date inputs animation */
        .date-input {
            padding: 0.5rem;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            font-size: 0.875rem;
            color: var(--text-primary);
            background: var(--bg-primary);
            transition: all 0.3s ease;
        }

        .date-input:hover {
            border-color: var(--accent-orange);
        }

        .date-input:focus {
            outline: none;
            border-color: var(--accent-orange);
            box-shadow: 0 0 0 3px rgba(244, 164, 96, 0.2);
            transform: translateY(-1px);
        }

        /* Button states */
        .control-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        .control-btn .fa-spin {
            animation: fa-spin 1s infinite linear;
        }

        @keyframes fa-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive adjustments */
        @media screen and (max-width: 768px) {
            .attendance-controls {
                flex-direction: column;
                align-items: stretch;
            }

            .control-group {
                flex-wrap: wrap;
            }

            .control-group.right {
                width: 100%;
            }

            .date-filter {
                flex-wrap: wrap;
                gap: 1rem;
            }

            .date-input-group {
                flex: 1;
                min-width: 140px;
            }

            .control-btn {
                flex: 1;
                justify-content: center;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .control-btn.secondary {
                background: rgba(255, 255, 255, 0.05);
            }

            .control-btn.secondary:hover {
                background: rgba(255, 255, 255, 0.1);
            }

            .date-input {
                background: rgba(255, 255, 255, 0.05);
            }
        }

        /* Table styles with equal column distribution */
        .attendance-table {
            width: 100%;
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            border: 1px solid var(--accent-orange);
            font-size: var(--font-size-sm);
        }

        .attendance-table:hover {
            box-shadow: 0 4px 12px rgba(244, 164, 96, 0.08),
                        0 8px 24px rgba(244, 164, 96, 0.04);
        }

        .attendance-table table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: fixed; /* Important for equal column widths */
        }

        /* Equal column width distribution - 10 columns */
        .attendance-table th,
        .attendance-table td {
            width: 10%; /* Each column gets exactly 10% width */
            padding: 0.5rem 0.375rem;
        }

        /* Header cells styling */
        .attendance-table th {
            background: var(--accent-orange);
            color: white; /* Changed to white for better contrast */
            font-weight: 600;
            font-size: 0.813rem;
            text-align: center;
            border-bottom: 2px solid rgba(139, 69, 19, 0.3); /* Darker border using primary color */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            transition: all 0.2s ease;
            position: relative;
            padding: 0.75rem 0.375rem; /* Slightly increased padding */
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.1); /* Subtle text shadow for depth */
        }

        /* Header hover effect */
        .attendance-table th:hover {
            background: #e5955c; /* Slightly darker shade of accent-orange */
        }

        /* Header after-element modification */
        .attendance-table th::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: white; /* Changed to white for better visibility */
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .attendance-table th:hover::after {
            width: 80%;
        }

        /* First row after header subtle shadow */
        .attendance-table tbody tr:first-child td {
            box-shadow: inset 0 4px 3px -3px rgba(0, 0, 0, 0.05);
        }

        /* Data cells */
        .attendance-table td {
            color: var(--text-secondary);
            font-size: 0.75rem; /* 12px */
            border-bottom: 1px solid rgba(244, 164, 96, 0.1);
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            transition: all 0.2s ease;
        }

        /* Time columns with hover effect */
        .attendance-table td:nth-child(3),
        .attendance-table td:nth-child(4),
        .attendance-table td:nth-child(5),
        .attendance-table td:nth-child(6),
        .attendance-table td:nth-child(7),
        .attendance-table td:nth-child(8) {
            font-family: 'SF Mono', SFMono-Regular, ui-monospace, monospace;
            font-size: 0.813rem;
            color: var(--text-primary);
            font-weight: var(--font-medium);
            transition: all 0.2s ease;
            position: relative;
        }

        .attendance-table tr:hover td:nth-child(3),
        .attendance-table tr:hover td:nth-child(4),
        .attendance-table tr:hover td:nth-child(5),
        .attendance-table tr:hover td:nth-child(6),
        .attendance-table tr:hover td:nth-child(7),
        .attendance-table tr:hover td:nth-child(8) {
            transform: scale(1.02);
            color: var(--accent-orange);
        }

        /* Date column specific styling */
        .attendance-table td:nth-child(2) {
            font-size: 0.813rem; /* 13px */
            font-weight: var(--font-medium);
            color: var(--text-primary);
        }

        /* Name column */
        .attendance-table td:first-child {
            font-weight: 500;
            font-size: 0.75rem; /* 12px */
            transition: all 0.2s ease;
            position: relative;
        }

        .attendance-table tr:hover td:first-child {
            padding-left: 1rem;
            color: var(--accent-orange);
        }

        /* Status column */
        .attendance-table td:nth-child(9) {
            font-weight: 500;
            font-size: 0.75rem; /* 12px */
            transition: all 0.2s ease;
        }

        .attendance-table tr:hover td:nth-child(9) {
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        /* Action buttons column */
        .attendance-table td:last-child {
            white-space: nowrap;
        }

        .attendance-table .control-btn {
            padding: 0.25rem;
            font-size: 0.75rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .attendance-table .control-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(244, 164, 96, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.4s ease, height 0.4s ease;
        }

        .attendance-table .control-btn:hover::before {
            width: 200%;
            height: 200%;
        }

        .attendance-table .control-btn:hover {
            transform: translateY(-1px);
        }

        .attendance-table .control-btn:active {
            transform: translateY(1px);
        }

        .attendance-table .control-btn i {
            font-size: 0.875rem;
        }

        /* Responsive adjustments for 1368x768 */
        @media screen and (max-width: 1368px) {
            .attendance-table {
                margin: 0.5rem 0;
            }

            .attendance-table th,
            .attendance-table td {
                padding: 0.375rem 0.25rem;
            }

            /* Adjust row height */
            .attendance-table tbody tr {
                height: 32px;
            }

            /* Header text */
            .attendance-table th {
                font-size: 0.75rem; /* 12px */
            }

            /* Cell content */
            .attendance-table td {
                font-size: 0.688rem; /* 11px */
            }

            /* Time columns - still keeping them larger than before */
            .attendance-table td:nth-child(2),
            .attendance-table td:nth-child(3),
            .attendance-table td:nth-child(4),
            .attendance-table td:nth-child(5),
            .attendance-table td:nth-child(6),
            .attendance-table td:nth-child(7),
            .attendance-table td:nth-child(8) {
                font-size: 0.75rem; /* Increased from 0.625rem to 0.75rem (12px) */
            }

            /* Action buttons */
            .attendance-table .control-btn {
                padding: 0.2rem;
            }

            .attendance-table .control-btn i {
                font-size: 0.75rem;
            }
        }

        /* Table wrapper for horizontal scroll if needed */
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            margin: 1rem 0;
        }

        /* Hover effects */
        .attendance-table tbody tr:hover {
            background: rgba(244, 164, 96, 0.03);
        }

        /* Add horizontal scroll for very narrow screens */
        @media screen and (max-width: 1200px) {
            .table-wrapper {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .attendance-table {
                min-width: 1000px; /* Ensure table doesn't compress too much */
            }
        }

        /* Footer Styles */
        .footer {
            height: var(--footer-height);
            background: var(--card-bg);
            border-top: 1px solid #e2e8f0;
            position: fixed;
            bottom: 0;
            left: var(--sidebar-width);
            right: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: var(--font-size-xs);
            color: var(--text-secondary);
            transition: all 0.3s ease;
        }

        .footer.expanded {
            left: var(--sidebar-collapsed-width);
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

        /* Responsive Design */
        @media screen and (max-width: 1024px) {
            .attendance-controls {
                flex-direction: column;
            }

            .date-filter {
                margin-left: 0;
            }
        }

        @media screen and (max-width: 768px) {
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

            .attendance-table {
                overflow-x: auto;
            }
        }

        /* Styling for name column */
        .attendance-table td:first-child {
            font-weight: 500;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 150px; /* Adjust this value as needed */
            font-size: var(--font-size-sm); /* Default size */
        }

        /* Add class for long names */
        .attendance-table td:first-child.long-name {
            font-size: var(--font-size-xs); /* Smaller size for long names */
        }

        /* Add these to your existing CSS */
        .text-center {
            text-align: center;
        }

        .attendance-table td[colspan] {
            padding: 2rem;
            font-style: italic;
            color: var(--text-secondary);
        }

        /* Optional loading animation */
        .loading {
            position: relative;
            opacity: 0.7;
            pointer-events: none;
        }

        .loading::after {
            content: "Loading...";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-style: italic;
            background: rgba(255, 255, 255, 0.8);
            padding: 0.5rem 1rem;
            border-radius: 4px;
        }

        /* Enhanced Date Filter Styles */
        .date-filter {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.25rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--accent-orange);
            transition: all 0.3s ease;
        }

        .date-filter:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transform: translateY(-1px);
        }

        .date-filter-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            color: var(--text-primary);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .date-filter-header i {
            color: var(--accent-orange);
        }

        .date-inputs-wrapper {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .date-input-group {
            flex: 1;
        }

        .date-input-group label {
            display: block;
            font-size: 0.75rem;
            color: var(--text-secondary);
            margin-bottom: 0.375rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .date-input-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .date-input {
            width: 100%;
            padding: 0.75rem;
            padding-right: 2.5rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.875rem;
            color: var(--text-primary);
            background: var(--bg-primary);
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .date-input:hover {
            border-color: var(--accent-orange);
        }

        .date-input:focus {
            outline: none;
            border-color: var(--accent-orange);
            box-shadow: 0 0 0 3px rgba(244, 164, 96, 0.2);
        }

        .input-icon {
            position: absolute;
            right: 0.75rem;
            color: var(--text-secondary);
            pointer-events: none;
            transition: color 0.2s ease;
        }

        .date-input-container:hover .input-icon {
            color: var(--accent-orange);
        }

        .date-separator {
            display: flex;
            align-items: center;
            color: var(--text-secondary);
            padding: 0 0.5rem;
        }

        .date-filter-apply {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem;
            background: var(--accent-orange);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .date-filter-apply:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(244, 164, 96, 0.3);
        }

        .date-filter-apply:active {
            transform: translateY(1px);
        }

        /* Hide default calendar picker icon */
        .date-input::-webkit-calendar-picker-indicator {
            opacity: 0;
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            cursor: pointer;
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .date-filter {
                background: rgba(255, 255, 255, 0.05);
            }

            .date-input {
                background: rgba(255, 255, 255, 0.05);
            }

            .date-filter-apply {
                background: var(--accent-orange);
            }
        }

        /* Responsive adjustments */
        @media screen and (max-width: 768px) {
            .date-inputs-wrapper {
                flex-direction: column;
                gap: 1rem;
            }

            .date-separator {
                transform: rotate(90deg);
            }

            .date-input-group {
                width: 100%;
            }
        }

        /* Quick date presets dropdown (optional enhancement) */
        .date-presets {
            position: absolute;
            top: 100%;
            left: 0;
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            z-index: 10;
            min-width: 150px;
            margin-top: 0.5rem;
            display: none;
        }

        .date-presets.show {
            display: block;
        }

        .date-preset-option {
            padding: 0.75rem 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .date-preset-option:hover {
            background: rgba(244, 164, 96, 0.1);
            color: var(--accent-orange);
        }

        /* Minimal Date Filter Styles */
        .date-filter {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--bg-secondary);
            padding: 0.5rem;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .date-input-container {
            position: relative;
        }

        .date-input-container i {
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            pointer-events: none;
            font-size: 0.875rem;
        }

        .date-input {
            padding: 0.5rem;
            padding-right: 2rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 0.875rem;
            color: var(--text-primary);
            background: var(--bg-primary);
            transition: all 0.2s ease;
            width: 130px;
        }

        .date-input:hover {
            border-color: var(--accent-orange);
        }

        .date-input:focus {
            outline: none;
            border-color: var(--accent-orange);
            box-shadow: 0 0 0 2px rgba(244, 164, 96, 0.2);
        }

        .date-separator {
            color: var(--text-secondary);
            font-weight: 300;
        }

        .date-filter-apply {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 6px;
            background: var(--accent-orange);
            color: white;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .date-filter-apply:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(244, 164, 96, 0.3);
        }

        /* Hide default calendar picker icon */
        .date-input::-webkit-calendar-picker-indicator {
            opacity: 0;
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            cursor: pointer;
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .date-filter {
                background: rgba(255, 255, 255, 0.05);
            }
            
            .date-input {
                background: rgba(255, 255, 255, 0.05);
            }
        }

        /* Responsive adjustments */
        @media screen and (max-width: 480px) {
            .date-filter {
                flex-wrap: wrap;
                gap: 0.5rem;
            }
            
            .date-input-container {
                flex: 1;
                min-width: 120px;
            }
            
            .date-filter-apply {
                width: 100%;
                height: 36px;
            }
        }

        /* Consolidated Media Queries */
        @media screen and (max-width: 768px) {
            /* Sidebar */
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

            /* Controls */
            .attendance-controls {
                flex-direction: column;
                align-items: stretch;
            }

            .control-group {
                flex-wrap: wrap;
            }

            .control-group.right {
                width: 100%;
            }

            .date-filter {
                flex-wrap: wrap;
                gap: 1rem;
            }

            .date-input-group {
                flex: 1;
                min-width: 140px;
            }

            .control-btn {
                flex: 1;
                justify-content: center;
            }

            /* Table */
            .attendance-table {
                overflow-x: auto;
            }
        }

        /* Dark Mode Consolidated */
        @media (prefers-color-scheme: dark) {
            .control-btn.secondary {
                background: rgba(255, 255, 255, 0.05);
            }

            .control-btn.secondary:hover {
                background: rgba(255, 255, 255, 0.1);
            }

            .date-input {
                background: rgba(255, 255, 255, 0.05);
                color: var(--text-primary);
            }
            
            .date-input::-webkit-calendar-picker-indicator {
                filter: invert(1);
            }
        }

        /* Pagination Styles */
        .pagination-info {
            color: var(--text-secondary);
            font-size: 0.875rem;
            text-align: right;
            padding: 1rem 0.5rem;
            border-top: 1px solid var(--border-color);
            margin-top: 0.5rem;
        }

        .pagination-info span {
            font-weight: 400;
        }

        .pagination-info #startRecord,
        .pagination-info #endRecord,
        .pagination-info #totalRecords {
            font-weight: 600;
            color: var(--text-primary);
        }

        .pagination-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-top: 1.5rem;
            padding: 1rem 0;
            border-top: 1px solid var(--border-color);
        }

        .pagination-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            background: var(--bg-secondary);
            color: var(--text-primary);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination-btn:not(:disabled):hover {
            background: var(--accent-orange);
            color: white;
            border-color: var(--accent-orange);
        }

        .pagination-pages {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .page-number {
            padding: 0.5rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .page-number.active {
            background: var(--accent-orange);
            color: white;
            border-color: var(--accent-orange);
        }

        .page-number:not(.active):hover {
            background: var(--bg-secondary);
        }

        @media screen and (max-width: 768px) {
            .pagination-controls {
                flex-wrap: wrap;
            }
            
            .pagination-pages {
                order: -1;
                width: 100%;
                justify-content: center;
                margin-bottom: 1rem;
            }
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        /* Add this to ensure modal is above other elements */
        .modal.show {
            display: block;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: var(--primary-color);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: var(--text-primary);
            font-weight: var(--font-medium);
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: var(--font-size-base);
        }

        .modal-footer {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* For 1368x768 and similar laptop displays */
        @media screen and (max-width: 1368px) {
            /* Adjust main content padding */
            .main-content {
                padding: 1.5rem;
            }

            /* Optimize table for smaller screens */
            .attendance-table th,
            .attendance-table td {
                padding: 0.625rem 0.5rem;
                font-size: var(--font-size-sm);
            }

            /* Adjust column widths */
            .attendance-table th:nth-child(1) { width: 20%; } /* Name */
            .attendance-table th:nth-child(2) { width: 15%; } /* Date */
            .attendance-table th:nth-child(3),
            .attendance-table th:nth-child(4) { width: 12%; } /* Time columns */
            .attendance-table th:nth-child(5),
            .attendance-table th:nth-child(6),
            .attendance-table th:nth-child(7),
            .attendance-table th:nth-child(8) { width: 10%; }
            .attendance-table th:last-child { width: 8%; } /* Actions */

            /* Optimize controls section */
            .attendance-controls {
                gap: 1rem;
                flex-wrap: wrap;
            }

            .control-group {
                gap: 0.5rem;
            }

            .date-filter {
                flex: 1;
                min-width: 300px;
            }

            /* Adjust sidebar width */
            :root {
                --sidebar-width: 220px;
                --sidebar-collapsed-width: 60px;
            }

            /* Optimize font sizes */
            .nav-item {
                font-size: var(--font-size-xs);
                padding: 0.625rem 1.25rem;
            }

            .nav-item i {
                font-size: 16px;
            }

            /* Adjust header elements */
            .header {
                padding: 0 1rem;
            }

            .brand h1 {
                font-size: var(--font-size-lg);
            }

            /* Optimize date inputs */
            .date-input-group {
                min-width: 120px;
            }

            .date-input {
                padding: 0.375rem 0.5rem;
                font-size: var(--font-size-sm);
            }

            /* Adjust footer */
            .footer {
                padding: 0.5rem;
            }
        }

        /* Additional optimization for height */
        @media screen and (max-height: 768px) {
            :root {
                --header-height: 50px;
                --footer-height: 25px;
            }

            /* Adjust vertical spacing */
            .sidebar {
                top: var(--header-height);
                padding: 0.75rem 0;
            }

            .main-content {
                margin-top: var(--header-height);
                margin-bottom: var(--footer-height);
            }

            /* Optimize table vertical spacing */
            .attendance-table tbody tr {
                height: 40px;
            }

            /* Adjust content header */
            .content-header {
                margin-bottom: 1.5rem;
            }

            .content-header h1 {
                font-size: var(--font-size-xl);
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
        <a href="attendance.php" class="nav-item active">
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
            <h1>Attendance Management</h1>
        </div>

        <div class="attendance-controls">
            <div class="control-group">
                <button class="control-btn primary" data-action="sync">
                    <i class="fas fa-sync"></i>
                    <span>Sync Device</span>
                </button>
                <button class="control-btn primary" data-action="export">
                    <i class="fas fa-download"></i>
                    <span>Export</span>
                </button>
            </div>
            
            <div class="date-filter">
                <div class="date-input-container">
                    <input type="date" id="startDate" class="date-input" name="startDate">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="date-separator">—</div>
                <div class="date-input-container">
                    <input type="date" id="endDate" class="date-input" name="endDate">
                    <i class="fas fa-calendar"></i>
                </div>
                <button class="date-filter-apply" title="Apply date filter">
                    <i class="fas fa-check"></i>
                </button>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Date</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Break In</th>
                        <th>Break Out</th>
                        <th>OT In</th>
                        <th>OT Out</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="attendanceTableBody">
                    <!-- Data will be populated here -->
                </tbody>
            </table>
            <div class="pagination-controls">
                <button class="pagination-btn" id="prevPage" disabled>
                    <i class="fas fa-chevron-left"></i> Previous
                </button>
                <div class="pagination-pages" id="paginationPages">
                    <!-- Page numbers will be inserted here -->
                </div>
                <button class="pagination-btn" id="nextPage" disabled>
                    Next <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            <div class="pagination-info">
                <span>Showing <span id="startRecord">1</span>-<span id="endRecord">10</span> of <span id="totalRecords">0</span> records</span>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy;<?php echo date('Y'); ?> WorkPulse. All rights reserved.</p>
    </footer>

    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Consolidated JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar functionality
            const sidebarToggle = document.querySelector('.sidebar-toggle');
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            const footer = document.querySelector('.footer');
            const overlay = document.querySelector('.mobile-menu-overlay');

            function handleSidebarToggle() {
                if (window.innerWidth <= 768) {
                    sidebar.classList.toggle('show');
                    overlay.classList.toggle('show');
                } else {
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');
                    footer.classList.toggle('expanded');
                }
            }

            sidebarToggle.addEventListener('click', handleSidebarToggle);
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });

            // Handle window resize
            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    if (window.innerWidth > 768) {
                        sidebar.classList.remove('show');
                        overlay.classList.remove('show');
                    }
                }, 250);
            });

            // Navigation active state
            const navItems = document.querySelectorAll('.sidebar .nav-item');
            const currentPath = window.location.pathname;
            const activePath = localStorage.getItem('activePath');

            navItems.forEach(item => {
                const href = item.getAttribute('href');
                
                // Set active state based on current path or stored path
                if (currentPath.endsWith(href) || (activePath && activePath === href)) {
                    item.classList.add('active');
                }

                // Handle click events
                item.addEventListener('click', function() {
                    navItems.forEach(nav => nav.classList.remove('active'));
                    this.classList.add('active');
                    localStorage.setItem('activePath', this.getAttribute('href'));
                });
            });

            // Initialize date range filter
            const startDateInput = document.getElementById('startDate');
            const endDateInput = document.getElementById('endDate');
            
            // Set default dates (current month)
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            
            startDateInput.value = firstDay.toISOString().split('T')[0];
            endDateInput.value = lastDay.toISOString().split('T')[0];

            // Fetch initial data
            fetchAttendanceData(startDateInput.value, endDateInput.value);

            // Handle date filter
            document.querySelector('.date-filter-apply').addEventListener('click', function() {
                const startDateInput = document.getElementById('startDate');
                const endDateInput = document.getElementById('endDate');

                if (!startDateInput.value || !endDateInput.value) {
                    alert('Please select both start and end dates');
                    return;
                }
                
                // Reset to page 1 when applying new filters
                currentPage = 1;
                fetchAttendanceData(startDateInput.value, endDateInput.value, currentPage);
            });

            // Validate date range
            [startDateInput, endDateInput].forEach(input => {
                input.addEventListener('change', function() {
                    const startDate = new Date(startDateInput.value);
                    const endDate = new Date(endDateInput.value);
                    
                    if (endDate < startDate) {
                        endDateInput.value = startDateInput.value;
                    }
                });
            });

            // Handle long names in table
            function adjustLongNames() {
                const nameColumns = document.querySelectorAll('.attendance-table td:first-child');
                nameColumns.forEach(cell => {
                    if (cell.textContent.length > 15) {
                        cell.classList.add('long-name');
                    }
                });
            }

            // Call after data load
            adjustLongNames();

            // Handle export button click
            document.querySelector('[data-action="export"]').addEventListener('click', function() {
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;
                
                // Create export URL with date parameters
                const exportUrl = `../api/export_attendance.php?startDate=${startDate}&endDate=${endDate}`;
                
                // Create temporary link and trigger download
                const link = document.createElement('a');
                link.href = exportUrl;
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });

            // Add loading state to export button
            const exportButton = document.querySelector('[data-action="export"]');
            exportButton.addEventListener('click', function() {
                const originalContent = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Exporting...</span>';
                this.disabled = true;

                setTimeout(() => {
                    this.innerHTML = originalContent;
                    this.disabled = false;
                }, 2000);
            });

            // Handle sync button click
            const syncButton = document.querySelector('[data-action="sync"]');
            syncButton.addEventListener('click', function() {
                // Add loading state to button
                const originalContent = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Syncing...</span>';
                this.disabled = true;

                // Call the dashboard API to sync data
                fetch('../api/dashboard.php')
                    .then(response => response.json())
                    .then(() => {
                        // After sync is complete, refresh the attendance data
                        fetchAttendanceData(
                            document.getElementById('startDate').value,
                            document.getElementById('endDate').value
                        );
                        
                        // Show success state
                        this.innerHTML = '<i class="fas fa-check"></i><span>Synced!</span>';
                        
                        // Reset button after 2 seconds
                        setTimeout(() => {
                            this.innerHTML = originalContent;
                            this.disabled = false;
                        }, 2000);
                    })
                    .catch(error => {
                        console.error('Sync error:', error);
                        // Show error state
                        this.innerHTML = '<i class="fas fa-exclamation-triangle"></i><span>Sync Failed</span>';
                        
                        // Reset button after 3 seconds
                        setTimeout(() => {
                            this.innerHTML = originalContent;
                            this.disabled = false;
                        }, 3000);
                    });
            });
        });
    </script>

    <script>
        let currentPage = 1;  // Track current page globally

        function fetchAttendanceData(startDate = null, endDate = null, page = 1) {
            // Update global currentPage
            currentPage = page;
            
            const perPage = 10; // Records per page
            
            // Add loading state
            $('#attendanceTableBody').html('<tr><td colspan="10" class="text-center loading">Loading...</td></tr>');
            
            // Build URL with parameters
            let url = `../api/attendance.php?page=${page}&perPage=${perPage}`;
            if (startDate && endDate) {
                url += `&startDate=${startDate}&endDate=${endDate}`;
            }

            $.ajax({
                url: url,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (!response.data) {
                        $('#attendanceTableBody').html('<tr><td colspan="10" class="text-center">No data available</td></tr>');
                        return;
                    }

                    const data = response.data;
                    const totalRecords = response.total;
                    const totalPages = Math.ceil(totalRecords / perPage);
                    const start = ((page - 1) * perPage) + 1;
                    const end = Math.min(start + perPage - 1, totalRecords);

                    // Update pagination info
                    $('#startRecord').text(start);
                    $('#endRecord').text(end);
                    $('#totalRecords').text(totalRecords);

                    // Generate table content
                    let tableBody = '';
                    data.forEach(function(record) {
                        tableBody += `
                            <tr>
                                <td${record.name.length > 15 ? ' class="long-name"' : ''}>${record.name}</td>
                                <td>${record.date}</td>
                                <td>${record.check_in || '-'}</td>
                                <td>${record.check_out || '-'}</td>
                                <td>${record.break_in || '-'}</td>
                                <td>${record.break_out || '-'}</td>
                                <td>${record.ot_in || '-'}</td>
                                <td>${record.ot_out || '-'}</td>
                                <td data-status="${record.status}">${record.status}</td>
                                <td>
                                    <button class="control-btn" title="Edit" onclick="editAttendance('${record.user_id}', '${record.date}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    $('#attendanceTableBody').html(tableBody);

                    // Update pagination controls
                    updatePagination(page, totalPages);
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching attendance data:', error);
                    $('#attendanceTableBody').html('<tr><td colspan="10" class="text-center">Error loading attendance data</td></tr>');
                }
            });
        }

        function updatePagination(currentPage, totalPages) {
            currentPage = parseInt(currentPage);
            
            // Update prev/next buttons
            $('#prevPage').prop('disabled', currentPage === 1);
            $('#nextPage').prop('disabled', currentPage === totalPages);

            // Generate page numbers
            let pagesHtml = '';
            for (let i = 1; i <= totalPages; i++) {
                if (
                    i === 1 || // First page
                    i === totalPages || // Last page
                    (i >= currentPage - 2 && i <= currentPage + 2) // Pages around current page
                ) {
                    pagesHtml += `<span class="page-number${i === currentPage ? ' active' : ''}" data-page="${i}">${i}</span>`;
                } else if (i === currentPage - 3 || i === currentPage + 3) {
                    pagesHtml += '<span class="page-number">...</span>';
                }
            }
            $('#paginationPages').html(pagesHtml);

            // Add click handlers
            $('.page-number').click(function() {
                const page = $(this).data('page');
                if (page) {
                    fetchAttendanceData(
                        $('#startDate').value,
                        $('#endDate').value,
                        page
                    );
                }
            });

            // Add prev/next handlers
            $('#prevPage').click(() => {
                if (currentPage > 1) {
                    fetchAttendanceData(
                        $('#startDate').value,
                        $('#endDate').value,
                        currentPage - 1
                    );
                }
            });

            $('#nextPage').click(() => {
                if (currentPage < totalPages) {
                    fetchAttendanceData(
                        $('#startDate').value,
                        $('#endDate').value,
                        currentPage + 1
                    );
                }
            });
        }

        function editAttendance(userId, date) {
            console.log('Edit clicked for user:', userId, 'date:', date);
            
            // Validate parameters
            if (!userId || !date) {
                console.error('Invalid parameters:', { userId, date });
                alert('Invalid parameters provided');
                return;
            }

            const apiUrl = '../api/get_attendance.php';
            console.log('Requesting:', apiUrl, { userId, date });

            // Show loading state in modal instead of table
            $('#editModal .modal-body').addClass('loading');

            $.ajax({
                url: apiUrl,
                method: 'GET',
                data: {
                    user_id: userId,
                    date: date
                },
                dataType: 'json',
                success: function(response) {
                    console.log('API Response:', response);

                    if (response.success && response.data) {
                        const data = response.data;
                        
                        // Populate the form
                        $('#editUserId').val(userId);
                        $('#editDate').val(date);
                        $('#editCheckIn').val(data.check_in || '');
                        $('#editCheckOut').val(data.check_out || '');
                        $('#editBreakIn').val(data.break_in || '');
                        $('#editBreakOut').val(data.break_out || '');
                        $('#editOTIn').val(data.ot_in || '');
                        $('#editOTOut').val(data.ot_out || '');
                        $('#editStatus').val(data.status || 'Present');
                        
                        // Show the modal
                        $('#editModal').show().addClass('show');
                    } else {
                        console.error('Invalid response data:', response);
                        alert('Failed to load attendance data: ' + (response.message || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                    alert('Failed to load attendance data. Please check the console for details.');
                },
                complete: function() {
                    $('#editModal .modal-body').removeClass('loading');
                }
            });
        }

        // Update modal close handlers
        $(document).ready(function() {
            // Close modal when clicking the X or Cancel button
            $('.close, #cancelEdit').click(function() {
                $('#editModal').hide().removeClass('show');
            });

            // Close modal when clicking outside
            $(window).click(function(event) {
                if ($(event.target).is('#editModal')) {
                    $('#editModal').hide().removeClass('show');
                }
            });

            // Handle form submission
            $('#editAttendanceForm').submit(function(e) {
                e.preventDefault();
                
                const formData = $(this).serialize();
                const startDate = $('#startDate').val();
                const endDate = $('#endDate').val();
                
                $.ajax({
                    url: '../api/update_attendance.php',
                    method: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#editModal').hide().removeClass('show');
                            
                            // Refresh the attendance table with current filters and page
                            fetchAttendanceData(startDate, endDate, currentPage);
                            
                            // Show success message
                            alert('Attendance updated successfully!');
                        } else {
                            alert('Failed to update attendance: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error updating attendance:', error);
                        alert('Failed to update attendance. Please try again.');
                        
                        // Refresh the table even on error to restore the view
                        fetchAttendanceData(startDate, endDate, currentPage);
                    },
                    complete: function() {
                        // Always make sure the loading state is cleared
                        $('#attendanceTableBody').find('.loading').removeClass('loading');
                    }
                });
            });
        });
    </script>

    <!-- Add this modal HTML just before the closing </body> tag -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Attendance</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="editAttendanceForm">
                    <input type="hidden" id="editUserId" name="user_id">
                    <input type="hidden" id="editDate" name="date">
                    
                    <div class="form-group">
                        <label for="editCheckIn">Check In</label>
                        <input type="time" id="editCheckIn" name="check_in">
                    </div>
                    
                    <div class="form-group">
                        <label for="editCheckOut">Check Out</label>
                        <input type="time" id="editCheckOut" name="check_out">
                    </div>
                    
                    <div class="form-group">
                        <label for="editBreakIn">Break In</label>
                        <input type="time" id="editBreakIn" name="break_in">
                    </div>
                    
                    <div class="form-group">
                        <label for="editBreakOut">Break Out</label>
                        <input type="time" id="editBreakOut" name="break_out">
                    </div>
                    
                    <div class="form-group">
                        <label for="editOTIn">OT In</label>
                        <input type="time" id="editOTIn" name="ot_in">
                    </div>
                    
                    <div class="form-group">
                        <label for="editOTOut">OT Out</label>
                        <input type="time" id="editOTOut" name="ot_out">
                    </div>
                    
                    <div class="form-group">
                        <label for="editStatus">Status</label>
                        <select id="editStatus" name="status">
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                            <option value="Late">Late</option>
                        </select>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="control-btn" id="cancelEdit">Cancel</button>
                        <button type="submit" class="control-btn primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
















