
oTable = $('#data-table').DataTable();   
$('#search-input').keyup(function(){
      oTable.search($(this).val()).draw() ;
})

let btnText = 'Create';
let id = null;

$(function () {
    $(".fa-spinner-button").hide();
});

$("#form-add").on('submit', function(e){
    e.preventDefault();
    var form = $(this);

    form.parsley().validate();

    if (form.parsley().isValid()){
        e.preventDefault()
        $('.submitbutton').attr('disabled',true);
        $(".fa-spinner-button").show();
        if(id){
            $('.submitbutton').attr('disabled',true).html('Saving...');
        }else{
            $('.submitbutton').attr('disabled',true).html('Creating...');
        }
        
        $.ajax({
            url:'ajax.php?action=save_department',
            method:'POST',
            data:$(this).serialize(),
            error:err=>{
                alert("Something went wrong!");
              $('.submitbutton').removeAttr('disabled');

            },
            success:function(resp){  
                if(resp == 1){
                    toastr['success']('New department successfully saved!',  "Success Notification");
                }else if(resp==2){
                    toastr['success']('Department successfully updated!',  "Success Notification");
                }
                setTimeout(function(){
                    location.reload()
                },2000)
            }
        })
    }
});

function edit_function(e){
    //uni_modal("Edit Employee","manage_employee.php?id="+e)
    id = $(e).attr("id");
    $("#modal").modal('show');
    $(".title").html('Edit Department');
    $("#name").val($(e).attr("name"));
    $("#id").val($(e).attr("id"));
    $('.submitbutton').html('Save Changes');
}



$(document).on('hide.bs.modal','#modal', function () {
    $(".title").html('Create Department');
    $("#name").val("");
    $("#id").val("");
    $('.submitbutton').html(btnText);

});