let id = null;
$(document).ready(function () {
    // Get p2 from URL or set default
    function getUrlParameter(name) {
        name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
        var regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
        var results = regex.exec(location.search);
        return results === null
            ? ""
            : decodeURIComponent(results[1].replace(/\+/g, " "));
    }
    var p2Raw = getUrlParameter("p2"); // get raw p2 value from URL

    // convert true/false or other to yes/no
    var p2 = p2Raw === "true" ? "yes" : "no";
    $("#table").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "payroll-server.php",
            type: "POST",
            data: function (d) {
                d.p2 = p2; // add p2 param to POST data
            },
        },
        columns: [
            { data: "ref_no" },
            { data: "employer_name" },
            { data: "period" },
            { data: "category" },
            { data: "type" },
            { data: "status" },
            { data: "action", orderable: false },
        ],
    });
});

$(document).ready(function () {
    oTable = $("#data-table").DataTable({
        order: [[1, "asc"]],
    });
    $("#search-input").keyup(function () {
        oTable.search($(this).val()).draw();
    });
    $(".select2").select2({
        dropdownParent: $("#modal"),
    });
    $(document).ready(function () {
        $(".datetimepicker").datetimepicker({
            allowInputToggle: true,
            showClose: true,
            showClear: true,
            showTodayButton: true,
            format: "YYYY/MM/DD",
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
    });

    $(document).on("click", ".edit_payroll", function () {
        var $id = $(this).attr("data-id");
        uni_modal("Edit Employee", "manage_payroll.php?id=" + $id);
    });

    $(document).on("click", ".add_settings", function () {
        $("#settings-id").val($(this).attr("data-id"));
        $("#modal-settings").modal("show");
        var settings = $(this).attr("settings");
        try {
            var settingsObject = JSON.parse(settings);
            settingsObject.forEach((item) => {
                if (item.type == 1) {
                    $("#contributions-" + item.id).prop("checked", true);
                }
                if (item.type == 2) {
                    $("#deductions-" + item.id).prop("checked", true);
                }
                if (item.type == 3) {
                    $("#loan-" + item.id).prop("checked", true);
                }
                if (item.type == 4) {
                    $("#refund-" + item.id).prop("checked", true);
                }
            });
        } catch (e) {
            console.error("Invalid JSON in settings attribute:", e);
        }
    });

    $(document).on("click", ".view_payroll", function () {
        var $id = $(this).attr("data-id");
        location.href = "index.php?page=payroll_calculations&id=" + $id;
    });

    $(document).on("click", ".remove_payroll", function () {
        _conf("Are you sure to delete this payroll?", "remove_payroll", [
            $(this).attr("data-id"),
        ]);
    });

    $(document).on("click", ".calculate_payroll", async function () {
        Swal.fire({
            title: "Calculating, please wait...",
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });
        await new Promise((resolve) => setTimeout(resolve, 1000));
        const id = $(this).attr("data-id");
        $.ajax({
            url: "ajax.php?action=calculate_payroll",
            method: "POST",
            dataType: "json",
            data: { id: id },
            error: (xhr, status, error) => {
                Swal.close();
                handleError(error || "");
                $(".submitbutton").removeAttr("disabled");
            },
            success: function (res) {
                if (res?.result) {
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: "Payroll successfully calculated.",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = `index.php?page=payroll_calculations&id=${id}`;
                        }
                    });
                } else {
                    Swal.close();
                    handleError(res?.message || "");
                    $(".submitbutton").removeAttr("disabled");
                }
            },
        });
    });
});

