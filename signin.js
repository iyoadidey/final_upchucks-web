document.addEventListener("DOMContentLoaded", () => {
    const tabs = document.querySelectorAll(".tab");
    const signupForm = document.getElementById("signup-form");
    const signinForm = document.getElementById("signin-form");
    const togglePassword = document.getElementById("togglePassword");
    const passwordInput = document.getElementById("password");

    // Validate TIP email
    function validateTIPEmail(email) {
        return /^[a-zA-Z0-9._%+-]+@tip\.edu\.ph$/.test(email.trim());
    }

    // Tab switch
    tabs.forEach((tab) => {
        tab.addEventListener("click", () => {
            tabs.forEach((t) => t.classList.remove("active"));
            tab.classList.add("active");

            if (tab.dataset.form === "signup") {
                signupForm.classList.remove("hidden");
                signinForm.classList.add("hidden");
            } else {
                signupForm.classList.add("hidden");
                signinForm.classList.remove("hidden");
            }
        });
    });

    // Toggle password visibility
    togglePassword?.addEventListener("click", () => {
        passwordInput.type = passwordInput.type === "password" ? "text" : "password";
    });

    // Handle signup
    signupForm.addEventListener("submit", async function (e) {
        e.preventDefault();
        
        const email = this.email.value;
        if (!validateTIPEmail(email)) {
            alert("Please use your TIP email address (@tip.edu.ph)");
            this.email.focus();
            return;
        }
        
        const formData = {
            firstName: this.firstName.value,
            lastName: this.lastName.value,
            email: email,
            password: this.password.value
        };

        try {
            const response = await fetch("backend/register.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();
            
            if (data.success) {
                alert(data.message);
                // Switch to signin form after successful registration
                document.querySelector('[data-form="signin"]').click();
            } else {
                alert(data.message);
            }
        } catch (error) {
            alert("An error occurred. Please try again.");
            console.error("Error:", error);
        }
    });

    // Handle signin
    signinForm.addEventListener("submit", async function (e) {
        e.preventDefault();
        
        const email = this.email.value;
        if (!validateTIPEmail(email)) {
            alert("Please use your TIP email address (@tip.edu.ph)");
            this.email.focus();
            return;
        }
        
        const formData = {
            email: email,
            password: this.password.value
        };

        try {
            const response = await fetch("backend/login.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();
            
            if (data.success) {
                alert(data.message);
                // Redirect to the main website page after successful login
                window.location.href = "website.html";
            } else {
                alert(data.message);
            }
        } catch (error) {
            alert("An error occurred. Please try again.");
            console.error("Error:", error);
        }
    });
});
