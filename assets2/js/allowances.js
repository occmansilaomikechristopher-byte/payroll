let btnText = 'Create';
let id = null; 

oTable = $('#data-table').DataTable();   
$('#search-input').keyup(function(){
      oTable.search($(this).val()).draw() ;
})

$(function () {

    $("#form-add").on('submit', function(e){
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
	    	
            $.ajax({
                url:'ajax.php?action=save_allowances',
                method:'POST',
                data:$(this).serialize(),
                error:err=>{
                    alert("Something went wrong!");
                  $('.submitbutton').removeAttr('disabled');

                },
                success:function(resp){  
                    if(resp == 1){
                    	toastr['success']('New allowance successfully created!',  "Success Notification");
                    }else if(resp==2){
                        toastr['success']('Allowance successfully updated!',  "Success Notification");
                    }
                    setTimeout(function(){
						location.reload()
					},2000)
                }
            })
        }
    });

});

function edit_function(e){
    //uni_modal("Edit Employee","manage_employee.php?id="+e)
    id = $(e).attr("id");
    $("#modal").modal('show');
    $(".title").html('Edit Allowance');
    $("#allowance").val($(e).attr("name"));
    $("#description").val($(e).attr("description"));
    $("#id").val($(e).attr("id"));
    $('.submitbutton').html('Save Changes');
}



$(document).on('hide.bs.modal','#modal', function () {
    $(".title").html('Create Department');
    $("#allowance").val("");
    $("#description").val("");
    $("#id").val("");
    $('.submitbutton').html(btnText);

}); 