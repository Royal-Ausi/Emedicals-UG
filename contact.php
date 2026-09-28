<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contact Us — E-Medicals UG</title>
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
    --soft: #e9f3fb;
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

  
  .hero {
    background: var(--navy);
    color: #fff;
    padding: 48px 18px 36px;
    text-align: center;
    position: relative;
    overflow: hidden;
  }
  .hero-inner { position: relative; max-width: 820px; margin: 0 auto; }
  .btn-primary { display: inline-block; background: var(--accent-blue); color: #fff; text-decoration: none; padding: 10px 18px; border-radius: 10px; font-weight: 600; margin-top: 16px; }
  .badge { display: inline-block; background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.25); border-radius: 50px; padding: 6px 18px; font-size: .78rem; letter-spacing: .1em; text-transform: uppercase; margin-bottom: 22px; }
  .hero h1 { font-family: 'DM Serif Display', serif; font-size: clamp(2rem, 5vw, 3rem); line-height: 1.15; margin-bottom: 16px; }
  .hero h1 em { font-style: italic; color: #7de8dc; }
  .hero p { font-size: 1rem; line-height: 1.75; color: rgba(255,255,255,.82); }


  .contact-section { max-width: 900px; margin: 0 auto; padding: 32px 16px 40px; }
  .section-label { font-size: .72rem; letter-spacing: .14em; text-transform: uppercase; color: var(--accent-blue); font-weight: 600; margin-bottom: 10px; }
  .section-title { font-family: 'Inter', sans-serif; font-size: 1.6rem; margin-bottom: 10px; }
  .section-intro { font-size: .98rem; color: #374151; line-height: 1.6; margin-bottom: 28px; max-width: 680px; }

  
  .contact-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; }
  .contact-card { background: #ffffff; border: 1px solid var(--line); border-radius: 12px; padding: 18px 14px; transition: transform .14s, box-shadow .14s; }
  .contact-card:hover { transform: translateY(-3px); box-shadow: 0 8px 18px rgba(0,0,0,.06); }
  .card-icon { width: 44px; height: 44px; background: var(--accent-blue); color: #fff; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; margin-bottom: 12px; }
  .card-label { font-size: .72rem; text-transform: uppercase; letter-spacing: .12em; color: var(--accent-blue); font-weight: 700; margin-bottom: 6px; }
  .card-value { font-size: .97rem; color: #1f2937; line-height: 1.6; font-weight: 500; }
  .card-value a { color: #1f2937; text-decoration: none; }
  .card-value a:hover { color: var(--accent-blue); text-decoration: underline; }

  
  .availability { margin-top: 24px; background: #fff9f0; border: 1px solid #fde6d9; border-radius: 12px; padding: 16px 18px; display: flex; align-items: flex-start; gap: 12px; }
  .avail-icon { font-size: 1.2rem; flex-shrink: 0; margin-top: 2px; }
  .availability p { font-size: .92rem; color: #334155; line-height: 1.6; }
  .availability p strong { font-weight: 700; }

  
  .mock-footer { background: #0a1f1c; color: rgba(255,255,255,.6); padding: 40px 32px; text-align: center; font-size: .85rem; line-height: 1.8; }
  .mock-footer strong { color: rgba(255,255,255,.9); font-size: 1rem; display: block; margin-bottom: 6px; }
  .footer-links { display: flex; justify-content: center; gap: 24px; margin-top: 16px; flex-wrap: wrap; }
  .footer-links a { color: rgba(255,255,255,.5); text-decoration: none; font-size: .8rem; }

  @media (max-width: 768px) { .mock-nav { display: none; } .contact-grid { grid-template-columns: 1fr; } }
</style>
</head>
<body>
  <?php include 'header.php'; ?>
  

<section class="hero">
  <div class="hero-inner">
    <span class="badge">We're here to help</span>
    <h1>Get in <em>Touch</em><br>with Our Team</h1>
    <p>Questions, feedback, or suggestions about E-Medicals UG? We'd love to hear from you reach out through any of the channels below.</p>
   
  </div>
</section>

<div class="contact-section">
 
  <h2 class="section-title">Contact Details</h2>
  <p class="section-intro">We are final year BBC students at Makerere University Business School. Your feedback helps us continuously improve the platform.</p>

  <div class="contact-grid">
    <div class="contact-card">
      <div class="card-icon">✉️</div>
      <p class="card-label">General Email</p>
      <p class="card-value"><a href="mailto:info@emedicalsUG.com">info@emedicalsUG.com</a></p>
    </div>
    <div class="contact-card">
      <div class="card-icon">📧</div>
      <p class="card-label">Project Lead</p>
      <p class="card-value"><a href="mailto:royalausi@emedicalsUG.com">royalkim256@gmail.com</a></p>
    </div>
    <div class="contact-card">
      <div class="card-icon"></div>
      <p class="card-label">Phone / WhatsApp</p>
      <p class="card-value"><a href="tel:+256775164389">+256 775 164 389</a></p>
    </div>
    <div class="contact-card">
      <div class="card-icon"></div>
      <p class="card-label">Our Office</p>
      <p class="card-value">MUBS Nakawa Campus<br>Kampala, Uganda</p>
    </div>
    <div class="contact-card">
      <div class="card-icon"></div>
      <p class="card-label">Patner Pharmacies & Medical Centers</p>
      <p class="card-value">Partner pharmacies & Medical Centers will be unveiled soon! For Better Recommendations <br>Prescribed medicines can be Bought 
      online from our E-pharmacy<br></p>
    </div>
  </div>

  <div class="availability">
    <div class="avail-icon"></div>
    <p><strong>Response times:</strong> We typically respond to emails within <strong>24–48 hours</strong> on weekdays. For urgent matters, WhatsApp is the fastest way to reach us. We appreciate your patience and support.</p>
  </div>
</div>

 <?php include 'footer.php'; ?>


</body>
</html>