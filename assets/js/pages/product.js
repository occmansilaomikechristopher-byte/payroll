document
    .getElementById("product-image-input")
    .addEventListener("change", function (event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById("product-img").src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

(function () {
    ("use strict");
    var forms = document.querySelectorAll("#form");
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener(
            "submit",
            function (event) {
                event.preventDefault();
                event.stopPropagation();
                let formData = new FormData(this); // Create a FormData object from the form
                let _token = $('meta[name="csrf-token"]').attr('content');
                formData.append('_token', _token); // Append the CSRF token to the form data

                if (form.checkValidity()) {
                    $.ajax({
                        url: "/products",
                        type: "POST",
                        data: formData,
                        contentType: false, 
                        processData: false, 
                        success: function (response) {
                            
                        },
                        error: function (error) {
                            $("#error-message").html(
                                `<div class="alert alert-danger mt-4" role="alert" id="error-message">Invalid credential!</div>`
                            );
                            $("#btn-submit").attr("disabled", false);
                            $("#btn-submit").html(`Sign Me In`);
                        },
                    });
                }
                form.classList.add("was-validated");
                return false;
            },
            false
        );
        return false;
    });
})();


