const loginForm = document.getElementById("loginForm");
const emailInput = document.getElementById("email");
const passwordInput = document.getElementById("password");
const togglePassword = document.getElementById("togglePassword");
const messageBox = document.getElementById("message");

togglePassword.addEventListener("click", function () {
  if (passwordInput.type === "password") {
    passwordInput.type = "text";
    togglePassword.textContent = "Hide";
  } else {
    passwordInput.type = "password";
    togglePassword.textContent = "Show";
  }
});

loginForm.addEventListener("submit", function (event) {
  event.preventDefault();

  const email = emailInput.value.trim();
  const password = passwordInput.value.trim();

  if (email === "" || password === "") {
    showMessage("Please fill in all fields.", "error");
    return;
  }

  if (!validateEmail(email)) {
    showMessage("Please enter a valid email address.", "error");
    return;
  }

  if (password.length < 6) {
    showMessage("Password must be at least 6 characters.", "error");
    return;
  }

  showMessage("Login successful. Welcome back!", "success");

  loginForm.reset();
  togglePassword.textContent = "Show";
  passwordInput.type = "password";
});

function validateEmail(email) {
  const emailPattern = /^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/;
  return emailPattern.test(email);
}

function showMessage(message, type) {
  messageBox.textContent = message;
  messageBox.className = "message-box";
  messageBox.classList.add(type);
}