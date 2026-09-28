<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>E-Medicals UG | Thrive Healthy</title>
    <link rel="icon" type="image/jpeg" href="includes/images/logo 22.jpg">
    

    <!-- Fonts + Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        
        :root {
            --navy: #004080;
            --navy-700: #003166;
            --accent-blue: #0055aa;
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        
        .top-nav {
        background-color: #003366;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 2rem;
        }
        .top-nav .logo {
      
            background: #ffffff;
            padding: 12px 14px;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: transform .28s cubic-bezier(.2, .9, .2, 1), box-shadow .28s;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .top-nav .logo img{
            height: 20px;
            width: auto;
            max-height: 48px;
            display: block;
            object-fit: contain;
        }
        
        .top-nav ul {
        list-style: none;
        display: flex;
        gap: 1.5rem;
        }
        .top-nav a {
        color: white;
        text-decoration: none;
        }
        .btn-login {
        background-color: #007bff;
        padding: 0.5rem 1rem;
        border-radius: 5px;
        }
        
.hero {
  color: white;
  position: relative;
  background-image: url('includes/images/flag\ 1.jpg');
  background-size: cover;
  background-position: center;
  height: 75vh;
  width: 100%;
  background-repeat: no-repeat;
  align-items: center;
  text-align: center;
  padding: 4rem 2rem;
  justify-content: center;
  display: flex;
  overflow: hidden;
}
.hero .overlay{
  position: absolute;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: linear-gradient(to bottom right, rgba(0, 0, 0, 0.9), rgba(0, 51, 102, 0.9));
  z-index: 1;
}
hero::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 6, 0.9);
    z-index: -1;
}
.hero h1 {
  font-size: 2.5rem;
  color: #fafcfd;
}
.hero p {
  font-size: 1.2rem;
  margin-top: 1rem;
}
.hero-button {
  margin-top: 5rem;
  display: inline-block;
  background-color: #007bff;
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  text-decoration: none;
}
.hero-content {
  position: relative;
  z-index: 2;
  padding: 2rem;
}

        
        header.site-header {
            background: linear-gradient(180deg, var(--navy) 0%, rgba(0, 64, 128, 0.95) 100%);
            color: #fff;
            padding: 44px 0;
            text-align: center;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        }

        header.site-header h1 {
            margin: 0;
            font-size: 2.1rem;
            font-weight: 700;
            letter-spacing: -0.5px
        }

        header.site-header p {
            margin: 8px 0 0;
            color: rgba(255, 255, 255, 0.95);
            font-size: 1.05rem
        }

        .hero-cta {
            margin-top: 18px
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

        /* ================= Auth Section ================= */
        .auth-row {
            display: flex;
            gap: 20px;
            align-items: center;
            justify-content: center;
            margin: 28px 0
        }

        @media(max-width:720px) {
            .auth-row {
                flex-direction: column
            }
        }

        .auth-right {
            max-height: 90vh;
            overflow-y: auto;
            padding-right: 10px;
        }

        /* Keep the left welcome panel stable */
        .auth-panel {
            display: flex;
            height: 80vh;
            /* prevents stretching and keeps layout clean */
        }

        /* Smooth, nice scrollbar styling */
        .auth-right::-webkit-scrollbar {
            width: 6px;
        }

        .auth-right::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 10px;
        }

        .auth-right::-webkit-scrollbar-track {
            background: transparent;
        }

                /* Quick Access Cards */
        .quick-access {
        background-color: #fdfdfd;
        }
        .access-card {
        background-color: var(--card);
        border-radius: var(--radius);
        padding: 22px;
        box-shadow: 0 6px 18px rgba(6, 30, 78, 0.06);
        cursor: pointer;
        transition: transform .28s cubic-bezier(.2, .9, .2, 1), box-shadow .28s;
        height:100%;
        display:flex;
        flex-direction:column;
        gap:10px;
        }
        .access-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 14px 40px rgba(6, 30, 78, 0.12);
        }
        .access-card h5 {
        color: var(--navy);
        margin: 0;
        font-weight: 600;
        font-size: 1.1rem;
        }
        .access-card p {
        font-size: 0.95rem;
        color: var(--muted);
        margin: 0;
        flex-grow: 1;
        }
        .link-text {
        color: var(--navy);
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        margin-top: 0.5rem;
        }

        /* ================= Health Topics ================= */
        section.health-topics {
            padding: 44px 0;
            background: linear-gradient(180deg, #eaf6ff 0%, #f8fcff 100%);
            border-radius: 16px;
            margin-top: 24px
        }

        .section-title {
            font-size: 1.6rem;
            color: var(--navy);
            text-align: center;
            margin: 6px 0 22px;
            font-weight: 700
        }

        .topics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            align-items: stretch;
        }

        .topic-card {
            background: var(--card);
            padding: 22px;
            border-radius: var(--radius);
            text-align: center;
            cursor: pointer;
            box-shadow: 0 6px 18px rgba(6, 30, 78, 0.06);
            transition: transform .28s cubic-bezier(.2, .9, .2, 1), box-shadow .28s;
            display: flex;
            flex-direction: column;
            gap: 12px;
            justify-content: flex-start;
        }

        .topic-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 14px 40px rgba(6, 30, 78, 0.12)
        }

        .topic-icon {
            font-size: 44px
        }

        .topic-card h3 {
            margin: 0;
            font-size: 1.05rem;
            color: var(--navy)
        }

        .topic-card p {
            margin: 0;
            color: var(--muted);
            font-size: .95rem
        }

        /* animated expanded view under the grid */
        .topic-info-wrap {
            margin-top: 26px;
            overflow: hidden
        }

        .topic-info {
            background: var(--card);
            border-radius: var(--radius);
            padding: 18px;
            box-shadow: 0 8px 30px rgba(6, 30, 78, 0.08);
            opacity: 0;
            transform: translateY(12px);
            transition: opacity .45s ease, transform .45s ease, max-height .45s ease;
            max-height: 0;
        }

        .topic-info.show {
            opacity: 1;
            transform: none;
            max-height: 1600px
        }

        .topic-info .meta {
            display: flex;
            gap: 16px;
            align-items: center
        }

        .topic-info img {
            max-width: 100%;
            border-radius: 10px;
            margin: 14px 0
        }

        .topic-info iframe {
            width: 100%;
            height: 380px;
            border-radius: 8px;
            border: 0;
            margin-top: 10px
        }

        /* close btn */
        .close-topic {
            background: transparent;
            border: 0;
            color: var(--navy);
            font-weight: 700;
            cursor: pointer;
            border-radius: 8px;
            padding: 6px 10px
        }

        .close-topic:hover {
            background: #f1f7ff
        }

        /* ================= Dashboard (hidden by default) ================= */
        .dashboard-section {
            margin-top: 34px
        }

        .dashboard-selector {
            text-align: center;
            margin-bottom: 22px
        }

        .dashboard-btn {
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid #e6eefb;
            background: transparent;
            cursor: pointer;
            margin: 0 6px
        }

        .dashboard-btn.active {
            background: var(--navy);
            color: #fff;
            border-color: var(--navy)
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px
        }

        .card {
            background: var(--card);
            padding: 18px;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(6, 30, 78, 0.06)
        }

        .card h3 {
            margin: 0;
            color: var(--navy)
        }

        .card p {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: .95rem
        }

        table.app-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
            background: var(--card);
            border-radius: 10px;
            overflow: hidden
        }

        table.app-table th,
        table.app-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #eef3fb;
            text-align: left
        }

        table.app-table th {
            background: #f6fbff;
            color: var(--navy);
            font-weight: 700
        }

        .status-pill {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            color: #fff;
            font-weight: 700;
            font-size: .85rem
        }

        .status-confirmed {
            background: var(--accent-blue)
        }

        .status-pending {
            background: #f59e0b
        }

        .action-btn {
            padding: 7px 12px;
            border-radius: 8px;
            border: 0;
            cursor: pointer;
            font-weight: 600
        }

        /* Pharmacy */
        .pharmacy {
      background-color: #f5f5f5;
      border-radius: 12px;
      margin: 30px 0;
        }

        /* ================= Footer ================= */
        footer.site-footer {
            background: var(--navy);
            color: #fff;
            padding: 20px 0;
            margin-top: 36px;
            border-top-left-radius: 14px;
            border-top-right-radius: 14px
        }

        footer.site-footer a {
            color: #fff;
            text-decoration: none;
            margin: 0 10px
        }

        /* Responsive tweaks */
        @media(max-width:900px) {
            header.site-header h1 {
                font-size: 1.6rem
            }

            .topic-info iframe {
                height: 260px
            }
        }
    </style>
