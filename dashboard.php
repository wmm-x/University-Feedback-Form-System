<?php

if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    http_response_code(403);
    exit('Direct access is not allowed!');
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit();
}
if (!isset($_SESSION["role"]) || $_SESSION["role"] != "sys_admin") {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit();
}
include './inc/dbh.inc.php';
$email= $_SESSION["email"];
// counts
$role = 'lecture';
$stmt = $conn->prepare("SELECT COUNT(*) FROM user WHERE role= ?");
$stmt->bind_param('s', $role);
$stmt->execute();
$stmt->bind_result($totalLectuer);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) FROM feedback_forms");
$stmt->execute();
$stmt->bind_result($totalForms);
$stmt->fetch();
$stmt->close();

$status = "active";
$stmt = $conn->prepare("SELECT COUNT(*) FROM feedback_forms WHERE status=?");
$stmt->bind_param('s', $status);
$stmt->execute();
$stmt->bind_result($ActiveForms);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) FROM feedback_responses");
$stmt->execute();
$stmt->bind_result($totalresponses);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>University Feedback System</title>
    <link rel="icon" href="./src/img/logo.png" type="image/png">
    <link rel="stylesheet" href="./src/css/style.css" />
    <link rel="stylesheet" href="./src/css/dashboard.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

 
</head>

