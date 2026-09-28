<?php
require_once 'db.php';

// Utility functions
function truncateText($text, $length = 120) {
    return strlen($text) > $length ? substr($text, 0, $length) . "..." : $text;
}
function formatDate($date) {
    return date("M d, Y", strtotime($date));
}

// Data fetchers
function getSliderTips($conn) {
    $sql = "SELECT a.*, c.name AS category_name, 
                   au.name AS author_name, au.credentials 
            FROM articles a
            LEFT JOIN categories c ON a.category_id = c.category_id
            LEFT JOIN authors au ON a.author_id = au.author_id
            WHERE a.is_slider_tip = 1 AND a.status = 'published'
            ORDER BY a.published_at DESC LIMIT 10";
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getArticles($conn, $category_id = null, $limit = 10, $offset = 0) {
    if ($category_id) {
        $sql = "SELECT a.*, c.name AS category_name, 
                       au.name AS author_name, au.credentials 
                FROM articles a
                LEFT JOIN categories c ON a.category_id = c.category_id
                LEFT JOIN authors au ON a.author_id = au.author_id
                WHERE a.status = 'published' AND a.category_id = ?
                ORDER BY a.published_at DESC LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $category_id, $limit, $offset);
    } else {
        $sql = "SELECT a.*, c.name AS category_name, 
                       au.name AS author_name, au.credentials 
                FROM articles a
                LEFT JOIN categories c ON a.category_id = c.category_id
                LEFT JOIN authors au ON a.author_id = au.author_id
                WHERE a.status = 'published'
                ORDER BY a.published_at DESC LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $limit, $offset);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getCategories($conn) {
    $result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getTrendingArticles($conn, $limit = 3) {
    $sql = "SELECT a.*, c.name AS category_name, au.name AS author_name 
            FROM articles a
            LEFT JOIN categories c ON a.category_id = c.category_id
            LEFT JOIN authors au ON a.author_id = au.author_id
            WHERE a.status = 'published'
            ORDER BY a.views_count DESC
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// Handle category filter
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;

// Handle AJAX actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'trackView') {
        $article_id = (int)($_POST['article_id'] ?? 0);
        if ($article_id) {
            $stmt = $conn->prepare("INSERT INTO article_views (article_id) VALUES (?)");
            $stmt->bind_param("i", $article_id);
            $stmt->execute();
            $stmt2 = $conn->prepare("UPDATE articles SET views_count = views_count + 1 WHERE article_id = ?");
            $stmt2->bind_param("i", $article_id);
            $stmt2->execute();
        }
        echo json_encode(['success' => true]);
        exit;
    }
    if ($action === 'subscribe') {
        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $stmt = $conn->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
            $stmt->bind_param("s", $email);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Subscribed successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Already subscribed']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email']);
        }
        exit;
    }
}

// Fetch data
$sliderTips = getSliderTips($conn);
$categories = getCategories($conn);
$articles = getArticles($conn, $category_id, 10, 0);
$trending = getTrendingArticles($conn, 3);

