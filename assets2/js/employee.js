// oTable = $("#table-employee").DataTable();
document.addEventListener("DOMContentLoaded", function () {
    const employeeDetailsTabs = document.getElementById("employee-details-tabs");
    const tabStorageKey = "employeeDetailsActiveTab";

    if (!employeeDetailsTabs || !window.bootstrap || !bootstrap.Tab) {
        return;
    }

    const activeTab = localStorage.getItem(tabStorageKey);
    const activeTabElement = activeTab && employeeDetailsTabs.querySelector(`[href="${activeTab}"]`);

    if (activeTabElement) {
        bootstrap.Tab.getOrCreateInstance(activeTabElement).show();
    }

    employeeDetailsTabs.querySelectorAll('[data-bs-toggle="tab"]').forEach((tab) => {
        tab.addEventListener("shown.bs.tab", function () {
            localStorage.setItem(tabStorageKey, tab.getAttribute("href"));
        });
    });
});

$(document).ready(function () {
    var oTable = $("#table-employee").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "employee-server.php",
            type: "POST",
            data: function (d) {
                d.status = $("#filter-status").val(); // Send selected status to server
                d.weekly_payroll = $("#filter-ptype").val();
                d.position_id = $("#filter-position").val();
            },
        },
        columns: [
            { data: "0" },
            { data: "1" },
            { data: "2" },
            { data: "3", className: "text-end" },
            { data: "4", className: "text-end" },
            { data: "5", className: "text-end" },
            { data: "7", className: "text-center" },
            { data: "8", className: "text-center" },
            { data: "9", className: "text-center", orderable: false },
        ],
        columnDefs: [
            { width: "90px", targets: 0 },
            { width: "90px", targets: 6 },
            { width: "90px", targets: 7 },
        ],
    }).on("draw.dt", function () {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            bootstrap.Tooltip.getInstance(el)?.dispose();
            new bootstrap.Tooltip(el, { trigger: "hover" });
        });
    });

    // Search input filter
    $("#search-input").keyup(function () {
        oTable.search($(this).val()).draw();
    });

    $("#filter-status").select2({
        allowClear: true,
        width: "resolve",
        dropdownParent: $("body"),
    }).on("change", function () {
        oTable.draw();
    });

    $("#filter-ptype").select2({
        allowClear: true,
        width: "resolve",
        dropdownParent: $("body"),
    }).on("change", function () {
        oTable.draw();
    });

    $("#filter-position").select2({
        allowClear: true,
        width: "resolve",
        dropdownParent: $("body"),
    }).on("change", function () {
        oTable.draw();
    });
});

let tloan = $("#table-loan").DataTable();
let tcontribution = $("#table-contributions").DataTable();
let tdeductions = $("#table-deductions").DataTable();
// $("#search-input").keyup(function () {
//     oTable.search($(this).val()).draw();
// });

$(function () {
    $("#position-select").select2({
        dropdownParent: $("#addemployee"),
    });
    $(".fa-spinner-button").hide();
    //$(".select2").select2();
    $("#table-payslip").DataTable();
    // $('#table-deductions').DataTable();
    // $('#table-contribution').DataTable();
    // $('#table-allowances').DataTable();
    $("#table-timelogs").DataTable();
    // $('.datetimepicker').datetimepicker({
    //     format:"H:i",
    //     datepicker:false,
    // })

    $(".datetimepicker").datetimepicker({
        allowInputToggle: true,
        showClose: true,
        showClear: true,
        showTodayButton: true,
        format: "YYYY-MM-DD ",
        icons: {
            time: "fa fa-clock-o",
            date: "fa fa-calendar",
            up: "fa fa-chevron-up",
            down: "fa fa-chevron-down",
            previous: "fa fa-chevron-left", // Fix for missing prev button
            next: "fa fa-chevron-right", // Fix for missing next button
            today: "fa fa-crosshairs",
            clear: "fa fa-trash",
            close: "fa fa-times",
        },
    });

    $(".datetimepicker2").datetimepicker({
        allowInputToggle: true,
        showClose: true,
        showClear: true,
        showTodayButton: true,
        format: "HH:mm",
        icons: {
            time: "fa fa-clock-o",
            date: "fa fa-calendar",
            up: "fa fa-chevron-up",
            down: "fa fa-chevron-down",
            previous: "fa fa-chevron-left", // Fix for missing prev button
            next: "fa fa-chevron-right", // Fix for missing next button
            today: "fa fa-crosshairs",
            clear: "fa fa-trash",
            close: "fa fa-times",
        },
    });

    $('[name="department_id"]').change(function () {
        //$( "#position-select" ).val([]);
        var did = $(this).val();
        $("#position-select").select2("val", "");
        $('[name="position_id"] .opt').each(function () {
            if ($(this).attr("data-did") == did) {
                $(this).attr("disabled", false);
            } else {
                $(this).attr("disabled", true);
            }
        });
    });
});

