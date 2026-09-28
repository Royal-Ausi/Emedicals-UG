</main>
<link rel="stylesheet" href="style.css"> 
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

    .container {
        width: 92%;
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    
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
</style>
<footer class="site-footer">
    <div class="container" style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;text-align:center;gap:20px">
       
        <div style="display:flex;gap:15px;flex-wrap:wrap;justify-content:center">
            <a href="index.php">Home</a>
            <a href="about.php">About</a>
            <a href="blogs.php">Blogs</a>
            <a href="contact.php">Contact</a>
             <a href="terms.php">Terms</a> 
            <!-- <a href="privacy.php">Privacy</a> -->
        </div>
        <div>
            <strong style="font-size:0.95rem">Disclaimer:</strong> All health information provided here is for guidance only and is not a substitute for professional medical diagnosis or treatment.
            <div style="margin-top:12px">
                &copy; <?= date('Y'); ?> E-Medicals UG. All rights reserved.
            </div>
        </div>
    </div>
</footer>
</body>
</html>
