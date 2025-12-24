<?php
session_start();
require_once 'config.php';

$conn = getConnection();

// Handle login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($email) && !empty($password)) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                header('Location: index.php');
                exit;
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "User not found";
        }
        $stmt->close();
    } else {
        $error = "Please fill all fields";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile - JERSEY WEAR</title>
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
    <?php if(isset($_SESSION['user_id'])): ?>
        <!-- User is logged in - show profile -->
        <div class="profile-card">
            <div class="profile-avatar">👤</div>
            <h2><?php echo htmlspecialchars($_SESSION['user_name']); ?></h2>
            <p class="email"><?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
            
            <div class="profile-actions">
                <button onclick="window.location.href='orders.php'">My Orders</button>
                <button onclick="window.location.href='profile.php?logout=true'">Logout</button>
            </div>
        </div>
    <?php else: ?>
        <!-- User is not logged in - show login form -->
        <div class="profile-card">
            <div class="profile-avatar">🔒</div>
            <h2>Login</h2>
            
            <?php if($error): ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    <?php endif; ?>
</div>

<?php
closeConnection($conn);
?>
</body>
</html>