$("#form-add").on("submit", async function (e) {
    e.preventDefault();
    var form = $(this);

    form.parsley().validate();
    if (form.parsley().isValid()) {
        Swal.fire({
            title: "Saving, please wait...",
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });
        await new Promise((resolve) => setTimeout(resolve, 1000));
        $.ajax({
            url: "ajax.php?action=save_employee",
            method: "POST",
            data: $(this).serialize(),
            // dataType: 'JSON',
            error: (xhr, status, error) => {
                Swal.close();
                handleError(error || "");
                $(".submitbutton").removeAttr("disabled");
            },
            success: function (res) {
                if (res == "updated") {
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: "Employee successfully saved!",
                    }).then((result) => {
                        window.location.reload();
                    });
                    return;
                }
                Swal.fire({
                    icon: "success",
                    title: "Success!",
                    text: "Employee successfully saved!",
                    showCancelButton: true,
                    confirmButtonText: "View",
                    cancelButtonText: "Close",
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `index.php?page=employee-details&id=${res}`;
                    } else {
                        window.location.reload();
                    }
                });
            },
        });
    } else {
        Swal.fire({
            icon: 'warning', // Use 'error', 'success', 'info', or 'warning'
            title: 'Validation Error',
            text: 'Please enter required fields!',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
          });
    }
});

$("#employee-contribution").on("submit", async function (e) {
    e.preventDefault();
    var form = $(this);

    form.parsley().validate();

    if (form.parsley().isValid()) {
        Swal.fire({
            title: "Saving, please wait...",
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });
        await new Promise((resolve) => setTimeout(resolve, 1000));
        $.ajax({
            url: "ajax.php?action=save_contribution",
            method: "POST",
            data: $(this).serialize(),
            error: (xhr, status, error) => {
                Swal.close();
                handleError(error || "");
                $(".submitbutton").removeAttr("disabled");
            },
            success: function (resp) {
                if (resp == 1) {
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: "Cotribution successfully updated!",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                } else {
                    handleError(error || "");
                }
            },
        });
    }
});

$("#employee-deduction").on("submit", async function (e) {
    e.preventDefault();
    var form = $(this);

    form.parsley().validate();

    if (form.parsley().isValid()) {
        Swal.fire({
            title: "Saving, please wait...",
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });
        await new Promise((resolve) => setTimeout(resolve, 1000));
        $.ajax({
            url: "ajax.php?action=save_employee_deduction",
            method: "POST",
            data: $(this).serialize(),
            error: (xhr, status, error) => {
                Swal.close();
                handleError(error || "");
                $(".submitbutton").removeAttr("disabled");
            },
            success: function (resp) {
                if (resp == 1) {
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: "New deduction successfully saved!",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                } else if (resp == 2) {
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: "Deduction successfully updated!",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                }
            },
        });
    }
});

$("#employee-allowance").on("submit", async function (e) {
    e.preventDefault();
    var form = $(this);

    form.parsley().validate();

    if (form.parsley().isValid()) {
        Swal.fire({
            title: "Saving, please wait...",
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });
        await new Promise((resolve) => setTimeout(resolve, 1000));
        $.ajax({
            url: "ajax.php?action=save_employee_allowance",
            method: "POST",
            data: $(this).serialize(),
            error: (xhr, status, error) => {
                Swal.close();
                handleError(error || "");
                $(".submitbutton").removeAttr("disabled");
            },
            success: function (resp) {
                if (resp == 1) {
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: "New allowance successfully saved!",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                } else if (resp == 2) {
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: "Site successfully updated!",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                }
            },
        });
    }
});

function add_deductions(e) {
    openEmployeeModal('modal-deduction', function () {
        try {
            $('#deduction_id').val('').trigger('change');
            $('#amount').val('');
            $('#edate').val(moment().format('YYYY-MM-DD'));
            $('#dfield').hide();
        } catch (err) {}
    });
}

