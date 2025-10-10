let btnText = 'Create';
let id = null;

oTable = $('#data-table').DataTable();   
$('#search-input').keyup(function(){
      oTable.search($(this).val()).draw() ;
})

$(function () {
    $("#form-add").on('submit',  async function(e){
        e.preventDefault();
        var form = $(this);
        form.parsley().validate();
        if (form.parsley().isValid()){
            e.preventDefault()
            Swal.fire({
                title: ` ${id ? 'Saving' : 'Creating' } , please wait...`,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });
            await new Promise((resolve) => setTimeout(resolve, 1000));
            $.ajax({
                url:'ajax.php?action=save_position',
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
                            text: 'New position successfully saved!',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.reload();
                            }
                        });
                    }else if(resp==2){
                        Swal.fire({
                            icon: "success",
                            title: "Success!",
                            text: 'Position successfully updated!',
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

});

function edit_function(e){
    //uni_modal("Edit Employee","manage_employee.php?id="+e)
    id = $(e).attr("id");
    let department_id = $(e).attr("department_id");
    $("#position-select").select2("val", department_id);
    $("#modal").modal('show');
    $(".title").html('Edit Position');
    $("#name").val($(e).attr("name"));
    $("#id").val($(e).attr("id"));
    $('.submitbutton').html('Save Changes');
}



$(document).on('hide.bs.modal','#modal', function () {
    $(".title").html('Create Position');
    $("#name").val("");
    $("#id").val("");
    $('.submitbutton').html(btnText);

});

$(document).on('hide.bs.modal','#modal', function () {
    window.location.reload();

});