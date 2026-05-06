$(document).ready(function () {
  $("#registerForm").submit(function (e) {
    e.preventDefault();

    let first_name = $("#firstName").val().trim();
    let last_name = $("#lastName").val().trim();
    let email = $("#email").val().trim();
    let phone = $("#phone").val().trim();
    let dob = $("#dob").val();
    let pan = $("#pan").val().trim().toUpperCase();
    let password = $("#password").val().trim();


    $(".error").text("");

    let isValid = true;

    if (first_name === "") {
      $("#firstName").next(".error").text("First name is required");
      isValid = false;
    }

    if (last_name === "") {
      $("#lastName").next(".error").text("Last name is required");
      isValid = false;
    }

    if (email === "") {
      $("#email").next(".error").text("Email is required");
      isValid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      $("#email").next(".error").text("Invalid email format");
      isValid = false;
    }

    if (phone === "") {
      $("#phone").next(".error").text("Phone is required");
      isValid = false;
    } else if (!/^[6-9][0-9]{9}$/.test(phone)) {
      $("#phone").next(".error").text("Invalid phone number");
      isValid = false;
    }

    if (dob === "") {
      $("#dob").next(".error").text("Date of birth is required");
      isValid = false;
    }

    if (pan === "") {
      $("#pan").next(".error").text("PAN is required");
      isValid = false;
    } else if (!/^[A-Z]{5}[0-9]{4}[A-Z]$/.test(pan)) {
      $("#pan").next(".error").text("Invalid PAN format");
      isValid = false;
    }

    if (password === "") {
      $("#password").next(".error").text("Password is required");
      isValid = false;
    } else if (password.length < 6) {
      $("#password").next(".error").text("Minimum 6 characters");
      isValid = false;
    }

    if (!isValid) return;

    // AJAX call
    $.ajax({
     url: "api/register.php",
      type: "POST",
      data: {
        first_name: first_name,
        last_name: last_name,
        email: email,
        phone: phone,
        dob: dob,
        pan: pan,
        password: password,
      },
      success: function (res) {
        if (res.status) {
          Swal.fire("Success", res.message, "success");

          setTimeout(() => {
            window.location.href = "login.html";
          }, 1500);
        } else {
          Swal.fire("Error", res.message, "error");
        }
      },
      error: function () {
        Swal.fire("Error", "Server error", "error");
      },
    });
  });
});