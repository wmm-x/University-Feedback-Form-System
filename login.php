<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>University of Vavuniya - Feedback Portal</title>
  <link rel="icon" href="./src/img/logo.png" type="image/png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    :root {
      --primary: #6c04cd;
      --primary-dark: #4a0589ff;
      --text-dark: #1f2937;
      --text-light: #64748b;
      --bg: #f8f9fc;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Inter', 'Segoe UI', sans-serif;
    }

    body {
      background: var(--bg);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      line-height: 1.6;
    }

    header {
      background: var(--primary);
      color: white;
      padding: 0.5rem 2rem;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
    }

    .header-content {
      max-width: 1200px;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .logo {
      height: 40px;
    }

    .header-title {
      font-size: 1.25rem;
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
      font-size: 1.1rem;
      cursor: pointer;
      padding: 0;
      outline: none;
    }
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
      max-width: 420px;
      margin: 0 ;
    }

    .login-card {
      background: #fff;
      border-radius: 12px;
      border: 1px solid #e0e0e0;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
      overflow: hidden;
    }

    .card-header {
      padding: 2rem 1.5rem 1.25rem;
      text-align: center;
      border-bottom: 1px solid #eee;
    }

    .center-logo {
      height: 60px;
      margin-bottom: 0.1rem;
    }

    .card-title {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--text-dark);
    }

    .card-subtitle {
      font-size: 0.95rem;
      color: var(--text-light);
      margin-top: 0.25rem;
    }

    .card-body {
      padding: 1.25rem;
    }

    .form-group {
      margin-bottom: 1.25rem;
    }

    .form-label {
      font-size: 0.9rem;
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
      padding: 0.75rem 1rem 0.75rem 2.75rem;
      border: 1.5px solid #d1d5db;
      border-radius: 8px;
      font-size: 1rem;
      background: #fff;
      transition: border 0.2s ease;
    }

    .form-input:focus {
      border-color: var(--primary);
      outline: none;
    }

    .form-input::placeholder {
      color: #9ca3af;
    }

    .login-button {
      width: 100%;
      padding: 0.9rem;
      background: var(--primary);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s ease, transform 0.1s ease;
    }

    .login-button:hover {
      background: var(--primary-dark);
    }

    .login-button:active {
      transform: scale(0.98);
    }

    footer {
      background: #1f1f2e;
      color: white;
      text-align: center;
      padding: 1.25rem;
      font-size: 0.875rem;
    }

    .footer-text {
      opacity: 0.85;
    }

    @media (max-width: 768px) {
      .header-content {
      flex-direction: row;
      align-items: center;
      text-align: left;
      justify-content: flex-start;
      gap: 1rem;
      }
      
    .logo {
      height: 40px;
      display: block;
    }

      .header-title {
        font-size: 1.1rem;
      }
    }

    .form-input:focus,
    .login-button:focus {
      outline: 2px solid var(--primary);
      outline-offset: 2px;
    }

    .login-button:disabled {
      opacity: 0.6;
      cursor: not-allowed;
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
                <button type="button" class="show-hide-btn" onclick="togglePasswordVisibility()" aria-label="Show or hide password">
                  <i class="fas fa-eye" id="togglePasswordIcon"></i>
                </button>
                <script>
                  function togglePasswordVisibility() {
                  const passwordInput = document.getElementById('password');
                  const icon = document.getElementById('togglePasswordIcon');
                  if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                  } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                  }
                  }
                </script>
                <input type="password" id="password" name="password" class="form-input" placeholder="Enter your password" required />
              </div>
            </div>
            <button type="submit" name="submit" class="login-button">Sign In</button>
          </form>
        </div>
      </div>
    </div>
  </main>

  <footer>
    <div class="footer-text">
      &copy; <?php echo date('Y'); ?>Department of ICT Faculty of Technological Studies University of Vavuniya
    </div>
  </footer>

  <?php
  if (isset($_SESSION["username"]) && $_SESSION["role"] == "sys_admin") {
    header('Location: dashboard.php');
    exit();
  } elseif (isset($_SESSION["username"]) && $_SESSION["role"] == "lecture") {
    header('Location: lectuer.php');
    exit();
  }
  ?>
</body>
</html>
