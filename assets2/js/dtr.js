// $("#search-input").keyup(function () {
//     oTable1.search($(this).val()).draw();
//     oTable.search($(this).val()).draw();
// });

let btnText = "Create";
let id = null;

$(document).ready(function () {
    $('#data-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: 'dtr-server.php', type: 'POST' },
        columns: [
            { data: 'period' },
            { data: 'employer_name' },
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

(async () => {
    const file = await dataURLtoFile(
        "data:text/plain;base64,ICAgICAgICAgICAgIDQwCTIwMjQtMDYtMTcgNzozNToyMAk3CTAJMQkwCiAgICAgICAgICAgIDE5MgkyMDI0LTA2LTE3IDExOjUyOjQ3CTcJMAkxCTAKICAgICAgICAgICAgIDQwCTIwMjQtMDYtMTcgMTc6MDI6MjAJNwkwCTEJMAogICAgICAgICAgICAxOTIJMjAyNC0wNi0xNyAxNzo1Mjo0Nwk3CTAJMQkwCiAgICAgICAgICAgICA0MAkyMDI0LTA2LTE4IDA4OjAyOjIwCTcJMAkxCTAKICAgICAgICAgICAgMTkyCTIwMjQtMDYtMTggMDc6NTg6MjAJNwkwCTEJMAogICAgICAgICAgICAgNDAJMjAyNC0wNi0xOCAxNzoxMDoyMAk3CTAJMQkwCiAgICAgICAgICAgIDE5MgkyMDI0LTA2LTE4IDE3OjA4OjIwCTcJMAkxCTAKICAgICAgICAgICAgIDQwCTIwMjQtMDYtMTkgMDg6MDI6MTAJNwkwCTEJMAogICAgICAgICAgICAxOTIJMjAyNC0wNi0xOSAwNzo1ODoyMwk3CTAJMQkwCiAgICAgICAgICAgICA0MAkyMDI0LTA2LTE5IDE3OjEwOjIxCTcJMAkxCTAKICAgICAgICAgICAgMTkyCTIwMjQtMDYtMTkgMTc6MDg6MjMJNwkwCTEJMAogICAgICAgICAgICAgNDAJMjAyNC0wNi0yMCAwODowMjoyMQk3CTAJMQkwCiAgICAgICAgICAgIDE5MgkyMDI0LTA2LTIwIDA3OjU4OjI1CTcJMAkxCTAKICAgICAgICAgICAgIDQwCTIwMjQtMDYtMjAgMTc6MTA6MjAJNwkwCTEJMAogICAgICAgICAgICAxOTIJMjAyNC0wNi0yMCAxNzowODoxMAk3CTAJMQkwCiAgICAgICAgICAgICA0MAkyMDI0LTA2LTIxIDA4OjAyOjEwCTcJMAkxCTAKICAgICAgICAgICAgIDQwCTIwMjQtMDYtMjEgMTc6MTA6MTUJNwkwCTEJMAogICAgICAgICAgICAgNDAJMjAyNC0wNi0yMiAwODowMjoxNwk3CTAJMQkwCiAgICAgICAgICAgIDE5MgkyMDI0LTA2LTIyIDA3OjU4OjE5CTcJMAkxCTAKICAgICAgICAgICAgIDQwCTIwMjQtMDYtMjIgMTc6MTA6MTYJNwkwCTEJMAogICAgICAgICAgICAxOTIJMjAyNC0wNi0yMiAxNzowODoyMAk3CTAJMQkwCiAgICAgICAgICA=",
        "hello.txt"
    );
    // const reader = new FileReader();
    // reader.onload = function() {
    //     const parsedData = parseBiometricData(reader.result);
    //     console.table(parsedData)
    //     const myTable = createTable(parsedData);
    //     const targetDiv = document.getElementById("table-container");
    //     targetDiv.appendChild(myTable);
    // };
    // reader.readAsText(file);
    // const fileContentBioOrig = await readFileAsText(file);
    // const parsedData = parseBiometricData(fileContentBioOrig);
    // console.log({parsedData})
})();

$("#fileUploadForm").on("submit", async function (e) {
    e.preventDefault();
    var form = $(this);
    if (form.parsley().isValid()) {
        try {
            Swal.fire({
                title: "Uploading, please wait...",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });
            await new Promise((resolve) => setTimeout(resolve, 1000));
            const filePathDBFile = $("#fileDB")[0].files[0];
            const fileBiometric = $("#fileBiometric")[0].files[0];

            const fileContent = await readFileAsText(filePathDBFile);
            const filePathDB = atob(fileContent);
            const rawDatas = JSON.parse(filePathDB);
            // console.table(rawDatas);
            const fileContentBio = await readFileAsText(fileBiometric);
            const filePathBio = atob(fileContentBio);
            const bioDatas = JSON.parse(filePathBio);
            // const fileDataBio = await dataURLtoFile(
            //     bioDatas?.file,
            //     "hello.txt"
            // );
            // const fileContentBioOrig = await readFileAsText(fileDataBio);
            // const parsedDataBio = parseBiometricData(fileContentBioOrig);
            $.ajax({
                url: "ajax.php?action=manual-push-dtr",
                method: "POST",
                dataType: "JSON",
                data: { dtr: bioDatas, dtr_details: rawDatas },
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
                            text: "DTR successfully uploaded.",
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload();
                            }
                        });
                    } else {
                        Swal.close();
                        handleError(res?.message || "");
                        $(".submitbutton").removeAttr("disabled");
                    }
                },
            });
        } catch (error) {
            $(".submitbutton").removeAttr("disabled");
            Swal.close();
            handleError(res?.message || "");
        }
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
            error: (err) => {
                Swal.close();
                handleError();
            },
            success: function (resp) {
                if (resp == 1) {
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
                    handleError();
                }
            },
        });
    }
}
