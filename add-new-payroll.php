
<?php include('./db_connect.php'); ?>

<!doctype html>
<html lang="en">


<head>
<title>Payroll Management</title>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<meta name="description" content="Lucid Bootstrap 4x Admin Template">
<meta name="author" content="WrapTheme, design by: ThemeMakker.com">

<link rel="icon" href="favicon.ico" type="image/x-icon">

<link rel="stylesheet" href="assets/vendor/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/vendor/font-awesome/css/font-awesome.min.css">


<link rel="stylesheet" href="assets/css/main.css">
<link rel="stylesheet" href="assets/css/color_skins.css">
</head>

<body class="theme-cyan">
	<!-- WRAPPER -->
	<div id="wrapper">
        <div class="freeze-table">
            <table class="table">
                <thead >
                    <tr>
                        <th>Employee</th>
                        <th>Salary</th>
                        <th>Late</th>
                        <th>Absences</th>
                        <?php
                           $query = $conn->query("SELECT * FROM deductions order by id asc");
                           while($row=$query->fetch_assoc()){
                        ?>
                        <th><?php echo $row['deduction']?></th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody class="scrollContent">
                  <?php
                        $dept = $conn->query("SELECT * from employee ");
                            while($row=$dept->fetch_assoc()){
                              
					?>
                    <tr>
                        <td><?php echo $row['firstname']?> <?php echo $row['middlename']?>. <?php echo $row['lastname']?></td>
                        
                    </tr>
                    <?php }?>
                </tbody>
            </table>
        </div>
	</div>
</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="assets/js/freeze-table.min.js"></script>
<script>
  $(function() {
    $('.freeze-table').freezeTable({});
   });

</script>

</html>
