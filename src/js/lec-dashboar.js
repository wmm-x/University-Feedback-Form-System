function addQuestion() {
  const container = document.getElementById("formBuilder");

  const block = document.createElement("div");
  block.className = "question-block";

  const questionDiv = document.createElement("div");
  questionDiv.className = "question";

  const select = document.createElement("select");
  select.onchange = function () {
    handleTypeChange(this);
  };
  ["short", "paragraph", "checkbox", "radio"].forEach((val, i) => {
    const option = document.createElement("option");
    option.value = val;
    option.textContent = [
      "Short Answer",
      "Paragraph",
      "Multiple Choice",
      "Checkboxes",
    ][i];
    select.appendChild(option);
  });

  const questionInput = document.createElement("input");
  questionInput.type = "text";
  questionInput.placeholder = "Enter your question";

  const optionsDiv = document.createElement("div");
  optionsDiv.className = "options";

  const addOptionLink = document.createElement("a");
  addOptionLink.href = "#";
  addOptionLink.className = "add-option";
  addOptionLink.textContent = "+ Add Option";
  addOptionLink.style.display = "none";
  addOptionLink.onclick = function (e) {
    addOption(e, this);
  };

  const actionsDiv = document.createElement("div");
  actionsDiv.className = "actions";

  const requiredLabel = document.createElement("label");
  requiredLabel.textContent = "Required ";
  const requiredCheckbox = document.createElement("input");
  requiredCheckbox.type = "checkbox";
  requiredCheckbox.checked = true;
  requiredCheckbox.classList.add("required-checkbox"); // ADD THIS LINE
  requiredLabel.appendChild(requiredCheckbox);

  const copyIcon = document.createElement("i");
  copyIcon.className = "fas fa-copy";
  copyIcon.onclick = function () {
    duplicateQuestion(this);
  };

  const trashIcon = document.createElement("i");
  trashIcon.className = "fas fa-trash";
  trashIcon.onclick = function () {
    block.remove();
  };

  actionsDiv.appendChild(requiredLabel);
  actionsDiv.appendChild(copyIcon);
  actionsDiv.appendChild(trashIcon);

  questionDiv.appendChild(select);
  questionDiv.appendChild(questionInput);
  questionDiv.appendChild(optionsDiv);
  questionDiv.appendChild(addOptionLink);
  questionDiv.appendChild(actionsDiv);

  block.appendChild(questionDiv);
  container.appendChild(block);
}

function handleTypeChange(selectEl) {
  const type = selectEl.value;
  const block = selectEl.closest(".question");
  const options = block.querySelector(".options");
  const addOptionBtn = block.querySelector(".add-option");

  options.innerHTML = "";
  addOptionBtn.style.display =
    type === "radio" || type === "checkbox" ? "inline" : "none";

  if (type === "radio" || type === "checkbox") {
    addSampleOptions(type, options);
  }
}

function addSampleOptions(type, container) {
  for (let i = 1; i <= 2; i++) {
    const optionDiv = document.createElement("div");
    const inputType = document.createElement("input");
    inputType.type = type;
    inputType.disabled = true;

    const textInput = document.createElement("input");
    textInput.type = "text";
    textInput.value = `Option ${i}`;

    const delIcon = document.createElement("i");
    delIcon.className = "fas fa-trash";
    delIcon.onclick = function () {
      optionDiv.remove();
    };

    optionDiv.appendChild(inputType);
    optionDiv.appendChild(textInput);
    optionDiv.appendChild(delIcon);
    container.appendChild(optionDiv);
  }
}

function addOption(e, el) {
  e.preventDefault();
  const container = el.parentElement.querySelector(".options");
  const type = el.parentElement.querySelector("select").value;

  const optionDiv = document.createElement("div");
  const inputType = document.createElement("input");
  inputType.type = type;
  inputType.disabled = true;

  const textInput = document.createElement("input");
  textInput.type = "text";
  textInput.value = "Option";

  const delIcon = document.createElement("i");
  delIcon.className = "fas fa-trash";
  delIcon.onclick = function () {
    optionDiv.remove();
  };

  optionDiv.appendChild(inputType);
  optionDiv.appendChild(textInput);
  optionDiv.appendChild(delIcon);
  container.appendChild(optionDiv);
}

