// $("#search-input").keyup(function () {
//     oTable1.search($(this).val()).draw();
//     oTable.search($(this).val()).draw();
// });

let btnText = "Create";
let id = null;

// Auto-refresh interval for DTR tables (every 5 seconds)
let dtrRefreshInterval = null;

function startDTRAutoRefresh() {
    // Clear any existing interval
    if (dtrRefreshInterval) {
        clearInterval(dtrRefreshInterval);
    }
    
    // Refresh every 5 seconds
    dtrRefreshInterval = setInterval(() => {
        if ($.fn.DataTable.isDataTable('#data-table')) {
            $('#data-table').DataTable().ajax.reload(null, false);
        }
    }, 5000);
}

function stopDTRAutoRefresh() {
    if (dtrRefreshInterval) {
        clearInterval(dtrRefreshInterval);
        dtrRefreshInterval = null;
    }
}

$(document).ready(function () {
    if ($('#data-table').length) {
        $('#data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: { url: 'dtr-server.php', type: 'POST' },
            columns: [
                { data: 'period' },
                { data: 'site' },
                { data: 'uploaded_by' },
                { data: 'timekeeper_name' },
                { data: 'approve_by' },
                { data: 'action', orderable: false, searchable: false, className: 'text-center' },
            ],
        }).on('draw.dt', function () {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
                bootstrap.Tooltip.getInstance(el)?.dispose();
                new bootstrap.Tooltip(el, { trigger: 'hover' });
            });
        });
    }

    // Start auto-refresh for DTR tables
    startDTRAutoRefresh();

    // Manual refresh button handler
    $(document).on("click", "#refresh-dtr-btn", function () {
        var btn = $(this);
        btn.prop("disabled", true);
        btn.find("i").addClass("ri-spin");
        
        if ($.fn.DataTable.isDataTable('#data-table')) {
            $('#data-table').DataTable().ajax.reload(null, false);
        }
        
        setTimeout(() => {
            btn.prop("disabled", false);
            btn.find("i").removeClass("ri-spin");
        }, 1000);
    });

    // View button event listener
    $(document).on("click", ".view-dtr", function () {
        var id = $(this).attr("data-id");
        var timekeeper = $(this).attr("data-timekeeper");
        var device = $(this).attr("data-device");
        var site = $(this).attr("data-site");
        var status = $(this).attr("data-status");

        window.location.href = "index.php?page=dtr-details&id=" + id + 
                               "&timekeeper_name=" + timekeeper + 
                               "&device_id=" + device + 
                               "&site_id=" + site + 
                               "&status=" + status;
    });
});

$(function () {
  
    $("#data-table1").DataTable();
    
});

function readFileAsText(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = () => reject(reader.error);
        reader.readAsText(file);
    });
}

function isJsonString(value) {
    try {
        JSON.parse(value);
        return true;
    } catch (e) {
        return false;
    }
}

function parseUploadContent(content) {
    const trimmed = content.trim();

    if (isJsonString(trimmed)) {
        return JSON.parse(trimmed);
    }

    try {
        const decoded = atob(trimmed);
        if (isJsonString(decoded.trim())) {
            return JSON.parse(decoded.trim());
        }
    } catch (error) {
        // not base64 JSON; continue with plain text parsing
    }

    return parseBiometricData(trimmed);
}

(function () {
    // DTR upload helpers
})();