async function remove_payroll(id) {
    Swal.fire({
        title: "Deleting, please wait...",
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });
    await new Promise((resolve) => setTimeout(resolve, 1000));
    $.ajax({
        url: "ajax.php?action=delete_payroll",
        method: "POST",
        data: { id: id },
        error: (err) => {
            Swal.close();
            handleError();
        },
        success: function (resp) {
            if (resp == 1) {
                Swal.fire({
                    icon: "success",
                    title: "Success!",
                    text: "Selected payroll successfully deleted.",
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            } else {
                Swal.close();
                handleError();
            }
        },
    });
}

let form_data = "";
$("#form-submit").on("submit", async function (e) {
    e.preventDefault();
    var form = $(this);
    form.parsley().validate();

    if (form.parsley().isValid()) {
        e.preventDefault();
        Swal.fire({
            title: "Creating, please wait...",
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });
        await new Promise((resolve) => setTimeout(resolve, 1000));
        form_data = $(this).serialize();
        $.ajax({
            url: "ajax.php?action=get_sites",
            method: "POST",
            // dataType: "JSON",
            data: $(this).serialize(),
            error: (xhr, status, error) => {
                Swal.close();
                handleError(error || "");
            },
            success: function (res) {
                $("#modal").modal("hide");
                $("#show-sites").html(res);
                $("#modal-sites").modal("show");
                Swal.close();
            },
        });
    }
});

$("#form-add").on("submit", async function (e) {
    e.preventDefault();
    Swal.fire({
        title: "Creating, please wait...",
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });
    await new Promise((resolve) => setTimeout(resolve, 1000));
    $.ajax({
        url: "ajax.php?action=save_payroll",
        method: "POST",
        dataType: "JSON",
        data: { site_ids: $(this).serialize(), form_data },
        error: (xhr, status, error) => {
            Swal.close();
            handleError(error || "");
            $(".submitbutton").removeAttr("disabled");
        },
        success: function (res) {
            if (res?.result) {
                Swal.fire({
                    icon: "success",
                    title: "Success!",
                    text: "Payroll successfully create.",
                }).then((result) => {
                    if (result.isConfirmed) {
                        $("#modal-sites").modal("hide");
                        $("#settings-id").val(res?.id);
                        $("#modal-settings").modal("show");
                    }
                });
            } else {
                Swal.close();
                handleError(res?.message || "");
                $(".submitbutton").removeAttr("disabled");
            }
        },
    });
});

$("#form-settings").on("submit", async function (e) {
    e.preventDefault();
    Swal.fire({
        title: "Creating, please wait...",
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });
    await new Promise((resolve) => setTimeout(resolve, 1000));
    $.ajax({
        url: "ajax.php?action=save_settings",
        method: "POST",
        dataType: "JSON",
        data: $(this).serialize(),
        error: (xhr, status, error) => {
            Swal.close();
            handleError(error || "");
            $(".submitbutton").removeAttr("disabled");
        },
        success: function (res) {
            $("#modal-settings").modal("hide");
            if (res?.result) {
                Swal.fire({
                    icon: "success",
                    title: "Success!",
                    text: "Payroll successfully create.",
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            } else {
                Swal.close();
                handleError(res?.message || "");
                $(".submitbutton").removeAttr("disabled");
            }
        },
    });
});

async function islock(id, isLock) {
    let message = isLock == 2 ? "Lock" : "Unlock";
    // Show confirmation dialog
    const result = await Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, " + message + " it!",
    });

    // If the user confirmed the deletion
    if (result.isConfirmed) {
        // Show loading dialog
        Swal.fire({
            title: "Updating, please wait...",
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        await new Promise((resolve) => setTimeout(resolve, 1000));

        $.ajax({
            url: "ajax.php?action=isLock222",
            method: "POST",
            data: { id: id, isLock },
            dataType: "JSON",
            error: (err) => {
                Swal.close();
                handleError();
            },
            success: function (res) {
                if (res?.result) {
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: "Selected payroll successfully updated.",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    });
                } else {
                    Swal.close();
                    handleError();
                }
            },
        });
    }
}

async function recalculate(id) {
    // Show confirmation dialog
    const result = await Swal.fire({
        title: "Are you sure?",
        text: "This action will modify existing values!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, recalculate it!",
    });

    // If the user confirmed the deletion
    if (result.isConfirmed) {
        // Show loading dialog
        Swal.fire({
            title: "Recalculating, please wait...",
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        await new Promise((resolve) => setTimeout(resolve, 1000));

        $.ajax({
            url: "ajax.php?action=calculate_payroll",
            method: "POST",
            data: { id: id, type: "recalculate" },
            dataType: "JSON",
            error: (err) => {
                Swal.close();
                handleError();
            },
            success: function (res) {
                if (res?.result) {
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: "Selected payroll successfully recalculated.",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = `index.php?page=payroll_calculations&id=${id}`;
                        }
                    });
                } else {
                    Swal.close();
                    handleError();
                }
            },
        });
    }
}

function formatDate(dateString) {
    let date = new Date(dateString);
    return date.toLocaleString("en-US", {
        month: "long",
        day: "numeric",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        hour12: true,
    });
}

async function payroll_history(id) {
    Swal.fire({
        title: "Fetching, please wait...",
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });
    await new Promise((resolve) => setTimeout(resolve, 1000));
    $.ajax({
        url: "ajax.php?action=payroll_history_details",
        method: "POST",
        dataType: "JSON",
        data: { id: id },
        error: (err) => {
            console.error("Error fetching payroll history:", err);
            $("#loanHistoryDiv").html(
                "<p class='text-danger'>Failed to load payroll history.</p>"
            );
        },
        success: function (res) {
            $("#modal-payroll-history").modal("show"); // Show modal
            Swal.close();
            if (res && res.length > 0) {
                let tableHTML = `
                    <table id="payrollHistoryTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                 <th>Date Created</th>
                                <th>User</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>`;

                res.forEach((row) => {
                    let formattedDate = formatDate(row.created_at); // Format date before inserting into table

                    tableHTML += `
                        <tr>
                            <td>${formattedDate}</td>
                            <td>${row.name}</td>
                            <td class="text-info">${row.details}</td>
                            
                        </tr>`;
                });

                tableHTML += `</tbody></table>`;

                $("#loanHistoryDiv").html(tableHTML); // Append the table

                // Initialize DataTable
                $("#payrollHistoryTable").DataTable({
                    responsive: true,
                    autoWidth: false,
                    lengthMenu: [
                        [5, 10, 25, 50],
                        [5, 10, 25, 50],
                    ], // Pagination options
                    language: {
                        emptyTable: "No payroll history available.",
                    },
                });

                // Initialize tooltips
                $('[data-toggle="tooltip"]').tooltip();
            } else {
                $("#loanHistoryDiv").html("<p>No payroll history found.</p>");
            }
        },
    });
}

$(document).on("hide.bs.modal", "#modal-settings", function () {
    window.location.reload();
});
