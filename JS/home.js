$(document).ready(function () {
  let token = localStorage.getItem("token");

  if (!token) {
    window.location.href = "login.html";
  }

  let user = JSON.parse(localStorage.getItem("user"));
  let user_id = user.id;

  if (user) {
    let fullName = user.first_name + " " + user.last_name;
    let initials =
      user.first_name.charAt(0).toUpperCase() +
      user.last_name.charAt(0).toUpperCase();

    $("#navUserName").text(fullName);
    $("#dropdownUserName").text(fullName);
    $("#dropdownUserEmail").text(user.email);

    $("#userAvatar").text(initials);
    $("#dropdownAvatar").text(initials);
  }

  $(document).on("click", "#logoutBtn", function () {
    localStorage.clear();
    window.location.href = "login.html";
  });
  //   load company details like name and industry
  $("#companyForm").submit(function (e) {
    e.preventDefault();

    let company_name = $("#companyName").val().trim();
    let industry = $("#industry").val().trim();

    $(".error").text("");

    let isValid = true;

    if (company_name === "") {
      $("#companyName").next(".error").text("Company name required");
      isValid = false;
    }

    if (industry === "") {
      $("#industry").next(".error").text("Industry required");
      isValid = false;
    }

    if (!isValid) return;

    $.ajax({
      url: "api/company/create.php",
      method: "POST",
      data: {
        company_name: company_name,
        industry: industry,
        created_by: user_id
      },
      success: function (res) {
        if (res.status) {
          Swal.fire("Success", res.message, "success");

          $("#companyForm")[0].reset();

          let modal = bootstrap.Modal.getInstance(
            document.getElementById("companyModal"),
          );
          modal.hide();
        } else {
          Swal.fire("Error", res.message, "error");
        }
      },
      error: function () {
        Swal.fire("Error", "Server error", "error");
      },
    });
  });
  //  load the comapanies list in dropdown button for selecting the comapny
  function loadCompanies() {
    

    $.ajax({
      url: "api/company/list.php",
      method: "GET",
      data: {
      user_id: user.id
    },
      success: function (res) {
        if (res.status) {
          $("#departmentCompany").html(
            `<option value="">Select Company</option>`,
          );
          $("#empCompany").html(`<option value="">Select Company</option>`);
          $("#salaryCompany").html(`<option value="">Select Company</option>`);

          $.each(res.data, function (index, company) {
            $("#departmentCompany").append(
              `<option value="${company.id}">${company.company_name}</option>`,
            );
            $("#empCompany").append(
              `<option value="${company.id}">${company.company_name}</option>`,
            );
            $("#salaryCompany").append(
              `<option value="${company.id}">${company.company_name}</option>`,
            );
          });
        }
      },
    });
  }

  loadCompanies();

  //   this is department details for submiting the details
  $("#departmentForm").submit(function (e) {
    e.preventDefault();

    let department_name = $("#departmentName").val().trim();
    let company_id = $("#departmentCompany").val();

    $("#departmentForm .error").text("");

    let isValid = true;

    if (department_name === "") {
      $("#departmentName").next(".error").text("Department name required");
      isValid = false;
    }

    if (company_id === "") {
      $("#departmentCompany").next(".error").text("Company required");
      isValid = false;
    }

    if (!isValid) return;

    $.ajax({
      url: "api/department/create.php",
      method: "POST",
      data: {
        department_name: department_name,
        company_id: company_id,
      },
      success: function (res) {
        if (res.status) {
          Swal.fire("Success", res.message, "success");

          $("#departmentForm")[0].reset();

          let modal = bootstrap.Modal.getInstance(
            document.getElementById("departmentModal"),
          );
          modal.hide();

          loadDepartments();
        } else {
          Swal.fire("Error", res.message, "error");
        }
      },
      error: function () {
        Swal.fire("Error", "Server error", "error");
      },
    });
  });

  //   it is load the department name via dropdownd for selecting the department name etc..
  function loadDepartments() {
    $.ajax({
      url: "api/department/list.php",
      method: "GET",
      success: function (res) {
        if (res.status) {
          $("#empDepartment").html(
            `<option value="">Select Department</option>`,
          );

          $.each(res.data, function (index, dept) {
            $("#empDepartment").append(`
            <option value="${dept.id}">${dept.department_name} - ${dept.company_name}</option>
          `);
          });
        }
      },
    });
  }

  loadDepartments();

  $("#employeeForm").submit(function (e) {
    e.preventDefault();

    let first_name = $("#empFirstName").val().trim();
    let last_name = $("#empLastName").val().trim();
    let email = $("#empEmail").val().trim();
    let phone = $("#empPhone").val().trim();
    let company_id = $("#empCompany").val();
    let department_id = $("#empDepartment").val();
    let role = $("#empRole").val().trim();
    let join_date = $("#empJoinDate").val();

    let isValid = true;

    $("#employeeForm .error").text("");

    if (first_name === "") {
      $("#empFirstName").next(".error").text("First name required");
      isValid = false;
    }

    if (last_name === "") {
      $("#empLastName").next(".error").text("Last name required");
      isValid = false;
    }

    if (email === "") {
      $("#empEmail").next(".error").text("Email required");
      isValid = false;
    }

    if (phone === "") {
      $("#empPhone").next(".error").text("Phone required");
      isValid = false;
    }

    if (company_id === "") {
      $("#empCompany").next(".error").text("Select company");
      isValid = false;
    }

    if (department_id === "") {
      $("#empDepartment").next(".error").text("Select department");
      isValid = false;
    }

    if (role === "") {
      $("#empRole").next(".error").text("Role required");
      isValid = false;
    }

    if (join_date === "") {
      $("#empJoinDate").next(".error").text("Join date required");
      isValid = false;
    }

    if (!isValid) return;

    $.ajax({
      url: "api/employee/create.php",
      method: "POST",
      data: {
        first_name: first_name,
        last_name: last_name,
        email: email,
        phone: phone,
        company_id: company_id,
        department_id: department_id,
        role: role,
        join_date: join_date,
      },
      success: function (res) {
        if (res.status) {
          Swal.fire("Success", res.message, "success");

          $("#employeeForm")[0].reset();

          let modal = bootstrap.Modal.getInstance(
            document.getElementById("employeeModal"),
          );
          modal.hide();

          loadEmployees();
        } else {
          Swal.fire("Error", res.message, "error");
        }
      },
      error: function () {
        Swal.fire("Error", "Server error", "error");
      },
    });
  });

  function loadEmployees() {
    $.ajax({
      url: "api/employee/list.php",
      method: "GET",
      success: function (res) {
        if (res.status) {
          $("#salaryEmployee").html(
            `<option value="">Select Employee</option>`,
          );

          $.each(res.data, function (index, emp) {
            $("#salaryEmployee").append(`
            <option value="${emp.id}">
              ${emp.first_name} ${emp.last_name} - ${emp.company_name}
            </option>
          `);
          });
        }
      },
    });
  }

  loadEmployees();

  // Load salary components
  let salaryComponents = [];

  function loadSalaryComponents() {
    $.ajax({
      url: "api/salary/components.php",
      method: "GET",
      success: function (res) {
        if (res.status) {
          salaryComponents = res.data;
        }
      },
    });
  }

  loadSalaryComponents();

  $("#salaryModal").on("show.bs.modal", function () {
    $("#salaryForm")[0].reset();
    $("#salaryLines").html("");
  });

  function addSalaryLine() {
    let options = `<option value="">Select Component</option>`;

    $.each(salaryComponents, function (index, comp) {
      options += `<option value="${comp.id}">${comp.component_name}</option>`;
    });

    $("#salaryLines").append(`
    <div class="row g-2 mb-2 salary-line">
      <div class="col-md-6">
        <select class="form-select salary-component">
          ${options}
        </select>
      </div>

      <div class="col-md-4">
        <input type="number" class="form-control salary-amount" placeholder="Amount">
      </div>

      <div class="col-md-2">
        <button type="button" class="btn btn-danger w-100 removeSalaryLine">X</button>
      </div>
    </div>
  `);
  }

  $("#addSalaryLine").click(function () {
    addSalaryLine();
  });

  $(document).on("click", ".removeSalaryLine", function () {
    $(this).closest(".salary-line").remove();
  });

  $("#salaryForm").submit(function (e) {
    e.preventDefault();

    let employee_id = $("#salaryEmployee").val();
    let salary_month = $("#salaryMonth").val();
    let payment_date = $("#paymentDate").val();

    let components = [];
    let usedComponents = [];

    $(".salary-line").each(function () {
      let component_id = $(this).find(".salary-component").val();
      let amount = $(this).find(".salary-amount").val();

      if (component_id && amount && amount > 0) {
       
        if (usedComponents.includes(component_id)) {
          Swal.fire("Error", "Duplicate component not allowed", "error");
          components = [];
          return false;
        }

        usedComponents.push(component_id);

        components.push({
          component_id: component_id,
          amount: parseFloat(amount), 
        });
      }
    });

    if (employee_id === "" || salary_month === "" || payment_date === "") {
      Swal.fire("Error", "All fields are required", "error");
      return;
    }

    if (components.length === 0) {
      Swal.fire("Error", "Add at least one salary component", "error");
      return;
    }

    console.log(components); 

    $.ajax({
      url: "api/salary/create.php",
      method: "POST",
      data: {
        employee_id,
        salary_month,
        payment_date,
        components: JSON.stringify(components),
      },
      success: function (res) {
        if (res.status) {
          Swal.fire("Success", `Net Salary: ${res.data.net}`, "success");

          $("#salaryForm")[0].reset();
          $("#salaryLines").html("");

          let modal = bootstrap.Modal.getInstance(
            document.getElementById("salaryModal"),
          );
          if (modal) modal.hide();
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
