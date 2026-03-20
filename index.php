<?php
session_start();

if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if (isset($_GET["error"])) {
    if ($_GET["error"] == "empty") {
        $error = "Please fill in all fields.";
    } elseif ($_GET["error"] == "notfound") {
        $error = "User not found.";
    } elseif ($_GET["error"] == "wrongpassword") {
        $error = "Wrong password.";
    } else {
        $error = "Login failed.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Professional Login</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <div class="background-shape shape-1"></div>
  <div class="background-shape shape-2"></div>

  <main class="page">
    <section class="login-section">
      <div class="brand-side">
        <div class="brand-badge">L</div>
        <h1>Welcome Back</h1>
        <p>
          Sign in to access your dashboard, manage your account, and continue
          your work with a clean and modern experience.
        </p>

        <div class="info-cards">
          <div class="info-card">
            <h3>Secure Access</h3>
            <p>Your data is protected with a professional login experience.</p>
          </div>
          <div class="info-card">
            <h3>Fast Workflow</h3>
            <p>Designed for speed, clarity, and smooth interaction.</p>
          </div>
          <div class="info-card">
            <h3>Modern UI</h3>
            <p>A polished layout with elegant spacing, shadows, and effects.</p>
          </div>
        </div>
      </div>

      <div class="form-side">
        <form class="login-form" id="loginForm" action="login.php" method="POST">
          <div class="form-header">
            <h2>Login</h2>
            <p>Please enter your details to continue</p>
          </div>

          <div class="input-group">
            <label for="email">Email Address</label>
            <input
              type="email"
              id="email"
              name="email"
              placeholder="Enter your email"
              required
            />
          </div>

          <div class="input-group">
            <label for="password">Password</label>
            <div class="password-box">
              <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter your password"
                required
              />
              <button type="button" id="togglePassword">Show</button>
            </div>
          </div>

          <div class="form-options">
            <label class="remember-me">
              <input type="checkbox" id="remember" name="remember" />
              <span>Remember me</span>
            </label>

            <a href="#" class="forgot-link">Forgot password?</a>
          </div>

          <button type="submit" class="login-btn">Sign In</button>

          <div class="divider">
            <span>or continue with</span>
          </div>

          <div class="social-buttons">
            <button type="button" class="social-btn">Google</button>
            <button type="button" class="social-btn">LinkedIn</button>
          </div>

          <p class="register-text">
            Don’t have an account?
            <a href="register.html">Create account</a>
          </p>

          <div id="message" class="message-box">
            <?php if (!empty($error)) echo htmlspecialchars($error); ?>
          </div>
        </form>
      </div>
    </section>
  </main>

  <script src="script.js"></script>
</body>
</html>