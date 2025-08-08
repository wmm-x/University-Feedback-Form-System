// === dashboard.js (updated) ===

// State guard to prevent duplicate concurrent add-lecturer submissions per form
const addingLecturerFlags = {
  dash: false,
  section: false
};

/* ---------- Ensure modals exist (unchanged) ---------- */
function ensureModalsExist() {
  if (!document.getElementById("modal-delete-backdrop")) {
    document.body.insertAdjacentHTML(
      "beforeend",
      `
        <div id="modal-delete-backdrop" class="confirm-backdrop" aria-hidden="true" style="display:none;">
          <div class="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="delete-title">
            <h2 id="delete-title">Confirm Deletion</h2>
            <p>Are you sure you want to delete this lecturer? This action cannot be undone.</p>
            <div class="confirm-actions">
              <button id="delete-yes" class="btn yes-delete">Yes, Delete</button>
              <button id="delete-cancel" class="btn cancel-btn">Cancel</button>
            </div>
          </div>
        </div>`
    );
  }
  if (!document.getElementById("modal-reset-backdrop")) {
    document.body.insertAdjacentHTML(
      "beforeend",
      `
        <div id="modal-reset-backdrop" class="confirm-backdrop" aria-hidden="true" style="display:none;">
          <div class="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="reset-title">
            <h2 id="reset-title">Confirm Reset</h2>
            <p>Are you sure you want to reset this lecturer’s password? The new password will be shown once reset.</p>
            <div class="confirm-actions">
              <button id="reset-yes" class="btn yes-reset">Yes, Reset</button>
              <button id="reset-cancel" class="btn cancel-btn">Cancel</button>
            </div>
          </div>
        </div>`
    );
  }
  if (!document.getElementById("modal-password-backdrop")) {
    document.body.insertAdjacentHTML(
      "beforeend",
      `
        <div id="modal-password-backdrop" class="confirm-backdrop" aria-hidden="true" style="display:none;">
          <div class="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="password-title">
            <h2 id="password-title">Password Reset</h2>
            <p id="password-message">New password: <strong id="new-pw"></strong></p>
            <div style="display:flex; gap:8px; align-items:center; margin-bottom:12px; justify-content:center; flex-wrap:wrap;">
              <button id="copy-password" class="btn yes-reset" style="flex:1; min-width:120px;">Copy</button>
              <div id="copy-feedback" style="opacity:0; transition:opacity .3s; font-size:0.875rem;">Copied!</div>
            </div>
            <div class="confirm-actions">
              <button id="password-ok" class="btn ok-btn">OK</button>
            </div>
          </div>
        </div>`
    );
  }
}
ensureModalsExist();

/* ---------- Utility Functions ---------- */
function escapeHtml(str) {
  if (!str) return "";
  return str
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#39;");
}

function hideModal(id) {
  const backdrop = document.getElementById(id);
  if (!backdrop) return;
  backdrop.style.display = "none";
  backdrop.setAttribute("aria-hidden", "true");
}

function showFeedback(elem, message, isSuccess = true) {
  if (!elem) return;
  elem.textContent = message;
  elem.className = "status " + (isSuccess ? "active" : "inactive");
  elem.style.display = "block";
  setTimeout(() => {
    elem.style.display = "none";
  }, 3000);
}

function showGlobalLoading(message = "Adding lecturer...") {
  const overlay = document.getElementById("global-loading-backdrop");
  if (!overlay) return;
  const textEl = overlay.querySelector(".loading-text");
  if (textEl) textEl.textContent = message;
  overlay.style.display = "flex";
}

function hideGlobalLoading() {
  const overlay = document.getElementById("global-loading-backdrop");
  if (!overlay) return;
  overlay.style.display = "none";
}

function showDeleteConfirm(onYes, onCancel) {
  const backdrop = document.getElementById("modal-delete-backdrop");
  const yesBtn = document.getElementById("delete-yes");
  const cancelBtn = document.getElementById("delete-cancel");
  if (!backdrop || !yesBtn || !cancelBtn) return;

  const freshYes = yesBtn.cloneNode(true);
  const freshCancel = cancelBtn.cloneNode(true);
  yesBtn.replaceWith(freshYes);
  cancelBtn.replaceWith(freshCancel);

  backdrop.style.display = "flex";
  backdrop.setAttribute("aria-hidden", "false");
  freshYes.focus();

  const escListener = (e) => {
    if (e.key === "Escape") {
      cleanup();
      onCancel?.();
    }
  };

  function cleanup() {
    hideModal("modal-delete-backdrop");
    document.removeEventListener("keydown", escListener);
    freshYes.removeEventListener("click", yesHandler);
    freshCancel.removeEventListener("click", cancelHandler);
  }

  const yesHandler = () => {
    cleanup();
    onYes?.();
  };
  const cancelHandler = () => {
    cleanup();
    onCancel?.();
  };

  freshYes.addEventListener("click", yesHandler);
  freshCancel.addEventListener("click", cancelHandler);
  document.addEventListener("keydown", escListener);
}

