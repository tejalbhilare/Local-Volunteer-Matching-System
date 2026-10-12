
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Local Volunteer Matching System</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .site-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 18px 7%;
            background: #ffffff;
            border-bottom: 1px solid #dce6e1;
        }

        .brand {
            font-size: 1.2rem;
            font-weight: 700;
            color: #167d63;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
        }

        .hero {
            max-width: 1100px;
            margin: 55px auto;
            padding: 65px 24px;
            text-align: center;
        }

        .hero h1 {
            max-width: 780px;
            margin: 0 auto 20px;
            font-size: clamp(2rem, 5vw, 3.5rem);
        }

        .hero p {
            max-width: 650px;
            margin: 0 auto 28px;
            color: #64756f;
            font-size: 1.1rem;
        }

        .hero-actions {
            display: flex;
            justify-content: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .btn-outline {
            display: inline-block;
            padding: 10px 18px;
            border: 1px solid #167d63;
            border-radius: 8px;
            font-weight: 600;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 22px;
            max-width: 1100px;
            margin: 0 auto 60px;
            padding: 0 24px;
        }

        .feature-card {
            padding: 25px;
            background: #ffffff;
            border: 1px solid #dce6e1;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(25, 60, 45, 0.06);
        }

        .feature-card h2 {
            font-size: 1.2rem;
        }

        .site-footer {
            padding: 22px;
            text-align: center;
            color: #64756f;
            border-top: 1px solid #dce6e1;
        }

        @media (max-width: 700px) {
            .site-nav {
                align-items: flex-start;
                flex-direction: column;
            }

            .features {
                grid-template-columns: 1fr;
            }

            .hero {
                margin: 20px auto;
                padding: 45px 20px;
            }
        }
    </style>
</head>
<body>

    <header class="site-nav">
        <a class="brand" href="index.php">Volunteer Connect</a>

        <nav class="nav-links">
            <a href="login.php">Login</a>
            <a class="btn" href="register.php">Get Started</a>
        </nav>
    </header>

    <main>
        <section class="hero">
            <h1>Make a Difference in Your Community</h1>

            <p>
                Discover local volunteering opportunities, share your skills,
                and connect with organizations that are making a positive impact.
            </p>

            <div class="hero-actions">
                <a class="btn" href="register.php">Join as a Volunteer</a>
                <a class="btn-outline" href="login.php">Login to Your Account</a>
            </div>
        </section>

        <section class="features">
            <article class="feature-card">
                <h2>Discover Opportunities</h2>
                <p>Explore community activities and find opportunities that interest you.</p>
            </article>

            <article class="feature-card">
                <h2>Connect with Organizations</h2>
                <p>Help local organizations find volunteers for their community initiatives.</p>
            </article>

            <article class="feature-card">
                <h2>Track Your Applications</h2>
                <p>Apply for opportunities and check your application status.</p>
            </article>
        </section>
    </main>

    <footer class="site-footer">
        <p>&copy; <?php echo date('Y'); ?> Local Volunteer Matching System</p>
    </footer>

</body>
</html>
