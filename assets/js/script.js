document.addEventListener("DOMContentLoaded", function () {
  // Show SignUp Form
  function showSignUp() {
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('signupForm').style.display = 'block';
}


function showLogin() {
    document.getElementById('loginForm').style.display = 'block';
    document.getElementById('signupForm').style.display = 'none';
}

// Check for signup parameter when page loads
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('action') === 'signup') {
        showSignUp();
    }
});
// Execute when DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Make sure login form is visible by default
    showLogin();
});
  // Make showLogin and showSignUp globally accessible (for buttons)
  window.showLogin = showLogin;
  window.showSignUp = showSignUp;

  // Handle Login Form Submission
  const loginForm = document.getElementById("loginForm");
  if (loginForm) {
    loginForm.addEventListener("submit", (e) => {
      e.preventDefault();
      // Perform login actions here
      console.log("Login form submitted");
    });
  }

  // Handle Sign-Up Form Submission
  const signupForm = document.getElementById("signupForm");
  if (signupForm) {
    signupForm.addEventListener("submit", (e) => {
      e.preventDefault();
      // Perform signup actions here
      console.log("Signup form submitted");
    });
  }
});
