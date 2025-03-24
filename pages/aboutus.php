<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - EMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../public/styles/style.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <script>
        new WOW().init();
    </script>
    <style>
        .about-hero {
            background: url('../public/images/about-bg.jpg') no-repeat center center/cover;
            height: 110vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            position: relative;
            z-index: 1;
        }
        .about-hero::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: -1;
        }
        .content-section {
            padding: 60px 20px;
        }
    </style>
</head>
<body>
    <?php include("../components/header.php") ?>

    <section class="about-hero">
        <div class="container">
            <h1 class="display-4 wow animate__animated animate__fadeInDown">About Us</h1>
            <p class="lead wow animate__animated animate__fadeInUp">Discover how EMS simplifies event planning.</p>
        </div>
    </section>

    <section class="content-section text-center">
        <div class="container">
            <h2 class="wow animate__animated animate__fadeInDown">Who We Are</h2>
            <p class="wow animate__animated animate__fadeInUp">EMS is a leading platform for effortless event planning, connecting organizers, venues, and vendors.</p>
        </div>
    </section>

    <section class="content-section bg-light">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-4 wow animate__animated animate__fadeInLeft">
                    <h3>Our Mission</h3>
                    <p>To provide a seamless event planning experience.</p>
                </div>
                <div class="col-md-4 wow animate__animated animate__fadeInUp">
                    <h3>Our Vision</h3>
                    <p>To be the most trusted event management platform.</p>
                </div>
                <div class="col-md-4 wow animate__animated animate__fadeInRight">
                    <h3>Our Values</h3>
                    <p>Innovation, reliability, and customer satisfaction.</p>
                </div>
            </div>
        </div>
    </section>

    <?php include("../components/footer.php") ?>
</body>
</html>
