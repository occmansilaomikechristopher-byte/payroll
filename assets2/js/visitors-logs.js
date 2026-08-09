(function($){
    $('#site-select').select2({
        dropdownParent: $('#modal-filter-add')
    });
    $(function(){
        $(".datetimepicker").datetimepicker({
            allowInputToggle: true,
            showClose: true,
            showClear: true,
            showTodayButton: true,
            format: "YYYY/MM/DD",
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
        
       
    });
})(jQuery);


$(function () {


    let oTable = $('#table-logs').DataTable( {
        "order": [[ 2, "asc" ]]
    } );
 
    // $('#search-input').keyup(function(){
    //     oTable.search($(this).val()).draw() ;
    // })
    

  



    // var start = moment().subtract(29, 'days');
    // var end = moment();

    // function cb(start, end) {
    //     $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    // }

    // $('#reportrange').daterangepicker({
    //     // startDate: start,
    //     // endDate: end,
    //     startDate: moment().startOf('hour'),
    //     endDate: moment().startOf('hour').add(23, 'hour'),
    //     ranges: {
    //        'Today': [moment(), moment()],
    //        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
    //        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
    //        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
    //        'This Month': [moment().startOf('month'), moment().endOf('month')],
    //        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
    //     }
    // }, cb);

    // cb(start, end);

    $("#form-add").on('submit', function(e){
        e.preventDefault();
        var form = $(this);

        form.parsley().validate();

        if (form.parsley().isValid()){ 
            e.preventDefault()
            $('.submitbutton').attr('disabled',true).html('Saving...');
            $.ajax({
                url:'ajax.php?action=save_employee_attendance',
                method:'POST',
                data:$(this).serialize(),
                error:err=>{
                    alert("Something went wrong!");
                  $('.submitbutton').removeAttr('disabled');

                },
                success:function(resp){  
                    if(resp == 1){
                    	toastr['success']('Attendance data successfully saved!',  "Success Notification");
                    }
                    setTimeout(function(){
						location.reload()
					},2000)
                }
            })
        }
    });

});

$("#form-filter").on('submit', function(e){
    e.preventDefault();
    var form = $(this);

    form.parsley().validate();

    if (form.parsley().isValid()){
        var params = $(this).serializeArray().filter(function(p){ return p.name !== 'page'; });
        window.location.href = window.location.pathname + '?' + $.param(params);
    }
});


$('.remove_attendance').click(function(){
    var d = '"'+($(this).attr('data-id')).toString()+'"';
    _conf("Are you sure to delete this employee's time log record?","remove_attendance",[d])
})
$('.rem_att').click(function(){
    var $id=$(this).attr('data-id');
    _conf("Are you sure to delete this time log?","rem_att",[$id])
})

function remove_attendance(id){
    $.ajax({
        url:'ajax.php?action=delete_employee_attendance',
        method:"POST",
        data:{id:id},
        error:err=>console.log(err),
        success:function(resp){
                if(resp == 1){
                    toastr['success']("Selected employee's time log data successfully deleted",  "Success Notification");
                    setTimeout(function(){
						location.reload()
					},2000)
                }
            }
    })
}
function rem_att(id){
    $.ajax({
        url:'ajax.php?action=delete_employee_attendance_single',
        method:"POST",
        data:{id:id},
        error:err=>console.log(err),
        success:function(resp){
                if(resp == 1){
                    toastr['success']("Selected employee's time log data successfully deleted",  "Success Notification");
                    setTimeout(function(){
						location.reload()
					},2000)
                }
            }
    })
}

