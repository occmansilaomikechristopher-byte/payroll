(function ($) {
    $(function () {
        $("#date-picker").select2({
            dropdownParent: $("#form-add"),
        });
        $("#employee_id").select2({
            dropdownParent: $("#form-add"),
        });
    });
})(jQuery);

$(document).ready(function () {
    function initializeDateTimePicker() {
        $(".date").datetimepicker({
            allowInputToggle: true,
            showClose: true,
            showClear: true,
            showTodayButton: true,
            format: "hh:mm:ss A",
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
    }

    // Initialize the datetime picker for the initial input
    initializeDateTimePicker();
    // Function to clone the div
    $("#container-clone").on("click", ".cloneButton", function () {
        var clonedDiv = $(this).closest(".item").clone();
        clonedDiv.find(".date").val("08:00:00"); // Clear the value of the cloned input
        $("#container-clone").append(clonedDiv);
        initializeDateTimePicker(); // Re-initialize the datetime picker for the new input
    });

    // Function to remove the div
    $("#container-clone").on("click", ".removeButton", function () {
        if ($("#container-clone .item").length > 1) {
            // Ensure at least one item remains
            $(this).closest(".item").remove();
        } else {
            alert("You must have at least one date and time input field.");
        }
    });

    $("#form-add").on("submit", async function (e) {
        e.preventDefault();
        var form = $(this);

        form.parsley().validate();

        if (form.parsley().isValid()) {
            e.preventDefault();
            // $('.submitbutton').attr('disabled',true).html('Saving...');
            Swal.fire({
                title: "Saving, please wait...",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });
            await new Promise((resolve) => setTimeout(resolve, 1000));
            $.ajax({
                url: "ajax.php?action=save_employee_attendance",
                method: "POST",
                data: $(this).serialize(),
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
                            text: "Attendace  successfully saved.",
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
    });
});

async function deleteDTRLogs(id) {
    const result = await Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!",
    });

    // If the user confirmed the deletion
    if (result.isConfirmed) {
        // Show loading dialog
        Swal.fire({
            title: "Deleting, please wait...",
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });
        await new Promise((resolve) => setTimeout(resolve, 1000));
        $.ajax({
            url: "ajax.php?action=delete_dtr_logs",
            method: "POST",
            data: {
                id: id,
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
                        text: "Selected payroll successfully deleted.",
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

function handleError(e) {
    $(".submitbutton").removeAttr("disabled");
    $(".fa-spinner-button").hide();
    toastr["error"](
        e ? e : "Someting went wrong. Please contact administrator.",
        "Error Notification"
    );
}

function addSchedule(id) {
    $("#id").val(id);
    $("#modal").modal("show");
}

// Single event handler for all updates
$(document).on("click", ".update-dtr-field", function () {
    const button = $(this);
    const id = button.data("id");
    const fieldType = button.data("field");
    const inputField = button.closest(".editable-field").find("input");


    updateDTRField(inputField[0], id, fieldType);
});

async function updateDTRField(el, id, fieldType) {
   
    const inputField = $(el).closest(".input-group").find('input[type="text"]');
    const value =  $(el).val();
    

    // Field configuration
    const fieldConfig = {
        work_hours: {
            title: "Hours Worked",
            field: "work_hours",
            color: "#28a745",
        },
        overtime: {
            title: "Overtime",
            field: "overtime",
            color: "#ffc107",
        },
        undertime: {
            title: "Undertime",
            field: "undertime",
            color: "#17a2b8",
        },
        late: {
            title: "Late",
            field: "late",
            color: "#dc3545",
        },
    };

    const config = fieldConfig[fieldType];
    if (!config) {
        handleError("Invalid field type");
        return;
    }

    // Validation
    if (Number.isNaN(value) || value < 0) {
        Swal.fire({
            icon: "error",
            title: "Invalid Value",
            text: `Please enter a valid ${config.title.toLowerCase()} value.`,
        });
        return;
    }

    // Confirmation dialog
    const result = await Swal.fire({
        title: `Update ${config.title}?`,
        text: `Are you sure you want to update ${config.title.toLowerCase()} to ${value}?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: config.color,
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes, update it!",
        cancelButtonText: "Cancel",
    });

    if (!result.isConfirmed) return;

    // Show loading
    Swal.fire({
        title: `Updating ${config.title}...`,
        text: "Please wait while we save your changes.",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    try {
        // Simulate API delay
        await new Promise((resolve) => setTimeout(resolve, 800));

        // Make API call
        const response = await $.ajax({
            url: "ajax.php?action=update_dtr_logs",
            method: "POST",
            data: {
                id: id,
                [config.field]: value,
            },
            dataType: "JSON",
        });

        // Handle response
        if (response?.result) {
            await Swal.fire({
                icon: "success",
                title: "Updated!",
                text: `${config.title} has been successfully updated.`,
                confirmButtonColor: config.color,
                timer: 2000,
                timerProgressBar: true,
            });

            // Optional: Update UI without full reload for better UX
            updateUIAfterSuccess(el, value, config);
        } else {
            throw new Error(response?.message || "Update failed");
        }
    } catch (error) {
        Swal.close();
        handleError(error.message || "An error occurred while updating");
    }
}

// Optional: Update UI without page reload for better user experience
function updateUIAfterSuccess(el, value, config) {
    // Update the input field visually
    const inputField = $(el).closest(".input-group").find('input[type="text"]');

    // Add success animation
    inputField.addClass("update-success");
    setTimeout(() => inputField.removeClass("update-success"), 2000);

    // If you want to update totals without reloading, you could add that logic here
    // For now, we'll keep the reload for data consistency
    location.reload();
}

// Individual wrapper functions for backward compatibility
async function updateHoursWork(el, id) {
    return updateDTRField(el, id, "work_hours");
}

async function updateOvertime(el, id) {
    return updateDTRField(el, id, "overtime");
}

async function updateUndertime(el, id) {
    return updateDTRField(el, id, "undertime");
}

async function updateLate(el, id) {
    return updateDTRField(el, id, "late");
}

// Enhanced error handler
function handleError(message) {
    Swal.fire({
        icon: "error",
        title: "Error",
        text: message || "Something went wrong. Please try again.",
        confirmButtonColor: "#dc3545",
    });
}

let isduplicate = "<?= $is_duplicate ?>";

// (function($) {
//     $(function() {
//         var isMouseDown = false,
//             $panelOne = $(".panel.one"),
//             $panelTwo = $(".panel.two"),
//             $panelContainer = $panelOne.parent(),
//             getParentWidth = function() {
//                 return $panelContainer.width();
//             },
//             mouseMoveHandler = function(e) {
//                 if (!isMouseDown) return;

//                 var clientX = e.clientX || (e.touches && e.touches[0].clientX);
//                 if (isNaN(clientX))
//                     return;

//                 var width = (clientX / getParentWidth()) * 100;

//                 // don't allow a value that's smaller than zero;
//                 width = width < 0 ? 0 : width;

//                 // apply size to panel 1
//                 $panelOne.css({
//                     width: width + "%"
//                 });

//                 // apply size to panel 2
//                 $panelTwo.css({
//                     width: 100 - width + "%"
//                 });
//             };

//         // mouseDown event
//         $(".slider").on("mousedown touchstart", function() {
//             // only bind a the mouseMove handler on the first cycle
//             !isMouseDown && $panelContainer.on("mousemove touchmove", mouseMoveHandler);
//             isMouseDown = true;
//         });

//         $(window).on("mouseup touchend", function() {
//             isMouseDown = false;
//             // detach then mouseMove handler
//             $panelContainer.off("mousemove touchmove");
//         });
//     });
// })(jQuery);
$(document).ready(function () {
    // $(".table-basic").freezeTable();
    // $('#table-modal').one('shown.bs.modal', function(e) {
    //     $(this).find(".table-modal").freezeTable({
    //         'container': '#table-modal.modal',
    //     });
    // });
    // $(".table-columns-only").freezeTable({
    //     'freezeHead': false,
    // });
    // $(".table-head-only").freezeTable({
    //     'freezeColumn': false,
    // });
    // // 2 Columns to be fixed
    // $(".table-multi-columns").freezeTable({
    //     'columnNum': 2,
    // });
    // // Shadow enabled
    // $(".table-shadow").freezeTable({
    //     'shadow': true,
    // });
    // // Customized styles
    // $(".table-wrap-styles").freezeTable({
    //     'headWrapStyles': {
    //         'box-shadow': '0px 9px 10px -5px rgba(159, 159, 160, 0.8)'
    //     },
    // });
    // $(".table-with-scrollbar").freezeTable({
    //     'scrollBar': true,
    // });
    // // Freeze Column(s) Keep
    // $(".table-column-keep").freezeTable({
    //     'columnNum': 2,
    //     'columnKeep': true,
    // });
});
$(document).ready(function () {
    $("#myInput").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#table-1 tbody tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
        // $("#table-2 tbody tr").filter(function() {
        //     $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        // });
    });
});

function approveDtr(id) {
    if (isduplicate == "1") {
        Swal.fire({
            icon: "error",
            title: "Erorr!",
            text: "The system cannot process duplicate attendance entries. Please ensure you are submitting a unique record.",
        }).then((result) => {});
        return;
    }
    Swal.fire({
        title: "Are you sure?",
        text: "You are about to approve this DTR.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, approve it!",
    }).then(async (result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: "Approving, please wait...",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });
            await new Promise((resolve) => setTimeout(resolve, 1000));
            $.ajax({
                url: "ajax.php?action=update_status_dtr",
                method: "POST",
                dataType: "JSON",
                data: {
                    id,
                    status,
                },
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
                            text: "DTR successfully approve.",
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = `index.php?page=dtr`;
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Erorr!",
                            text: res.message,
                        }).then((result) => {});
                        return false;
                    }
                },
            });
        }
    });
}
