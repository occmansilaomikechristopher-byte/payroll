$(document).ready(function () {
    // DataTable
    $("#data-table").DataTable({
        order: [[0, "asc"]],
        pageLength: 10,
    });

    // Populate edit modal from button data attributes
    $('[data-bs-target="#modal-edit-branch"]').click(function () {
        var btn = $(this);
        $("#edit-branch-id").val(btn.data("id"));
        $("#edit-branch-code").val(btn.data("code"));
        $("#edit-branch-name").val(btn.data("name"));
        $("#edit-branch-city").val(btn.data("city"));
        $("#edit-branch-phone").val(btn.data("phone"));
        $("#edit-branch-email").val(btn.data("email"));
        $("#edit-branch-status").val(btn.data("status"));
    });

    // Shared submit handler (Add + Edit) with Parsley validation
    function submitBranch(form, action, savingText) {
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
                    Swal.fire({ icon: "success", title: "Success!", text: "Branch saved successfully!" })
                        .then(() => location.reload());
                } else {
                    btn.removeAttr("disabled").html(btnHtml);
                    Swal.fire({ icon: "error", title: "Error!", text: resp });
                }
            },
        });
    }

    $("#form-add-branch").on("submit", function (e) {
        e.preventDefault();
        submitBranch($(this), "add_pos_branch", "Saving...");
    });

    $("#form-edit-branch").on("submit", function (e) {
        e.preventDefault();
        submitBranch($(this), "update_pos_branch", "Updating...");
    });
});
