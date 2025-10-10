let btnText = 'Create';
let id = null;

oTable = $('#data-table').DataTable();   
$('#search-input').keyup(function(){
      oTable.search($(this).val()).draw() ;
})

$(function () {

    $("#form-add").on('submit', async function(e){
        e.preventDefault();
        var form = $(this);

        form.parsley().validate();

        if (form.parsley().isValid()){
            e.preventDefault()
            if(id){
                $('.submitbutton').attr('disabled',true).html('Saving...');
            }else{
                $('.submitbutton').attr('disabled',true).html('Adding...');
            }
	    	await new Promise((resolve) => setTimeout(resolve, 1000));
            $.ajax({
                url:'ajax.php?action=save_refunds',
                method:'POST',
                data:$(this).serialize(),
                error:err=>{
                    alert("Something went wrong!");
                  $('.submitbutton').removeAttr('disabled');

                },
                success:function(resp){  
                    if(resp == 1){
                    	Swal.fire({
                            icon: "success",
                            title: "Success!",
                            text: 'New deduction successfully saved!',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.reload();
                            }
                        });
                    }else if(resp==2){
                        Swal.fire({
                            icon: "success",
                            title: "Success!",
                            text: 'Deduction successfully updated!',
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
    $("#modal").modal('show');
    $(".title").html('Edit Deduction');
    $("#refunds").val($(e).attr("refunds"));
    $("#id").val($(e).attr("id"));
    $('.submitbutton').html('Save Changes');
}




$(document).on('hide.bs.modal','#modal', function () {
    window.location.reload();

});