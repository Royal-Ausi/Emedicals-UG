<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>About Us — E-Medicals UG</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
  :root {
    --navy: #004080;
    --navy-700: #003166;
    --accent-blue: #0055aa;
    --muted: #4a5568;
    --bg: #f5f8fc;
    --card: #ffffff;
    --line: #e6eef6;
    --soft: #eaf4ff;
    --ink: #0f1724;
  }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, Arial, sans-serif; color: var(--muted); background: var(--bg); }

  /* HEADER */
  .mock-header {
    background: #fff;
    border-bottom: 1px solid var(--line);
    padding: 0 32px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 1px 8px rgba(0,0,0,.06);
  }
  .logo { font-family: 'DM Serif Display', serif; font-size: 1.3rem; color: var(--accent-blue); display: flex; align-items: center; gap: 8px; }
  .logo-icon { background: var(--accent-blue); color: #fff; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: .9rem; }
  .mock-nav { display: flex; gap: 28px; align-items: center; }
  .mock-nav a { text-decoration: none; font-size: .88rem; color: var(--muted); font-weight: 500; }
  .mock-nav a.active { color: var(--accent-blue); font-weight: 600; border-bottom: 2px solid var(--accent-blue); padding-bottom: 2px; }
  .mock-nav .nav-btn { background: var(--accent-blue); color: #fff !important; padding: 8px 18px; border-radius: 8px; font-weight: 600 !important; }

  /* HERO */
  .about-hero {
    background: var(--navy);
    color: #fff;
    padding: 48px 18px 40px;
    position: relative;
    overflow: hidden;
  }
  .about-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    opacity: .04;
    background: linear-gradient(180deg, rgba(255,255,255,.02), transparent);
  }
  .about-hero-inner { max-width: 900px; margin: 0 auto; position: relative; text-align: center; }
  .badge { display: inline-block; background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.25); border-radius: 50px; padding: 6px 18px; font-size: .78rem; letter-spacing: .1em; text-transform: uppercase; margin-bottom: 22px; }
  .about-hero h1 { font-family: 'Inter', sans-serif; font-size: clamp(2rem, 4.5vw, 3rem); line-height: 1.15; margin-bottom: 16px; }
  .about-hero h1 em { font-style: italic; color: #7de8dc; }
  .about-hero p { font-size: 1.05rem; line-height: 1.75; color: rgba(255,255,255,.82); max-width: 620px; margin: 0 auto; }

  /* SECTIONS */
  .about-section { max-width: 920px; margin: 0 auto; padding: 40px 18px; }
  .about-section + .about-section { padding-top: 8px; }
  .section-label { font-size: .72rem; letter-spacing: .14em; text-transform: uppercase; color: var(--accent-blue); font-weight: 600; margin-bottom: 10px; }
  .section-title { font-family: 'Inter', sans-serif; font-size: clamp(1.4rem, 2.2vw, 1.9rem); color: #0f1724; margin-bottom: 14px; }
  .section-body { font-size: 1rem; line-height: 1.6; color: #334155; }
  .section-body + .section-body { margin-top: 14px; }

  /* FEATURES GRID */
  .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-top: 28px; }
  .feature-card { background: var(--soft); border-radius: 12px; padding: 18px 16px; border: 1px solid #d6eaff; transition: transform .16s, box-shadow .16s; }
  .feature-card:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(10,124,110,.12); }
  .feature-card .icon { font-size: 1.8rem; margin-bottom: 12px; }
  .feature-card h3 { font-size: .92rem; font-weight: 600; color: var(--accent-blue); margin-bottom: 6px; }
  .feature-card p { font-size: .85rem; color: #4b5563; line-height: 1.6; }

  /* DIVIDER */
  .divider { max-width: 860px; margin: 0 auto; border: none; border-top: 1px solid var(--line); }

  /* MVV CARDS */
  .mvv-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; margin-top: 24px; }
  .mvv-card { border-radius: 18px; padding: 34px 28px; position: relative; overflow: hidden; }
  .mvv-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-top: 20px; }
  .mvv-card { border-radius: 12px; padding: 24px 18px; position: relative; overflow: hidden; }
  .mvv-card.mission { background: var(--accent-blue); color: #fff; }
  .mvv-card.vision  { background: var(--navy-700); color: #fff; }
  .mvv-card.values  { background: #eaf4ff; border: 1px solid #d6eaff; color: var(--ink); }
  .mvv-card::before { content: ''; position: absolute; top: -30px; right: -30px; width: 110px; height: 110px; border-radius: 50%; background: rgba(255,255,255,.07); }
  .mvv-card .mvv-label { font-size: .7rem; letter-spacing: .13em; text-transform: uppercase; opacity: .7; margin-bottom: 10px; font-weight: 600; }
  .mvv-card h3 { font-family: 'DM Serif Display', serif; font-size: 1.3rem; margin-bottom: 14px; }
  .mvv-card p { font-size: .92rem; line-height: 1.7; opacity: .88; }
  .mvv-card.values .mvv-label { color: var(--accent-blue); opacity: 1; }
  .mvv-card.values h3 { color: var(--ink); }
  .values-list { list-style: none; margin-top: 14px; }
  .values-list li { display: flex; align-items: center; gap: 10px; font-size: .9rem; padding: 7px 0; border-bottom: 1px solid #d1f0ec; color: #374151; }
  .values-list li:last-child { border-bottom: none; }
  .values-list li::before { content: '✦'; color: var(--accent-blue); font-size: .65rem; flex-shrink: 0; }

  /* ── TEAM SECTION ── */
  .team-section {
    background: #f9fafb;
    border-top: 1px solid var(--line);
    border-bottom: 1px solid var(--line);
    padding: 28px 12px;
  }
  .team-section-inner {
    max-width: 860px;
    margin: 0 auto;
  }
  .team-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 16px;
    margin-top: 20px;
  }
  .team-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 12px;
    margin-top: 14px;
  }
  .team-card {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 12px;
    padding: 18px 14px 16px;
    text-align: center;
    transition: transform .16s, box-shadow .16s, border-color .16s;
  }
  .team-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 28px rgba(10,124,110,.10);
    border-color: #c5ebe7;
  }
  .team-avatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--accent-blue), var(--navy));
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 14px;
    font-size: 1.4rem;
    color: #fff;
    font-family: 'DM Serif Display', serif;
    font-weight: 700;
    letter-spacing: .02em;
  }
  .team-name {
    font-size: 1rem;
    font-weight: 600;
    color: var(--ink);
    margin-bottom: 4px;
  }
  .team-role {
    font-size: .78rem;
    color: var(--accent-blue);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .08em;
    margin-bottom: 8px;
  }
  .team-reg {
    font-size: .78rem;
    color: var(--muted);
    font-family: 'DM Sans', monospace;
  }

  /* CTA BANNER */
  .team-banner {
    background: var(--accent-blue);
    color: #fff;
    padding: 20px 18px;
    border-radius: 12px;
    max-width: 920px;
    margin: 0 auto 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
  }
  .team-banner p { font-size: 1rem; line-height: 1.7; opacity: .9; max-width: 520px; }
  .team-banner strong { display: block; font-size: 1.2rem; margin-bottom: 6px; font-weight: 600; }
  .team-banner a { flex-shrink: 0; background: #fff; color: var(--accent-blue); text-decoration: none; font-weight: 600; font-size: .9rem; padding: 11px 20px; border-radius: 50px; transition: opacity .2s; }
  .team-banner a:hover { opacity: .85; }

  /* FOOTER */
  .mock-footer { background: #0a1f1c; color: rgba(255,255,255,.6); padding: 40px 32px; text-align: center; font-size: .85rem; line-height: 1.8; }
  .mock-footer strong { color: rgba(255,255,255,.9); font-size: 1rem; display: block; margin-bottom: 6px; }
  .footer-links { display: flex; justify-content: center; gap: 24px; margin-top: 16px; flex-wrap: wrap; }
  .footer-links a { color: rgba(255,255,255,.5); text-decoration: none; font-size: .8rem; }

  @media (max-width: 768px) {
    .mock-nav { display: none; }
    .mvv-grid { grid-template-columns: 1fr; }
    .team-grid { grid-template-columns: repeat(2, 1fr); }
    .team-banner { padding: 22px 16px; }
  }

  
  @media (min-width: 1024px) {
    .team-grid { display: flex; gap: 12px; align-items: stretch; }
    .team-grid .team-card { flex: 1 1 0; min-width: 0; }
  }
</style>
</head>
<body>
   <?php include 'header.php'; ?>

<section class="about-hero">
  <div class="about-hero-inner">
    <span class="badge">Tele-Health Project.  BBC 3 2026</span>
    <h1>Healthcare Access,<br><em>Reimagined</em> for Uganda</h1>
    <p>E-Medicals UG is a tele-health web application built by final year Bachelor of Business Computing students at Makerere University Business School to bridge the gap between patients and medical professionals across Uganda.</p>
  </div>
</section>

<!-- STORY -->
<section class="about-section">
  
  <h2 class="section-title">Why We Built This</h2>
  <p class="section-body">As Business Computing students at MUBS, we saw a clear problem that millions of Ugandans face long travel distances to hospitals, 
    high medical costs, extended waiting times, and limited access to qualified doctors largely across the Country</p>
  <p class="section-body">We designed and developed E-Medicals UG as our academic research project which is a web based platform that connects patients and medical professionals online in a simple, affordable, and accessible way. 
    We believe technology can be used to solve real world problems and improve healthcare service delivery in Uganda today.</p>

  <div class="features-grid">
    <div class="feature-card">
      <h3>Book Appointments</h3>
      <p>Schedule medical consultations online without long queues or travel.</p>
    </div>
    <div class="feature-card">
      <h3>Remote Consultations</h3>
      <p>Consult approved doctors from the comfort of your home.</p>
    </div>
    <div class="feature-card">
      <h3>First Aid & Prevention Topics</h3>
      <p>Access reliable preventive health tips and first aid guides anytime.</p>
    </div>
    <div class="feature-card">
      <h3>E-Pharmacy</h3>
      <p>Purchase basic prescribed medications online and also use our diagnosis reports to get recommended medications.</p>
    </div>
    <div class="feature-card">
      <h3>Doctor Diagnosis Reports</h3>
      <p>Get detailed diagnosis reports from approved doctors to guide your treatment after our online consultations from the patient dashboards when you log in.</p>
    </div>
  </div>
</section>

<hr class="divider">

<!-- MISSION / VISION / VALUES -->
<section class="about-section">
  
  <h2 class="section-title">Mission, Vision & Values</h2>
  <div class="mvv-grid">
    <div class="mvv-card mission">
      <p class="mvv-label">Our Mission</p>
      <h3>Affordable Healthcare for All</h3>
      <p>To provide affordable, accessible, and technology-driven healthcare solutions that connect patients and medical professionals across Uganda.</p>
    </div>
    <div class="mvv-card vision">
      <p class="mvv-label">Our Vision</p>
      <h3>A Trusted Digital Platform</h3>
      <p>To become a trusted digital healthcare platform that improves health service delivery and promotes healthy living in Uganda.</p>
    </div>
    
    
    <div class="mvv-card values">
      <p class="mvv-label">Our Core Values</p>
      <h3>What We Stand For</h3>
      <ul class="values-list">
        <li>Innovation</li>
        <li>Accessibility</li>
        <li>Integrity</li>
        <li>User-Centered Service</li>
        <li>Continuous Improvement</li>
      </ul>
    </div>
  </div>
</section>

<!-- TEAM SECTION -->
<div class="team-section">
  <div class="team-section-inner">
   
    <h2 class="section-title">Meet Our Team</h2>
    <p class="section-body">E-Medicals UG was designed, developed, and tested by this team of final year Bachelor of Business Computing students at Makerere University Business School.</p>

    <div class="team-grid">
      <!-- Update names, roles & reg numbers to match your actual team -->
      <div class="team-card">
        <div class="team-avatar">RA</div>
        <p class="team-name">Royal Ausi</p>
        
      </div>
      <div class="team-card">
        <div class="team-avatar">NJ</div>
        <p class="team-name">Nansamba Jovia</p>
        
      </div>
      <div class="team-card">
        <div class="team-avatar">KIM</div>
        <p class="team-name">Kabaale Ian Mark</p>
       
      </div>
      <div class="team-card">
        <div class="team-avatar">AP</div>
        <p class="team-name">Abaasa Primos</p>
        
      </div>
      <div class="team-card">
        <div class="team-avatar">EEW</div>
        <p class="team-name">Eshwanyi Edgar Wanyama</p>
        
      </div>
    </div>
  </div>
</div>

<!-- CTA BANNER -->
<div style="padding: 64px 24px 0;">
  <div class="team-banner">
    <div>
      <strong>Got a question or feedback?</strong>
      <p>We'd love to hear from you. Whether you're a user, fellow student, or healthcare professional reach out and help us improve.</p>
    </div>
    <a href="contact.php">Contact Us </a>
  </div>
</div>

<!-- FOOTER -->
 <?php include 'footer.php'; ?>


</body>
</html>