function showResetConfirm(onYes, onCancel) {
  const backdrop = document.getElementById("modal-reset-backdrop");
  const yesBtn = document.getElementById("reset-yes");
  const cancelBtn = document.getElementById("reset-cancel");
  if (!backdrop || !yesBtn || !cancelBtn) return;

  const freshYes = yesBtn.cloneNode(true);
  const freshCancel = cancelBtn.cloneNode(true);
  yesBtn.replaceWith(freshYes);
  cancelBtn.replaceWith(freshCancel);

  backdrop.style.display = "flex";
  backdrop.setAttribute("aria-hidden", "false");
  freshYes.focus();

  const escListener = (e) => {
    if (e.key === "Escape") {
      cleanup();
      onCancel?.();
    }
  };

  function cleanup() {
    hideModal("modal-reset-backdrop");
    document.removeEventListener("keydown", escListener);
    freshYes.removeEventListener("click", yesHandler);
    freshCancel.removeEventListener("click", cancelHandler);
  }

  const yesHandler = () => {
    cleanup();
    onYes?.();
  };
  const cancelHandler = () => {
    cleanup();
    onCancel?.();
  };

  freshYes.addEventListener("click", yesHandler);
  freshCancel.addEventListener("click", cancelHandler);
  document.addEventListener("keydown", escListener);
}

function showPasswordPopup(newPwd) {
  const backdrop = document.getElementById("modal-password-backdrop");
  if (!backdrop) return;
  const pwdEl = document.getElementById("new-pw");
  const ok = document.getElementById("password-ok");
  const copyBtn = document.getElementById("copy-password");
  if (!pwdEl || !ok || !copyBtn) return;

  pwdEl.textContent = newPwd;

  const freshOk = ok.cloneNode(true);
  ok.replaceWith(freshOk);
  const freshCopy = copyBtn.cloneNode(true);
  copyBtn.replaceWith(freshCopy);

  backdrop.style.display = "flex";
  backdrop.setAttribute("aria-hidden", "false");
  freshOk.focus();

  const escListener = (e) => {
    if (e.key === "Escape") cleanup();
  };

  function cleanup() {
    hideModal("modal-password-backdrop");
    document.removeEventListener("keydown", escListener);
    freshOk.removeEventListener("click", okHandler);
    freshCopy.removeEventListener("click", copyHandler);
  }

  const okHandler = () => {
    cleanup();
  };

  const copyHandler = () => {
    navigator.clipboard
      .writeText(newPwd)
      .then(() => {
        freshCopy.textContent = "Copied!";
        setTimeout(() => {
          freshCopy.innerHTML = '<i class="fas fa-copy"></i> Copy';
        }, 1500);
      })
      .catch(() => {
        alert("Failed to copy password.");
      });
  };

  freshOk.addEventListener("click", okHandler);
  freshCopy.addEventListener("click", copyHandler);
  document.addEventListener("keydown", escListener);
}

/* ---------- Navigation and View Switching ---------- */
const sectionMap = {
  "nav-dashboard": "section-dashboard",
  "nav-add-lecturer": "section-add-lecturer",
  "nav-add-department": "section-add-department",
  "nav-manage-lecturers": "section-manage-lecturers",
  "nav-settings": "section-settings",
};

function clearActiveNav() {
  Object.keys(sectionMap).forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.classList.remove("active");
  });
}

function hideAllSections() {
  Object.values(sectionMap).forEach((secId) => {
    const el = document.getElementById(secId);
    if (el) el.style.display = "none";
  });
}

function showSection(navId) {
  hideAllSections();
  clearActiveNav();
  const secId = sectionMap[navId];
  if (secId) {
    const sectionEl = document.getElementById(secId);
    if (sectionEl) sectionEl.style.display = "block";
  }
  const navEl = document.getElementById(navId);
  if (navEl) navEl.classList.add("active");
}