</head>

<body>

    <!-- ================= Header ================= -->

    <header class="top-nav">
    <div class="logo"><img src="includes/images/logo 22.jpg" alt="logos"> </div>
    <!-- <div class="logo-text" style="color: #fff; font-weight: 700; font-size: 1.02rem; padding-left: 4px; background: transparent; box-shadow: none;">E-Medicals UG</div> -->

    
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

        <section class="hero">
            <div class="overlay"></div>
            <div class="hero-content">
            <h1><strong>E-Medicals UG</strong></h1>
            <h2>Thrive Healthy</h2>
            <p>Good Health and wellbeing: Accessible, affordable healthcare across Uganda</p>

            <div class="hero-cta">
                <button class="btn btn-primary" onclick="openAuthModal('login')"><i class="fa fa-right-to-bracket"></i>
                    Login</button>
                <button class="btn btn-outline" onclick="openAuthModal('register')"><i class="fa fa-user-plus"></i>
                    Register</button>
            </div>
            <!-- <a href="login.html" class="hero-button">Login / Register</a> -->
            </div>
        </section>

        <!-- <header class="site-header">
            <div class="container">
                <h1>E-Medicals UG</h1>
                <p>Your Health, Your Way — accessible, affordable healthcare across Uganda.</p>

                <div class="hero-cta">
                    <button class="btn btn-primary" onclick="openAuthModal('login')"><i class="fa fa-right-to-bracket"></i>
                        Login</button>
                    <button class="btn btn-outline" onclick="openAuthModal('register')"><i class="fa fa-user-plus"></i>
                        Register</button>
                </div>
            </div>
        </header>  -->
        <!-- Disclaimer & Quick Access Cards -->
        <section class="quick-access py-5">
  <div class="container">
    <div class="alert alert-warning text-center fw-bold mb-4" style="background:#fff3cd;border:1px solid #ffc107;border-radius:8px;padding:12px">
       Disclaimer: All health information provided here is for guidance only and not a substitute for professional medical diagnosis or treatment.
    </div>

    <div style="display:flex;gap:20px;justify-content:center;align-items:stretch;flex-wrap:wrap">
      <!-- First Aid Card -->
      <div style="flex:1;min-width:260px;max-width:320px">
        <div class="access-card">
          <h5>First Aid Information</h5>
          <p>Step-by-step guides for handling burns, bleeding, choking, and more.</p>
          <a href="blogs.php" class="link-text" style="text-decoration:none;display:inline-block;cursor:pointer">View First Aid</a>
        </div>
      </div>

      <!-- Emergency Contacts Card -->
      <div style="flex:1;min-width:260px;max-width:320px">
        <div class="access-card">
          <h5>Emergency Contacts & USSD</h5>
          <p>Call 0775164389 or dial <strong>0****</strong> for quick access to help.</p>
          <a href="contact.php" class="link-text" style="text-decoration:none;display:inline-block;cursor:pointer">View Emergency Info</a>
        </div>
      </div>

      <!-- Preventive Care Card -->
      <div style="flex:1;min-width:260px;max-width:320px">
        <div class="access-card">
          <h5>Preventive Care</h5>
          <p>Learn how to prevent common illnesses through lifestyle and hygiene tips.</p>
          <a href="blogs.php" class="link-text" style="text-decoration:none;display:inline-block;cursor:pointer">Explore Preventive Guides</a>
        </div>
      </div>
    </div>
  </div>
