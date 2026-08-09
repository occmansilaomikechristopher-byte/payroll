$(document).ready(function () {
    // DataTable
    $("#data-table").DataTable({
        order: [[0, "asc"]],
        pageLength: 10,
    });

    // Populate edit modal from button data attributes
    $('[data-bs-target="#modal-edit-category"]').click(function () {
        var btn = $(this);
        $("#edit-category-id").val(btn.data("id"));
        $("#edit-category-code").val(btn.data("code"));
        $("#edit-category-name").val(btn.data("name"));
        $("#edit-category-desc").val(btn.data("desc"));
        $("#edit-category-status").val(btn.data("status"));
    });

    // Shared submit handler (Add + Edit) with Parsley validation
    function submitCategory(form, action, savingText) {
        form.parsley().validate();
        if (!form.parsley().isValid()) return;

        var btn = form.find('button[type="submit"]');
        var btnHtml = btn.html();

        Swal.fire({
            title: savingText,
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });
        btn.attr("disabled", true).html('<i class="fa fa-spinner fa-spin me-1"></i> ' + savingText);

        $.ajax({
            url: "ajax.php?action=" + action,
            method: "POST",
            data: form.serialize(),
            error: function () {
                Swal.close();
                btn.removeAttr("disabled").html(btnHtml);
                Swal.fire({ icon: "error", title: "Error!", text: "Something went wrong. Please try again." });
            },
            success: function (resp) {
                Swal.close();
                if (resp == 1) {
                    Swal.fire({ icon: "success", title: "Success!", text: "Category saved successfully!" })
                        .then(() => location.reload());
                } else {
                    btn.removeAttr("disabled").html(btnHtml);
                    Swal.fire({ icon: "error", title: "Error!", text: resp });
                }
            },
        });
    }

    $("#form-add-category").on("submit", function (e) {
        e.preventDefault();
        submitCategory($(this), "add_pos_category", "Saving...");
    });

    $("#form-edit-category").on("submit", function (e) {
        e.preventDefault();
        submitCategory($(this), "update_pos_category", "Updating...");
    });
});
