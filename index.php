<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EMS - Event Management System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="public/styles/style.css">
    <link rel="stylesheet" href="public/styles/header.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <script>
        new WOW().init();
    </script>
    <style>
        .hero-section {
            background: url('public/images/hero-bg2.jpg') no-repeat center center/cover;
            height: 190vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            position: relative;
            z-index: 1;
            padding: 50px 20px;
        }
        .hero-section::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: -1;
        }
        .container {
            padding-top: 60px;
            padding-bottom: 60px;
        }
        .card {
            padding: 20px;
            margin-bottom: 30px;
        }
    </style>
</head>

<body>
    <?php include("components/header.php") ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1 class="display-4 wow animate__animated animate__fadeInDown">Plan Your Perfect Event</h1>
            <p class="lead wow animate__animated animate__fadeInUp">Hire organizers, book venues, and vendors effortlessly!</p>
            <a href="./pages/register.php" class="btn btn-primary btn-lg wow animate__animated animate__zoomIn">Get Started</a>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-5 text-center">
        <div class="container">
            <h2 class="wow animate__animated animate__fadeInDown">Our Services</h2>
            <div class="row mt-4">
                <div class="col-md-4 wow animate__animated animate__fadeInLeft">
                    <div class="card p-4 shadow-lg">
                        <img src="public/images/organizers.png" class="card-img-top" alt="Organizers">
                        <h4 class="mt-3">Hire Organizers</h4>
                        <p>Find the best event planners to make your event successful.</p>
                        <a href="./pages/organizers/organizers.php" class="btn btn-primary btn-lg wow animate__animated animate__zoomIn">Hire now</a>
                    </div>
                </div>
                <div class="col-md-4 wow animate__animated animate__fadeInUp">
                    <div class="card p-4 shadow-lg">
                        <img src="public/images/venue.png" class="card-img-top" alt="Venue">
                        <h4 class="mt-3">Book Venues</h4>
                        <p>Choose the most stunning and perfect venue that best suits your event</p>
                        <a href="./pages/venue/venues.php" class="btn btn-primary btn-lg wow animate__animated animate__zoomIn">Find Venue</a>
                    </div>
                </div>
                <div class="col-md-4 wow animate__animated animate__fadeInRight">
                    <div class="card p-4 shadow-lg">
                        <img src="public/images/vendor.png" class="card-img-top" alt="Vendors">
                        <h4 class="mt-3">Book Vendors</h4>
                        <p>Get the best catering, decoration, and event services.</p>
                        <a href="./pages/vendors/vendors.php" class="btn btn-primary btn-lg wow animate__animated animate__zoomIn">Find Vendor</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="bg-light py-5">
        <div class="container text-center">
            <h2 class="wow animate__animated animate__fadeInDown">What Our Clients Say</h2>
            <div class="row mt-4">
                <div class="col-md-6 wow animate__animated animate__fadeInLeft">
                    <blockquote class="blockquote">
                        <p>"EMS made my wedding planning so much easier. Highly recommend!"</p>
                        <footer class="blockquote-footer">Magnus Rodrigues</footer>
                    </blockquote>
                </div>
                <div class="col-md-6 wow animate__animated animate__fadeInRight">
                    <blockquote class="blockquote">
                        <p>"Amazing service! Found the best venue and vendors for my event."</p>
                        <footer class="blockquote-footer">Yash Gaonkar</footer>
                    </blockquote>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="text-center text-light py-5" style="background: #007bff;">
        <div class="container">
            <h2 class="wow animate__animated animate__fadeInDown">Ready to Plan Your Event?</h2>
            <a href="./pages/register.php" class="btn btn-light btn-lg wow animate__animated animate__zoomIn">Get Started</a>
        </div>
    </section>

    <?php include("components/footer.php") ?>
</body>
</html>
