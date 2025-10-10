let state_date = '';
let end_date = '';

(function($){
    $(function(){
        $('#id_1').datetimepicker({
            "allowInputToggle": true,
            "showClose": true,
            "showClear": true,
            "showTodayButton": true,
            "format": "YYYY-MM-DD hh:mm:ss A",
        });
        $('#id_2').datetimepicker({
            "allowInputToggle": true,
            "showClose": true,
            "showClear": true,
            "showTodayButton": true,
            "format": "YYYY-MM-DD hh:mm:ss A",
        });
       
    });
})(jQuery);


$(function () {
    

    $('.select2').select2();

    // $('.datetimepicker').datetimepicker({
    //     format:'Y/m/d H:i',
    //     startDate: '+3d'
    // })


    oTable = $('#data-table').DataTable( {
        "order": [[ 0, "desc" ]]
    } );
 
    $('#search-input').keyup(function(){
        oTable.search($(this).val()).draw() ;
    })

    $("#form-add").on('submit', function(e){
        e.preventDefault();
        var form = $(this);

        form.parsley().validate();

        if (form.parsley().isValid()){ 
            e.preventDefault()
            $('.submitbutton').attr('disabled',true).html('Saving...');
            $.ajax({
                url:'ajax.php?action=save_time_logs',
                method:'POST',
                data:$(this).serialize(),
                error:err=>{
                    handleError();
                    $('.submitbutton').removeAttr('disabled');

                },
                success:function(resp){      
                    if(resp == 1){
                        toastr['success']('Time log  successfully created!',  "Success Notification");
                        setTimeout(function(){
                            location.reload()
                        },2000)
                    }else{
                        handleError();
                    }
                }
            })
        }
    });

});


$('#id_1').on('dp.change', function(e){ 
    start_date = e.date;
    calculate_amount();
})

$('#id_2').on('dp.change', function(e){ 
    end_date = e.date;
    calculate_amount();
})

function calculate_amount(){
    if(start_date !=='' || end_date !==''){
        var start = moment(start_date);   
        var end = moment(end_date); 
        let total = end.diff(start, 'minutes');

        var dt1 = new Date(start);
        var dt2 = new Date(end);


        var diff =(dt2.getTime() - dt1.getTime()) ;
        var hours = Math.floor(diff / (1000 * 60 * 60));
        diff -= hours * (1000 * 60 * 60);
        var mins = Math.floor(diff / (1000 * 60));
        diff -= mins * (1000 * 60);
        if(!isNaN(hours)){
          $("#time-log-display").html(hours+'Hrs '+ mins+'Mins');
        }
       // console.log('total', total);
      
       // console.log(moment.duration('1:50').asHours());
        //console.log('total',total);
        // if(!isNaN(total)){
        //     $("#total_hours").val(total);
        // }
    }else{
        $("#total_hours").val("");
    }
    
}

$('.remove_timelog').click(function(){
    var d = '"'+($(this).attr('data-id')).toString()+'"';
    _conf("Are you sure to delete this employee's log time?","remove_timelogs",[d])
})

function remove_timelogs(id){    
    $.ajax({
        url:'ajax.php?action=delete_employee_timelogs',
        method:"POST",
        data:{id: id},
        error:err=>{
            console.log(err)
            handleError();

        },
        success:function(resp){  
              $('#confirm_modal').modal('hide')
                if(resp == 1){
                    toastr['success']('Selected log time successfully deleted!',  "Success Notification");
                }
                setTimeout(function(){
                    location.reload()
                },2000)
            }
    })
}





