$(document).ready(function () {

  let token = localStorage.getItem("token");
  if (!token) { window.location.href = "login.html"; return; }

  let user = JSON.parse(localStorage.getItem("user"));
  let user_id = user.id;
  let fullName = user.first_name + " " + user.last_name;
  let initials = user.first_name.charAt(0).toUpperCase() + user.last_name.charAt(0).toUpperCase();
  let role = user.role || "Admin";

  let hr = new Date().getHours();
  let greet = hr < 12 ? "Good Morning" : hr < 17 ? "Good Afternoon" : "Good Evening";

  $("#avSm, #avLg").text(initials);
  $("#navFirst").text(user.first_name);
  $("#navGreet").html(greet + ", <b>" + user.first_name + "</b> &nbsp;👋");
  $("#navName").text(fullName);
  $("#navRole").text(role);
  $("#dName").text(fullName);
  $("#dEmail").text(user.email);
  $("#dRole").text("Role: " + role);

  $("#profBtn").on("click", function (e) {
    e.stopPropagation();
    $("#pdrop").toggleClass("open");
  });
  $(document).on("click", function () { $("#pdrop").removeClass("open"); });
  $("#pdrop").on("click", function (e) { e.stopPropagation(); });

  $("#logoutBtn").on("click", function () {
    localStorage.clear();
    window.location.href = "login.html";
  });

  $("#hamBtn").on("click", function () {
    if ($(window).width() <= 768) {
      $("#sidebar").toggleClass("mob");
    } else {
      $("#sidebar").toggleClass("closed");
      $("#main").toggleClass("wide");
    }
  });

  $(".si-parent").on("click", function () {
    let grp = $(this).data("group");
    let children = $("#grp-" + grp);
    let isOpen = children.hasClass("open");
    $(".si-children").removeClass("open");
    $(".si-parent").removeClass("open");
    if (!isOpen) {
      children.addClass("open");
      $(this).addClass("open");
    }
  });

  $(".si-child").on("click", function () {
    $(".si, .si-child").removeClass("on");
    $(this).addClass("on");
    let s = $(this).data("sec");
    $(".sec").removeClass("on");
    $("#sec-" + s).addClass("on");
    if ($(window).width() <= 768) $("#sidebar").removeClass("mob");
    if (s === "list-company")    { compState.open = false; loadListCompany(); }
    if (s === "list-department") { deptState.open = false; loadListDept(); }
    if (s === "list-employee")   { empState.open  = false; loadListEmp(); }
    if (s === "list-salary")     { salState.open  = false; loadListSalary(); }
  });


  $("[data-sec='dashboard']").on("click", function () {
    $(".si, .si-child").removeClass("on");
    $(this).addClass("on");
    $(".sec").removeClass("on");
    $("#sec-dashboard").addClass("on");
    $(".si-children").removeClass("open");
    $(".si-parent").removeClass("open");
  });


  function counter(sel, target) {
    let cur = 0;
    let step = Math.max(1, Math.ceil(target / 28));
    let t = setInterval(function () {
      cur = Math.min(cur + step, target);
      $(sel).text(cur);
      if (cur >= target) clearInterval(t);
    }, 28);
  }

  function loadStats() {
    $.ajax({
      url: "api/dashboard/stats.php",
      method: "GET",
      data: { user_id: user_id },
      success: function (res) {
        if (!res.status) return;
        let d = res.data;

        counter("#stEmpTotal", d.total_employees);
        counter("#stEmpActive", d.active_employees);
        counter("#stComp", d.total_companies);
        counter("#stIndustry", d.total_industries);
        counter("#stDept", d.total_departments);
        counter("#stSalCount", d.salary_processed);
        counter("#stPaidEmp", d.paid_employees);
        $("#stSalTotal").text("₹" + Number(d.total_salary).toLocaleString("en-IN"));

        let eh = "";
        if (d.recent_employees.length) {
          $.each(d.recent_employees, function (i, e) {
            eh += `<tr>
              <td>${e.first_name} ${e.last_name}</td>
              <td>${e.company_name}</td>
              <td>${e.role}</td>
              <td><span class="bactive">${e.status || "active"}</span></td>
            </tr>`;
          });
        } else {
          eh = `<tr><td colspan="4" class="text-center py-3" style="color:var(--mu)">No employees yet</td></tr>`;
        }
        $("#tblEmp").html(eh);

        let sh = "";
        if (d.recent_salaries.length) {
          $.each(d.recent_salaries, function (i, s) {
            sh += `<tr>
              <td>${s.first_name} ${s.last_name}</td>
              <td>${s.salary_month}</td>
              <td class="bamt">₹${Number(s.net).toLocaleString("en-IN")}</td>
            </tr>`;
          });
        } else {
          sh = `<tr><td colspan="3" class="text-center py-3" style="color:var(--mu)">No salaries yet</td></tr>`;
        }
        $("#tblSal").html(sh);
      },
      error: function () {
        Swal.fire("Error", "Could not load dashboard data", "error");
      }
    });
  }

  loadStats();

  // Pagination
  let compState = { page: 1, status: 'active', grpStart: 1, open: false };
  let deptState = { page: 1, status: 'active', grpStart: 1, open: false };
  let empState  = { page: 1, status: 'active', grpStart: 1, open: false };
  let salState  = { page: 1, grpStart: 1, open: false };
  const PER_PAGE = 5, GRP = 10;

  function renderPagination(containerId, meta, state, loadFn) {
    if (!meta || meta.total_pages <= 1) { $('#' + containerId).html(''); return; }
    const { page, total_pages, total } = meta;
    const $c = $('#' + containerId);

    if (!state.open) {
      // ── Collapsed: show only "Load More" button ──
      $c.html(`
        <div class="pg-wrap">
          <button class="pg-loadmore pg-expand"><i class="bi bi-grid-3x3-gap me-1"></i>Load More Pages</button>
          <span class="pg-info">Page ${page}/${total_pages} &bull; ${total} records</span>
        </div>`);
      $c.find('.pg-expand').on('click', function () {
        state.open = true;
        renderPagination(containerId, meta, state, loadFn);
      });
    } else {
      // ── Expanded: show page number group ──
      let s = state.grpStart, e = Math.min(s + GRP - 1, total_pages);
      let h = '<div class="pg-wrap">';
      if (page > 1) h += `<button class="pg-btn pg-nav" data-p="${page - 1}">&#8249;</button>`;
      if (s > 1)    h += `<button class="pg-loadmore pg-grp" data-gs="${s - GRP}">&#8249; Prev</button>`;
      for (let i = s; i <= e; i++) {
        h += `<button class="pg-btn ${i === page ? 'pg-active' : ''}" data-p="${i}">${i}</button>`;
      }
      if (e < total_pages) h += `<button class="pg-loadmore pg-grp" data-gs="${e + 1}">Load More &#8250;</button>`;
      if (page < total_pages) h += `<button class="pg-btn pg-nav" data-p="${page + 1}">&#8250;</button>`;
      h += `<span class="pg-info">Page ${page}/${total_pages} &bull; ${total} records</span></div>`;
      $c.html(h);
      $c.find('.pg-btn').on('click', function () {
        state.page = parseInt($(this).data('p')); loadFn();
      });
      $c.find('.pg-grp').on('click', function () {
        state.grpStart = parseInt($(this).data('gs'));
        state.page = state.grpStart; loadFn();
      });
    }
  }

  // active inactive
  window.switchCompTab = function (st) {
    compState.status = st; compState.page = 1; compState.grpStart = 1; compState.open = false;
    $('#compTabActive').removeClass('on');
    $('#compTabInactive').removeClass('on');
    if (st === 'active') $('#compTabActive').addClass('on');
    else $('#compTabInactive').addClass('on');
    loadListCompany();
  };
  window.switchDeptTab = function (st) {
    deptState.status = st; deptState.page = 1; deptState.grpStart = 1; deptState.open = false;
    $('#deptTabActive').removeClass('on');
    $('#deptTabInactive').removeClass('on');
    if (st === 'active') $('#deptTabActive').addClass('on');
    else $('#deptTabInactive').addClass('on');
    loadListDept();
  };
  window.switchEmpTab = function (st) {
    empState.status = st; empState.page = 1; empState.grpStart = 1; empState.open = false;
    $('#empTabActive').removeClass('on');
    $('#empTabInactive').removeClass('on');
    if (st === 'active') $('#empTabActive').addClass('on');
    else $('#empTabInactive').addClass('on');
    loadListEmp();
  };


  // List Companies
  function loadListCompany() {
    $.ajax({
      url: 'api/company/list.php', method: 'GET',
      data: { user_id, page: compState.page, per_page: PER_PAGE, status: compState.status },
      success: function (res) {
        const isInactive = compState.status === 'inactive';
        if (!res.status || !res.data || !res.data.length) {
          $('#tblListCompany').html(`<tr><td colspan="5" class="text-center py-3" style="color:var(--mu)">No ${compState.status} companies</td></tr>`);
          $('#pgCompany').html(''); return;
        }
        let h = '', offset = (compState.page - 1) * PER_PAGE;
        $.each(res.data, function (i, c) {
          let cData = encodeURIComponent(JSON.stringify(c));
          let actions = isInactive
            ? `<button class="btn-restore btn-restore-comp" data-id="${c.id}"><i class="bi bi-arrow-counterclockwise me-1"></i>Restore</button>`
            : `<button class="btn-edit-comp" data-info='${cData}' style="background:none;border:none;color:#38bdf8;cursor:pointer;margin-right:8px;"><i class="bi bi-pencil"></i></button>
               <button class="btn-del-comp" data-id="${c.id}" style="background:none;border:none;color:#ef4444;cursor:pointer;"><i class="bi bi-trash"></i></button>`;
          h += `<tr><td>${offset+i+1}</td><td>${c.company_name}</td><td>${c.industry}</td><td>${(c.created_at||'').split('T')[0]||'—'}</td><td>${actions}</td></tr>`;
        });
        $('#tblListCompany').html(h);
        renderPagination('pgCompany', res.meta, compState, loadListCompany);
      }
    });
  }

  //List Departments 
  function loadListDept() {
    $.ajax({
      url: 'api/department/list.php', method: 'GET',
      data: { user_id, page: deptState.page, per_page: PER_PAGE, status: deptState.status },
      success: function (res) {
        const isInactive = deptState.status === 'inactive';
        if (!res.status || !res.data || !res.data.length) {
          $('#tblListDept').html(`<tr><td colspan="4" class="text-center py-3" style="color:var(--mu)">No ${deptState.status} departments</td></tr>`);
          $('#pgDept').html(''); return;
        }
        let h = '', offset = (deptState.page - 1) * PER_PAGE;
        $.each(res.data, function (i, d) {
          let dData = encodeURIComponent(JSON.stringify(d));
          let actions = isInactive
            ? `<button class="btn-restore btn-restore-dept" data-id="${d.id}"><i class="bi bi-arrow-counterclockwise me-1"></i>Restore</button>`
            : `<button class="btn-edit-dept" data-info='${dData}' style="background:none;border:none;color:#38bdf8;cursor:pointer;margin-right:8px;"><i class="bi bi-pencil"></i></button>
               <button class="btn-del-dept" data-id="${d.id}" style="background:none;border:none;color:#ef4444;cursor:pointer;"><i class="bi bi-trash"></i></button>`;
          h += `<tr><td>${offset+i+1}</td><td>${d.department_name}</td><td>${d.company_name}</td><td>${actions}</td></tr>`;
        });
        $('#tblListDept').html(h);
        renderPagination('pgDept', res.meta, deptState, loadListDept);
      }
    });
  }

  //List Employees
  function loadListEmp() {
    $.ajax({
      url: 'api/employee/list.php', method: 'GET',
      data: { user_id, page: empState.page, per_page: PER_PAGE, status: empState.status },
      success: function (res) {
        const isInactive = empState.status === 'inactive';
        if (!res.status || !res.data || !res.data.length) {
          $('#tblListEmp').html(`<tr><td colspan="8" class="text-center py-3" style="color:var(--mu)">No ${empState.status} employees</td></tr>`);
          $('#pgEmp').html(''); return;
        }
        let h = '', offset = (empState.page - 1) * PER_PAGE;
        $.each(res.data, function (i, e) {
          let eData = encodeURIComponent(JSON.stringify(e));
          let badge = e.status === 'inactive' ? `<span class="binactive">inactive</span>` : `<span class="bactive">active</span>`;
          let actions = isInactive
            ? `<button class="btn-restore btn-restore-emp" data-id="${e.id}"><i class="bi bi-arrow-counterclockwise me-1"></i>Restore</button>`
            : `<button class="btn-edit-emp" data-info='${eData}' style="background:none;border:none;color:#38bdf8;cursor:pointer;margin-right:8px;"><i class="bi bi-pencil"></i></button>
               <button class="btn-del-emp" data-id="${e.id}" style="background:none;border:none;color:#ef4444;cursor:pointer;"><i class="bi bi-trash"></i></button>`;
          h += `<tr><td>${offset+i+1}</td><td>${e.first_name} ${e.last_name}</td><td>${e.email}</td><td>${e.company_name}</td><td>${e.department_name}</td><td>${e.role}</td><td>${badge}</td><td>${actions}</td></tr>`;
        });
        $('#tblListEmp').html(h);
        renderPagination('pgEmp', res.meta, empState, loadListEmp);
      }
    });
  }

  // Delete Deactivate Employee 
  $(document).on('click', '.btn-del-emp', function () {
    let emp_id = $(this).data('id');
    Swal.fire({ title: 'Deactivate employee?', text: 'They will be moved to Inactive tab.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Yes, deactivate' })
    .then(r => { if (r.isConfirmed) {
      $.ajax({ url: 'api/employee/delete.php', method: 'POST', data: { employee_id: emp_id },
        success: function (res) {
          if (res.status) { Swal.fire('Deactivated!', res.message, 'success'); loadListEmp(); loadStats(); loadEmployees(); }
          else Swal.fire('Error', res.message, 'error');
        }
      });
    }});
  });

  //  Delete Deactivat Company
  $(document).on('click', '.btn-del-comp', function () {
    let comp_id = $(this).data('id');
    Swal.fire({ title: 'Deactivate company?', text: 'Company, its departments and employees will be set to Inactive.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Yes, deactivate' })
    .then(r => { if (r.isConfirmed) {
      $.ajax({ url: 'api/company/delete.php', method: 'POST', data: { company_id: comp_id },
        success: function (res) {
          if (res.status) { Swal.fire('Deactivated!', res.message, 'success'); loadListCompany(); loadStats(); loadCompanies(); }
          else Swal.fire('Error', res.message, 'error');
        }
      });
    }});
  });

  // List Salaries 
  function loadListSalary() {
    $.ajax({
      url: 'api/salary/list.php', method: 'GET',
      data: { user_id, page: salState.page, per_page: PER_PAGE },
      success: function (res) {
        if (!res.status || !res.data || !res.data.length) {
          $('#tblListSal').html(`<tr><td colspan="8" class="text-center py-3" style="color:var(--mu)">No salaries yet</td></tr>`);
          $('#pgSal').html(''); return;
        }
        let h = '', offset = (salState.page - 1) * PER_PAGE;
        $.each(res.data, function (i, s) {
          h += `<tr>
            <td>${offset+i+1}</td><td>${s.first_name} ${s.last_name}</td><td>${s.company_name}</td>
            <td>${s.salary_month}</td>
            <td>₹${Number(s.gross).toLocaleString('en-IN')}</td>
            <td style="color:#f87171">₹${Number(s.deduction).toLocaleString('en-IN')}</td>
            <td class="bamt">₹${Number(s.net).toLocaleString('en-IN')}</td>
            <td>
              <button class="btn-view-sal" data-info='${encodeURIComponent(JSON.stringify(s))}' style="background:rgba(56,189,248,0.1);border:1px solid rgba(56,189,248,0.2);color:#38bdf8;border-radius:6px;padding:4px 10px;font-size:11px;font-weight:600;cursor:pointer;margin-right:6px;">View</button>
              <button class="btn-edit-sal" data-info='${encodeURIComponent(JSON.stringify(s))}' style="background:none;border:none;color:#38bdf8;cursor:pointer;margin-right:6px;"><i class="bi bi-pencil"></i></button>
              <button class="btn-del-sal" data-id="${s.id}" style="background:none;border:none;color:#ef4444;cursor:pointer;"><i class="bi bi-trash"></i></button>
            </td></tr>`;
        });
        $('#tblListSal').html(h);
        renderPagination('pgSal', res.meta, salState, loadListSalary);
      }
    });
  }

  $(document).on("click", ".btn-view-sal", function () {
    let data = JSON.parse(decodeURIComponent($(this).data("info")));

    let earningsHtml = "";
    let deductionsHtml = "";

    if (data.components) {
      try {
        let comps = typeof data.components === 'string' ? JSON.parse(data.components) : data.components;
        comps.forEach(c => {
          let html = `
                    <div class="d-flex justify-content-between py-1 border-bottom" style="border-color:var(--bdr)!important; font-size: 13px;">
                        <span style="color:var(--mu);">${c.name}</span>
                        <span style="${c.type === 'deduction' ? 'color:#f87171;' : ''}">₹${Number(c.amount).toLocaleString("en-IN")}</span>
                    </div>
                `;
          if (c.type === 'deduction') {
            deductionsHtml += html;
          } else {
            earningsHtml += html;
          }
        });
      } catch (e) { }
    }

    let h = `
        <div class="mb-3">
            <h6 style="font-size:18px;font-weight:700;margin-bottom:2px;">${data.first_name} ${data.last_name}</h6>
            <div style="font-size:12px;color:var(--mu);"><i class="bi bi-building me-1"></i>${data.company_name}</div>
        </div>
        <div class="d-flex justify-content-between mb-4 p-3 rounded" style="background:rgba(255,255,255,0.03);">
            <div>
                <div style="font-size:10px;color:var(--mu);text-transform:uppercase;letter-spacing:1px;">Salary Month</div>
                <div style="font-weight:600;font-size:14px;">${data.salary_month}</div>
            </div>
            <div class="text-end">
                <div style="font-size:10px;color:var(--mu);text-transform:uppercase;letter-spacing:1px;">Payment Date</div>
                <div style="font-weight:600;font-size:14px;">${data.payment_date || "—"}</div>
            </div>
        </div>
        
        <div class="row g-3 mb-3">
            <div class="col-6">
                <div style="font-size:12px;font-weight:700;margin-bottom:8px;color:var(--g2);text-transform:uppercase;letter-spacing:1px;">Earnings</div>
                ${earningsHtml}
                <div class="d-flex justify-content-between py-2 mt-2">
                    <span style="color:var(--tx);font-weight:700;font-size:13px;">Gross Salary</span>
                    <span style="color:var(--tx);font-weight:700;font-size:13px;">₹${Number(data.gross).toLocaleString("en-IN")}</span>
                </div>
            </div>
            <div class="col-6" style="border-left: 1px solid var(--bdr);">
                <div style="font-size:12px;font-weight:700;margin-bottom:8px;color:#f87171;text-transform:uppercase;letter-spacing:1px;">Deductions</div>
                ${deductionsHtml}
                <div class="d-flex justify-content-between py-2 mt-2">
                    <span style="color:var(--tx);font-weight:700;font-size:13px;">Total Deductions</span>
                    <span style="color:#f87171;font-weight:700;font-size:13px;">-₹${Number(data.deduction).toLocaleString("en-IN")}</span>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between p-3 rounded mt-4" style="background:rgba(56,239,125,.1); border:1px solid rgba(56,239,125,.2);">
            <div style="font-weight:700;color:var(--g2);font-size:16px;">Net Pay</div>
            <div style="font-weight:800;font-size:20px;color:var(--g2);">₹${Number(data.net).toLocaleString("en-IN")}</div>
        </div>
    `;

    $("#salaryModalBody").html(h);
    let modal = new bootstrap.Modal(document.getElementById("salaryModal"));
    modal.show();
  });

  function loadCompanies() {
    $.ajax({
      url: "api/company/list.php",
      method: "GET",
      data: { user_id: user_id },
      success: function (res) {
        if (!res.status) return;
        window.companies_data = res.data;
        let opts = `<option value="">Select Company</option>`;
        $.each(res.data, function (i, c) {
          opts += `<option value="${c.id}">${c.company_name}</option>`;
        });
        $("#departmentCompany, #empCompany, #salaryCompany").html(opts);
      }
    });
  }

  loadCompanies();

  function loadDepartments() {
    $.ajax({
      url: "api/department/list.php",
      method: "GET",
      data: { user_id: user_id },
      success: function (res) {
        if (!res.status) return;
        let opts = `<option value="">Select Department</option>`;
        $.each(res.data, function (i, d) {
          opts += `<option value="${d.id}">${d.department_name} - ${d.company_name}</option>`;
        });
        $("#empDepartment").html(opts);
      }
    });
  }

  loadDepartments();

  function loadEmployees() {
    $.ajax({
      url: "api/employee/list.php",
      method: "GET",
      data: { user_id: user_id },
      success: function (res) {
        if (!res.status) return;
        let opts = `<option value="">Select Employee</option>`;
        $.each(res.data, function (i, e) {
          opts += `<option value="${e.id}">${e.first_name} ${e.last_name} — ${e.company_name}</option>`;
        });
        $("#salaryEmployee").html(opts);
      }
    });
  }

  loadEmployees();

  let comps = [];
  $.ajax({
    url: "api/salary/components.php",
    method: "GET",
    success: function (res) { if (res.status) comps = res.data; }
  });

  $("#companyForm").submit(function (e) {
    e.preventDefault();
    let name = $("#companyName").val().trim();
    let ind = $("#industry").val().trim();
    $(".error").text("");
    let ok = true;
    if (!name) { $("#companyName").next(".error").text("Required"); ok = false; }
    if (!ind) { $("#industry").next(".error").text("Required"); ok = false; }
    if (!ok) return;
    $.ajax({
      url: "api/company/create.php", method: "POST",
      data: { company_name: name, industry: ind, created_by: user_id },
      success: function (res) {
        if (res.status) {
          Swal.fire("Success", res.message, "success");
          $("#companyForm")[0].reset();
          loadCompanies(); loadStats();
        } else { Swal.fire("Error", res.message, "error"); }
      },
      error: function () { Swal.fire("Error", "Server error", "error"); }
    });
  });

  $("#departmentForm").submit(function (e) {
    e.preventDefault();
    let name = $("#departmentName").val().trim();
    let cid = $("#departmentCompany").val();
    $("#departmentForm .error").text("");
    let ok = true;
    if (!name) { $("#departmentName").next(".error").text("Required"); ok = false; }
    if (!cid) { $("#departmentCompany").next(".error").text("Select a company"); ok = false; }
    if (!ok) return;
    $.ajax({
      url: "api/department/create.php", method: "POST",
      data: { department_name: name, company_id: cid },
      success: function (res) {
        if (res.status) {
          Swal.fire("Success", res.message, "success");
          $("#departmentForm")[0].reset();
          loadDepartments(); loadStats();
        } else { Swal.fire("Error", res.message, "error"); }
      },
      error: function () { Swal.fire("Error", "Server error", "error"); }
    });
  });

  $("#employeeForm").submit(function (e) {
    e.preventDefault();
    let fn = $("#empFirstName").val().trim();
    let ln = $("#empLastName").val().trim();
    let em = $("#empEmail").val().trim();
    let ph = $("#empPhone").val().trim();
    let cid = $("#empCompany").val();
    let did = $("#empDepartment").val();
    let rl = $("#empRole").val().trim();
    let jd = $("#empJoinDate").val();
    $("#employeeForm .error").text("");
    let ok = true;
    if (!fn) { $("#empFirstName").next(".error").text("Required"); ok = false; }
    if (!ln) { $("#empLastName").next(".error").text("Required"); ok = false; }
    if (!em) { $("#empEmail").next(".error").text("Required"); ok = false; }
    if (!ph) { $("#empPhone").next(".error").text("Required"); ok = false; }
    if (!cid) { $("#empCompany").next(".error").text("Select company"); ok = false; }
    if (!did) { $("#empDepartment").next(".error").text("Select department"); ok = false; }
    if (!rl) { $("#empRole").next(".error").text("Required"); ok = false; }
    if (!jd) { $("#empJoinDate").next(".error").text("Required"); ok = false; }
    if (!ok) return;
    $.ajax({
      url: "api/employee/create.php", method: "POST",
      data: { first_name: fn, last_name: ln, email: em, phone: ph, company_id: cid, department_id: did, role: rl, join_date: jd },
      success: function (res) {
        if (res.status) {
          Swal.fire("Success", res.message, "success");
          $("#employeeForm")[0].reset();
          loadEmployees(); loadStats();
        } else { Swal.fire("Error", res.message, "error"); }
      },
      error: function () { Swal.fire("Error", "Server error", "error"); }
    });
  });

  $("#addLine").on("click", function () {
    let opts = `<option value="">Select Component</option>`;
    $.each(comps, function (i, c) {
      opts += `<option value="${c.id}">${c.component_name}</option>`;
    });
    $("#salaryLines").append(`
      <div class="row g-2 slr">
        <div class="col-md-6"><select class="form-select sal-c">${opts}</select></div>
        <div class="col-md-4"><input type="number" class="form-control sal-a" placeholder="Amount"></div>
        <div class="col-md-2"><button type="button" class="btn-rm rmLine"><i class="bi bi-trash"></i></button></div>
      </div>`);
  });

  $(document).on("click", ".rmLine", function () {
    $(this).closest(".slr").remove();
  });

  $("#salaryForm").submit(function (e) {
    e.preventDefault();
    let eid = $("#salaryEmployee").val();
    let mon = $("#salaryMonth").val();
    let pdt = $("#paymentDate").val();
    if (!eid || !mon || !pdt) { Swal.fire("Error", "All fields required", "error"); return; }

    let lines = [], used = [], dup = false;
    $(".slr").each(function () {
      let cid = $(this).find(".sal-c").val();
      let amt = parseFloat($(this).find(".sal-a").val());
      if (cid && amt > 0) {
        if (used.includes(cid)) { dup = true; return false; }
        used.push(cid);
        lines.push({ component_id: cid, amount: amt });
      }
    });
    if (dup) { Swal.fire("Error", "Duplicate component", "error"); return; }
    if (!lines.length) { Swal.fire("Error", "Add at least one component", "error"); return; }

    $.ajax({
      url: "api/salary/create.php", method: "POST",
      data: { employee_id: eid, salary_month: mon, payment_date: pdt, components: JSON.stringify(lines) },
      success: function (res) {
        if (res.status) {
          Swal.fire("Success", "Net Salary: ₹" + res.data.net, "success");
          $("#salaryForm")[0].reset();
          $("#salaryLines").html("");
          loadStats();
        } else { Swal.fire("Error", res.message, "error"); }
      },
      error: function () { Swal.fire("Error", "Server error", "error"); }
    });
  });

  $(document).on("click", ".btn-edit-comp", function () {
    let data = JSON.parse(decodeURIComponent($(this).data("info")));
    $("#edit_company_id").val(data.id);
    $("#edit_company_name").val(data.company_name);
    $("#edit_industry").val(data.industry);
    new bootstrap.Modal(document.getElementById("editCompModal")).show();
  });

  $("#btnUpdateComp").click(function () {
    let id = $("#edit_company_id").val();
    let name = $("#edit_company_name").val().trim();
    let ind = $("#edit_industry").val().trim();
    if (!name || !ind) { Swal.fire("Error", "All fields required", "error"); return; }

    $.ajax({
      url: "api/company/update.php", method: "POST", data: { company_id: id, company_name: name, industry: ind },
      success: function (res) {
        if (res.status) {
          Swal.fire("Updated!", res.message, "success");
          bootstrap.Modal.getInstance(document.getElementById("editCompModal")).hide();
          loadListCompany(); loadCompanies();
        } else { Swal.fire("Error", res.message, "error"); }
      }
    });
  });

  $(document).on("click", ".btn-edit-dept", function () {
    let data = JSON.parse(decodeURIComponent($(this).data("info")));
    $("#edit_department_id").val(data.id);
    $("#edit_department_name").val(data.department_name);
    new bootstrap.Modal(document.getElementById("editDeptModal")).show();
  });

  $("#btnUpdateDept").click(function () {
    let id = $("#edit_department_id").val();
    let name = $("#edit_department_name").val().trim();
    if (!name) { Swal.fire("Error", "Department name required", "error"); return; }

    $.ajax({
      url: "api/department/update.php", method: "POST", data: { department_id: id, department_name: name },
      success: function (res) {
        if (res.status) {
          Swal.fire("Updated!", res.message, "success");
          bootstrap.Modal.getInstance(document.getElementById("editDeptModal")).hide();
          loadListDept();
        } else { Swal.fire("Error", res.message, "error"); }
      }
    });
  });

  $(document).on('click', '.btn-del-dept', function () {
    let dept_id = $(this).data('id');
    Swal.fire({ title: 'Deactivate department?', text: 'Department and its employees will be set to Inactive.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Yes, deactivate' })
    .then(r => { if (r.isConfirmed) {
      $.ajax({ url: 'api/department/delete.php', method: 'POST', data: { department_id: dept_id },
        success: function (res) {
          if (res.status) { Swal.fire('Deactivated!', res.message, 'success'); loadListDept(); loadStats(); }
          else Swal.fire('Error', res.message, 'error');
        }
      });
    }});
  });

  //  Restore handlers 
  $(document).on('click', '.btn-restore-comp', function () {
    $.ajax({ url: 'api/company/restore.php', method: 'POST', data: { company_id: $(this).data('id') },
      success: function (res) {
        if (res.status) { Swal.fire('Restored!', res.message, 'success'); loadListCompany(); loadStats(); loadCompanies(); }
        else Swal.fire('Error', res.message, 'error');
      }
    });
  });
  $(document).on('click', '.btn-restore-dept', function () {
    $.ajax({ url: 'api/department/restore.php', method: 'POST', data: { department_id: $(this).data('id') },
      success: function (res) {
        if (res.status) { Swal.fire('Restored!', res.message, 'success'); loadListDept(); loadStats(); }
        else Swal.fire('Error', res.message, 'error');
      }
    });
  });
  $(document).on('click', '.btn-restore-emp', function () {
    $.ajax({ url: 'api/employee/restore.php', method: 'POST', data: { employee_id: $(this).data('id') },
      success: function (res) {
        if (res.status) { Swal.fire('Restored!', res.message, 'success'); loadListEmp(); loadStats(); loadEmployees(); }
        else Swal.fire('Error', res.message, 'error');
      }
    });
  });

  $(document).on("click", ".btn-edit-emp", function () {
    let data = JSON.parse(decodeURIComponent($(this).data("info")));
    $("#edit_emp_id").val(data.id);
    $("#edit_emp_fname").val(data.first_name);
    $("#edit_emp_lname").val(data.last_name);
    $("#edit_emp_email").val(data.email);
    $("#edit_emp_phone").val(data.phone);
    $("#edit_emp_role").val(data.role);
    $("#edit_emp_status").val(data.status || 'active');

    let compHtml = '<option value="">Select Company</option>';
    window.companies_data?.forEach(c => {
      compHtml += `<option value="${c.id}" ${c.id == data.company_id ? 'selected' : ''}>${c.company_name}</option>`;
    });
    $("#edit_emp_company").html(compHtml).trigger("change");

    setTimeout(() => {
      $.ajax({
        url: "api/department/list_by_company.php", method: "GET", data: { company_id: data.company_id },
        success: function (res) {
          let deptHtml = '<option value="">Select Department</option>';
          if (res.status) {
            res.data.forEach(d => {
              deptHtml += `<option value="${d.id}" ${d.id == data.department_id ? 'selected' : ''}>${d.department_name}</option>`;
            });
          }
          $("#edit_emp_department").html(deptHtml);
        }
      });
    }, 200);

    new bootstrap.Modal(document.getElementById("editEmpModal")).show();
  });

  $("#edit_emp_company").change(function () {
    let cid = $(this).val();
    if (!cid) { $("#edit_emp_department").html('<option value="">Select Department</option>'); return; }
    $.ajax({
      url: "api/department/list_by_company.php", method: "GET", data: { company_id: cid },
      success: function (res) {
        let h = '<option value="">Select Department</option>';
        if (res.status) { res.data.forEach(d => h += `<option value="${d.id}">${d.department_name}</option>`); }
        $("#edit_emp_department").html(h);
      }
    });
  });

  $("#btnUpdateEmp").click(function () {
    let id = $("#edit_emp_id").val();
    let data = {
      employee_id: id,
      first_name: $("#edit_emp_fname").val().trim(),
      last_name: $("#edit_emp_lname").val().trim(),
      email: $("#edit_emp_email").val().trim(),
      phone: $("#edit_emp_phone").val().trim(),
      company_id: $("#edit_emp_company").val(),
      department_id: $("#edit_emp_department").val(),
      role: $("#edit_emp_role").val().trim(),
      status: $("#edit_emp_status").val()
    };

    $.ajax({
      url: "api/employee/update.php", method: "POST", data: data,
      success: function (res) {
        if (res.status) {
          Swal.fire("Updated!", res.message, "success");
          bootstrap.Modal.getInstance(document.getElementById("editEmpModal")).hide();
          loadListEmp(); loadEmployees();
        } else { Swal.fire("Error", res.message, "error"); }
      }
    });
  });

  $(document).on("click", ".btn-del-sal", function () {
    let sal_id = $(this).data("id");
    Swal.fire({
      title: "Are you sure?", text: "This will delete this salary record!",
      icon: "warning", showCancelButton: true, confirmButtonColor: "#ef4444", confirmButtonText: "Yes, delete"
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: "api/salary/delete.php", method: "POST", data: { salary_id: sal_id },
          success: function (res) {
            if (res.status) { Swal.fire("Deleted!", res.message, "success"); loadListSalary(); loadStats(); }
            else Swal.fire("Error", res.message, "error");
          }
        });
      }
    });
  });

  function buildEditSalLine(selectedId, amount) {
    let opts = `<option value="">Select Component</option>`;
    $.each(comps, function (i, c) {
      let sel = (c.id == selectedId) ? 'selected' : '';
      opts += `<option value="${c.id}" ${sel}>${c.component_name}</option>`;
    });
    return `
      <div class="row g-2 edit-slr mb-1">
        <div class="col-md-6"><select class="form-select edit-sal-c">${opts}</select></div>
        <div class="col-md-4"><input type="number" class="form-control edit-sal-a" placeholder="Amount" value="${amount || ''}"></div>
        <div class="col-md-2"><button type="button" class="btn-rm editRmLine"><i class="bi bi-trash"></i></button></div>
      </div>`;
  }

  function recalcEditTotals() {
    let gross = 0, ded = 0, net = 0;
    $(".edit-slr").each(function () {
      let cid = $(this).find(".edit-sal-c").val();
      let amt = parseFloat($(this).find(".edit-sal-a").val()) || 0;
      if (!cid || amt <= 0) return;
      let comp = comps.find(c => c.id == cid);
      if (!comp) return;
      if (comp.component_type == 1) { gross += amt; net += amt; }
      else { ded += amt; net -= amt; }
    });
    $("#edit_sal_gross").text("₹" + gross.toLocaleString("en-IN"));
    $("#edit_sal_ded").text("₹" + ded.toLocaleString("en-IN"));
    $("#edit_sal_net").text("₹" + net.toLocaleString("en-IN"));
  }

  $(document).on("click", ".btn-edit-sal", function () {
    let data = JSON.parse(decodeURIComponent($(this).data("info")));

    $("#edit_sal_id").val(data.id);
    $("#edit_sal_emp_name").text(data.first_name + " " + data.last_name);
    $("#edit_sal_month").text(data.salary_month);
    $("#edit_sal_payment_date").val(data.payment_date || "");

    $("#editSalLines").html("");
    let existingComps = data.components || [];
    if (typeof existingComps === 'string') {
      try { existingComps = JSON.parse(existingComps); } catch (e) { existingComps = []; }
    }
    if (existingComps.length) {
      existingComps.forEach(function (ec) {
        let match = comps.find(c => c.component_name === ec.name);
        let cid = match ? match.id : "";
        $("#editSalLines").append(buildEditSalLine(cid, ec.amount));
      });
    } else {
      $("#editSalLines").append(buildEditSalLine("", ""));
    }
    recalcEditTotals();
    new bootstrap.Modal(document.getElementById("editSalModal")).show();
  });

  $("#editSalAddLine").on("click", function () {
    $("#editSalLines").append(buildEditSalLine("", ""));
    recalcEditTotals();
  });

  $(document).on("click", ".editRmLine", function () {
    $(this).closest(".edit-slr").remove();
    recalcEditTotals();
  });

  $(document).on("change input", ".edit-sal-c, .edit-sal-a", function () {
    recalcEditTotals();
  });

  $("#btnUpdateSal").on("click", function () {
    let sal_id = $("#edit_sal_id").val();
    let paymentDate = $("#edit_sal_payment_date").val();
    if (!paymentDate) { Swal.fire("Error", "Payment date is required", "error"); return; }

    let lines = [], used = [], dup = false;
    $(".edit-slr").each(function () {
      let cid = $(this).find(".edit-sal-c").val();
      let amt = parseFloat($(this).find(".edit-sal-a").val());
      if (cid && amt > 0) {
        if (used.includes(cid)) { dup = true; return false; }
        used.push(cid);
        lines.push({ component_id: cid, amount: amt });
      }
    });
    if (dup) { Swal.fire("Error", "Duplicate component", "error"); return; }
    if (!lines.length) { Swal.fire("Error", "Add at least one component", "error"); return; }

    $.ajax({
      url: "api/salary/update.php", method: "POST",
      data: { salary_id: sal_id, payment_date: paymentDate, components: JSON.stringify(lines) },
      success: function (res) {
        if (res.status) {
          Swal.fire("Updated!", "Net Pay: ₹" + Number(res.data.net).toLocaleString("en-IN"), "success");
          bootstrap.Modal.getInstance(document.getElementById("editSalModal")).hide();
          loadListSalary(); loadStats();
        } else { Swal.fire("Error", res.message, "error"); }
      },
      error: function () { Swal.fire("Error", "Server error", "error"); }
    });
  });

});