<body>

    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="logo">
                <img src="./src/img/logo.png" alt="UOV Logo" class="logo-img" />
                <div class="logo-text">
                    <div class="portal-title">Feedback Form Generator Portal</div>
                </div>
            </div>
            <ul class="nav-menu">
                <li id="nav-dashboard" class="active"><i class="fas fa-chart-pie"></i> Dashboard Overview</li>
                <li id="nav-add-lecturer"><i class="fas fa-user-plus"></i> Add New Lecturer</li>
                <li id="nav-add-department"><i class="fas fa-building"></i> Manage Department &amp; Faculty</li>
                <li id="nav-manage-lecturers"><i class="fas fa-users-cog"></i> Manage Lecturer Accounts</li>
                <li id="nav-settings"><i class="fas fa-cogs"></i> System Settings</li>
            </ul>
        </aside>

        <main class="main-content">
            <!-- Dashboard Overview -->
            <div id="section-dashboard">
                <header class="header">
                    <h1>Admin Dashboard</h1>
                    <div class="user-profile">
                        <a href="?logout=true" class="logout-btn">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </header>

                <section class="stats">
                    <div class="stat-card"><i class="fas fa-user-graduate icon"></i>
                        <h2><?= htmlspecialchars($totalLectuer) ?></h2>
                        <p>Total Lecturers</p>
                    </div>
                    <div class="stat-card"><i class="fas fa-file-alt icon"></i>
                        <h2><?= htmlspecialchars($totalForms) ?></h2>
                        <p>Forms Created</p>
                    </div>
                    <div class="stat-card"><i class="fas fa-check-circle icon"></i>
                        <h2><?= htmlspecialchars($ActiveForms) ?></h2>
                        <p>Active Forms</p>
                    </div>
                    <div class="stat-card"><i class="fas fa-comment-dots icon"></i>
                        <h2><?= htmlspecialchars($totalresponses) ?></h2>
                        <p>Total Responses</p>
                    </div>
                </section>

                <!-- Add Lecturer (dashboard) -->
                <section class="form-section">
                    <h2>Add New Lecturer (Quick)</h2>
                    <form id="add-lecturer-form-dash">
                        <div class="form-row">
                            <input type="text" name="lecturer_name" placeholder="Enter full name" required />
                            <input type="email" name="lecturer_email" placeholder="Enter email address" required />
                        </div>
                        <div class="form-row">
                            <select id="faculty-dash" required>
                                <option value="">Select Faculty</option>
                            </select>
                            <select id="department-dash" required>
                                <option value="">Select Department</option>
                            </select>
                            <div class="password-box">
                                <input type="text" id="password-field-dash" placeholder="Generate password" readonly />
                                <button type="button" id="generate-btn-dash">Generate</button>
                            </div>
                        </div>
                        <button type="submit" class="submit-btn">Add Lecturer</button>
                        <div id="add-lecturer-feedback-dash" class="status" style="margin-top:8px; display:none;"></div>
                    </form>
                </section>
            </div>

            <!-- Add Lecturer full section -->
            <div id="section-add-lecturer" style="display:none;">
                <div class="user-profile">
                    <a href="?logout=true" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
                <section class="form-section" style="margin-top: 3%;">
                    <h2>Add New Lecturer</h2>
                    <form id="add-lecturer-form-section">
                        <div class="form-row">
                            <input type="text" name="lecturer_name" placeholder="Enter full name" required />
                            <input type="email" name="lecturer_email" placeholder="Enter email address" required />
                        </div>
                        <div class="form-row">
                            <select id="faculty-section" required>
                                <option value="">Select Faculty</option>
                            </select>
                            <select id="department-section" required>
                                <option value="">Select Department</option>
                            </select>
                            <div class="password-box">
                                <input type="text" id="password-field-section" placeholder="Generate password" readonly />
                                <button type="button" id="generate-btn-section">Generate</button>
                            </div>
                        </div>
                        <button type="submit" class="submit-btn">Add Lecturer</button>
                        <div id="add-lecturer-feedback-section" class="status" style="margin-top:8px; display:none;">
                             <button id="close-feedback" aria-label="Close">×</button>
                        </div>
                    </form>
                </section>
            </div>

            <!-- Manage Lecturers -->
            <div id="section-manage-lecturers" style="display:none;">
                <div class="user-profile">
                    <a href="?logout=true" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
                <section class="lecturer-management" style="margin-top: 3%;">
                    <div class="section-header">
                        <h2>Lecturer Management</h2>
                        <div class="search-group">
                            <input type="text" id="lecturer-search" class="search-input" placeholder="Search lecturers by name, email, or department..." />
                        </div>
                    </div>
                    <table class="lecturer-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="lecturer-table-body">
                            <tr>
                                <td colspan="4">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </section>
            </div>

            <!-- Manage Department & Faculty (placeholder content) -->
            <div id="section-add-department" style="display:none;">
                <div class="user-profile">
                    <a href="?logout=true" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
                <div class="card" style="margin-top:1.5rem; position: relative;">
                    <h2>Manage Department &amp; Faculty</h2>

                    <!-- Toggle buttons -->
                    <div style="display:flex; gap:12px; margin-bottom:16px;padding-top:2%; ">
                        <button type="button" id="tab-add-faculty" class="tab-btn active" style="padding:8px 16px; border:none; background:#6c04cd; color:#fff; border-radius:8px; cursor:pointer;">
                            Add Faculty
                        </button>
                        <button type="button" id="tab-add-department" class="tab-btn" style="padding:8px 16px; border:none; background:#eee; color:#333; border-radius:8px; cursor:pointer;">
                            Add Department
                        </button>
                    </div>

                    <!-- Add Faculty panel -->
                    <div id="panel-add-faculty">
                        <form id="faculty-form">
                            <div class="form-row" style="gap:1rem; display:flex; flex-wrap:wrap;">
                                <div style="flex:1; min-width:250px;">
                                    <label>Faculty Name
                                        <input type="text" name="faculty_name" required placeholder="Faculty of Applied Sciences" />
                                    </label>
                                    <button type="submit" class="submit-btn" style="margin-left:1rem;">
                                        Add Faculty
                                    </button>
                                </div>

                            </div>
                        </form>
                        <div id="faculty-feedback" class="status" style="margin-top:12px; display:none;"></div>
                    </div>

                    <!-- Add Department panel -->
                    <div id="panel-add-department" style="display:none; margin-top:12px;">
                        <form id="department-form">
                            <div class="form-row" style="display:flex; gap:1rem; flex-wrap:wrap;">
                                <div style="flex:1; min-width:200px;">
                                    <label>Department Name
                                        <input type="text" name="department_name" required placeholder="Department of ICT" />
                                    </label>
                                </div>
                                <div style="flex:1; min-width:200px;">
                                    <label>Faculty
                                        <select name="faculty_id" id="dept-faculty-select" required>
                                            <option value="">Loading...</option>
                                        </select>
                                    </label>
                                </div>
                                <div style="align-self:flex-end;">
                                    <button type="submit" class="submit-btn" style="background:#6a00ff;color:#fff;border:none;padding:10px 20px;border-radius:8px;">
                                        Add Department
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div id="department-feedback" class="status" style="margin-top:12px; display:none;"></div>
                    </div>
                </div>
            </div>

            <!-- Settings stub -->
            <!-- Settings stub -->
            <div id="section-settings" style="display:none;">
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
                                <input type="text" class="form-input-alt" value="<?php echo htmlspecialchars($email)  ?>" readonly>
                            </div>
                            <div class="form-group-alt">
                                <label class="form-label-alt">Role</label>
                                <input type="text" class="form-input-alt" value="System Administrator" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </main>
    </div>

    <!-- Custom confirm deletion modal -->
    <div id="modal-delete-backdrop" class="confirm-backdrop" aria-hidden="true" style="display:none;">
        <div class="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="delete-title">
            <h2 id="delete-title">Confirm Deletion</h2>
            <p>Are you sure you want to delete this lecturer? This action cannot be undone.</p>
            <div class="confirm-actions">
                <button id="delete-yes" class="btn yes-delete">Yes, Delete</button>
                <button id="delete-cancel" class="btn ok-btn">Cancel</button>
            </div>
        </div>
    </div>

    <div id="modal-password-backdrop" class="confirm-backdrop" aria-hidden="true" style="display:none;">
        <div class="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="password-title">
            <h2 id="password-title">Password Reset</h2>
            <p id="password-message" style="display:flex; align-items:center; gap:10px; justify-content:center; font-size:1.1rem;">
                New password: <strong id="new-pw" style="margin-left:5px;"></strong>
                <button id="copy-password" class="btn copy-btn" title="Copy password" style="display:flex; align-items:center; gap:5px;">
                    <i class="fas fa-copy"></i> Copy
                </button>
            </p>

            <div class="confirm-actions">
                <button id="password-ok" class="btn ok-btn" style="flex:1;">OK</button>
            </div>
        </div>
    </div>

    
<div id="global-loading-backdrop" style="display:none;position:fixed;top:0;left:0;
     width:100%;height:100%;background:rgba(255,255,255,0.85);z-index:10000;
     align-items:center;justify-content:center;flex-direction:column;gap:8px;">
  <img src="./src/img/loding.gif" alt="Loading" style="width:80px;height:80px;" />
  <div class="loading-text" style="font-weight:600;font-size:16px;">Processing...</div>
</div>



    <footer class="footer">
        © 2025 Department of ICT, Faculty of Technological Studies - University of Vavuniya
    </footer>

    <!-- Script -->
  


    <script src="src/js/password.js"></script>
    <script src="src/js/dashboard.js"></script>

</body>

</html>