document
  .getElementById("nav-dashboard")
  ?.addEventListener("click", () => showSection("nav-dashboard"));
document
  .getElementById("nav-add-lecturer")
  ?.addEventListener("click", () => showSection("nav-add-lecturer"));
document
  .getElementById("nav-add-department")
  ?.addEventListener("click", () => showSection("nav-add-department"));
document
  .getElementById("nav-manage-lecturers")
  ?.addEventListener("click", () => showSection("nav-manage-lecturers"));
document
  .getElementById("nav-settings")
  ?.addEventListener("click", () => showSection("nav-settings"));
showSection("nav-dashboard");


const FACULTY_API = "./inc/get-departmet.php";
async function wireLecturerForm(suffix) {
  const facultySelect = document.getElementById(`faculty-${suffix}`);
  const departmentSelect = document.getElementById(`department-${suffix}`);
  const generateBtn = document.getElementById(`generate-btn-${suffix}`);
  const passwordField = document.getElementById(`password-field-${suffix}`);
  const form = document.getElementById(`add-lecturer-form-${suffix}`);
  const feedback = document.getElementById(`add-lecturer-feedback-${suffix}`);

  async function loadFaculties() {
    try {
      const res = await fetch(FACULTY_API);
      const data = await res.json();
      if (data.faculties && Array.isArray(data.faculties) && facultySelect) {
        facultySelect.innerHTML = '<option value="">Select Faculty</option>';
        data.faculties.forEach((f) => {
          const opt = document.createElement("option");
          opt.value = f.id;
          opt.textContent = f.name;
          facultySelect.appendChild(opt);
        });
      }
    } catch (err) {
      console.error("loadFaculties error", err);
      if (facultySelect)
        facultySelect.innerHTML = '<option value="">Error loading</option>';
    }
  }

  facultySelect?.addEventListener("change", async function () {
    if (!departmentSelect) return;
    const facultyId = this.value;
    departmentSelect.innerHTML = '<option value="">Select Department</option>';
    if (!facultyId) return;
    try {
      const res = await fetch(
        `${FACULTY_API}?faculty_id=${encodeURIComponent(facultyId)}`
      );
      const data = await res.json();
      if (data.departments && Array.isArray(data.departments)) {
        data.departments.forEach((d) => {
          const o = document.createElement("option");
          o.value = d.id;
          o.textContent = d.name;
          departmentSelect.appendChild(o);
        });
      }
    } catch (err) {
      console.error("load departments error", err);
    }
  });

  generateBtn?.addEventListener("click", () => {
    if (passwordField) passwordField.value = generatePassword();
  });

  if (form) {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      if (!feedback) return;

      if (addingLecturerFlags[suffix]) {
        return;
      }
      addingLecturerFlags[suffix] = true;
      showGlobalLoading();

      const name = form.lecturer_name?.value?.trim();
      const email = form.lecturer_email?.value?.trim();
      const facultyText =
        document
          .querySelector(`#faculty-${suffix} option:checked`)
          ?.textContent.trim() || "";
      const deptText =
        document
          .querySelector(`#department-${suffix} option:checked`)
          ?.textContent.trim() || "";
      const password = passwordField?.value;

      if (!name || !email || !facultyText || !deptText || !password) {
        showFeedback(feedback, "All fields are required.", false);
        addingLecturerFlags[suffix] = false;
        hideGlobalLoading();
        return;
      }

      const payload = new FormData();
      payload.append("lecturer_name", name);
      payload.append("lecturer_email", email);
      payload.append("faculty_name", facultyText);
      payload.append("department_name", deptText);
      payload.append("password", password);

      try {
        const res = await fetch("./inc/add_lecturer.php", {
          method: "POST",
          body: payload,
        });
        const json = await res.json();
        if (json.success) {
          showFeedback(feedback, json.success, true);
          form.reset();
          performSearch("");
        } else {
          showFeedback(
            feedback,
            json.error || "Failed to add lecturer.",
            false
          );
        }
      } catch (err) {
        console.error("submit lecturer error", err);
        showFeedback(feedback, "Request error.", false);
      } finally {
        addingLecturerFlags[suffix] = false;
        hideGlobalLoading(); 
      }
    });
  }

  await loadFaculties();
}

wireLecturerForm("dash");
wireLecturerForm("section");


const searchInput = document.getElementById("lecturer-search");
const lecturerTbody = document.getElementById("lecturer-table-body");
let debounceTimer;

