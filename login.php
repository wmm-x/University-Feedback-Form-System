<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>University of Vavuniya - Feedback Portal</title>
  <link rel="icon" href="./src/img/logo.png" type="image/png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      /* Refined color palette */
      --primary: #5A189A;
      --primary-dark: #3C096C;
      --text-dark: #1f2937;
      --text-light: #6b7280;
      --surface: #ffffff;
      --background: #f9fafb;
      --border-light: #e5e7eb;
      --error-bg: #FEE2E2;
      --error-text: #B91C1C;
      --error-border: #FCA5A5;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Inter', sans-serif;
      /* Subtle gradient background */
      background-image: linear-gradient(120deg, #fdfbfb 0%, #ebedee 100%);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      line-height: 1.6;
      color: var(--text-dark);
    }

    header {
      background: var(--surface);
      color: var(--text-dark);
      padding: 0.75rem 2rem;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
      border-bottom: 1px solid var(--border-light);
    }

    .header-content {
      max-width: 1200px;
      margin: 0 auto;
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .logo {
      height: 45px;
    }

    .header-title {
      font-size: 1.2rem;
      font-weight: 600;
      line-height: 1.4;
    }

    .show-hide-btn {
      position: absolute;
      right: 1rem;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: #9ca3af;
      font-size: 1rem;
      cursor: pointer;
      padding: 0;
      outline: none;
    }

    .show-hide-btn:hover,
    .show-hide-btn:focus {
      color: var(--primary);
    }

    main {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 1rem;
    }

    .login-container {
      width: 100%;
      max-width: 400px;
    }

    .login-card {
      background: var(--surface);
      border-radius: 16px;
      /* Softer, more layered shadow */
      box-shadow: 0 4px 6px rgba(0,0,0,0.02), 0 10px 20px rgba(0,0,0,0.07);
      overflow: hidden;
      border: 1px solid var(--border-light);
    }

    .card-header {
      padding: 2rem;
      text-align: center;
    }

    .center-logo {
      height: 60px;
      margin-bottom: 0.75rem;
    }

    .card-title {
      font-size: 1.75rem;
      font-weight: 700;
      color: var(--text-dark);
    }

    .card-subtitle {
      font-size: 1rem;
      color: var(--text-light);
      margin-top: 0.25rem;
    }

    .card-body {
      padding: 0.5rem 2rem 2rem;
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-label {
      font-size: 0.875rem;
      font-weight: 600;
      color: var(--text-dark);
      margin-bottom: 0.5rem;
      display: block;
    }

    .input-wrapper {
      position: relative;
    }

    .input-icon {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: #9ca3af;
    }

    .form-input {
      width: 100%;
      padding: 0.85rem 1rem 0.85rem 3rem;
      border: 1px solid var(--border-light);
      border-radius: 8px;
      font-size: 1rem;
      background: #fff;
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    /* Enhanced focus state */
    .form-input:focus {
      border-color: var(--primary);
      outline: none;
      box-shadow: 0 0 0 3px rgba(90, 24, 154, 0.15);
    }

    .form-input::placeholder {
      color: #9ca3af;
    }

    .login-button {
      width: 100%;
      padding: 0.9rem;
      background-image: linear-gradient(to right, var(--primary) 0%, var(--primary-dark) 100%);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
      letter-spacing: 0.5px;
    }

    .login-button:hover {
      background-size: 200% auto;
      box-shadow: 0 4px 15px 0 rgba(90, 24, 154, 0.3);
    }

    .login-button:active {
      transform: scale(0.98);
    }

    footer {
      background: #111827; /* Darker footer */
      color: #9ca3af;
      text-align: center;
      padding: 1.5rem;
      font-size: 0.875rem;
    }

    .footer-text {
      opacity: 0.9;
    }

    @media (max-width: 768px) {
      .header-content {
        justify-content: flex-start;
      }
      .logo {
        height: 40px;
      }
      .header-title {
        font-size: 1.1rem;
      }
    }

    .login-button:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }
    
    /* Modernized error box */
    .error-box {
        margin-top: 1rem;
        padding: 0.75rem 1rem;
        background: var(--error-bg);
        color: var(--error-text);
        border: 1px solid var(--error-border);
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.9rem;
        font-weight: 500;
    }

  </style>
</head>

<body>
  <header>
    <div class="header-content">
      <img src="./src/img/logo.png" alt="University Logo" class="logo" />
      <h1 class="header-title">
        University of Vavuniya<br>
        <span style="font-weight: 400; font-size: 0.85em;">Feedback Form Generator Portal</span>
      </h1>
    </div>
  </header>

  <main>
    <div class="login-container">
      <div class="login-card">
        <div class="card-header">
          <img src="./src/img/logo.png" alt="University Logo" class="center-logo" />
          <h2 class="card-title">Welcome Back</h2>
          <p class="card-subtitle">Sign in to access the portal</p>
        </div>

        <div class="card-body">
          <form action="./inc/login.inc.php" method="post">
            <div class="form-group">
              <label for="username" class="form-label">USERNAME</label>
              <div class="input-wrapper">
                <i class="fas fa-user input-icon"></i>
                <input type="text" id="username" name="uname" class="form-input" placeholder="Enter your username" required />
              </div>
            </div>

            <div class="form-group">
              <label for="password" class="form-label">PASSWORD</label>
              <div class="input-wrapper">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" id="password" name="password" class="form-input" placeholder="Enter your password" required />
                <button type="button" class="show-hide-btn" onclick="togglePasswordVisibility()" aria-label="Show or hide password">
                  <i class="fas fa-eye" id="togglePasswordIcon"></i>
                </button>
              </div>
            </div>
            
            <?php if (isset($_GET['error']) && $_GET['error'] === 'wronglogin'): ?>
              <div class="error-box" id="errorBox">
                <i class="fas fa-exclamation-circle"></i>
                <span>Invalid login. Please try again.</span>
              </div>
              <script>
                setTimeout(function() {
                  var errorBox = document.getElementById('errorBox');
                  if (errorBox) {
                    errorBox.style.transition = 'opacity 0.5s ease';
                    errorBox.style.opacity = '0';
                    setTimeout(() => errorBox.style.display = 'none', 500);
                  }
                }, 4000);
              </script>
            <?php endif; ?>

            <button type="submit" name="submit" class="login-button" style="margin-top: 1rem;">Sign In</button>
          </form>
        </div>
      </div>
    </div>
  </main>

  <footer>
    <div class="footer-text">
      &copy; <?php echo date('Y'); ?> Department of ICT, Faculty of Technological Studies, University of Vavuniya
    </div>
  </footer>

  <script>
    function togglePasswordVisibility() {
      const passwordInput = document.getElementById('password');
      const icon = document.getElementById('togglePasswordIcon');
      const isPassword = passwordInput.type === 'password';
      passwordInput.type = isPassword ? 'text' : 'password';
      icon.classList.toggle('fa-eye', !isPassword);
      icon.classList.toggle('fa-eye-slash', isPassword);
    }
  </script>

  <?php
  // This PHP block should be at the top of the file before any HTML output to ensure headers can be sent correctly.
  // For this example, we'll leave it here, but for production, it's best practice to handle session logic before the doctype.
  if (isset($_SESSION["username"]) && isset($_SESSION["role"])) {
    if ($_SESSION["role"] == "sys_admin") {
        header('Location: dashboard.php');
        exit();
    } elseif ($_SESSION["role"] == "lecture") {
        header('Location: lectuer.php'); // Note: 'lecturer.php' might be a typo for 'lecturer.php'
        exit();
    }
  }
  ?>
</body>

</html>