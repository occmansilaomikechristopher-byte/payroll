$(document).ready(function () {
    $("#site-wrapper").hide();

    // Toggle password visibility
    $("#togglePassword").click(function () {
        const passwordField = $("#password");
        const type =
            passwordField.attr("type") === "password" ? "text" : "password";
        passwordField.attr("type", type);
        $(this).find("i").toggleClass("fa-eye-slash fa-eye");
    });

    // DataTable
    const oTable = $("#data-table").DataTable();
    $("#search-input").keyup(function () {
        oTable.search($(this).val()).draw();
    });

    // Select2 setup
    $("#role").select2({ dropdownParent: $("#modal") });
    $("#employer-select").select2({ dropdownParent: $("#modal") });
    $("#branch-select").select2({ dropdownParent: $("#modal") });

    // Hide conditional selections by default
    $("#site-select").removeAttr("required");
    $("#branch-select").removeAttr("required");
    $("#branch-wrapper").hide();
    $(".fa-spinner-button").hide();

    // Role change toggle
    $("#role").on("change", function () {
        const selectedValue = $(this).val();

        // Timekeeper / PIC / Cashier → branch selection
        if (selectedValue == 5 || selectedValue == 6 || selectedValue == 9) {
            $("#branch-select").attr("required", true);
            $("#branch-wrapper").show();
            $("#all-branches-option").hide();
        }
        // Owner → branch is optional and can be all branches
        else if (selectedValue == 10) {
            $("#branch-select").removeAttr("required");
            $("#branch-wrapper").show();
            $("#all-branches-option").show();
        } else {
            $("#branch-select").removeAttr("required");
            $("#branch-wrapper").hide();
            $("#all-branches-option").hide();
        }
    });

    // Apply the current role immediately (including a browser-restored Cashier selection).
    $("#role").trigger("change");
});

// Track mode
let id = null;

// Handle form submit (Create + Edit)
$("#form-add").on("submit", async function (e) {
    e.preventDefault();

    const form = $(this);
    form.parsley().validate();

    if (form.parsley().isValid()) {
        Swal.fire({
            title: id ? "Saving changes..." : "Creating user...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        $(".submitbutton")
            .attr("disabled", true)
            .html(
                id
                    ? '<i class="fa fa-spinner fa-spin me-1"></i> Saving...'
                    : '<i class="fa fa-spinner fa-spin me-1"></i> Creating...'
            );

        $.ajax({
            url: "ajax.php?action=save_user",
            method: "POST",
            dataType: "JSON",
            data: form.serialize(),
            error: function (xhr, status, error) {
                Swal.close();
                handleError(error || "");
                $(".submitbutton")
                    .removeAttr("disabled")
                    .html(id ? "Save Changes" : "Create");
            },
            success: function (res) {
                Swal.close();
                if (res?.result) {
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: res.message,
                    }).then(() => window.location.reload());
                } else {
                    $(".submitbutton")
                        .removeAttr("disabled")
                        .html(id ? "Save Changes" : "Create");
                    Swal.fire({
                        icon: "error",
                        title: "Error!",
                        text: res.message,
                    });
                }
            },
        });
    }
});

// Edit button handler (called from table button)
function edit_function(e) {
    console.log("edit_function --->", $(e).attr("employer_id"));
    id = $(e).attr("id");

    // Show modal
    $("#modal").modal("show");

    // Hide only employer and role fields
    $("#employer-select").closest(".form-group").hide();
    $("#role").closest(".form-group").hide();

    // Keep password visible and required (for optional update)
    $("#password-wrapper").show();
    $("#password").removeAttr("required"); // optional: only required for create

    // Hide username field (cannot edit username)
    // $("#username-wrapper").hide();
    // $("#username").removeAttr("required");

    // Update title and button text
    $(".modal-title").html("Edit User");
    $(".submitbutton").html("Save Changes");

    // Fill form fields
    $("#name").val($(e).attr("name"));
    $("#username").val($(e).attr("username"));
    $("#id").val($(e).attr("id"));
    $("#employer-select").val($(e).attr("employer_id")).trigger("change");
    $("#role").val($(e).attr("role")).trigger("change");

    // Cashier / Owner branch (role change above already toggled visibility)
    if ($(e).attr("role") == 9 || $(e).attr("role") == 10) {
        const branchId = $(e).attr("branch_id") || ($(e).attr("role") == 10 ? '0' : '');
        $("#branch-select").val(branchId).trigger("change");
    }
}

$(document).on("hide.bs.modal", "#modal", function () {
    // Reset title and button
    $(".modal-title").html("Create User");
    $(".submitbutton").html("Create");

    // Reset all form fields
    $("#form-add")[0].reset();
    $("#id").val("");

    // Re-show all form groups for next create action
    $("#employer-select").closest(".form-group").show();
    $("#role").closest(".form-group").show();
    $("#username-wrapper").show();
    $("#password-wrapper").show();

    // Reset conditional selects
    $("#branch-wrapper").hide();
    $("#branch-select").val("").trigger("change").removeAttr("required");
});