function renderLecturers(lecturers) {
  if (!lecturerTbody) return;
  if (!Array.isArray(lecturers) || lecturers.length === 0) {
    lecturerTbody.innerHTML =
      '<tr><td colspan="4">No lecturers found.</td></tr>';
    return;
  }
  lecturerTbody.innerHTML = lecturers
    .map((l) => {
      const name = escapeHtml(l.username);
      const email = escapeHtml(l.email);
      const dept = escapeHtml(l.department);
      const id = l.id ?? l.user_id ?? l.ID ?? "";
      const safeId = id || "MISSING";
      return `
        <tr>
          <td>${name}</td>
          <td>${email}</td>
          <td>${dept}</td>
          <td class="actions">
            <button class="btn action-reset" data-user-id="${safeId}" title="Reset Password">
              <i class="fas fa-key"></i>Reset Password
            </button>
            <button class="btn action-delete" data-user-id="${safeId}" title="Delete">
              <i class="fas fa-trash"></i> Delete
            </button>
          </td>
        </tr>`;
    })
    .join("");
}
function generatePassword() {
  const upper = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
  const lower = "abcdefghijklmnopqrstuvwxyz";
  const numbers = "0123456789";
  const special = "!@#$%^&*";
  const all = upper + lower + numbers + special;


  let pwd = "";
  pwd += upper[Math.floor(Math.random() * upper.length)];
  pwd += lower[Math.floor(Math.random() * lower.length)];
  pwd += numbers[Math.floor(Math.random() * numbers.length)];
  pwd += special[Math.floor(Math.random() * special.length)];


  for (let i = 4; i < 12; i++) {
    pwd += all[Math.floor(Math.random() * all.length)];
  }

  // shuffle
  pwd = pwd.split("").sort(() => Math.random() - 0.5).join("");
  return pwd;
}
async function performSearch(query = "") {
  if (!lecturerTbody) return;
  try {
    const resp = await fetch(
      `inc/search_lecturers.php?q=${encodeURIComponent(query)}`
    );
    const data = await resp.json();
    if (data.error) {
      lecturerTbody.innerHTML =
        '<tr><td colspan="4">Error loading results.</td></tr>';
      return;
    }
    renderLecturers(data.lecturers);
  } catch (err) {
    console.error("Search fetch failed", err);
    lecturerTbody.innerHTML = '<tr><td colspan="4">Network error.</td></tr>';
  }
}

if (searchInput) {
  searchInput.addEventListener("input", () => {
    const q = searchInput.value.trim();
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => performSearch(q), 300);
  });
  performSearch("");
}

document.addEventListener("click", function (e) {
  const resetBtn = e.target.closest(".action-reset");
  if (resetBtn) {
    const userId = resetBtn.dataset.userId;
    if (!userId || userId === "MISSING") {
      alert("No user ID found.");
      return;
    }
    showResetConfirm(
      () => {
        fetch("inc/action.php", {
          method: "POST",
          body: new URLSearchParams({
            action: "reset_password",
            user_id: userId,
          }),
        })
          .then((r) => r.json())
          .then((json) => {
            if (json.success) {
              showPasswordPopup(json.new_password);
            } else {
              alert("Error: " + (json.error || "Unknown"));
            }
          })
          .catch(() => {
            alert("Network error resetting password.");
          });
      },
      () => {
        console.log("Password reset cancelled");
      }
    );
    return;
  }

  const delBtn = e.target.closest(".action-delete");
  if (delBtn) {
    const userId = delBtn.dataset.userId;
    if (!userId || userId === "MISSING") {
      alert("No user ID found.");
      return;
    }
    showDeleteConfirm(
      () => {
        fetch("inc/action.php", {
          method: "POST",
          body: new URLSearchParams({
            action: "delete_user",
            user_id: userId,
          }),
        })
          .then((r) => r.json())
          .then((json) => {
            if (json.success) {
              const row = delBtn.closest("tr");
              if (row) row.remove();
            } else {
              alert("Error: " + (json.error || "Unknown"));
            }
          })
          .catch(() => {
            alert("Network error deleting user.");
          });
      },
      () => {
        console.log("Deletion cancelled");
      }
    );
  }
});

const apiEndpoint = "./inc/get-departmet.php";
const tabFaculty = document.getElementById("tab-add-faculty");
const tabDepartment = document.getElementById("tab-add-department");
const panelFaculty = document.getElementById("panel-add-faculty");
const panelDepartment = document.getElementById("panel-add-department");
const facultyForm = document.getElementById("faculty-form");
const facultyFeedback = document.getElementById("faculty-feedback");
const deptForm = document.getElementById("department-form");
const deptFeedback = document.getElementById("department-feedback");
const deptFacultySelect = document.getElementById("dept-faculty-select");

