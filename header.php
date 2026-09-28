<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Medicals UG</title>
    <link rel="stylesheet" href="style.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>

    :root {
        --background: #003366;
        --background-color: #003366;
        /* --navy-700: #003366;
        --accent-blue: #003366; */
        --muted: #4a5568;
        --bg: #f5f8fc;
        --card: #fff;
        --radius: 12px;
        --glass: rgba(255, 255, 255, 0.85);
    }

    * {
        box-sizing: border-box
    }

    html,
    body {
        height: 100%
    }

    body {
        margin: 0;
        font-family: "Inter", Arial, sans-serif;
        background: var(--bg);
        color: #1f2937;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        line-height: 1.45;
    }

    .container {
        width: 92%;
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    /* Top Navigation */
    .top-nav {
        background-color: #003366;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 2rem;
    }
    .top-nav .brand { display: flex; align-items: center; gap: 10px; }
    .top-nav .logo-img {
        background: #ffffff;
        padding: 10px 12px;
        border-radius: 10px;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0,0,0,.08);
        transition: transform .28s cubic-bezier(.2, .9, .2, 1), box-shadow .28s;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .top-nav .logo-img img { height: 28px; width: auto; max-height: 48px; display: block; object-fit: contain; }
    .top-nav .logo-text { color: #fff; font-weight: 700; font-size: 1.02rem; padding-left: 4px; background: transparent; box-shadow: none; }
    
    .top-nav ul {
        list-style: none;
        display: flex;
        gap: 1.5rem;
    }
    .top-nav a {
        color: white;
        text-decoration: none;
    }
    .btn {
        display: inline-block;
        padding: 10px 18px;
        border-radius: 10px;
        border: 0;
        font-weight: 600;
        cursor: pointer
    }

    .btn-primary {
        background: var(--accent-blue);
        color: #fff
    }

    .btn-outline {
        background: transparent;
        color: #fff;
        border: 2px solid rgba(255, 255, 255, 0.18);
    }

    /* MOBILE RESPONSIVENESS */
    @media (max-width: 992px) {
        .top-nav {
            padding: 0.8rem 1.2rem;
            gap: 1rem;
        }

        .top-nav ul {
            gap: 1.2rem;
        }

        .top-nav a {
            font-size: 0.95rem;
        }

        .container {
            width: 95%;
            padding: 16px;
        }

        .btn {
            padding: 8px 14px;
            font-size: 0.95rem;
        }
    }

    @media (max-width: 768px) {
        .top-nav {
            padding: 0.6rem 1rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .top-nav .logo-img { padding: 8px 10px; }
        .top-nav .logo-img img { height: 20px; max-height: 36px; }

        .top-nav ul {
            gap: 0.8rem;
        }

        .top-nav a {
            font-size: 0.85rem;
        }

        .container {
            padding: 12px;
        }

        .btn {
            padding: 6px 12px;
            font-size: 0.9rem;
        }
    }

    @media (max-width: 576px) {
        .top-nav {
            padding: 0.5rem 0.8rem;
            gap: 0.3rem;
        }

        .top-nav ul {
            gap: 0.5rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .top-nav a {
            font-size: 0.75rem;
        }

        .top-nav .logo-img img { height: 16px; }

        .container {
            width: 100%;
            padding: 8px;
        }

        .btn {
            padding: 6px 10px;
            font-size: 0.8rem;
        }

        main.container {
            padding: 12px;
        }
    }
</style>
</head>
<body>
    <header class="top-nav">
        <div class="brand">
            <div class="logo-img"><img src="includes/images/logo 22.jpg" alt="logos"></div>
            <div class="logo-text">E-Medicals UG</div>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="pharmacy.php">Pharmacy</a></li>
                <li><a href="blogs.php">Blogs</a></li>
            </ul>
        </nav>
    </header>
<main class="container">
