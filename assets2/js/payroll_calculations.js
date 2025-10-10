const scrollContainer = document.getElementById("table-responsive2");

// Restore scroll position from localStorage
window.addEventListener("load", () => {
    const scrollPos = localStorage.getItem("scrollPosition");
    if (scrollPos) {
        scrollContainer.scrollLeft = scrollPos;
    }
});

// Save scroll position before unload
scrollContainer.addEventListener("scroll", () => {
    localStorage.setItem("scrollPosition", scrollContainer.scrollLeft);
});

$(function () {
    $(".select2").select2();
});
$("#form-payroll").on("submit", function (event) {
    event.preventDefault();
});
$(document).ready(function () {
    setTimeout(() =>{
        $(".topnav-hamburger").click();
    },1000)
    $('.net-class').each(function() {
        if (parseFloat($(this).val()) <= 0) {  // Check if value is ≤ 0
            $(`.name-${$(this).attr("did")}`).addClass('net-danger'); // Add class
        }
    });

    $("#myInput").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#table-1 tbody tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
        $("#table-2 tbody tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
    updatePayroll();
});

function view_site() {
    $("#modal-sites-2").modal("show");
}

async function updateData(el, id, type, dd_id = null) {
    const inputField = $(el).closest(".input-group").find('input[type="text"]');
    const value = parseFloat(inputField.val());
    if (Number.isNaN(value)) {
        Swal.fire({
            icon: "error",
            title: "Oops...",
            text: "Invalid value!",
        });
    } else {
        const result = await Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, update it!",
        });
        // If the user confirmed the deletion
        if (result.isConfirmed) {
            // Show loading dialog
            Swal.fire({
                title: "Saving, please wait...",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });
            await new Promise((resolve) => setTimeout(resolve, 1000));
            $.ajax({
                url: "ajax.php?action=update_payroll_item",
                method: "POST",
                data: {
                    id: id,
                    value,
                    type,
                    dd_id,
                },
                dataType: "JSON",
                error: (xhr, status, error) => {
                    Swal.close();
                    handleError(error || "");
                },
                success: function (res) {
                    if (res?.result) {
                        Swal.fire({
                            icon: "success",
                            title: "Success!",
                            text: "Selected logs successfully saved.",
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload();
                            }
                        });
                    } else {
                        Swal.close();
                        handleError(res?.message || "");
                    }
                },
            });
        }
    }
}

async function updatePayroll() {
    const form_data = $("#form-payroll").serialize();
    $.ajax({
        url: "ajax.php?action=save_payroll_amount",
        method: "POST",
        data: form_data,
        dataType: "JSON",
        success: function (res) {},
    });
}

$("#site-select").on("change", function () {
    var selectedValue = $(this).val();
    window.location.href = `payroll_calculations.php?id=${id}&site_id=${selectedValue}`;
});

function handleError(e) {
    $(".submitbutton").removeAttr("disabled");
    $(".fa-spinner-button").hide();
    toastr["error"](
        e ? e : "Someting went wrong. Please contact administrator.",
        "Error Notification"
    );
}

async function lockPayroll(id) {
    const result = await Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, lock it!",
    });
    // If the user confirmed the deletion
    if (result.isConfirmed) {
        // Show loading dialog
        Swal.fire({
            title: "Saving, please wait...",
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });
        await new Promise((resolve) => setTimeout(resolve, 1000));
        $.ajax({
            url: "ajax.php?action=update_payroll_status",
            method: "POST",
            data: {
                id: id,
                status: 2,
            },
            error: (xhr, status, error) => {
                Swal.close();
                handleError(error || "");
            },
            success: function (res) {
                if (res) {
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: "Selected logs successfully saved.",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    });
                } else {
                    Swal.close();
                    handleError(res?.message || "");
                }
            },
        });
    }
}

const myDiv = document.getElementById("myDiv");

function openFullscreen() {
    $(".table-responsive2").css("height", "80vh");
    $("#sf").hide();
    $("#hf").show();
    if (myDiv.requestFullscreen) {
        myDiv.requestFullscreen();
    } else if (myDiv.webkitRequestFullscreen) {
        // Safari
        myDiv.webkitRequestFullscreen();
    } else if (myDiv.msRequestFullscreen) {
        // IE11
        myDiv.msRequestFullscreen();
    }
}

function closeFullscreen() {
    $(".table-responsive2").css("height", "40vh");
    $("#hf").hide();
    $("#sf").show();
    if (document.exitFullscreen) {
        document.exitFullscreen();
    } else if (document.webkitExitFullscreen) {
        // Safari
        document.webkitExitFullscreen();
    } else if (document.msExitFullscreen) {
        // IE11
        document.msExitFullscreen();
    }
}

$("#form-print-settings").on("submit", async function (e) {
    e.preventDefault();
    var form = $(this);
    form.parsley().validate();

    if (form.parsley().isValid()) {
        e.preventDefault();
        Swal.fire({
            title: "Updating, please wait...",
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });
        await new Promise((resolve) => setTimeout(resolve, 1000));
        form_data = $(this).serialize();
        $.ajax({
            url: "ajax.php?action=update_payroll_print",
            method: "POST",
            // dataType: "JSON",
            data: $(this).serialize(),
            error: (xhr, status, error) => {
                Swal.close();
                handleError(error || "");
            },
            success: function (res) {
                Swal.fire({
                    icon: "success",
                    title: "Success!",
                    text: "Print Setting successfully updated!",
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    }
                });
            },
        });
    }
});
let changedInputs = [];
$(document).ready(function () {
    countUnsaved();

    $(".input-class").on("input", function () {
        let inputId = $(this).data("id");
        let inputType = $(this).data("type");
        let inputValue = $(this).val().trim(); // Trim to remove extra spaces
        let inputDID = $(this).data("dd_id");

        // Remove existing entry if it exists
        changedInputs = changedInputs.filter(
            (item) => !(item.id === inputId && item.type === inputType && item.dd_id === inputDID)
        );

        // Only add if value is not empty
        if (inputValue !== "") {
            changedInputs.push({
                id: inputId,
                type: inputType,
                value: inputValue,
                dd_id: inputDID
            });
        }
        countUnsaved();
    });
});

function countUnsaved() {
    if (changedInputs.length === 0) {
        $("#btn-unsaved").prop("disabled", true);
    } else {
        $("#btn-unsaved").prop("disabled", false);
    }
    $("#counter-unsaved").text(changedInputs.length);
}

async function saveUnsaved() {
    closeFullscreen();
    Swal.fire({
        title: "Saving, please wait...",
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });
    await new Promise((resolve) => setTimeout(resolve, 1000));
    $.ajax({
        url: "ajax.php?action=update_payroll_item_new",
        method: "POST",
        data: {
            items: changedInputs
        },
        dataType: "JSON",
        error: (xhr, status, error) => {
            Swal.close();
            handleError(error || "");
        },
        success: function (res) {
            if (res?.result) {
                Swal.fire({
                    icon: "success",
                    title: "Success!",
                    text: "Selected logs successfully saved.",
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            } else {
                Swal.close();
                handleError(res?.message || "");
            }
        },
    });
}