function activateTab(tab) {
  if (tab === "faculty") {
    if (tabFaculty) {
      tabFaculty.classList.add("active");
      tabFaculty.style.background = "#6c04cd";
      tabFaculty.style.color = "#fff";
    }
    if (tabDepartment) {
      tabDepartment.classList.remove("active");
      tabDepartment.style.background = "#eee";
      tabDepartment.style.color = "#333";
    }
    if (panelFaculty) panelFaculty.style.display = "block";
    if (panelDepartment) panelDepartment.style.display = "none";
  } else {
    if (tabDepartment) {
      tabDepartment.classList.add("active");
      tabDepartment.style.background = "#6c04cd";
      tabDepartment.style.color = "#fff";
    }
    if (tabFaculty) {
      tabFaculty.classList.remove("active");
      tabFaculty.style.background = "#eee";
      tabFaculty.style.color = "#333";
    }
    if (panelDepartment) panelDepartment.style.display = "block";
    if (panelFaculty) panelFaculty.style.display = "none";
  }
}

tabFaculty?.addEventListener("click", () => activateTab("faculty"));
tabDepartment?.addEventListener("click", () => activateTab("department"));
activateTab("faculty");

async function loadFacultyOptions() {
  try {
    const res = await fetch(apiEndpoint);
    const data = await res.json();
    if (data.faculties && Array.isArray(data.faculties) && deptFacultySelect) {
      deptFacultySelect.innerHTML = '<option value="">Select Faculty</option>';
      data.faculties.forEach((f) => {
        const o = document.createElement("option");
        o.value = f.id;
        o.textContent = f.name;
        deptFacultySelect.appendChild(o);
      });
    }
  } catch (err) {
    console.error("Failed to load faculties for department:", err);
    if (deptFacultySelect)
      deptFacultySelect.innerHTML = '<option value="">Error loading</option>';
  }
}
loadFacultyOptions();

facultyForm?.addEventListener("submit", async (e) => {
  e.preventDefault();
  if (facultyFeedback) facultyFeedback.style.display = "none";
  const formData = new FormData(facultyForm);
  formData.append("action", "add_faculty");
  try {
    const res = await fetch(apiEndpoint, {
      method: "POST",
      body: formData,
    });
    const json = await res.json();
    if (json.success) {
      if (facultyFeedback) {
        facultyFeedback.textContent = json.success;
        facultyFeedback.className = "status active";
        facultyFeedback.style.display = "block";
      }
      await loadFacultyOptions();
      if (json.faculty && deptFacultySelect) {
        const opt = document.createElement("option");
        opt.value = json.faculty.id;
        opt.textContent = json.faculty.name;
        deptFacultySelect.appendChild(opt);
        deptFacultySelect.value = json.faculty.id;
      }
      setTimeout(() => activateTab("department"), 800);
    } else {
      if (facultyFeedback) {
        facultyFeedback.textContent = json.error || "Failed to add faculty.";
        facultyFeedback.className = "status inactive";
        facultyFeedback.style.display = "block";
      }
    }
  } catch (err) {
    if (facultyFeedback) {
      facultyFeedback.textContent = "Request error.";
      facultyFeedback.className = "status inactive";
      facultyFeedback.style.display = "block";
    }
    console.error(err);
  }
});

deptForm?.addEventListener("submit", async (e) => {
  e.preventDefault();
  if (deptFeedback) deptFeedback.style.display = "none";
  const formData = new FormData(deptForm);
  formData.append("action", "add_department");
  try {
    const res = await fetch(apiEndpoint, {
      method: "POST",
      body: formData,
    });
    const json = await res.json();
    if (json.success) {
      if (deptFeedback) {
        deptFeedback.textContent = json.success;
        deptFeedback.className = "status active";
        deptFeedback.style.display = "block";
      }
      deptForm.reset();
    } else {
      if (deptFeedback) {
        deptFeedback.textContent = json.error || "Failed to add department.";
        deptFeedback.className = "status inactive";
        deptFeedback.style.display = "block";
      }
    }
  } catch (err) {
    if (deptFeedback) {
      deptFeedback.textContent = "Request error.";
      deptFeedback.className = "status inactive";
      deptFeedback.style.display = "block";
    }
    console.error(err);
  }
});
