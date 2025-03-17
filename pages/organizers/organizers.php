<?php
include("../../database/databaseConnection.php");

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch unique specialities
$speciality_query = "SELECT speciality FROM organizers";
$speciality_result = $conn->query($speciality_query);
$specialities = [];
if ($speciality_result) {
    while ($row = $speciality_result->fetch_assoc()) {
        $specialityArray = explode(',', $row['speciality']);
        foreach ($specialityArray as $spec) {
            $trimmedSpec = trim($spec);
            if (!empty($trimmedSpec) && !in_array($trimmedSpec, $specialities)) {
                $specialities[] = $trimmedSpec;
            }
        }
    }
    sort($specialities);
}

// Fetch all organizers
$query = "SELECT o.id, o.company_name, o.experience, o.website, o.instagram, o.speciality, 
                 u.username, u.location 
          FROM organizers o
          JOIN users u ON o.user_id = u.id";
$result = $conn->query($query);
if (!$result) {
    die("Query failed: " . $conn->error);
}
$organizers = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Organizers - EMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../../public/styles/style.css">
    <style>
        body {
            background-color: #f4f7fc;
            font-family: 'Poppins', sans-serif;
        }
        .container {
            max-width: 1200px;
        }
        .organizer-card {
            border-radius: 12px;
            box-shadow: 0px 5px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            background: #fff;
            overflow: hidden;
            padding: 20px;
        }
        .organizer-card:hover {
            transform: scale(1.05);
            box-shadow: 0px 10px 25px rgba(0, 0, 0, 0.2);
        }
        .card-title {
            font-weight: 600;
            color: #333;
        }
        .card p {
            font-size: 14px;
            color: #666;
        }
        .social-buttons a {
            margin: 5px;
            font-size: 16px;
        }
        .filter-section {
            margin-bottom: 30px;
        }
        @media (max-width: 768px) {
            .organizer-card {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <?php include("../../components/header.php"); ?>
    <div class="container mt-5">
        <h1 class="text-center text-primary fw-bold mb-4">Find Your Event Organizer</h1>
        <div class="filter-section text-center">
            <select id="specialityFilter" class="form-select w-auto d-inline-block">
                <option value="">All Specialities</option>
                <?php foreach ($specialities as $speciality) { ?>
                    <option value="<?= htmlspecialchars($speciality) ?>">
                        <?= htmlspecialchars($speciality) ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="row g-4" id="organizerList">
            <?php foreach ($organizers as $organizer) { ?>
                <div class="col-lg-4 col-md-6 col-sm-12 organizer-card-container" data-speciality="<?= htmlspecialchars($organizer['speciality']) ?>">
                    <div class="card organizer-card text-center p-4">
                        <div class="card-body">
                            <h5 class="card-title text-dark">
                                <?= !empty($organizer['company_name']) ? htmlspecialchars($organizer['company_name']) : 'Not Available' ?>
                            </h5>
                            <p><strong>Speciality:</strong> <?= !empty($organizer['speciality']) ? htmlspecialchars($organizer['speciality']) : 'Not Specified' ?></p>
                            <p><strong>Location:</strong> <?= !empty($organizer['location']) ? htmlspecialchars($organizer['location']) : 'Not Provided' ?></p>
                            <p><strong>Experience:</strong> <?= isset($organizer['experience']) ? htmlspecialchars($organizer['experience']) : '0' ?> years</p>
                            <div class="social-buttons d-flex justify-content-center">
                                <?php if (!empty($organizer['website'])) { ?>
                                    <a href="<?= htmlspecialchars($organizer['website']) ?>" target="_blank" class="btn btn-outline-info btn-sm"><i class="fa fa-globe"></i> Website</a>
                                <?php } ?>
                                <?php if (!empty($organizer['instagram'])) { ?>
                                    <a href="<?= htmlspecialchars($organizer['instagram']) ?>" target="_blank" class="btn btn-outline-danger btn-sm"><i class="fab fa-instagram"></i> Instagram</a>
                                <?php } ?>
                            </div>
                            <a href="organizerDetails.php?id=<?= $organizer['id'] ?>" class="btn btn-primary w-100 mt-3">View Profile</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php include("../../components/footer.php"); ?>
    <script>
        document.getElementById('specialityFilter').addEventListener('change', function() {
            let selectedSpeciality = this.value.toLowerCase();
            let organizers = document.querySelectorAll('.organizer-card-container');
            organizers.forEach(org => {
                let orgSpecialities = org.getAttribute('data-speciality').toLowerCase().split(',');
                orgSpecialities = orgSpecialities.map(spec => spec.trim());
                if (selectedSpeciality === "" || orgSpecialities.includes(selectedSpeciality)) {
                    org.style.display = "block";
                } else {
                    org.style.display = "none";
                }
            });
        });
    </script>
</body>
</html>