function add_contritions(e) {
    openEmployeeModal('modal-contrition');
}
function add_allowance(e) {
    openEmployeeModal('modal-allowance');
}

function edit_details(e) {
    openEmployeeModal('addemployee', function () {
        // Keep the form aligned with the employee record shown on the page.
        try {
            if (window.employeeData) {
                $('#form-add [name="id"]').val(window.employeeData.id || '');
                $('#form-add [name="firstname"]').val(window.employeeData.firstname || '');
                $('#form-add [name="middlename"]').val(window.employeeData.middlename || '');
                $('#form-add [name="lastname"]').val(window.employeeData.lastname || '');
                $('#form-add [name="ext"]').val(window.employeeData.ext || '');
                $('#form-add [name="bday"]').val(window.employeeData.bday || '');
                $('#form-add [name="age"]').val(window.employeeData.age || '');
                $('#form-add [name="contact_number"]').val(window.employeeData.contact_number || '');
                $('#form-add [name="address"]').val(window.employeeData.address || '');
                $('#form-add [name="position_id"]').val(window.employeeData.position_id || '').trigger('change');
                $('#form-add [name="clasification_id"]').val(window.employeeData.clasification_id || '').trigger('change');
                $('#form-add [name="basic_pay"]').val(window.employeeData.basic_pay || '');
                $('#form-add [name="salary"]').val(window.employeeData.salary || '');
                $('#form-add [name="ot_rate"]').val(window.employeeData.ot_rate || '');
                $('#form-add [name="allowance_rate"]').val(window.employeeData.allowance_rate || '');
                $('#form-add [name="sss_fund"]').val(window.employeeData.sss_fund || '0');
                $('#form-add [name="weekly_payroll"]').prop('checked', !!window.employeeData.weekly_payroll);
                $('#form-add [name="isAutoDeduct"]').prop('checked', !!window.employeeData.isAutoDeduct);
                $('#form-add [name="status"]').prop('checked', !!window.employeeData.status);
            }
        } catch (err) {}
    });
}

function add_loans(e) {
    openEmployeeModal('modal-loan', function () {
        try {
            $('#loan_id').val('');
            $('#loan-select').val('').trigger('change');
            $('#loan_date').val(moment().format('YYYY-MM-DD'));
            $('#damount').val('');
            $('#loan_balance').val('');
            $('#loan_amount').val('');
            $('#loan_status').prop('checked', false);
            $('#loan_employee_id').val(window.employeeData?.id || employee_id);
        } catch (err) {}
    });
}

function openEmployeeModal(modalId, prepareFn) {
    if (typeof prepareFn === 'function') {
        prepareFn();
    }

    const modalEl = document.getElementById(modalId);
    if (!modalEl) return;

    if (modalEl.parentElement !== document.body) {
        document.body.appendChild(modalEl);
    }

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
}

$("#type").change(function () {
    if ($(this).val() == 3) {
        $("#dfield").show();
    } else {
        $("#dfield").hide();
    }
});

$("#type2").change(function () {
    if ($(this).val() == 3) {
        $("#dfield2").show();
    } else {
        $("#dfield2").hide();
    }
});

$(".remove_deduction").click(function () {
    var d = $(this).attr("data-id");
    console.log({ d });
    _conf(
        "Are you sure to delete this employee's deduction?",
        "remove_deduction",
        [d]
    );
});

