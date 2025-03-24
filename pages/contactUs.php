<?php
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);
    
    if (!empty($name) && !empty($email) && !empty($message)) {
        $data = [
            'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
            'email' => filter_var($email, FILTER_SANITIZE_EMAIL),
            'message' => htmlspecialchars($message, ENT_QUOTES, 'UTF-8'),
            'date' => date('Y-m-d H:i:s')
        ];
        
        $file = '../uploads/contactUs/userQueries.json';
        if (!file_exists(dirname($file))) {
            mkdir(dirname($file), 0777, true);
        }
        
        $existingData = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
        $existingData[] = $data;
        
        file_put_contents($file, json_encode($existingData, JSON_PRETTY_PRINT));
        
        echo "<script>alert('Your message has been received!');</script>";
    } else {
        echo "<script>alert('All fields are required.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/styles/style.css">
    <title>Contact Us</title>
    <style>
        .contact-section {
            padding: 60px 20px;
            max-width: 600px;
            margin: auto;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            font-weight: bold;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <?php include("../components/header.php") ?>

    <section class="contact-section">
        <h2>Contact Us</h2>
        <p>Have any questions? Feel free to reach out!</p>
        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="message">Message:</label>
                <textarea id="message" name="message" class="form-control" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">Send Message</button>
        </form>
    </section>

    <?php include("../components/footer.php") ?>
</body>
</html>
