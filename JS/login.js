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
            window.location.href = "home.html";
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