$(document).ready(function () {
  let token = localStorage.getItem("token");

  if (!token) {
    window.location.href = "login.html";
    return;
  }

  let user = JSON.parse(localStorage.getItem("user"));

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

  $("#logoutBtn").click(function () {
    localStorage.clear();
    window.location.href = "login.html";
  });

  function loadDashCompanies() {
    let user = JSON.parse(localStorage.getItem("user"));

    $.ajax({
      url: "api/company/list.php",
      method: "GET",
      data: {
      user_id: user.id
    },
      success: function (res) {
        if (res.status) {
          $("#dashCompany").html(`<option value="">Select Company</option>`);

          $.each(res.data, function (i, company) {
            $("#dashCompany").append(`
              <option value="${company.id}">${company.company_name}</option>
            `);
          });
        }
      },
      error: function () {
        Swal.fire("Error", "Unable to load companies", "error");
      },
    });
  }

  loadDashCompanies();

  $("#dashCompany").change(function () {
    let company_id = $(this).val();

    if (company_id === "") {
      $("#dashBody").html(`
        <tr>
          <td colspan="7" class="text-center text-muted">
            Select a company to load data
          </td>
        </tr>
      `);
      return;
    }

    loadEmployeesByCompany(company_id);
  });

  function loadEmployeesByCompany(company_id) {
    $.ajax({
      url: "api/employee/list.php",
      method: "GET",
      success: function (res) {
        if (res.status) {
          let html = "";
          let count = 0;

          $.each(res.data, function (i, emp) {
            if (emp.company_id == company_id) {
              count++;

              html += `
                <tr>
                  <td>${emp.first_name} ${emp.last_name}</td>
                  <td>${emp.email}</td>
                  <td>${emp.department_name}</td>
                  <td>${emp.join_date}</td>
                  <td>
                    <span class="badge bg-success">
                      ${emp.status ?? "active"}
                    </span>
                  </td>
                  <td class="text-end fw-bold">
                    ${emp.net ? emp.net : "--"}
                  </td>
                  <td class="text-end">
                    <button class="btn btn-sm btn-outline-primary viewSalary" data-id="${emp.id}">
                      View Salary
                    </button>
                  </td>
                </tr>
              `;
            }
          });

          if (count === 0) {
            html = `
              <tr>
                <td colspan="7" class="text-center text-muted">
                  No employees found for this company
                </td>
              </tr>
            `;
          }

          $("#dashBody").html(html);
        }
      },
      error: function () {
        Swal.fire("Error", "Unable to load employees", "error");
      },
    });
  }

  $(document).on("click", ".viewSalary", function () {
    let employee_id = $(this).data("id");

    $.ajax({
      url: "api/salary/details.php",
      method: "GET",
      data: {
        employee_id: employee_id,
      },
      success: function (res) {
        if (res.status && res.data.length > 0) {
          let html = "";
          let net = 0;
          let gross = 0;
          let deduction = 0;

          $.each(res.data, function (i, row) {
            let type = row.component_type == 1 ? "Earning" : "Deduction";

            html += `
              <tr>
                <td>${row.component_name}</td>
                <td>
                  <span class="badge ${row.component_type == 1 ? "bg-success" : "bg-danger"}">
                    ${type}
                  </span>
                </td>
                <td class="text-end">${row.amount}</td>
              </tr>
            `;

            net = row.net;
            gross = row.gross;
            deduction = row.deduction;
          });

          html += `
            <tr class="table-light">
              <td colspan="2" class="fw-bold">Gross</td>
              <td class="text-end fw-bold">${gross}</td>
            </tr>
            <tr class="table-light">
              <td colspan="2" class="fw-bold">Deduction</td>
              <td class="text-end fw-bold text-danger">${deduction}</td>
            </tr>
            <tr class="table-success">
              <td colspan="2" class="fw-bold">Net Salary</td>
              <td class="text-end fw-bold">${net}</td>
            </tr>
          `;

          $("#bdLines").html(html);
          $("#bdTotal").text(net);

          let modal = new bootstrap.Modal(
            document.getElementById("modalBreakdown")
          );
          modal.show();
        } else {
          Swal.fire("Info", "No salary details found for this employee", "info");
        }
      },
      error: function () {
        Swal.fire("Error", "Unable to load salary details", "error");
      },
    });
  });
});