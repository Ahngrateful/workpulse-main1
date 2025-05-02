<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html>
<head>
    <title>404 - Page Not Found | WorkPulse</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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

            /* Typography variables */
            --font-primary: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            --font-size-xs: 0.75rem;
            --font-size-sm: 0.875rem;
            --font-size-base: 1rem;
            --font-size-lg: 1.125rem;
            --font-size-xl: 1.25rem;
            --font-size-2xl: 1.5rem;
            --font-size-3xl: 2.5rem;

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
            background: var(--dark-bg);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .error-container {
            text-align: center;
            background: var(--card-bg);
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }

        .logo {
            width: 80px;
            height: 80px;
            margin-bottom: 1.5rem;
        }

        .error-icon {
            font-size: 4rem;
            color: var(--accent-orange);
            margin-bottom: 1.5rem;
        }

        h1 {
            color: var(--primary-color);
            font-size: var(--font-size-3xl);
            font-weight: var(--font-bold);
            margin-bottom: 0.5rem;
        }

        p {
            color: var(--text-secondary);
            margin-bottom: 2rem;
            font-size: var(--font-size-sm);
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(to right, var(--gradient-start), var(--gradient-end));
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: var(--font-semibold);
            font-size: var(--font-size-sm);
            transition: all 0.3s ease;
        }

        .back-button:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        @media (prefers-color-scheme: dark) {
            .error-container {
                background: #1a1a1a;
            }

            h1 {
                color: var(--accent-orange);
            }

            p {
                color: #888;
            }
        }

        @media screen and (max-width: 480px) {
            .error-container {
                margin: 1rem;
                padding: 1.5rem;
            }

            .logo {
                width: 60px;
                height: 60px;
            }

            .error-icon {
                font-size: 3rem;
            }

            h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <!-- <img src="assets/logo.png" alt="WorkPulse Logo" class="logo"> -->
        <i class="fas fa-exclamation-triangle error-icon"></i>
        <h1>404</h1>
        <p>Oops! The page you're looking for doesn't exist.</p>
        <a href="/workpulse/" class="back-button">
            <i class="fas fa-arrow-left"></i>
            Back to Home
        </a>
    </div>
</body>
</html>
