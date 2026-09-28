// validate.js - client-side form validation with regular expressions.
// This is a first line of defence for a friendly UX only; every rule here
// is also enforced server-side in the action files, because JavaScript can
// always be bypassed.

// --- Shared validation patterns (from the lab spec) ---
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;      // basic a@b.c shape
const phoneRegex = /^[0-9+\-\s]{7,15}$/;               // 7-15 digits, allows + - space
const passRegex  = /^(?=.*\d).{8,}$/;                  // min 8 chars, at least one digit

// Write a message into the <span> that follows a field, and return false
// so callers can chain: valid = valid && showError(...).
function showError(spanId, message) {
    const el = document.getElementById(spanId);
    if (el) {
        el.textContent = message;
    }
    return false;
}

// Clear a previously shown error message.
function clearError(spanId) {
    const el = document.getElementById(spanId);
    if (el) {
        el.textContent = "";
    }
}

// ------------------------------------------------------------
// Registration form
// ------------------------------------------------------------
const registerForm = document.getElementById("registerForm");
if (registerForm) {
    registerForm.addEventListener("submit", function (e) {
        let valid = true;

        const name    = document.getElementById("reg_name").value.trim();
        const email   = document.getElementById("reg_email").value.trim();
        const pass    = document.getElementById("reg_pass").value;
        const country = document.getElementById("reg_country").value.trim();
        const city    = document.getElementById("reg_city").value.trim();
        const contact = document.getElementById("reg_contact").value.trim();

        // Reset all messages first
        ["err_name", "err_email", "err_pass", "err_country", "err_city", "err_contact"]
            .forEach(clearError);

        if (name.length < 2) {
            valid = showError("err_name", "Name must be at least 2 characters.");
        }
        if (!emailRegex.test(email)) {
            valid = showError("err_email", "Enter a valid email address.");
        }
        if (email.length > 50) {
            valid = showError("err_email", "Email must be 50 characters or fewer.");
        }
        if (!passRegex.test(pass)) {
            valid = showError("err_pass", "Password needs 8+ characters and a number.");
        }
        if (country === "") {
            valid = showError("err_country", "Please choose a country.");
        }
        if (city === "") {
            valid = showError("err_city", "City is required.");
        }
        if (!phoneRegex.test(contact)) {
            valid = showError("err_contact", "Enter a valid contact number (7-15 digits).");
        }

        if (!valid) {
            e.preventDefault();     // stop the form from submitting
            return;
        }

        // Optional: show a loading state on the button
        const btn = document.getElementById("registerBtn");
        if (btn) {
            btn.disabled = true;
            btn.textContent = "Registering...";
        }
    });
}

// ------------------------------------------------------------
// Login form
// ------------------------------------------------------------
const loginForm = document.getElementById("loginForm");
if (loginForm) {
    loginForm.addEventListener("submit", function (e) {
        let valid = true;

        const email = document.getElementById("login_email").value.trim();
        const pass  = document.getElementById("login_pass").value;

        ["err_login_email", "err_login_pass"].forEach(clearError);

        if (!emailRegex.test(email)) {
            valid = showError("err_login_email", "Enter a valid email address.");
        }
        if (pass === "") {
            valid = showError("err_login_pass", "Password is required.");
        }

        if (!valid) {
            e.preventDefault();
            return;
        }

        const btn = document.getElementById("loginBtn");
        if (btn) {
            btn.disabled = true;
            btn.textContent = "Logging in...";
        }
    });
}