</section>

    <main class="container" role="main">

        
        <section class="health-topics" aria-labelledby="health-topics-title">
            <h2 id="health-topics-title" class="section-title">Explore Health Topics</h2>

            <div class="topics-grid" id="topicsGrid">
                <div class="topic-card" role="button" tabindex="0" data-key="maternal" aria-pressed="false">
                    <div class="topic-icon">🤰</div>
                    <h3>Maternal Health</h3>
                    <p>Supporting mothers through pregnancy, childbirth and postpartum care.</p>
                </div>

                <div class="topic-card" role="button" tabindex="0" data-key="child" aria-pressed="false">
                    <div class="topic-icon">👶</div>
                    <h3>Child Health</h3>
                    <p>Vaccinations, nutrition and growth monitoring for children.</p>
                </div>

                <div class="topic-card" role="button" tabindex="0" data-key="chronic" aria-pressed="false">
                    <div class="topic-icon">🩺</div>
                    <h3>Chronic Diseases</h3>
                    <p>Long-term condition management (diabetes, hypertension, asthma).</p>
                </div>

                <div class="topic-card" role="button" tabindex="0" data-key="mental" aria-pressed="false">
                    <div class="topic-icon">🧠</div>
                    <h3>Mental Wellness</h3>
                    <p>Practical tips for stress, anxiety and emotional wellbeing.</p>
                </div>
            </div>

            <!-- Animated expanded info area -->
            <div class="topic-info-wrap">
                <div id="topicInfo" class="topic-info" aria-live="polite" aria-hidden="true">
                    <!-- Close + content inserted by JS -->
                </div>
            </div>
        </section>

        <!-- Pharmacy Access + Newsletter (side by side on larger screens) -->
        <div class="pharmacy-newsletter-wrap" style="display:flex;flex-wrap:wrap;gap:20px;margin:30px 0;align-items:stretch">
            <section class="pharmacy py-5" style="background-color:#f5f5f5;text-align:center;border-radius:12px;padding:40px 20px;flex:1;min-width:260px">
                <h2 style="color:var(--navy);margin-bottom:12px;font-size:1.8rem">Access Our Pharmacy</h2>
                <p style="color:var(--muted);margin-bottom:24px;font-size:1.05rem">Order prescriptions, browse essential medicine, and get it delivered.</p>
                <a href="pharmacy.php" class="btn btn-primary" style="display:inline-block;padding:10px 24px;text-decoration:none;border-radius:8px">Visit Pharmacy</a>
            </section>

            <!-- Newsletter Signup -->
            <section class="newsletter-section py-5" style="background-color:#f5f5f5;text-align:center;border-radius:12px;padding:40px 20px;flex:1;min-width:260px">
                <h2 style="color:var(--navy);margin-bottom:12px;font-size:1.8rem">Subscribe to Our Newsletter</h2>
                <div class="newsletter-container">
                    <form id="newsletter-form" method="post">
                        <input type="email" name="email" class="form-control mb-2" placeholder="Enter your email address" required>
                        <button type="submit" class="btn btn-primary">
                            Subscribe Now
                        </button>
                    </form>
                    <small class="text-muted" style="display: block; margin-top: 12px;">Get latest health tips delivered to your inbox</small>
                </div>
            </section> 
        </div>

        <!--  Dashboard Section -->
        <section class="dashboard-section" id="dashboardSection" style="display:none;" aria-hidden="true">
            <div class="dashboard-selector">
                <button class="dashboard-btn active" id="doctorBtn">Doctor Dashboard</button>
                <button class="dashboard-btn" id="adminBtn">Admin Dashboard</button>
            </div>

            <!-- DOCTOR DASHBOARD -->
            <div id="doctorDashboard">
                <div class="dashboard-cards">
                    <div class="card">
                        <h3>12</h3>
                        <p>Today's Appointments</p>
                    </div>
                    <div class="card">
                        <h3>8</h3>
                        <p>Completed</p>
                    </div>
                    <div class="card">
                        <h3>3</h3>
                        <p>Pending</p>
                    </div>
                    <div class="card">
                        <h3>1</h3>
                        <p>Cancelled</p>
                    </div>
                </div>

                <div class="appointments-table">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                        <h2 style="margin:0;color:var(--navy)">Today's Appointments</h2>
                        <button class="action-btn btn-primary">New Appointment</button>
                    </div>

                    <table class="app-table" role="table" aria-label="Today's appointments">
                        <thead>
                            <tr>
                                <th>Patient Name</th>
                                <th>Time</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Nansambaa Jovia</td>
                                <td>09:00 AM</td>
                                <td>Consultation</td>
                                <td><span class="status-pill status-confirmed">Confirmed</span></td>
                                <td><button class="action-btn btn-primary">Start</button></td>
                            </tr>
                            <tr>
                                <td>Royall Ausi</td>
                                <td>10:15 AM</td>
                                <td>Consultation</td>
                                <td><span class="status-pill status-pending">Pending</span></td>
                                <td><button class="action-btn btn-primary">Confirm</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ADMIN DASHBOARD -->
            <div id="adminDashboard" style="display:none;">
                <div class="dashboard-cards">
                    <div class="card">
                        <h3>42</h3>
                        <p>Total Appointments</p>
                    </div>
                    <div class="card">
                        <h3>15</h3>
                        <p>Active Doctors</p>
                    </div>
                </div>

                <div class="appointments-table">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                        <h2 style="margin:0;color:var(--navy)">All Appointments</h2>
                        <div>
                            <button class="action-btn btn-secondary">Export</button>
                            <button class="action-btn btn-primary">Add New</button>
                        </div>
                    </div>

                    <table class="app-table" role="table" aria-label="All appointments">
                        <thead>
                            <tr>
                                <th>Patient Name</th>
                                <th>Doctor</th>
                                <th>Date & Time</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Nansambaa Jovia</td>
                                <td>Dr. Royall Ausi</td>
                                <td>Nov 15, 2023 - 09:00 AM</td>
                                <td>Consultation</td>
                                <td><span class="status-pill status-confirmed">Confirmed</span></td>
                                <td>
                                    <button class="action-btn btn-primary">View</button>
                                    <button class="action-btn btn-secondary">Edit</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </section>

    </main>

    <!-- ================= Footer ================= -->
    <footer class="site-footer">
        <div class="container" style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center">
            <div>
                <strong>Disclaimer: All health information provided here is for guidance only & <br> Not a substitute for professional medical diagnosis or treatment </strong> 
                <footer class="footer"> © 2026 E-Medicals UG. All rights reserved.</footer>
            </div>
            <div style="margin-top:8px">
                <a href="index.php">Home</a>
                <a href="about.php">About</a>
                <a href="blogs.php">Blogs</a>
                <a href="contact.php">Contact</a>
                <a href="terms.php">Terms</a>
                <!-- <a href="privacy.php">Privacy</a> -->
            </div>
        </div>
    </footer>