function duplicateQuestion(el) {
  const block = el.closest(".question-block");
  const clone = block.cloneNode(true);


  const select = clone.querySelector("select");
  select.onchange = function () {
    handleTypeChange(this);
  };

  const addOptionLink = clone.querySelector(".add-option");
  addOptionLink.onclick = function (e) {
    addOption(e, this);
  };

  const copyIcon = clone.querySelector(".fa-copy");
  copyIcon.onclick = function () {
    duplicateQuestion(this);
  };

  const trashIcon = clone.querySelector(".fa-trash");
  trashIcon.onclick = function () {
    clone.remove();
  };

  block.parentNode.insertBefore(clone, block.nextSibling);
}

function clearErrors() {
  document.querySelectorAll(".error-message").forEach((e) => e.remove());
  document
    .querySelectorAll(".input-error")
    .forEach((e) => e.classList.remove("input-error"));
}

function showError(element, message) {
  element.classList.add("input-error");
  const error = document.createElement("div");
  error.className = "error-message";
  error.textContent = message;
  element.parentNode.appendChild(error);
}

function validateForm() {
  clearErrors();
  let hasError = false;

  const titleEl = document.getElementById("formTitle");
  const typeEl = document.getElementById("formType");

  if (titleEl.value.trim() === "") {
    showError(titleEl, "Form title is required.");
    hasError = true;
  }

  if (typeEl.value.trim() === "") {
    showError(typeEl, "Please select a form type.");
    hasError = true;
  }

  const questionBlocks = document.querySelectorAll(".question-block");
  if (questionBlocks.length === 0) {

    return false;
  }

  questionBlocks.forEach((block, i) => {
    const questionInput = block.querySelector('input[type="text"]');
    const selectType = block.querySelector("select").value;

    if (!questionInput || questionInput.value.trim() === "") {
      showError(questionInput, `Question ${i + 1} cannot be empty.`);
      hasError = true;
    }

    if (selectType === "checkbox" || selectType === "radio") {
      const options = block.querySelectorAll('.options input[type="text"]');
      if (options.length === 0) {
        showError(
          block.querySelector(".add-option"),
          `Question ${i + 1} needs options.`
        );
        hasError = true;
      } else {
        options.forEach((opt, j) => {
          if (opt.value.trim() === "") {
            showError(
              opt,
              `Option ${j + 1} in Question ${i + 1} cannot be empty.`
            );
            hasError = true;
          }
        });
      }
    }
  });

  return !hasError;
}
function viewForm(formid) {
  window.location.href = "view-response.php?form_id=" + formid;
}

function showDashboard() {
  document.getElementById("main-content").style.display = "block";
  document.getElementById("main-content2").style.display = "none";
  document.getElementById("main-content3").style.display = "none";
  document.getElementById("main-content4").style.display = "none";
  setActiveNav("nav-dashboard");
}

function showFormCreator() {
  document.getElementById("main-content").style.display = "none";
  document.getElementById("main-content2").style.display = "block";
  document.getElementById("main-content3").style.display = "none";
  document.getElementById("main-content4").style.display = "none";
  setActiveNav("nav-create");
}
function showsSetting() {
  document.getElementById("main-content").style.display = "none";
  document.getElementById("main-content2").style.display = "none";
  document.getElementById("main-content3").style.display = "none";
  document.getElementById("main-content4").style.display = "block";
  setActiveNav("nav-settings");
}

function showFormList() {
  localStorage.setItem("tabToShow", "formList");
  location.reload();
  document.getElementById("main-content").style.display = "none";
  document.getElementById("main-content2").style.display = "none";
  document.getElementById("main-content3").style.display = "block";
  document.getElementById("main-content4").style.display = "none";
}

window.addEventListener("DOMContentLoaded", () => {
  const tab = localStorage.getItem("tabToShow");

  if (tab === "formList") {
    document.getElementById("main-content").style.display = "none";
    document.getElementById("main-content2").style.display = "none";
    document.getElementById("main-content3").style.display = "block";
    setActiveNav("nav-forms");
    localStorage.removeItem("tabToShow");
  }
});

function setActiveNav(id) {
  const navItems = document.querySelectorAll(".nav-menu li");
  navItems.forEach((item) => item.classList.remove("active"));
  document.getElementById(id).classList.add("active");
}

function createForm() {
  const title = document.getElementById("formTitle").value.trim();
  const type = document.getElementById("formType").value;
  const questions = getFormQuestions(); 
  fetch("save-form.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      title,
      type,
      questions,
    }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        document.getElementById("formLinkInput").value = data.form_link;
        document.getElementById("popupMessage").textContent = data.message;
        document.getElementById("successPopup").style.display = "flex";
      } else {
        alert("Error: " + data.message);
      }
    })
    .catch((err) => {
      alert("An unexpected error occurred: " + err.message);
    });
}

