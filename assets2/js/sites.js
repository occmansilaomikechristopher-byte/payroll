oTable = $('#data-table').DataTable({
    "order": [[1, "asc"]] // Orders by the second column (index 1) in ascending order
});

$('#search-input').keyup(function(){
    oTable.search($(this).val()).draw();
});

let btnText = 'Create';
let id = null;

$(function () {

    $('.select2').select2({
        dropdownParent: $('#modal')
    });
    $(".fa-spinner-button").hide();
});

$("#form-add").on('submit', async function(e){
    e.preventDefault();
    var form = $(this);

    form.parsley().validate();

    if (form.parsley().isValid()){
        e.preventDefault()
        if(!id){
            $('.submitbutton').attr('disabled',true).html('Saving...');
            Swal.fire({
                title: "Creating, please wait...",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });
        }else{
            Swal.fire({
                title: "Saving, please wait...",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });
        }
        await new Promise((resolve) => setTimeout(resolve, 1000));
        $('.submitbutton').attr('disabled',true);
        $(".fa-spinner-button").show();
        $.ajax({
            url:'ajax.php?action=save_site',
            method:'POST',
            data:$(this).serialize(),
            error: (xhr, status, error) => {
                Swal.close();
                handleError(error || '');
                $(".submitbutton").removeAttr("disabled");
            },
            success:function(resp){  
                if(resp == 1){
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: 'New site successfully saved!',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                }else if(resp==2){
                    Swal.fire({
                        icon: "success",
                        title: "Success!",
                        text: 'Site successfully updated!',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                }
              
            }
        })
    }
});

function edit_function(e){  
    id = $(e).attr("id");
    $("#modal").modal('show');
    $(".title").html('Edit Site');
    $("#site_name").val($(e).attr("site_name"));
    $("#site_code").val($(e).attr("site_code"));
    $("#site_address").val($(e).attr("site_address"));
    $("#id").val($(e).attr("id"));
    $("#cluster-select").val($(e).attr("cluster_id")).trigger("change");
    $("#timekeeper-select").val($(e).attr("timekeeper_id")).trigger("change");
    $("#pic-select").val($(e).attr("pic_id")).trigger("change");
    if($(e).attr("status") == 1){
        $("#status2").prop('checked', true);
    }
    $('.submitbutton').html('Save Changes');
}



$(document).on('hide.bs.modal','#modal', function () {
    window.location.reload();
    $(".title").html('Create Site');
    $("#name").val("");
    $("#id").val("");
    $('.submitbutton').html(btnText);

});