async function remove_deduction(id) {
    Swal.fire({
        title: "Removing, please wait...",
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });
    await new Promise((resolve) => setTimeout(resolve, 1000));
    $.ajax({
        url: "ajax.php?action=delete_employee_deduction",
        method: "POST",
        data: { id: parseInt(id) },
        error: (err) => {
            console.log(err);
            handleError();
        },
        success: function (resp) {
            $("#confirm_modal").modal("hide");
            if (resp == 1) {
                Swal.fire({
                    icon: "success",
                    title: "Success!",
                    text: "Selected deduction successfully deleted.",
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            }
        },
    });
}

$(".remove_allowance").click(function () {
    _conf("Are you sure to delete this allowance?", "remove_allowance", [
        $(this).attr("data-id"),
    ]);
});

async function remove_allowance(id) {
    Swal.fire({
        title: "Removing, please wait...",
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });
    await new Promise((resolve) => setTimeout(resolve, 1000));
    $.ajax({
        url: "ajax.php?action=delete_employee_allowance",
        method: "POST",
        data: { id: id },
        error: (err) => console.log(err),
        success: function (resp) {
            if (resp == 1) {
                Swal.fire({
                    icon: "success",
                    title: "Success!",
                    text: "Selected allowance successfully deleted.",
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            } else {
                handleError();
            }
        },
    });
}

$(".remove_contrition").click(function () {
    _conf("Are you sure to delete this contribution?", "remove_contrition", [
        $(this).attr("data-id"),
    ]);
});

async function remove_contrition(id) {
    Swal.fire({
        title: "Removing, please wait...",
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });
    await new Promise((resolve) => setTimeout(resolve, 1000));
    $.ajax({
        url: "ajax.php?action=delete_employee_contribution",
        method: "POST",
        data: { id: id },
        error: (err) => console.log(err),
        success: function (resp) {
            if (resp == 1) {
                Swal.fire({
                    icon: "success",
                    title: "Success!",
                    text: "Selected contrition successfully deleted.",
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            } else {
                handleError();
            }
        },
    });
}

$(".select2").change(function () {
    let data_parsley_id = $(this).attr("data-parsley-id");
});

$(".remove_timelog").click(function () {
    var d = '"' + $(this).attr("data-id").toString() + '"';
    _conf(
        "Are you sure to delete this employee's log time?",
        "remove_timelogs",
        [d]
    );
});

function remove_timelogs(id) {
    $.ajax({
        url: "ajax.php?action=delete_employee_timelogs",
        method: "POST",
        data: { id: id },
        error: (err) => {
            console.log(err);
            handleError();
        },
        success: function (resp) {
            $("#confirm_modal").modal("hide");
            if (resp == 1) {
                toastr["success"](
                    "Selected log time successfully deleted!",
                    "Success Notification"
                );
            }
            setTimeout(function () {
                location.reload();
            }, 2000);
        },
    });
}

$(document).ready(function () {
    $(".sss_no").change(function () {
        $.ajax({
            url: "ajax.php?action=save_employee_contribution",
            method: "POST",
            data: {
                id: employee_id,
                value: $(this).val(),
                type: $(this).attr("data-id"),
            },
            error: (xhr, status, error) => {
                Swal.close();
                handleError(error || "");
                $(".submitbutton").removeAttr("disabled");
            },
            success: function (resp) {
                if (resp == 1) {
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: "Contribution successfully saved!",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // window.location.reload();
                        }
                    });
                }
            },
        });
    });
});

$("#employee-loan").on("submit", async function (e) {
    e.preventDefault();
    var form = $(this);

    form.parsley().validate();

    if (form.parsley().isValid()) {
        Swal.fire({
            title: "Saving, please wait...",
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });
        await new Promise((resolve) => setTimeout(resolve, 1000));
        $.ajax({
            url: "ajax.php?action=save_employee_loan",
            method: "POST",
            data: $(this).serialize(),
            error: (xhr, status, error) => {
                Swal.close();
                handleError(error || "");
                $(".submitbutton").removeAttr("disabled");
            },
            success: function (resp) {
                if (resp == 1) {
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: "New loan successfully saved!",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                } else if (resp == 2) {
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: "Loan successfully updated!",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                }
            },
        });
    }
});

function saveLoanActive() {
    var loan_id = $("#loan-select").val();
    var loan = $("#loan").val();
    var loan_deduction = $("#loan_deduction").val();
    if (!loan_id) {
        Swal.fire({
            icon: "error",
            title: "Oops...",
            text: "Please select loan!",
        });
        return;
    }
    Swal.fire({
        title: "Confirmation",
        text: "Are you certain you want to proceed with updating the loan?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonColor: "#2196F3",
        confirmButtonText: "Yes, update it!",
        cancelButtonText: "No, cancel!",
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "ajax.php?action=active_employee_loan",
                method: "POST",
                data: { loan_id, id: employee_id, loan, loan_deduction },
                error: (err) => console.log(err),
                success: function (resp) {
                    if (resp == 1) {
                        Swal.fire({
                            icon: "success",
                            title: "Success!",
                            text: "Loan  successfully updated.",
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload();
                            }
                        });
                    } else {
                        handleError();
                    }
                },
            });
        }
    });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", {
        month: "long",
        day: "2-digit",
        year: "numeric",
    });
}

