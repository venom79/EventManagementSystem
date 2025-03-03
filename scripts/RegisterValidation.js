document.addEventListener("DOMContentLoaded", function () {
    const roleSelect = document.getElementById("role");
    const organizerFields = document.getElementById("organizerFields");
    const vendorFields = document.getElementById("vendorFields");
    const form = document.querySelector(".form");

    // Function to show/hide fields based on role
    function toggleFields() {
        const selectedRole = roleSelect.value;
        organizerFields.style.display = selectedRole === "organizer" ? "block" : "none";
        vendorFields.style.display = selectedRole === "vendor" ? "block" : "none";

        setRequired(organizerFields, selectedRole === "organizer");
        setRequired(vendorFields, selectedRole === "vendor");
    }

    function setRequired(section, isRequired) {
        section.querySelectorAll("input, select").forEach(input => {
            if (["website", "instagram","website_v", "instagram_v", "priceRange"].includes(input.name)) {
                input.removeAttribute("required"); // These fields are always optional
            } else {
                isRequired ? input.setAttribute("required", "true") : input.removeAttribute("required");
            }
        });
    }

    roleSelect.addEventListener("change", toggleFields);
    toggleFields(); // Initial check to hide fields on page load

    // Form Validation
    form.addEventListener("submit", function (event) {
        let errors = [];

        const fields = {
            username: "Username is required",
            email: "Enter a valid email address",
            phone: "Enter a valid 10-digit phone number",
            password: "Password must be at least 6 characters long",
            location: "Location is required",
        };

        Object.keys(fields).forEach(name => {
            const input = document.querySelector(`input[name='${name}']`);
            if (!validateField(input, fields[name])) {
                errors.push(fields[name]);
            }
        });

        // Organizer specific validation
        if (roleSelect.value === "organizer") {
            const companyName = document.querySelector("input[name='companyName']");
            const experience = document.querySelector("input[name='experience']");
            if (!validateField(companyName, "Company Name is required")) errors.push("Company Name is required");
            if (!validateExperience(experience)) errors.push("Enter a valid experience");
        }

        // Vendor specific validation
        if (roleSelect.value === "vendor") {
            const businessName = document.querySelector("input[name='businessName']");
            const service = document.querySelector("select[name='service']");
            if (!validateField(businessName, "Business Name is required")) errors.push("Business Name is required");
            if (!validateSelect(service, "Please select a service type")) errors.push("Please select a service type");
        }

        if (errors.length > 0) {
            event.preventDefault();
            displayErrors(errors);
        }
    });

    // Validation Functions
    function validateField(field, message) {
        if (!field.value.trim()) {
            showError(field, message);
            return false;
        }
        clearError(field);
        return true;
    }

    function validateEmail(email) {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return validatePattern(email, emailPattern, "Enter a valid email address");
    }

    function validatePhone(phone) {
        const phonePattern = /^[0-9]{10}$/;
        return validatePattern(phone, phonePattern, "Enter a valid 10-digit phone number");
    }

    function validatePassword(password) {
        if (password.value.trim().length < 6) {
            showError(password, "Password must be at least 6 characters long");
            return false;
        }
        clearError(password);
        return true;
    }

    function validateExperience(experience) {
        if (!experience.value.trim() || isNaN(experience.value) || parseInt(experience.value) < 0) {
            showError(experience, "Enter a valid experience (0 or more years)");
            return false;
        }
        clearError(experience);
        return true;
    }

    function validateSelect(select, message) {
        if (!select.value.trim()) {
            showError(select, message);
            return false;
        }
        clearError(select);
        return true;
    }

    function validatePattern(field, pattern, message) {
        if (!pattern.test(field.value.trim())) {
            showError(field, message);
            return false;
        }
        clearError(field);
        return true;
    }

    function showError(field, message) {
        field.style.border = "2px solid red";
        let errorMsg = field.nextElementSibling;
        if (!errorMsg || !errorMsg.classList.contains("error-message")) {
            errorMsg = document.createElement("div");
            errorMsg.classList.add("error-message");
            errorMsg.style.color = "red";
            errorMsg.style.fontSize = "12px";
            errorMsg.innerText = message;
            field.parentNode.insertBefore(errorMsg, field.nextSibling);
        } else {
            errorMsg.innerText = message;
        }
    }

    function clearError(field) {
        field.style.border = "";
        let errorMsg = field.nextElementSibling;
        if (errorMsg && errorMsg.classList.contains("error-message")) {
            errorMsg.remove();
        }
    }

    function displayErrors(errors) {
        alert("Please fix the following errors:\n" + errors.join("\n"));
    }
});