$("#fileUploadForm").on("submit", async function (e) {
    e.preventDefault();
    const form = $(this);
    if (!form.parsley().isValid()) {
        return;
    }

    const fileBiometric = $("#fileBiometric")[0]?.files?.[0];
    if (!fileBiometric) {
        handleError("Please select a biometric file to upload.");
        return;
    }

    const extension = fileBiometric.name.split('.').pop().toLowerCase();
    if (extension !== 'dat') {
        handleError("Only .dat files are accepted.");
        return;
    }

    const formData = new FormData(this);
    let response = null;

    try {
            Swal.fire({
                title: "Uploading, please wait...",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            response = await $.ajax({
                url: "ajax.php?action=upload-biometric-dtr",
                method: "POST",
                dataType: "JSON",
                data: formData,
                processData: false,
                contentType: false,
            });

            Swal.close();

        if (response?.result) {
            // Build HTML summary for better presentation
            let summaryHtml = '<div style="text-align: left; font-size: 14px;">';
            summaryHtml += '<div style="margin-bottom: 10px;"><strong>✓ Imported Records:</strong> <span style="color: #28a745; font-weight: bold;">' + (response.imported_count || 0) + '</span></div>';
            
            if (response.skipped_count > 0) {
                summaryHtml += '<div style="margin-bottom: 10px;"><strong>⚠ Skipped:</strong> <span style="color: #ffc107;">' + response.skipped_count + '</span></div>';
            }
            
            if (Array.isArray(response.unknown_codes) && response.unknown_codes.length > 0) {
                const unknownList = response.unknown_codes.slice(0, 10).join(', ');
                const moreText = response.unknown_codes.length > 10 ? ` +${response.unknown_codes.length - 10} more` : '';
                summaryHtml += '<div style="margin-bottom: 10px;"><strong>❌ Unknown Codes:</strong> <span style="color: #dc3545;">' + unknownList + moreText + '</span></div>';
            }
            summaryHtml += '</div>';

            Swal.fire({
                icon: "success",
                title: "Upload Completed!",
                html: summaryHtml,
                didOpen: () => {
                    // Close upload modal if it exists
                    const uploadModal = document.getElementById('modal-dtr');
                    if (uploadModal) {
                        const bsModal = bootstrap.Modal.getInstance(uploadModal);
                        if (bsModal) bsModal.hide();
                    }
                }
            }).then((result) => {
                if (result.isConfirmed || result.isDismissed) {
                    // Refresh the pending DTR table (data-table1) - static table: redraw instead of ajax.reload
                    if ($.fn.DataTable.isDataTable('#data-table1')) {
                        $('#data-table1').DataTable().draw(false);
                    }
                    // Refresh the approved DTR table (data-table) if it exists and is initialized
                    if ($.fn.DataTable.isDataTable('#data-table')) {
                        $('#data-table').DataTable().ajax.reload(null, false);
                    }
                    // Reload the entire page after a short delay to refresh Employee Logs
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                    // Clear the form
                    $("#fileUploadForm")[0].reset();
                }
            });
        } else {
            handleError(response?.message || "An error occurred while uploading the DTR.");
        }
    } catch (error) {
        Swal.close();
        handleError(error?.responseJSON?.message || error?.statusText || error?.message || "An error occurred while uploading the DTR.");
    }
});

function edit_function(e) {
    //uni_modal("Edit Employee","manage_employee.php?id="+e)
    id = $(e).attr("id");
    $("#modal").modal("show");
    $(".title").html("Edit Department");
    $("#name").val($(e).attr("name"));
    $("#id").val($(e).attr("id"));
    $(".submitbutton").html("Save Changes");
}

$(document).on("hide.bs.modal", "#modal", function () {
    $(".title").html("Create Department");
    $("#name").val("");
    $("#id").val("");
    $(".submitbutton").html(btnText);
});

function uploadFile() {
    $("#modal-dtr").modal("show");
}

async function dataURLtoFile(dataurl, filename) {
    const arr = dataurl.split(",");
    const mime = arr[0].match(/:(.*?);/)[1];
    const bstr = atob(arr[arr.length - 1]);
    const n = bstr.length;
    const u8arr = new Uint8Array(n);

    for (let i = 0; i < n; i++) {
        u8arr[i] = bstr.charCodeAt(i);
    }

    const blob = new Blob([u8arr], {
        type: mime,
    });
    const file = new File([blob], filename, {
        type: mime,
    });
    return file;
}

const parseBiometricData = (fileContent) => {
    const lines = fileContent.split("\n").filter((line) => line.trim() !== "");
    const parsedData = lines.map((line) => {
        const [id, dateTime, device_id, ...biometricData] = line.split("\t");
        let updated_id = id.trim();
        return {
            updated_id,
            dateTime,
            device_id,
            biometricData,
        };
    });
    return parsedData;
};

async function deleteDTR(id) {
    // Show confirmation dialog
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
            url: "ajax.php?action=delete_dtr",
            method: "POST",
            data: { id: id },
            dataType: "JSON",
            error: (xhr) => {
                Swal.close();
                handleError(xhr.responseJSON?.message || xhr.statusText || "Unable to delete the DTR.");
            },
            success: function (response) {
                if (response?.result) {
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: "DTR successfully deleted.",
                    }).then((result) => {
                        if (result.isConfirmed || result.isDismissed) {
                            location.reload();
                        }
                    });
                } else {
                    Swal.close();
                    handleError(response?.message || "Unable to delete the DTR.");
                }
            },
        });
    }
}
