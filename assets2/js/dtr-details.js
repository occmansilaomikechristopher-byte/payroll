(function ($) {
    $(function () {
        $('#date-picker').select2({
            dropdownParent: $('#form-add')
        });
        $('#employee_id').select2({
            dropdownParent: $('#form-add')
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
                previous: "fa fa-chevron-left",  // Fix for missing prev button
                next: "fa fa-chevron-right",    // Fix for missing next button
                today: "fa fa-crosshairs",
                clear: "fa fa-trash",
                close: "fa fa-times"
            }
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
            data: { id: id },
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

async function updateHoursWork(el, id) {
    const inputField = $(el)
        .closest(".input-group")
        .find('input[type="text"]');
    const hours = parseFloat(inputField.val());
    if (Number.isNaN(hours) || hours < 0 ) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Invalid value!',
          });
    }else{
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
                url: "ajax.php?action=update_dtr_logs",
                method: "POST",
                data: { id: id,work_hours: hours },
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

async function updateOvertime(el, id) {
    const inputField = $(el)
        .closest(".input-group")
        .find('input[type="text"]');
    const hours = parseFloat(inputField.val());
    if (Number.isNaN(hours) || hours < 0 ) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Invalid value!',
          });
    }else{
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
                url: "ajax.php?action=update_dtr_logs",
                method: "POST",
                data: { id: id,overtime: hours },
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

async function updateUndertime(el, id) {
    const inputField = $(el)
        .closest(".input-group")
        .find('input[type="text"]');
    const hours = parseFloat(inputField.val());
    if (Number.isNaN(hours) || hours < 0 ) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Invalid value!',
          });
    }else{
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
                url: "ajax.php?action=update_dtr_logs",
                method: "POST",
                data: { id: id,undertime: hours },
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

async function updateLate(el, id) {
    const inputField = $(el)
        .closest(".input-group")
        .find('input[type="text"]');
    const hours = parseFloat(inputField.val());
    if (Number.isNaN(hours) || hours < 0 ) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Invalid value!',
          });
    }else{
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
                url: "ajax.php?action=update_dtr_logs",
                method: "POST",
                data: { id: id,late: hours },
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

let isduplicate = '<?= $is_duplicate ?>';

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
$(document).ready(function() {

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
$(document).ready(function() {
    $("#myInput").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#table-1 tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
        // $("#table-2 tbody tr").filter(function() {
        //     $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        // });
    });
});

function approveDtr(id) {
    if (isduplicate == '1') {
        Swal.fire({
            icon: "error",
            title: "Erorr!",
            text: 'The system cannot process duplicate attendance entries. Please ensure you are submitting a unique record.',
        }).then((result) => {

        });
        return
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
                    status
                },
                error: (xhr, status, error) => {
                    Swal.close();
                    handleError(error || '');
                    $(".submitbutton").removeAttr("disabled");
                },
                success: function(res) {
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
                        }).then((result) => {

                        });
                        return false;
                    }
                },
            });
        }
    });
}