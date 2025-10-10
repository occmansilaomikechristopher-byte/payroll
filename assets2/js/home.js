$(function() {
    $("#week-select").select2();
});

$("#week-select").change(function () {
    $("#form-week").submit();
});

