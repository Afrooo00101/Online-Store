<?php
session_start();
require_once 'config.php';

$conn = getConnection();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($full_name) || empty($email) || empty($password)) {
        $error = "Please fill all required fields";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        // Check if email already exists
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email already registered";
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_sql = "INSERT INTO users (full_name, email, phone, password) VALUES (?, ?, ?, ?)";
            $stmt2 = $conn->prepare($insert_sql);
            $stmt2->bind_param("ssss", $full_name, $email, $phone, $hashed_password);
            
            if ($stmt2->execute()) {
                $success = "Registration successful! You can now login.";
                // Optional: auto-login
                $_SESSION['user_id'] = $stmt2->insert_id;
                $_SESSION['user_name'] = $full_name;
                $_SESSION['user_email'] = $email;
                header('refresh:2;url=index.php');
            } else {
                $error = "Registration failed. Please try again.";
            }
            $stmt2->close();
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - JERSEY WEAR</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="test.css">
</head>
<body>

<!-- NAVBAR -->
<header class="navbar">
  <div class="menu" id="menuBtn">☰</div>
  <a href="index.php" class="logo">JERSEY<span>Wears</span></a>
  <div class="icons">
    <button id="themeToggle" class="theme-btn">🌙</button>
    <a href="profile.php">👤</a>
    <button id="cartBtn" class="cart-btn">🛒 
      <span class="cart-count" id="cartCount">
        <?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>
      </span>
    </button>
  </div>
</header>

<div class="profile">
    <div class="profile-card">
        <div class="profile-avatar">📝</div>
        <h2>Register</h2>
        
        <?php if($error): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
        
        <?php if($success): ?>
            <p style="color: green;"><?php echo $success; ?></p>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="tel" name="phone" placeholder="Phone (optional)">
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Register</button>
        </form>
        
        <p>Already have an account? <a href="profile.php">Login here</a></p>
    </div>
</div>

<?php
closeConnection($conn);
?>
</body>
</html>