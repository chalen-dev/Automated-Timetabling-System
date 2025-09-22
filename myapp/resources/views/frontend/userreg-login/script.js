function showTab(tabId) {
      const signupForm = document.getElementById("signupForm");
      const loginForm = document.getElementById("loginForm");
      const signInBtn = document.getElementById("signInBtn"); 

      if (tabId === "signupForm") {
        signupForm.classList.remove("hidden");
        loginForm.classList.add("hidden");
        if (signInBtn) signInBtn.style.display = "block"; 
      } else if (tabId === "loginForm") {
        loginForm.classList.remove("hidden");
        signupForm.classList.add("hidden");
        if (signInBtn) signInBtn.style.display = "none"; 
      }
    }

    function togglePassword(passwordId, eyeIconId) {
      const passwordInput = document.getElementById(passwordId);
      if (passwordInput.type === "password") passwordInput.type = "text";
      else passwordInput.type = "password";
    }

    document.getElementById("toggleSignupPassword").addEventListener("click", () => {
      togglePassword("signupPassword", "toggleSignupPassword");
    });
    document.getElementById("toggleLoginPassword").addEventListener("click", () => {
      togglePassword("loginPassword", "toggleLoginPassword");
    });

    function registerUser() {
      const email = document.getElementById("signupEmail").value;
      const password = document.getElementById("signupPassword").value;

      if (!email || !password) { alert("Please fill all fields!"); return; }

      let users = JSON.parse(localStorage.getItem("users") || "[]");
      if (users.find(u => u.email === email)) { alert("User already exists!"); return; }

      users.push({ email, password, verified: false });
      localStorage.setItem("users", JSON.stringify(users));

      alert("Registered! Please verify your account.");
      document.getElementById("verifyDiv").classList.remove("hidden");
    }

    function verifyUser() {
      const email = document.getElementById("signupEmail").value;
      let users = JSON.parse(localStorage.getItem("users") || "[]");

      let user = users.find(u => u.email === email);
      if (user) {
        user.verified = true;
        localStorage.setItem("users", JSON.stringify(users));
        alert("Email verified! You can now login.");
        document.getElementById("verifyDiv").classList.add("hidden");
        showTab("loginForm");
      } else { alert("User not found!"); }
    }

    function loginUser() {
      const email = document.getElementById("loginEmail").value;
      const password = document.getElementById("loginPassword").value;

      let users = JSON.parse(localStorage.getItem("users") || "[]");
      let user = users.find(u => u.email === email);

      if (!user) { alert("No such user!"); return; }
      if (!user.verified) { alert("Please verify your account first!"); return; }
      if (user.password !== password) { alert("Wrong password!"); return; }

      alert("Login successful! ✅");
    }