function loanHistory(id) {
    const modalHistoryEl = document.getElementById('modal-loan-history');
    if (modalHistoryEl) new bootstrap.Modal(modalHistoryEl).show();
    $.ajax({
        url: "ajax.php?action=loan_history_details",
        method: "POST",
        dataType: "JSON",
        data: { id: id },
        error: (err) => {
            console.log(err);
            handleError();
        },
        success: function (res) {
            if (res && res.length > 0) {
                let table = `
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Payroll ID</th>
                                <th>Current Balance</th>
                                <th>Amount</th>
                                <th>New Balance</th>
                                
                            </tr>
                        </thead>
                        <tbody>`;

                res.forEach((row) => {
                    table += `
                        <tr>
                            <td> <a data-toggle="tooltip" title="View Payroll Details"  href="index.php?page=payroll_calculations&id=${
                                row.payroll_id
                            }" class="btn btn-link waves-effect" >${
                        row.ref_no
                    }</a> (${formatDate(row.date_from)} - ${formatDate(
                        row.date_to
                    )})</td>
                            <td class="text-right text-info">
                                ${parseFloat(row.current_bal).toLocaleString(
                                    "en-US",
                                    {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2,
                                    }
                                )}
                            </td>
                            <td class="text-right text-success">
                                ${parseFloat(row.amount).toLocaleString(
                                    "en-US",
                                    {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2,
                                    }
                                )}
                            </td>
                            <td class="text-right text-danger">
                                ${parseFloat(row.new_bal).toLocaleString(
                                    "en-US",
                                    {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2,
                                    }
                                )}
                            </td>

                        </tr>`;
                });

                table += `</tbody></table>`;
                // Initialize tooltips for generated content (supports data-toggle or data-bs-toggle)
                document.querySelectorAll('[data-toggle="tooltip"], [data-bs-toggle="tooltip"]').forEach(function (el) {
                    try { new bootstrap.Tooltip(el); } catch (e) {}
                });

                // Append or replace the content inside a div (assumed div with ID #loanHistoryDiv)
                $("#loanHistoryDiv").html(table);
            } else {
                $("#loanHistoryDiv").html("<p>No loan history found.</p>");
            }
        },
    });
}

function editLoan(e) {
    $("#loan_id").val($(e).attr("loan_id"));
    $("#loan_employee_id").val($(e).attr("employee_id"));
    $("#loan-select").val($(e).attr("loan_type")).trigger('change');
    try {
        // ensure date is in YYYY-MM-DD
        const ld = $(e).attr("loan_date");
        if (ld) {
            $('#loan_date').val(ld);
        }
    } catch (err) {}
    $("#damount").val($(e).attr("damount"));
    $("#loan_balance").val($(e).attr("loan_balance"));
    $("#loan_amount").val($(e).attr("loan_amount"));
    console.log($(e).attr("loan_status"));
    if ($(e).attr("loan_status") == 1) {
        $("#loan_status").prop("checked", true);
    }
    const modalEl = document.getElementById('modal-loan');
    if (modalEl) new bootstrap.Modal(modalEl).show();
}

$(document).on("hide.bs.modal", "#modal-loan", function () {
    window.location.reload();
});

function editContriAmount(el) {
    $("#contribution-id").val($(el).attr("data-id"));
    $("#contribution-name").val($(el).attr("data-name"));
    $("#contribution-amount").val($(el).attr("data-amount"));
    const modalEl = document.getElementById('modal-contrition');
    if (modalEl) new bootstrap.Modal(modalEl).show();
    console.log(el);
}



$("#uploadForm").on("submit", async function (e) {
    e.preventDefault();
    var form = $(this);

    form.parsley().validate();

    if (form.parsley().isValid()) {
        Swal.fire({
            title: "Uploading, please wait...",
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });
        await new Promise((resolve) => setTimeout(resolve, 1000));
        var formData = new FormData(this);
        $.ajax({
            url: "ajax.php?action=import_employee",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            error: (xhr, status, error) => {
                Swal.close();
                handleError(error || "");
            },
            success: function (resp) {
                Swal.fire({
                    icon: "success",
                    title: "Success!",
                    text: "Employee successfully imported!",
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    }
                });
            },
        });
    }
});

// Keep the legacy handlers available to any remaining inline markup.
window.edit_details = edit_details;
window.add_loans = add_loans;
window.add_deductions = add_deductions;
window.add_contritions = add_contritions;
window.add_allowance = add_allowance;
window.editLoan = editLoan;
window.loanHistory = loanHistory;
window.editContriAmount = editContriAmount;

// The employee-details buttons use Bootstrap's data API, so their modals can
// open even if another page-specific script fails later during initialization.