<!-- ================= Auth Modal ================= -->
<div id="authModal"
    style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);align-items:center;justify-content:center;padding:20px;z-index:1200">
    <div
        style="width:100%;max-width:920px;background:var(--card);border-radius:12px;overflow:hidden;display:flex;flex-wrap:wrap;box-shadow:0 20px 60px rgba(2,6,23,0.4)">

        <!-- Left Welcome Panel -->
        <div
            style="flex:1;padding:28px;background:linear-gradient(180deg,var(--navy),var(--navy-700));color:#fff;min-width:280px">
            <h2 style="margin-top:0">Welcome to E-Medicals UG</h2>
            <p style="opacity:.95">Login or register to access appointments, reports and more.</p>
            <p style="margin-top:20px;font-size:.95rem;opacity:.95">We’ll never share your data without consent.</p>
        </div>

        <!-- Right Panel (Scrollable) -->
        <div style="flex:1;padding:22px;max-height:90vh;overflow-y:auto;min-width:300px;position:relative;">
            <button onclick="closeAuthModal()"
                style="position:absolute;top:12px;right:12px;border:0;background:transparent;font-size:20px;cursor:pointer;z-index:10">×</button>

            <!-- Login form -->
            <form id="loginForm" action="auth.php" method="POST">
                <input type="hidden" name="action" value="login">
                <h3 style="margin:6px 0 12px;color:var(--navy)">Login</h3>
                <label style="display:block;margin-bottom:8px">Email</label>
                <input name="email" type="email" required
                    style="width:100%;padding:10px;border-radius:8px;border:1px solid #e6eefb;margin-bottom:12px">
                <label style="display:block;margin-bottom:8px">Password</label>
                <input name="password" type="password" required
                    style="width:100%;padding:10px;border-radius:8px;border:1px solid #e6eefb;margin-bottom:14px">
                <div style="display:flex;gap:8px;align-items:center">
                    <button class="btn btn-primary" type="submit">Login</button>
                    <a href="#" onclick="switchAuth('register');return false;"
                        style="color:var(--navy);font-weight:600">Create account</a>
                </div>
            </form>

            <!-- Register form (hidden by default) -->
            <form id="registerForm" action="auth.php" method="POST" style="display:none;margin-top:6px">
                <input type="hidden" name="action" value="register">
                <h3 style="margin:6px 0 12px;color:var(--navy)">Register</h3>

                <label style="display:block;margin-bottom:8px">Full name</label>
                <input name="fullname" type="text" required
                    style="width:100%;padding:10px;border-radius:8px;border:1px solid #e6eefb;margin-bottom:10px">

                <label style="display:block;margin-bottom:8px">Email</label>
                <input name="email" type="email" required
                    style="width:100%;padding:10px;border-radius:8px;border:1px solid #e6eefb;margin-bottom:10px">

                <label style="display:block;margin-bottom:8px">Phone</label>
                <input name="phone" type="tel" required
                    style="width:100%;padding:10px;border-radius:8px;border:1px solid #e6eefb;margin-bottom:10px">

                <label style="display:block;margin-bottom:8px">Account type</label>
                <select id="userType" name="user_type" required
                    style="width:100%;padding:10px;border-radius:8px;border:1px solid #e6eefb;margin-bottom:10px">
                    <option value="">Select</option>
                    <option value="patient">Patient</option>
                    <option value="doctor">Doctor</option>
                </select>

                <!-- doctor-specific fields -->
                <div id="doctorFields" style="display:none;margin-bottom:10px">
                    <label style="display:block;margin-bottom:8px">Specialization</label>
                    <input name="specialization" type="text"
                        style="width:100%;padding:10px;border-radius:8px;border:1px solid #e6eefb;margin-bottom:10px">
                    <label style="display:block;margin-bottom:8px">License number</label>
                    <input name="license_number" type="text"
                        style="width:100%;padding:10px;border-radius:8px;border:1px solid #e6eefb;margin-bottom:10px">
                </div>

                <label style="display:block;margin-bottom:8px">Password</label>
                <input id="regPassword" name="password" type="password" required
                    style="width:100%;padding:10px;border-radius:8px;border:1px solid #e6eefb;margin-bottom:10px">
                <label style="display:block;margin-bottom:8px">Confirm password</label>
                <input id="confirmPassword" name="confirm_password" type="password" required
                    style="width:100%;padding:10px;border-radius:8px;border:1px solid #e6eefb;margin-bottom:12px">

                <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms" style="font-size:.95rem;color:#333">I agree to the <a
                    href="terms.php">Terms</a> & <a href="privacy.php">Privacy</a></label>
                </div>

                <div style="display:flex;gap:8px;align-items:center">
                    <button class="btn btn-primary" type="submit">Register</button>
                    <a href="#" onclick="switchAuth('login');return false;"
                        style="color:var(--navy);font-weight:600">Back to login</a>
                </div>
            </form>
        </div>
    </div>
