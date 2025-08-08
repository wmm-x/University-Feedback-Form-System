<?php


if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    // optional: if someone tries to load guard.php directly
    http_response_code(403);
    exit('Direct access is not allowed.');
}

if (isset($_GET['logout'])) {
  session_unset();
  session_destroy();
  header('Location: index.php');
  exit();
}

if ($_SESSION["role"] != "lecture" || !isset($_SESSION['userid'])) {
  session_unset();
  session_destroy();
  header('Location: index.php');
  exit();
}

include './inc/dbh.inc.php';
$lecturer_id = $_SESSION['userid'];
$email= $_SESSION["email"];

//Total Issued Forms (all submissions for this hospital)
$stmt1 = $conn->prepare("SELECT COUNT(*) FROM feedback_forms WHERE Lecture_ID = ?");
$stmt1->bind_param('i', $lecturer_id);
$stmt1->execute();
$stmt1->bind_result($totalForms);
$stmt1->fetch();
$stmt1->close();

$stmt1 = $conn->prepare("SELECT COUNT(*) FROM feedback_forms WHERE  Lecture_ID = ? AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE());");
$stmt1->bind_param('i', $lecturer_id);
$stmt1->execute();
$stmt1->bind_result($totalThismonth);
$stmt1->fetch();
$stmt1->close();


$stmt2 = $conn->prepare("SELECT COUNT(*) FROM feedback_responses WHERE Lecture_ID= ?;");
$stmt2->bind_param('i', $lecturer_id);
$stmt2->execute();
$stmt2->bind_result($totalResponse);
$stmt2->fetch();
$stmt2->close();



?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>EduFeedback - Lecturer Dashboard</title>
  <link rel="icon" href="./src/img/logo.png" type="image/png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="./src/css/form-builder.css">
  <link rel="stylesheet" href="./src/css/lec-dash.css">
  <link rel="stylesheet" href="./src/css/form.css">
  <link rel="stylesheet" href="./src/css/dashboard.css" />
  <link rel="stylesheet" href="./src/css/style.css" />
  <style>
    .form-status {
      font-weight: 600;
      margin-right: 10px;
      padding: 4px 10px;
      border-radius: 8px;
      font-size: 13px;
    }

    .status-active {
      background: #eafbe7;
      color: #1db954;
      border: 1px solid #c3ecc6;
    }

    .status-inactive {
      background: #fff3f3;
      color: #ec4646;
      border: 1px solid #f7d1d1;
    }

    .status-toggle-btn {
      padding: 4px 14px;
      border: none;
      border-radius: 8px;
      font-size: 13px;
      background: #f2f2f2;
      color: #222;
      cursor: pointer;
      font-weight: 600;
      margin-left: 3px;
      transition: background 0.16s;
    }

    .status-toggle-btn:hover {
      background: #ece8ff;
      color: #6c04cd;
    }


    .form-status-badge {
      display: inline-block;
      background: #e7fbe8;
      color: #21b461;
      font-weight: 600;
      font-size: 15px;
      padding: 3px 18px 3px 18px;
      border-radius: 15px;
      box-shadow: 0 2px 8px rgba(60, 180, 90, 0.07);
      margin-right: 8px;
      margin-bottom: 6px;
      vertical-align: middle;
      border: none;
    }

    .status-toggle-btn {
      display: inline-block;
      background: #f4f4f7;
      color: #575757;
      font-size: 15px;
      font-weight: 500;
      padding: 3px 20px;
      border-radius: 12px;
      border: none;
      cursor: pointer;
      margin-bottom: 6px;
      transition: background 0.15s;
      box-shadow: 0 2px 7px rgba(50, 54, 70, 0.04);
    }

    .status-toggle-btn:hover {
      background: #eceaf0;
      color: #222;
    }

    .action-btn-group {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    .action-btn {
      border: none;
      border-radius: 14px;
      font-size: 15px;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      gap: 0.3em;
      padding: 8px 18px;
      transition: background 0.16s, color 0.16s;
      cursor: pointer;
      background: #f4f6fe;
      color: #6c4ee6;
      box-shadow: 0 2px 8px rgba(76, 63, 184, 0.08);
    }

    .action-btn.copy-link-btn {
      background: #f0f7ff;
      color: #6395c9;
    }

    .action-btn.copy-link-btn:hover {
      background: #e3effd;
      color: #225ca7;
    }

    .action-btn.view-btn {
      background: #f5f2ff;
      color: #7e74c9;
    }

    .action-btn.view-btn:hover {
      background: #ece8fd;
      color: #4938a1;
    }

    .action-btn.reset-btn {
      background: #fff6e4;
      color: #e6a11a;
    }

    .action-btn.reset-btn:hover {
      background: #ffeac4;
      color: #a67817;
    }

    .action-btn.delete-btn {
      background: #fff1f0;
      color: #ec4646;
    }

    .action-btn.delete-btn:hover {
      background: #ffeaea;
      color: #b83b3b;
    }
  </style>

  <script src="./src/js/lec-dashboar.js"></script>
  <script src="./src/js/password2.js"></script>

  <style>#formDescription {
  width: 200%;
  margin-top: 0.5rem;
  padding: 0.75rem 1rem;
  font-size: 1rem;
  font-family: inherit;
  color: #2c3e50;
  background-color: #f9f9f9;
  border: 1px solid #d1d9e0;
  border-radius: 8px;
  resize: vertical;
  box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.05);
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

