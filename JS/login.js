$(document).ready(function () {
  $("#loginForm").submit(function (e) {
    e.preventDefault();

    let email = $("#email").val().trim();
    let password = $("#password").val().trim();

    $(".error").text("");

    let isValid = true;

    if (email === "") {
      $("#email").next(".error").text("Email is required");
      isValid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      $("#email").next(".error").text("Invalid email format");
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

    $.ajax({
      url: "api/login.php",
      method: "POST",
      data: {
        email: email,
        password: password,
      },
      success: function (res) {
        if (res.status) {
          localStorage.setItem("token", res.data.token);
          localStorage.setItem("user", JSON.stringify(res.data));

          Swal.fire("Success", res.message, "success");

          setTimeout(function () {
            window.location.href = "dashboard.html";
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

  $("#btnSendOTP").click(function(e) {
    e.preventDefault();
    let email = $("#forgotEmail").val().trim();
    if (!email) {
      Swal.fire("Error", "Please enter your email", "error");
      return;
    }
    
    let btn = $(this);
    btn.text("Sending...").prop("disabled", true);

    $.ajax({
      url: "api/auth/forgot_password.php",
      method: "POST",
      data: { email: email },
      success: function(res) {
        if (res.status) {
          Swal.fire("Sent!", res.message, "success");
          if (res.data && res.data.otp) {
            console.log("===============================");
            console.log("OTP IS: " + res.data.otp);
            console.log("===============================");
          }
          
          $("#stepEmail").hide();
          $("#stepReset").fadeIn();
        } else {
          Swal.fire("Error", res.message, "error");
          btn.text("Send OTP").prop("disabled", false);
        }
      },
      error: function() {
        Swal.fire("Error", "Failed to send OTP", "error");
        btn.text("Send OTP").prop("disabled", false);
      }
    });
  });

  $("#btnResetPassword").click(function(e) {
    e.preventDefault();
    let email = $("#forgotEmail").val().trim();
    let otp = $("#resetOTP").val().trim();
    let newPassword = $("#newPassword").val().trim();

    if (!otp || !newPassword) {
      Swal.fire("Error", "Please fill all fields", "error");
      return;
    }

    let btn = $(this);
    btn.text("Resetting...").prop("disabled", true);

    $.ajax({
      url: "api/auth/reset_password.php",
      method: "POST",
      data: { email: email, otp: otp, new_password: newPassword },
      success: function(res) {
        if (res.status) {
          Swal.fire("Success!", res.message, "success").then(() => {
            window.location.reload();
          });
        } else {
          Swal.fire("Error", res.message, "error");
          btn.text("Reset Password").prop("disabled", false);
        }
      },
      error: function() {
        Swal.fire("Error", "Failed to reset password", "error");
        btn.text("Reset Password").prop("disabled", false);
      }
    });
  });

  $("#forgotModal").on('hidden.bs.modal', function () {
    $("#stepEmail").show();
    $("#stepReset").hide();
    $("#forgotEmail, #resetOTP, #newPassword").val("");
    $("#btnSendOTP").text("Send OTP").prop("disabled", false);
    $("#btnResetPassword").text("Reset Password").prop("disabled", false);
  });
});