</div>


    <script>
        /* ================= Topics data ================= */
        const topics = {
            maternal: {
                title: "Maternal Health",
                lead: "Pregnancy, childbirth and postpartum care — practical guidance for mothers.",
                image: "https://images.pexels.com/photos/7089037/pexels-photo-7089037.jpeg",
                body: `<p>Maternal health covers prenatal care, nutrition, safe delivery practices and postpartum recovery.
               Regular antenatal visits help detect complications early. Key points:</p>
               <ul>
                 <li>Attend routine antenatal care visits.</li>
                 <li>Eat balanced meals and take prescribed supplements (iron, folic acid).</li>
                 <li>Know danger signs (heavy bleeding, severe headache, reduced fetal movement).</li>
                 <li>Plan for safe delivery and postnatal follow-up.</li>
               </ul>`,
                // video: "https://www.youtube.com/embed/5WOBv_Qt0_w"
            },
            child: {
                title: "Child Health",
                lead: "Growth, immunisation and nutrition — everything for healthy kids.",
                image: "https://images.pexels.com/photos/4345667/pexels-photo-4345667.jpeg",
                body: `<p>Child health focuses on vaccinations, growth monitoring, nutrition and early development checks.
               Practical tips:</p>
               <ul>
                 <li>Complete the vaccination schedule.</li>
                 <li>Monitor growth (weight/height) regularly.</li>
                 <li>Provide age-appropriate nutritious meals.</li>
                 <li>Seek prompt care for fever, breathing difficulties or dehydration.</li>
               </ul>`,
                video: "https://www.youtube.com/embed/0l7qWoeRveI"
            },
            chronic: {
                title: "Chronic Diseases",
                lead: "Managing long-term conditions — diabetes, hypertension and heart health.",
                image: "https://images.pexels.com/photos/30425668/pexels-photo-30425668.jpeg",
                body: `<p>Chronic disease care includes lifestyle, medications and regular monitoring.
              Key actions:</p>
              <ul>
                <li>Adhere to prescribed medications.</li>
                <li>Adopt a heart-healthy diet and exercise regularly.</li>
                <li>Monitor blood pressure and blood sugar as advised.</li>
                <li>Keep follow-up appointments to prevent complications.</li>
              </ul>`,
                video: "https://www.youtube.com/embed/6oqrK9Qbn5g"
            },
            mental: {
                title: "Mental Wellness",
                lead: "Practical steps to improve mood, reduce anxiety and strengthen resilience.",
                image: "https://images.pexels.com/photos/5699455/pexels-photo-5699455.jpeg",
                body: `<p>Mental wellness is maintained through social connection, stress management and seeking help when needed.</p>
               <ul>
                 <li>Practice daily relaxation (deep-breathing, short mindfulness).</li>
                 <li>Maintain social contact and ask for help.</li>
                 <li>Limit alcohol and ensure good sleep.</li>
                 <li>See a counsellor or doctor if symptoms persist.</li>
               </ul>`,
                video: "https://www.youtube.com/embed/lr3yEYvMi5U"
            }
        };

        /* ================= UI: Topic open/close with animation ================= */
        const topicInfo = document.getElementById('topicInfo');
        const topicCards = document.querySelectorAll('.topic-card');

        function clearTopicInfo() {
            topicInfo.innerHTML = '';
            topicInfo.setAttribute('aria-hidden', 'true');
            topicInfo.classList.remove('show');
            // reset aria-pressed on cards
            topicCards.forEach(c => c.setAttribute('aria-pressed', 'false'));
        }

        function renderTopic(key) {
            const t = topics[key];
            if (!t) return;
            topicInfo.setAttribute('aria-hidden', 'false');

            // build content
            const html = `
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
            <div style="flex:1;min-width:220px">
                <h2 style="margin:0;color:var(--navy)">${t.title}</h2>
                <p style="margin:8px 0;color:var(--muted)}">${t.lead}</p>
            </div>
            <div style="flex:0 0 auto">
                <button class="close-topic" onclick="clearTopicInfo()">Close</button>
            </div>
        </div>
        <div style="margin-top:12px">${t.body}</div>
        <img src="${t.image}" alt="${t.title} illustration">
        <h3 style="color:var(--navy);margin-top:6px">Watch: ${t.title}</h3>
        <iframe src="${t.video}" style="border:0" allowfullscreen></iframe>
    `;
            topicInfo.innerHTML = html;

            // show with class to animate
            setTimeout(() => topicInfo.classList.add('show'), 10);

            // scroll into view smoothly
            setTimeout(() => {
                topicInfo.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 200);
        }

        /* click / keyboard listeners for cards */
        topicCards.forEach(card => {
            card.addEventListener('click', () => {
                const key = card.dataset.key;
                // if already open same -> close
                if (topicInfo.classList.contains('show') && topicInfo.querySelector('h2')?.textContent === topics[key].title) {
                    clearTopicInfo(); return;
                }
                // mark pressed
                topicCards.forEach(c => c.setAttribute('aria-pressed', 'false'));
                card.setAttribute('aria-pressed', 'true');
                renderTopic(key);
            });
            card.addEventListener('keydown', e => {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); card.click(); }
            });
        });

        /* close when clicking outside expanded area */
        document.addEventListener('click', (e) => {
            const wrap = document.querySelector('.topic-info-wrap');
            if (!wrap) return;
            // if info visible and click outside topic-info and outside a card -> close
            if (topicInfo.classList.contains('show')) {
                const isInInfo = topicInfo.contains(e.target);
                const isInCard = e.target.closest('.topic-card');
                if (!isInInfo && !isInCard) clearTopicInfo();
            }
        });

        /* ================= Dashboard toggle logic ================= */
        const doctorBtn = document.getElementById('doctorBtn');
        const adminBtn = document.getElementById('adminBtn');
        const doctorDashboard = document.getElementById('doctorDashboard');
        const adminDashboard = document.getElementById('adminDashboard');

        doctorBtn?.addEventListener('click', () => {
            doctorDashboard.style.display = 'block';
            adminDashboard.style.display = 'none';
            doctorBtn.classList.add('active');
            adminBtn.classList.remove('active');
        });
        adminBtn?.addEventListener('click', () => {
            adminDashboard.style.display = 'block';
            doctorDashboard.style.display = 'none';
            adminBtn.classList.add('active');
            doctorBtn.classList.remove('active');
        });

        /*  Auth modal functions  */
        function openAuthModal(mode = 'login') {
            document.getElementById('authModal').style.display = 'flex';
            if (mode === 'register') switchAuth('register'); else switchAuth('login');
        }
        function closeAuthModal() { document.getElementById('authModal').style.display = 'none'; }
        function switchAuth(mode) {
            document.getElementById('loginForm').style.display = (mode === 'login') ? 'block' : 'none';
            document.getElementById('registerForm').style.display = (mode === 'register') ? 'block' : 'none';
        }

        /* show doctor fields on select */
        const userType = document.getElementById('userType');
        const doctorFields = document.getElementById('doctorFields');
        if (userType) {
            userType.addEventListener('change', () => {
                doctorFields.style.display = (userType.value === 'doctor') ? 'block' : 'none';
            });
        }

        /* password validation */
        const regPassword = document.getElementById('regPassword');
        const confirmPassword = document.getElementById('confirmPassword');
        if (regPassword && confirmPassword) {
            function validatePassword() {
                if (regPassword.value !== confirmPassword.value) confirmPassword.setCustomValidity("Passwords do not match");
                else confirmPassword.setCustomValidity("");
            }
            regPassword.addEventListener('input', validatePassword);
            confirmPassword.addEventListener('input', validatePassword);
        }

        /* simple accessibility: close modal with Escape */
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                clearTopicInfo();
                closeAuthModal();
            }
        });
    </script>

</body>

</html>