$(document).ready(function () {
    if ($("#data-table").length) {
        $("#data-table").DataTable({
            order: [[1, "asc"]],
            pageLength: 10,
            columnDefs: [{ orderable: false, targets: 0 }],
        });
    }

    if ($("#inventory-table").length) {
        $("#inventory-table").DataTable({
            order: [[0, "asc"]],
            pageLength: 10,
        });
    }

    // Select2 for category/branch dropdowns inside modals
    $("#modal-add-product .select2").select2({
        dropdownParent: $("#modal-add-product"),
    });
    $("#modal-edit-product .select2").select2({
        dropdownParent: $("#modal-edit-product"),
    });

    // Live preview when a file is chosen
    function previewImage(input, imgId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $("#" + imgId).attr("src", e.target.result);
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    $("#add-product-image").on("change", function () {
        previewImage(this, "add-product-preview");
    });
    $("#edit-product-image").on("change", function () {
        previewImage(this, "edit-product-preview");
    });

    // Reset add-form preview when modal closes
    $("#modal-add-product").on("hidden.bs.modal", function () {
        $("#add-product-preview").attr("src", "assets/images/no-image.svg");
    });

    // Populate add-stock modal from button data attributes and open it reliably
    $(document).on("click", ".add-stock-btn", function (e) {
        e.preventDefault();
        var btn = $(this);
        $("#add-stock-product-id").val(btn.data("id") || "");
        $("#add-stock-qty").val("");
        var modalEl = document.getElementById("modal-add-stock");
        if (modalEl) {
            try {
                var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            } catch (err) {
                if (typeof $(modalEl).modal === "function") {
                    $(modalEl).modal("show");
                }
            }
        }
    });

    // Populate edit modal from button data attributes
    $(document).on("click", '[data-bs-target="#modal-edit-product"]', function () {
        var btn = $(this);
        $("#edit-product-id").val(btn.data("id"));
        $("#edit-product-code").val(btn.data("code"));
        $("#edit-product-name").val(btn.data("name"));
        $("#edit-product-category").val(btn.data("category")).trigger("change");
        $("#edit-product-branch").val(btn.data("branch")).trigger("change");
        $("#edit-product-qty").val(btn.data("qty"));
        $("#edit-product-price").val(btn.data("price"));
        $("#edit-product-cost").val(btn.data("cost"));
        $("#edit-product-unit").val(btn.data("unit"));
        $("#edit-product-reorder").val(btn.data("reorder"));
        $("#edit-product-desc").val(btn.data("desc"));
        $("#edit-product-status").val(btn.data("status"));
        // Image: store current filename + show it (or placeholder)
        var img = btn.data("image");
        $("#edit-product-current-image").val(img || "");
        $("#edit-product-image").val("");
        $("#edit-product-preview").attr("src", img ? "uploads/products/" + img : "assets/images/no-image.svg");
    });

    // Shared submit handler (Add + Edit) with Parsley validation
    function submitProduct(form, action, savingText) {
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
            data: new FormData(form[0]),
            processData: false,
            contentType: false,
            error: function () {
                Swal.close();
                btn.removeAttr("disabled").html(btnHtml);
                console.error('AJAX error adding/updating product');
                Swal.fire({ icon: "error", title: "Error!", text: "Something went wrong. Please check console / network tab and try again." });
            },
            success: function (resp) {
                console.log('AJAX response for product action:', resp);
                Swal.close();
                if (resp == 1) {
                    Swal.fire({ icon: "success", title: "Success!", text: "Product saved successfully!" })
                        .then(() => location.reload());
                } else {
                    btn.removeAttr("disabled").html(btnHtml);
                    Swal.fire({ icon: "error", title: "Error!", text: resp });
                }
            },
        });
    }

    $("#form-add-product").on("submit", function (e) {
        e.preventDefault();
        submitProduct($(this), "add_pos_product", "Saving...");
    });

    $("#form-add-stock").on("submit", function (e) {
        e.preventDefault();
        submitProduct($(this), "add_pos_product_stock", "Adding stock...");
    });

    $("#form-edit-product").on("submit", function (e) {
        e.preventDefault();
        // The edit form can operate in two modes: 'full' (update product) or 'stock' (cashier adds stock).
        // The server-side modal sets data-mode="stock" when used for cashier stock updates.
        var mode = $(this).data('mode') || 'full';
        if (mode === 'stock') {
            submitProduct($(this), "add_pos_product_stock", "Adding stock...");
        } else {
            submitProduct($(this), "update_pos_product", "Updating...");
        }
    });
});