function closePopup() {
  const popup = document.getElementById("successPopup");
  const content = popup.querySelector(".popup-content");


  content.classList.add("fade-out");


  content.addEventListener("animationend", function handler() {
    popup.style.display = "none";
    content.classList.remove("fade-out");
    content.removeEventListener("animationend", handler);
  });
}

function copyLink() {
  const input = document.getElementById("formLinkInput");
  const button = document.getElementById("copyBtn");

  input.select();
  input.setSelectionRange(0, 99999); 
  document.execCommand("copy");

  
  button.textContent = "Copied!";

 
  setTimeout(() => {
    button.textContent = "Copy Link";
  }, 3000);
}

function createForm() {
  const formData = {
    title: document.getElementById("formTitle").value.trim(),
    type: document.getElementById("formType").value.trim(),
    description: document.getElementById("formDescription").value.trim(),
    lecturer_id: LECTURERID, 
    questions: [],
  };

  document.querySelectorAll(".question-block").forEach((block) => {
    const questionText = block.querySelector('input[type="text"]').value.trim();
    const type = block.querySelector("select").value;
    const isRequired = block.querySelector("input.required-checkbox").checked;

    const question = {
      question_text: questionText,
      question_type: type,
      is_required: isRequired,
      options: [],
    };

    if (type === "radio" || type === "checkbox") {
      block.querySelectorAll('.options input[type="text"]').forEach((opt) => {
        question.options.push(opt.value.trim());
      });
    }

    formData.questions.push(question);
  });

  fetch("inc/create-from.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(formData),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        document.getElementById("formLinkInput").value = data.form_link;
        document.getElementById("popupMessage").textContent = data.message;
        document.getElementById("successPopup").style.display = "flex";
      } else {
        alert("Error: " + data.message);
      }
    })
    .catch((error) => {
      console.error("Error creating form:", error);
      alert("An error occurred while creating the form.");
    });
}
const LECTURER_ID = "<?php echo $_SESSION['userid']; ?>";

function copyFormLink(btn) {

  const link = btn.getAttribute("data-form-link");


  if (navigator.clipboard) {
    navigator.clipboard.writeText(link);
  } else {
    
    const tempInput = document.createElement("input");
    tempInput.value = link;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand("copy");
    document.body.removeChild(tempInput);
  }

  const originalText = btn.innerHTML;
  btn.innerHTML = "<i class='fa fa-link'></i> Copied!";
  btn.disabled = true;

  setTimeout(() => {
    btn.innerHTML = originalText;
    btn.disabled = false;
  }, 3000);
}

function deleteForm(formId) {
  deleteFormId = Number(formId);
  console.log("deleteForm called with:", deleteFormId, typeof deleteFormId);
  document.getElementById("deleteConfirmModal").style.display = "flex";
}

// Close Delete Confirm Modal
function closeDeleteModal() {
  document.getElementById("deleteConfirmModal").style.display = "none";
  deleteFormId = null;
}

// Open Reset Confirm Modal
function resetForm(formId) {
  resetFormId = Number(formId);
  console.log("resetForm called with:", resetFormId, typeof resetFormId);
  document.getElementById("resetConfirmModal").style.display = "flex";
}

// Close Reset Confirm Modal
function closeResetModal() {
  document.getElementById("resetConfirmModal").style.display = "none";
  resetFormId = null;
}

// Show notification popup
function showNotification(message) {
  const notif = document.getElementById("actionNotification");
  document.getElementById("actionNotifText").textContent = message;
  notif.style.display = "flex";
  clearTimeout(notif._timeout);
  notif._timeout = setTimeout(() => {
    notif.style.display = "none";
  }, 3000);
}

function hideNotification() {
  document.getElementById("actionNotification").style.display = "none";
}


function removeFormRow(formId) {
  const btns = document.querySelectorAll(".delete-btn");
  for (let btn of btns) {
    if (btn.getAttribute("onclick") === `deleteForm(${formId})`) {
      const tr = btn.closest("tr");
      if (tr) tr.remove();
      break;
    }
  }
}