#formDescription:focus {
  outline: none;
  border-color: #2c3e50;
  background-color: #fff;
  box-shadow: 0 0 0 3px rgba(44, 62, 80, 0.1);
}


    </style>



</head>

<body>
  <!-- Sidebar -->
  <script>
    const LECTURERID = <?php echo json_encode($lecturer_id); ?>;
    console.log(LECTURERID);
  </script>
  <aside class="sidebar">
    <div class="logo">
      <img src="./src/img/logo.png" alt="UOV Logo" class="logo-img" />
      <div class="logo-text">
        <div class="portal-title">Feedback Form Generator Portal</div>
      </div>
    </div>
    <ul class="nav-menu">
      <li onclick="showDashboard()" id="nav-dashboard"><i class="fas fa-home"></i> Dashboard</li>
      <li onclick="showFormCreator()" id="nav-create"><i class="fas fa-plus-circle"></i> Create Feedback Form</li>
      <li onclick="showFormList()" id="nav-forms"><i class="fas fa-file-alt"></i> View Forms</li>
      <li onclick="showsSetting()" id="nav-settings"><i class="fas fa-cogs"></i> System Settings</li>

    </ul>
  </aside>

  <script>
   
  </script>

  <!-- Dashboard Section -->
  <div id="main-content" class="main-content" style="display: block;">
    <header class="header">
      <h1>Welcome back, <?php echo htmlentities($_SESSION["username"])?></h1>
      <div class="user-profile">
        <a href="?logout=true" class="logout-btn">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>
    </header>

    <section class="dashboard-welcome">
      <div class="dashboard-widgets">
        <div class="widget">
          <div class="widget-title">
            <i class="fas fa-file-alt"></i>
            <span>Active Forms</span>
          </div>
          <h3><?php echo htmlspecialchars($totalForms); ?></h3>
        </div>

        <div class="widget">
          <div class="widget-title">
            <i class="fas fa-check-circle"></i>
            <span>Total Responses</span>
          </div>
          <h3><?php echo htmlspecialchars($totalResponse); ?></h3>
        </div>
        <div class="widget">
          <div class="widget-title">
            <i class="fas fa-clock"></i>
            <span>Forms This Month</span>
          </div>
          <h3><?php echo htmlspecialchars($totalThismonth); ?></h3>
        </div>
      </div>
    </section>

    <section class="quick-actions">
      <div class="card" onclick="showFormCreator()">
        <i class="fas fa-plus"></i>
        <h4>New Form</h4>
        <p>Create a feedback form</p>
      </div>
      <div class="card" onclick="showFormList()">
        <i class="fas fa-file-alt"></i>
        <h4>Recent Forms</h4>
        <p>Manage your forms</p>
      </div>
    </section>

    <section class="recent-forms">
      <h3>Recent Forms</h3>
      <section class="form-list">
        <div class="form-table-container">
          <table class="forms-table">
            <thead>
              <tr>
                <th>Form Title</th>
                <th>Form Type</th>
                <th>Status</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $sql = "SELECT * FROM feedback_forms WHERE Lecture_ID = ?";
              $stmt = $conn->prepare($sql);
              $stmt->bind_param("i", $lecturer_id);
              $stmt->execute();
              $result = $stmt->get_result();

              if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                  echo "<tr>";
                  echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['type']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                  echo "<td><button class='action-btn' onclick=\"viewForm('" . $row['id'] . "')\">View</button></td>";
                  echo "</tr>";
                }
              } else {
                echo "<tr><td colspan='5'>No forms found for you.</td></tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </section>
    </section>
  </div>

  <!-- Form Creation Section -->
  <div id="main-content2" class="main-content2" style="display: none;">
    <header class="header">
      <h1>Create Feedback Form</h1>
      <div class="user-profile">
        <a href="?logout=true" class="logout-btn">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>
    </header>

    <div class="form-metadata">
      <div class="metadata-field">
        <label for="formTitle">Form Title</label>
        <input type="text" id="formTitle" placeholder="Enter Feedback Form Title" />
        <textarea id="formDescription" placeholder="Enter form description" rows="3" cols="5"></textarea>

      </div>

      <div class="metadata-field">
        <label for="formType">Form Type</label>
        <select id="formType">
          <option value="">Select Form Type</option>
          <option value="Mid-Semester">Mid-Semester</option>
          <option value="End-Semester">End-Semester</option>
          <option value="Lab-Feedback">Lab Feedback</option>
          <option value="Project-Evaluation">Project Evaluation</option>
        </select>
      </div>
    </div>

    <div class="form-builder-container" id="formBuilder"></div>

    <button class="create-btn" onclick="addQuestion()">+ Add Question</button>
    <button class="create-btn" onclick="if (validateForm()) { createForm(); }">Create Form</button>
  </div>

  <!-- Form List Table Section -->
  <div id="main-content3" class="main-content3" style="display: none;">
    <header class="header">
      <h1>Your Feedback Forms</h1>
      <div class="user-profile">
        <a href="?logout=true" class="logout-btn">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>
    </header>
    <section class="form-list">
      <div class="form-table-container">
        <table class="forms-table">
          <thead>
            <tr>
              <th>Form Title</th>
              <th>Form Type</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $host     = $_SERVER['HTTP_HOST'];
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $sql = "SELECT * FROM feedback_forms WHERE Lecture_ID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $lecturer_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                echo "<td>" . htmlspecialchars($row['type']) . "</td>";
                $status = htmlspecialchars($row['status']);
                $toggleLabel = $status === "active" ? "Deactivate" : "Activate";
                $badgeColor = $status === "active" ? "form-status-badge" : "form-status-badge status-inactive";
                echo "<td>
        <span class='$badgeColor' id='status-text-{$row['id']}'>" . ucfirst($status) . "</span>
        <button class='status-toggle-btn' onclick='toggleFormStatus(" . (int)$row['id'] . ", this)'>$toggleLabel</button>
        </td>";


                echo "<td>
      <div class='action-btn-group'>
      <button
      class='action-btn copy-link-btn'
      data-form-link=\"{$protocol}://{$host}/project2/view-from.php?key=" . htmlspecialchars($row['form_key']) . "\"
      onclick='copyFormLink(this)'
        >
      <i class='fa fa-link'></i> Copy Link
        </button>
        <button class='action-btn view-btn' onclick=\"viewForm(" . (int)$row['id'] . ")\">
          <i class='fa fa-eye'></i> Response
        </button>
        <button class='action-btn reset-btn' onclick=\"resetForm(" . (int)$row['id'] . ")\">
       <i class='fa fa-rotate-left'></i> Reset
        </button>
        <button class='action-btn delete-btn' onclick=\"deleteForm(" . (int)$row['id'] . ")\">
      <i class='fa fa-trash'></i> Delete
        </button>
    
      </div>
    </td>";
              }
            } else {
              echo "<tr><td colspan='5'>No forms found for you.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </section>
  </div>
  <div id="main-content4" class="main-content3" style="display: none;">
    <div class="user-profile">
      <a href="?logout=true" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </div>

    <div class="page-header-alt">
      <h1 class="page-title-alt">System Settings</h1>
      <p class="page-subtitle-alt">Manage system configuration and security settings</p>
    </div>
    <div class="card">
      <!-- Change Password Card -->
      <div class="settings-card-alt">
        <div class="card-header-alt">
          <h2 class="card-title-alt">Change Password</h2>
          <p class="card-description-alt">Update your account password for enhanced security</p>
        </div>
        <div class="card-content-alt">
          <!-- Alert Messages -->
          <div id="success-alert" class="alert alert-success-alt alert-hidden-alt">
            Password successfully updated!
          </div>
          <div id="error-alert" class="alert alert-error-alt alert-hidden-alt">
            <span id="error-message">An error occurred. Please try again.</span>
          </div>

          <form id="password-form">
            <div class="form-group-alt">
              <label for="current-password" class="form-label-alt">Current Password</label>
              <div class="password-input-container-alt">
                <input type="password" id="current-password" name="current_password" class="form-input-alt" placeholder="Enter your current password" required>
                <button type="button" class="password-toggle-alt" onclick="togglePassword('current-password')">
                  <svg class="eye-open" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                  </svg>
                  <svg class="eye-closed" viewBox="0 0 20 20" fill="currentColor" style="display: none;">
                    <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
                    <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z" />
                  </svg>
                </button>
              </div>
            </div>

            <div class="form-group-alt">
              <label for="new-password" class="form-label-alt">New Password</label>
              <div class="password-input-container-alt">
                <input type="password" id="new-password" name="new_password" class="form-input-alt" placeholder="Enter your new password" required>
                <button type="button" class="password-toggle-alt" onclick="togglePassword('new-password')">
                  <svg class="eye-open" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                  </svg>
                  <svg class="eye-closed" viewBox="0 0 20 20" fill="currentColor" style="display: none;">
                    <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
                    <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z" />
                  </svg>
                </button>
              </div>

              <div class="password-strength-alt">
                <div class="strength-bar-alt">
                  <div class="strength-fill-alt" id="strength-fill"></div>
                </div>
                <div class="strength-text-alt" id="strength-text">Password strength will appear here</div>
              </div>

              <div class="password-requirements-alt">
                <div class="requirements-title-alt">Password Requirements:</div>
                <ul class="requirements-list-alt">
                  <li class="requirement-item-alt" id="req-length">
                    <div class="requirement-check-alt"></div>
                    At least 8 characters long
                  </li>
                  <li class="requirement-item-alt" id="req-uppercase">
                    <div class="requirement-check-alt"></div>
                    Contains uppercase letter (A-Z)
                  </li>
                  <li class="requirement-item-alt" id="req-lowercase">
                    <div class="requirement-check-alt"></div>
                    Contains lowercase letter (a-z)
                  </li>
                  <li class="requirement-item-alt" id="req-number">
                    <div class="requirement-check-alt"></div>
                    Contains number (0-9)
                  </li>
                  <li class="requirement-item-alt" id="req-special">
                    <div class="requirement-check-alt"></div>
                    Contains special character (!@#$%^&*)
                  </li>
                </ul>
              </div>
            </div>

            <div class="form-group-alt">
              <label for="confirm-password" class="form-label-alt">Confirm New Password</label>
              <div class="password-input-container-alt">
                <input type="password" id="confirm-password" name="confirm_password" class="form-input-alt" placeholder="Re-enter your new password" required>
                <button type="button" class="password-toggle-alt" onclick="togglePassword('confirm-password')">
                  <svg class="eye-open" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                  </svg>
                  <svg class="eye-closed" viewBox="0 0 20 20" fill="currentColor" style="display: none;">
                    <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
                    <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z" />
                  </svg>
                </button>
              </div>
            </div>

            <div class="form-group-alt" style="display: flex; gap: 1rem; justify-content: flex-end;">
              <button type="button" class="btn btn-secondary-alt" onclick="resetForm()">Cancel</button>
              <button type="submit" class="btn btn-primary-alt">Update Password</button>
            </div>

            <!-- Alert Messages -->
            <div id="success-alert" class="alert alert-success-alt alert-hidden-alt">Password successfully updated!</div>
            <div id="error-alert" class="alert alert-error-alt alert-hidden-alt"><span id="error-message"></span></div>
          </form>
        </div>
      </div>

      <!-- Additional Settings Card -->
      <div class="settings-card-alt">
        <div class="card-header-alt">
          <h2 class="card-title-alt">Account Information</h2>
          <p class="card-description-alt">View and manage your account details</p>
        </div>
        <div class="card-content-alt">
          <div class="form-group-alt">
            <label class="form-label-alt">Username</label>
            <input type="text" class="form-input-alt" value="<?php echo htmlspecialchars($email) ?>" readonly>
          </div>
          <div class="form-group-alt">
            <label class="form-label-alt">Role</label>
            <input type="text" class="form-input-alt" value="Lecturer" readonly>
          </div>
        </div>
      </div>
    </div>


  </div>

  <footer class="footer">
    © 2025 Department of ICT, Faculty of Technological Studies - University of Vavuniya
  </footer>
  <!-- Success Popup -->




  <!-- Success Popup -->
  <div id="successPopup" style="display:none;" class="popup-overlay">
    <div class="popup-content">
      <span class="close-btn" onclick="closePopup()">×</span>
      <img src="./src/img/success.png" alt="Logo" class="popup-logo">
      <h2>Form Created Successfully!</h2>
      <p id="popupMessage">Your form link is ready.</p>
      <div class="popup-link">
        <input type="text" id="formLinkInput" readonly>
        <button id="copyBtn" onclick="copyLink()">Copy Link</button>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div id="deleteConfirmModal" class="custom-modal-overlay" style="display:none;">
    <div class="custom-modal-box">
      <h2 class="modal-title">Confirm Deletion</h2>
      <p class="modal-message">
        Are you sure you want to delete<br>this form? This action cannot be undone.
      </p>
      <button class="modal-btn delete-main-btn" id="confirmDeleteBtn">
        Yes, Delete
      </button>
      <button class="modal-btn cancel-btn" onclick="closeDeleteModal()">
        Cancel
      </button>
    </div>
  </div>
  <!-- Reset Confirmation Modal -->
  <div id="resetConfirmModal" class="custom-modal-overlay" style="display:none;">
    <div class="custom-modal-box">
      <h2 class="modal-title">Confirm Reset</h2>
      <p class="modal-message">
        Are you sure you want to reset<br>this form?<br>
        This will remove all responses but keep the form.
      </p>
      <button class="modal-btn reset-main-btn" id="confirmResetBtn">
        Yes, Reset
      </button>
      <button class="modal-btn cancel-btn" onclick="closeResetModal()">
        Cancel
      </button>
    </div>
  </div>

  

  <div id="actionNotification" style="
    display: none;
     position: fixed;
      top: 20px;
      left: 50%;
      transform: translateX(-50%);
      padding: 0.5em 1em;
      border-radius: 5px;
      font-weight: 400;
      z-index: 1000;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
      opacity: 1;
      transition: opacity 0.5s ease-out;
      margin-top: 4%;
      background: rgba(174, 234, 159, 0.2);
      color: #349234ff;
      border: 1px solid #b7e4c7;">
    <span id="actionNotifIcon" style="font-size: 15px; margin-right: 10px;">✅</span>
    <span id="actionNotifText"></span>
  </div>




  <script>
    let deleteFormId = null;
    let resetFormId = null;

    // Open Delete Confirm Modal
    document.getElementById("confirmDeleteBtn").onclick = function() {
      if (
        typeof deleteFormId !== "number" ||
        isNaN(deleteFormId) ||
        deleteFormId <= 0
      ) {
        showNotification("Error: Invalid form ID");
        return;
      }
      const formIdToDelete = deleteFormId; // Save before clearing
      fetch("./inc/form-hadel.php", {
          method: "POST",
          body: JSON.stringify({
            formID: formIdToDelete,
            action: "delete",
            userID: LECTURERID,
          }),
        })
        .then((res) => res.json())
        .then((data) => {
          if (data.status === "success") {
            showNotification("Form deleted successfully.");
            removeFormRow(formIdToDelete); // Remove row from UI
          } else {
            showNotification("Delete failed: " + (data.message || "Unknown error"));
          }
          closeDeleteModal(); // Now safe to clear modal & ID
        })
        .catch(() => {
          showNotification("An error occurred during deletion.");
          closeDeleteModal();
        });
    };

    // Reset Confirmed (AJAX)
    document.getElementById("confirmResetBtn").onclick = function() {
      if (
        typeof resetFormId !== "number" ||
        isNaN(resetFormId) ||
        resetFormId <= 0
      ) {
        showNotification("Error: Invalid form ID");
        return;
      }
      const formIdToReset = resetFormId; // Save before clearing
      fetch("./inc/form-hadel.php", {
          method: "POST",
          body: JSON.stringify({
            formID: formIdToReset,
            action: "reset",
            userID: LECTURERID,
          }),
        })
        .then((res) => res.json())
        .then((data) => {
          if (data.status === "success") {
            showNotification("Form responses reset successfully.");
          } else {
            showNotification("Reset failed: " + (data.message || "Unknown error"));
          }
          closeResetModal();
        })
        .catch(() => {
          showNotification("An error occurred during reset.");
          closeResetModal();
        });
    };

    function togglePassword(inputId) {
      const passwordInput = document.getElementById(inputId);
      const toggleButton = passwordInput.parentElement.querySelector('.password-toggle-alt');
      const eyeOpen = toggleButton.querySelector('.eye-open');
      const eyeClosed = toggleButton.querySelector('.eye-closed');

      if (passwordInput.type === 'password') {
        // Show password
        passwordInput.type = 'text';
        eyeOpen.style.display = 'none';
        eyeClosed.style.display = 'block';
      } else {
        // Hide password
        passwordInput.type = 'password';
        eyeOpen.style.display = 'block';
        eyeClosed.style.display = 'none';
      }
    }
  </script>




</body>

</html>