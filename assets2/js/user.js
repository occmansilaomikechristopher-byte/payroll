$(document).ready(function () {
    $("#site-wrapper").hide();
    $("#togglePassword").click(function () {
        var passwordField = $("#password");
        var passwordFieldType = passwordField.attr("type");
        passwordField.attr(
            "type",
            passwordFieldType === "password" ? "text" : "password"
        );
        $(this).find("i").toggleClass("fa-eye-slash fa-eye");
    });
});

oTable = $("#data-table").DataTable();
$("#search-input").keyup(function () {
    oTable.search($(this).val()).draw();
});

let btnText = "Create";
let id = null;

$(function () {
    $('#role').select2({
        dropdownParent: $('#modal')
    });
    $("#site-select").removeAttr("required");
    $(".fa-spinner-button").hide();
    $("#role").on("change", function () {
        var selectedValue = $(this).val();
        if (selectedValue == 5 || selectedValue == 6) {
            $("#site-select").attr("required", true);
            $("#site-wrapper").show();
        } else {
            $("#site-select").removeAttr("required");
            $("#site-wrapper").hide();
        }
    });
});

$("#form-add").on("submit", async function (e) {
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
        $(".submitbutton").attr("disabled", true);
        $(".fa-spinner-button").show();
        if (id) {
            $(".submitbutton").attr("disabled", true).html("Saving...");
        } else {
            $(".submitbutton").attr("disabled", true).html("Creating...");
        }
        $.ajax({
            url: "ajax.php?action=save_user",
            method: "POST",
            dataType: "JSON",
            data: $(this).serialize(),
            error: (xhr, status, error) => {
                Swal.close();
                handleError(error || '');
                $(".submitbutton").removeAttr("disabled");
            },
            success: function (res) {
                if (res?.result) {
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: res?.message,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                } else{
                    $(".submitbutton").removeAttr("disabled");
                    $(".submitbutton").html("Create");
                    Swal.fire({
                        icon: "error",
                        title: "Erorr!",
                        text: res?.message,
                    }).then((result) => {
                       
                    });
                    return false;
                }
            },
        });
    }
});

function edit_function(e) {
    console.log('edit_function  --->',$(e).attr("employer_id"))
    id = $(e).attr("id");
    $("#modal").modal("show");
    $("#password-wrapper").hide();
    $("#password").removeAttr("required");
    $("#username-wrapper").hide();
    $("#username").removeAttr("required");
    $(".title").html("Edit User");
    $("#name").val($(e).attr("name"));
    $("#id").val($(e).attr("id"));
    $("#employer-select").val($(e).attr("employer_id")).trigger("change");
    $("#role").val($(e).attr("role")).trigger("change");
    $(".submitbutton").html("Save Changes");
}

$(document).on("hide.bs.modal", "#modal", function () {
    $(".title").html("Create User");
    $("#name").val("");
    $("#id").val("");
    $(".submitbutton").html(btnText);
});

function updateUserStatus(id, status) {
    Swal.fire({
        title: "Are you sure?",
        text: "You are about to update the user status.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, update it!",
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "ajax.php?action=update_status_user",
                method: "POST",
                dataType: "JSON",
                data: {id, status},
                error: (xhr, status, error) => {
                    Swal.close();
                    handleError(error || '');
                    $(".submitbutton").removeAttr("disabled");
                },
                success: function (res) {
                    if (res?.result) {
                        Swal.fire({
                            icon: "success",
                            title: "Success!",
                            text: "User successfully updated.",
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.reload();
                            }
                        });
                    } else{
                        Swal.fire({
                            icon: "error",
                            title: "Erorr!",
                            text: res.message,
                        }).then((result) => {
                           
                        });
                        return false;
                    }
                },
            });
        }
    });
}