function toggleFormStatus(formId, btn) {
  const statusSpan = document.getElementById("status-text-" + formId);
  const currentStatus = statusSpan.textContent.trim().toLowerCase();
  const newStatus = currentStatus === "active" ? "inactive" : "active";

  fetch("./inc/form-hadel.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      formID: formId,
      userID: LECTURERID, 
      action: "status",
      status: newStatus,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        statusSpan.textContent =
          newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
        statusSpan.className =
          "form-status-badge" +
          (newStatus === "active" ? "" : " status-inactive");
        btn.textContent = newStatus === "active" ? "Deactivate" : "Activate";
      } else {
        alert(data.message || "Status change failed.");
      }
    })
    .catch(() => alert("Request failed!"));
}



document.addEventListener("DOMContentLoaded", function () {
  // Handle dashboard quick add lecturer form
  const dashForm = document.getElementById("add-lecturer-form-dash");
  if (dashForm) {
    dashForm.addEventListener("submit", handleLecturerSubmission);
  }

  const sectionForm = document.getElementById("add-lecturer-form-section");
  if (sectionForm) {
    sectionForm.addEventListener("submit", handleLecturerSubmission);
  }
});

async function handleLecturerSubmission(e) {
  e.preventDefault();

  const form = e.target;
  const formId = form.id;
  const isDashboard = formId.includes("dash");

  const submitBtn = form.querySelector(".submit-btn");
  const feedback = form.querySelector(
    `#add-lecturer-feedback-${isDirectory ? "dash" : "section"}`
  );

  // Show loading state
  showLoadingState(submitBtn);

  // Hide previous feedback
  if (feedback) {
    feedback.style.display = "none";
  }

  try {
    // Collect form data
    const formData = new FormData(form);
    const lecturerData = {
      lecturer_name: formData.get("lecturer_name"),
      lecturer_email: formData.get("lecturer_email"),
      faculty_id: form.querySelector(
        `#faculty-${isDirectory ? "dash" : "section"}`
      ).value,
      department_id: form.querySelector(
        `#department-${isDirectory ? "dash" : "section"}`
      ).value,
      password: form.querySelector(
        `#password-field-${isDirectory ? "dash" : "section"}`
      ).value,
    };

    // Validate required fields
    if (
      !lecturerData.lecturer_name ||
      !lecturerData.lecturer_email ||
      !lecturerData.faculty_id ||
      !lecturerData.department_id ||
      !lecturerData.password
    ) {
      throw new Error("Please fill in all required fields");
    }

    // Make API request to  backend
    const response = await fetch("./inc/add-lecturer.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(lecturerData),
    });

    const data = await response.json();

    if (data.success) {
      showFeedback(
        feedback,
        data.message || "Lecturer added successfully!",
        "success"
      );
      form.reset(); 

      // Reset password field
      const passwordField = form.querySelector(
        `#password-field-${isDirectory ? "dash" : "section"}`
      );
      if (passwordField) {
        passwordField.value = "";
      }

      if (isDirectory) {
        updateLecturerCount();
      }
    } else {
      throw new Error(data.message || "Failed to add lecturer");
    }
  } catch (error) {
    console.error("Error adding lecturer:", error);
    showFeedback(feedback, "Error: " + error.message, "error");
  } finally {
    // Hide loading state
    hideLoadingState(submitBtn);
  }
}

function showLoadingState(button) {
  if (!button) return;


  button.disabled = true;

  button.dataset.originalContent = button.innerHTML;

  button.innerHTML = `
        <div class="loading-spinner"></div>
        <span>Adding...</span>
    `;

  // Add loading class for styling
  button.classList.add("loading");
}

function hideLoadingState(button) {
  if (!button) return;

  button.disabled = false;

  if (button.dataset.originalContent) {
    button.innerHTML = button.dataset.originalContent;
    delete button.dataset.originalContent;
  }
  button.classList.remove("loading");
}

function showFeedback(element, message, type) {
  if (!element) return;

  element.textContent = message;
  element.className = "status " + type;
  element.style.display = "block";

  // Auto-hide success messagesg
  if (type === "success") {
    setTimeout(() => {
      element.style.display = "none";
    }, 5000);
  }
}

async function updateLecturerCount() {
  try {
    const response = await fetch("./inc/get-lecturer-count.php");
    const data = await response.json();

    if (data.success) {
      const countElement = document.querySelector(".stat-card h2");
      if (countElement) {
        countElement.textContent = data.count;
      }
    }
  } catch (error) {
    console.error("Error updating lecturer count:", error);
  }
}


