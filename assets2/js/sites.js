oTable = $("#data-table").DataTable({
    order: [[1, "asc"]], // Orders by the second column (index 1) in ascending order
});

$("#search-input").keyup(function () {
    oTable.search($(this).val()).draw();
});

let btnText = "Create";
let id = null;

$(function () {
    $(".select2").select2({
        dropdownParent: $("#modal"),
    });
    $(".fa-spinner-button").hide();
});

$("#form-add").on("submit", async function (e) {
    e.preventDefault();
    const form = $(this);

    form.parsley().validate();
    if (!form.parsley().isValid()) return;

    const formData = {};
    form.serializeArray().forEach((field) => {
        formData[field.name] = field.value;
    });

    // Determine whether it's an update or create
    const isUpdate = !!formData.id;

    // Update button text and show loading state
    $(".submitbutton")
        .attr("disabled", true)
        .html(isUpdate ? "Saving..." : "Creating...");
    $(".fa-spinner-button").show();

    Swal.fire({
        title: isUpdate ? "Saving, please wait..." : "Creating, please wait...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    try {
        await new Promise((resolve) => setTimeout(resolve, 500)); // optional delay

        $.ajax({
            url: "ajax.php?action=save_site",
            method: "POST",
            data: form.serialize(), // use standard form serialization
            dataType: "json",
            success: function (resp) {
                Swal.close();

                if (resp.status) {
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: resp.message || "Site saved successfully!",
                    }).then(() => window.location.reload());
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error!",
                        text: resp.message || "An error occurred while saving.",
                    });
                    $(".submitbutton").removeAttr("disabled").html("Save");
                }
            },
            error: (xhr, status, error) => {
                Swal.close();
                Swal.fire({
                    icon: "error",
                    title: "Error!",
                    text: "Failed to connect to the server.",
                });
                $(".submitbutton").removeAttr("disabled").html("Save");
            },
        });
    } catch (err) {
        Swal.close();
        Swal.fire({
            icon: "error",
            title: "Error!",
            text: err.message || "Unexpected error occurred.",
        });
        $(".submitbutton").removeAttr("disabled").html("Save");
    }
});

function edit_function(e) {
    id = $(e).attr("id");
    $("#modal").modal("show");
    $(".title").html("Edit Site");
    $("#site_name").val($(e).attr("site_name"));
    $("#employer_id").val($(e).attr("employer_id"));
    $("#site_code").val($(e).attr("site_code"));
    $("#site_address").val($(e).attr("site_address"));
    $("#id").val($(e).attr("id"));
    $("#cluster-select").val($(e).attr("cluster_id")).trigger("change");
    $("#timekeeper-select").val($(e).attr("timekeeper_id")).trigger("change");
    $("#pic-select").val($(e).attr("pic_id")).trigger("change");
    if ($(e).attr("status") == 1) {
        $("#status2").prop("checked", true);
    }
    $(".submitbutton").html("Save Changes");
}

$(document).on("hide.bs.modal", "#modal", function () {
    window.location.reload();
    $(".title").html("Create Site");
    $("#name").val("");
    $("#id").val("");
    $(".submitbutton").html(btnText);
});