// Debug: Check if articles are being fetched
$debug = isset($_GET['debug']) ? true : false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title> E-Medicals UG | Blogs</title>
     <link rel="icon" type="image/jpeg" href="includes/images/logo 22.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ================= Global Styling ================= */
        :root {
            --navy: #004080;
            --navy-700: #003166;
            --accent-blue: #0055aa;
            --muted: #4a5568;
            --bg: #f5f8fc;
            --card: #fff;
            --radius: 12px;
            --shadow: 0 2px 12px rgba(0, 51, 102, 0.08);
            --shadow-hover: 0 8px 20px rgba(0, 85, 170, 0.12);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', Arial, sans-serif;
            background: var(--bg);
            color: #1f2937;
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 16px;
        }

        /* ================= Hero Section ================= */
        .hero {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-700) 100%);
            color: white;
            padding: 50px 20px;
            text-align: center;
            margin-bottom: 30px;
        }

        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .hero p {
            font-size: 1.1rem;
            opacity: 0.95;
        }

        /* ================= Category Filter ================= */
        .category-filter-section {
            background: var(--card);
            padding: 20px;
            margin-bottom: 24px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .category-filter-section h6 {
            font-size: 0.85rem;
            color: var(--navy);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 14px;
        }

        .category-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        /* ================= Articles Section ================= */
        .articles-section {
            margin-bottom: 60px;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--accent-blue);
        }

        .section-header h2 {
            font-size: 1.8rem;
            color: var(--navy);
            font-weight: 700;
            margin: 0;
        }



        .articles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        /* ================= Article Card ================= */
        .article-card {
            background: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .article-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-hover);
        }

        .card-img-top {
            width: 100%;
            height: 220px;
            object-fit: cover;
            display: block;
        }

        .article-card .card-body {
            padding: 20px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .category-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 12px;
            background: var(--accent-blue);
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            width: fit-content;
        }

        .article-card h5 {
            color: var(--navy);
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 12px;
            line-height: 1.4;
            flex-grow: 1;
        }

        .excerpt {
            margin-bottom: 16px;
            flex-grow: 1;
        }

        .excerpt p {
            color: #555;
            font-size: 0.95rem;
        }

        .article-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .article-meta small {
            color: var(--muted);
            font-size: 0.85rem;
            font-weight: 600;
        }

        .read-more-link {
            color: var(--accent-blue);
            cursor: pointer;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .read-more-link:hover {
            color: var(--navy);
            transform: translateX(4px);
        }

        /* ================= Utility Classes ================= */
        .mb-4 { margin-bottom: 24px; }
        .mb-3 { margin-bottom: 16px; }
        .mb-2 { margin-bottom: 12px; }
        .pb-3 { padding-bottom: 16px; }
        .d-block { display: block; }
        .d-flex { display: flex; }
        .my-5 { margin: 40px 0; }

        /* ================= Lists ================= */
        .list-unstyled {
            list-style: none;
        }

        .list-unstyled li {
            transition: all 0.3s ease;
        }

        .list-unstyled li:hover {
            transform: translateX(4px);
        }

        .list-unstyled li a {
            display: block;
            padding: 10px 0;
            border-bottom: 1px solid #f0f4f8;
            color: var(--navy);
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .list-unstyled li a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 0;
            background: var(--accent-blue);
            border-radius: 2px;
            transition: height 0.3s ease;
        }

        .list-unstyled li a:hover::before {
            height: 18px;
        }

        .list-unstyled li a:hover {
            color: var(--accent-blue);
        }

        .list-unstyled li:last-child a {
            border-bottom: none;
        }

        /* ================= Alerts ================= */
        .alert {
            padding: 16px 20px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            border-left: 4px solid;
        }

        .alert-info {
            background: #f0f6ff;
            border-color: var(--accent-blue);
            color: #003d7a;
        }

        /* Sidebar cards & Newsletter */
        .sidebar-card {
            background: var(--card);
            border-radius: 12px;
            padding: 18px;
            box-shadow: 0 6px 18px rgba(6,30,78,0.06);
            margin-bottom: 16px;
        }

        .newsletter-container { display:flex; flex-direction:column; gap:10px; }
        .newsletter-container input[type="email"] { border-radius: 10px; border: 1px solid #e6eefb; padding: 10px 12px; }
        .newsletter-container button { border-radius: 8px; padding: 10px 12px; background: var(--accent-blue); color: #fff; border: none; }
        .newsletter-feedback { font-size: 0.9rem; color: #0f5132; display:block; }

        .full-content {
            display: none;
        }

        .small {
            font-size: 0.85rem;
        }

        /* ================= Responsive Design ================= */
        @media (max-width: 992px) {
            .row {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .sidebar {
                position: static;
                top: auto;
                flex-direction: row;
                flex-wrap: wrap;
                gap: 16px;
            }

            .sidebar-card {
                flex: 1;
                min-width: 220px;
            }

            .hero h1 {
                font-size: 1.8rem;
            }

            .articles-grid {
                grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
                gap: 16px;
            }

            .article-card .card-body {
                padding: 16px;
            }
        }

        @media (max-width: 768px) {
            .hero {
                padding: 35px 16px;
                margin-bottom: 20px;
            }

            .hero h1 {
                font-size: 1.5rem;
            }

            .hero p {
                font-size: 0.9rem;
            }

            .articles-grid {
                grid-template-columns: 1fr;
                gap: 14px;
            }

            .card-img-top {
                height: 160px;
            }

            .article-card h5 {
                font-size: 1rem;
            }

            .section-header h2 {
                font-size: 1.3rem;
            }

            .category-filter-section {
                padding: 16px;
                margin-bottom: 18px;
            }

            .category-filters {
                gap: 6px;
            }

            .btn-outline-secondary {
                padding: 8px 12px;
                font-size: 0.8rem;
            }

            .sidebar {
                gap: 16px;
                margin-top: 20px;
                flex-direction: column;
            }

            .sidebar-card {
                flex: none;
                padding: 16px;
            }

            .list-unstyled li a {
                padding: 8px 0;
                font-size: 0.85rem;
            }
        }

        @media (max-width: 576px) {
            .hero h1 {
                font-size: 1.2rem;
            }

            .hero p {
                font-size: 0.85rem;
            }

            .section-header h2 {
                font-size: 1rem;
            }

            .article-card h5 {
                font-size: 0.95rem;
            }

            .card-img-top {
                height: 140px;
            }

            .article-card .card-body {
                padding: 12px;
            }

            .category-badge {
                padding: 4px 10px;
                font-size: 10px;
            }

            .sidebar-card {
                padding: 12px;
            }

            .sidebar-card h5 {
                font-size: 0.95rem;
                margin-bottom: 12px;
            }

            .form-control {
                padding: 8px 10px;
                font-size: 14px;
            }

            .btn-primary {
                padding: 8px 10px;
            }

            .d-flex {
                flex-direction: column;
                gap: 6px;
            }

            .category-filters {
                gap: 4px;
            }

            .btn-outline-secondary {
                padding: 6px 10px;
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<!-- Hero Section -->
<div class="hero">
    <h1> Health & Medical Insights</h1>
    <p>Stay informed with our latest articles, health tips, and medical news to enhance your wellness journey</p>
</div> 

<!-- Blog Header Section with Statistics -->
<!-- <section class="blog-header-section">
    <div class="container">
        <div class="blog-stats">
            <div class="stat-item">
                <span class="stat-number"><?= count($articles) ?></span>
                <span class="stat-label">Articles</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?= count($categories) ?></span>
                <span class="stat-label">Categories</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?= count($trending) ?></span>
                <span class="stat-label">Trending</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">10K+</span>
                <span class="stat-label">Readers</span>
            </div>
        </div>
    </div>
</section>  -->

<div class="container my-5">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Category Filter Section -->
            <div class="category-filter-section">
                <h6>Filter by Category</h6>
                <div class="category-filters">
                    <a href="blogs.php" class="btn btn-outline-secondary btn-sm <?= !$category_id ? 'active' : '' ?>">All Articles</a>
                    <?php foreach($categories as $cat): ?>
                        <a href="blogs.php?category=<?= $cat['category_id'] ?>" class="btn btn-outline-secondary btn-sm <?= $category_id == $cat['category_id'] ? 'active' : '' ?>">
                            <?= htmlspecialchars($cat['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Articles Section -->
            <section class="articles-section">
                <div class="section-header">
                    <h2>Featured Articles</h2>
                </div>

                <?php if ($debug): ?>
                    <div class="alert alert-info">
                        <strong>Debug Info:</strong><br>
                        Total Articles Found: <?= count($articles) ?><br>
                        Total Categories: <?= count($categories) ?><br>
                        Total Trending: <?= count($trending) ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($articles)): ?>
                    <div class="alert alert-info"> No articles found. Check back soon!</div>
                <?php else: ?>
                    <div class="articles-grid">
                        <?php foreach($articles as $article): ?>
                            <div class="card article-card">
                                <?php if($article['featured_image']): ?>
                                    <img src="<?= htmlspecialchars($article['featured_image']) ?>" alt="<?= htmlspecialchars($article['title']) ?>" class="card-img-top">
                                <?php else: ?>
                                    <div class="card-img-top bg-secondary" style="height: 200px;"></div>
                                <?php endif; ?>
                                <div class="card-body">
                                    <div class="category-badge">
                                        <?= htmlspecialchars($article['category_name'] ?? 'Uncategorized') ?>
                                    </div>
                                    <h5 class="card-title"><?= htmlspecialchars($article['title']) ?></h5>
                                    
                                    <div class="excerpt mb-3">
                                        <p class="text-muted"><?= htmlspecialchars(truncateText($article['excerpt'], 150)) ?></p>
                                    </div>
                                    
                                    <div class="full-content mb-3">
                                        <p><?= $article['content'] ?></p>
                                    </div>

                                    <div class="article-meta">
                                        <div style="flex: 1;">
                                            <small class="text-muted">
                                                <?= htmlspecialchars($article['author_name'] ?? 'Unknown') ?>
                                            </small>
                                        </div>
                                        <div style="flex: 1;">
                                            <small class="text-muted">
                                                <?= formatDate($article['published_at'] ?? date('Y-m-d')) ?>
                                            </small>
                                        </div>
                                    </div>

                                    <div class="article-meta" style="border-top: none; padding-top: 0; margin-top: 0; margin-bottom: 0;">
                                        <small class="text-muted">
                                            <?= $article['read_time'] ?? 5 ?> min read
                                        </small>
                                        <small class="text-muted">
                                            <?= $article['views_count'] ?? 0 ?> views
                                        </small>
                                    </div>
                                    
                                    <a href="#" class="read-more-link toggle-read" data-id="<?= $article['article_id'] ?>">Read More </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4 sidebar">
            <!-- Newsletter Signup -->
            <div class="sidebar-card">
                <h5>Subscribe to Newsletter</h5>
                <div class="newsletter-container">
                    <form id="newsletter-form" method="post">
                                <input type="email" name="email" class="form-control mb-2" placeholder="Enter your email address" required>
                                <button type="submit" class="btn btn-primary">
                                    Subscribe Now
                                </button>
                                <div id="newsletter-feedback" class="newsletter-feedback" style="display:none;margin-top:8px"></div>
                            </form>
                            <small class="text-muted" style="display: block; margin-top: 12px;">Get latest health tips delivered to your inbox</small>
                </div>
            </div>

            <!-- Trending Articles -->
            <?php if (!empty($trending)): ?>
                <div class="sidebar-card">
                    <h5>Trending Now</h5>
                    <ul class="list-unstyled">
                        <?php foreach($trending as $t): ?>
                            <li>
                                <a href="blogs.php?category=<?= $t['category_id'] ?? '' ?>" class="text-decoration-none">
                                    <strong><?= htmlspecialchars(truncateText($t['title'], 75)) ?></strong>
                                </a>
                                <small class="text-muted d-block" style="margin-top: 6px;">
                                    <?= $t['views_count'] ?? 0 ?> views
                                </small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Categories -->
            <div class="sidebar-card">
                <h5>Categories</h5>
                <ul class="list-unstyled">
                    <?php foreach($categories as $cat): ?>
                        <li>
                            <a href="blogs.php?category=<?= $cat['category_id'] ?>" class="text-decoration-none">
                                 <?= htmlspecialchars($cat['name']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".toggle-read").forEach(link => {
        link.addEventListener("click", e => {
            e.preventDefault();
            const card = link.closest(".article-card");
            const excerpt = card.querySelector(".excerpt");
            const full = card.querySelector(".full-content");
            if (full.style.display === "none") {
                excerpt.style.display = "none";
                full.style.display = "block";
                link.textContent = "← Read Less";
                fetch("blogs.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "action=trackView&article_id=" + link.dataset.id
                });
            } else {
                excerpt.style.display = "block";
                full.style.display = "none";
                link.textContent = "Read More →";
            }
        });
    });

    const form = document.getElementById("newsletter-form");
    if (form) {
        form.addEventListener("submit", e => {
            e.preventDefault();
            const email = form.email.value;
            const feedback = document.getElementById('newsletter-feedback');
            if (feedback) { feedback.style.display = 'none'; feedback.textContent = ''; }
            fetch("blogs.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "action=subscribe&email=" + encodeURIComponent(email)
            })
            .then(res => res.text().then(text => ({ ok: res.ok, text })))
            .then(({ ok, text }) => {
                let data;
                try { data = JSON.parse(text || '{}'); } catch (err) { data = { success: !!ok, message: (text || (ok ? 'Subscribed' : 'Request failed')) }; }
                if (feedback) {
                    feedback.style.display = 'block';
                    feedback.textContent = data.message || (data.success ? 'Subscribed' : 'Failed');
                    feedback.style.color = data.success ? '#0f5132' : '#842029';
                } else {
                    alert(data.message || (data.success ? 'Subscribed' : 'Failed'));
                }
                if (data.success) form.reset();
            }).catch(err => {
                var f = document.getElementById('newsletter-feedback');
                if (f) { f.style.display='block'; f.textContent = 'Network error, please try again.'; f.style.color = '#842029'; } else { alert('Network error'); }
            });
        });
    }
});
</script>
<script>
    // Toggle read more
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('toggle-read')) {
            e.preventDefault();
            var id = e.target.getAttribute('data-id');
            var card = e.target.closest('.article-card');
            if (!card) return;
            var full = card.querySelector('.full-content');
            if (!full) return;
            if (full.style.display === 'block') {
                full.style.display = 'none';
                e.target.textContent = 'Read More →';
            } else {
                full.style.display = 'block';
                e.target.textContent = 'Show Less ←';
            }
            // smooth scroll to content
            full.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
</script>
</body>
</html>
