


<?php
    include 'db_connect.php';
	// use Dompdf\Dompdf;
	// use Dompdf\Options;
	// require_once 'library/dompdf/autoload.inc.php';
	// ob_start();
	
?>

<?php
	$pay = $conn->query("SELECT * FROM payroll where id = ".$_GET['id'])->fetch_array();
	$pt = array(1=>"Monhtly",2=>"Semi-Monthly");
	
?>
<style>
.page-break {
    page-break-after: always;
}
.top-content{
	text-align: center;
}
 p{
	line-height: 0.3;
}

.middle-content {
	
	height: 120px;
	
	
}

.middle-content .row{
  width: 50%;
  float:left;
   
}

.middle-content .row2{
  width: 50%;
  float:right;

}

.border-center{
	border-left: 1px solid #000;
}

/* .table {
   border-collapse: collapse;
   border: 1px solid #000;
}

.table thead {
   border: 1px solid #000;
}


/* And this to your table's `td` elements. */
/*.table td {
   padding: 0; 
   margin: 0;
   vertical-align: baseline;
} */

.text-right{
	text-align: right;
}


table {
    border-left: 0.01em solid #000;
    border-right: 0;
    border-top: 0.01em solid #000;
    border-bottom: 0;
    border-collapse: collapse;
}
table td,
table th {
    border-left: 0;
    border-right: 0.01em solid #000;
    border-top: 0;
    border-bottom: 0.01em solid #000;
}


</style>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<title>Payslip Details</title>
<head>
  <meta http-equiv="Content-Type" content="charset=utf-8" />
  <style type="text/css">
    * {
      font-family: "DejaVu Sans Mono", monospace;
      font-size: 11px;
    }
  </style>
</head>

<body>
	<div class="contriner-fluid">
	<div class="top-content">
	    <p><b>Meow Cafe</b></p>
		<p>2nd floor Jose Un Building</p>
		<p> Malaybalay City</p>
	</div>
	<br><br>
	<div class="middle-content">
       <p>Payroll Range: <b><?php echo date("M d, Y",strtotime($pay['date_from'])). " - ".date("M d, Y",strtotime($pay['date_to'])) ?></b> </p>
		<p>Payroll Type: <b><?php echo $pt[$pay['type']] ?></b></p>
		<table style="width: 100%" class="table">
        <thead class="thead-dark">
							   <tr>
							        <th>Payroll ID</th>
									<th>Name</th>
									<th>Late</th>
									<th>Total Allowance</th>
									<th>Total Contribution</th>
									<th>Total Deduction</th>
									<th>Net</th>
								</tr>
							</thead>
							<tbody>
								<?php
									
									$payroll=$conn->query("SELECT p.*,concat(e.lastname,', ',e.firstname,' ',e.middlename) as ename,e.employee_no FROM payroll_items p inner join employee e on e.id = p.employee_id where p.payroll_id=".$_GET['id']." ") or die(mysqli_error());
									while($row=$payroll->fetch_array()){
								?>
								<tr>
									<td><?php echo $row['employee_no'] ?></td>
									<td><?php echo ucwords($row['ename']) ?></td>
									<td class="text-right"><?= number_format($row['late'], 2) ?></td>
									<td class="text-right"><?= number_format($row['allowance_amount'], 2) ?></td>
									<td class="text-right"><?= number_format($row['contribute_amount'], 2) ?></td>
									<td class="text-right"><?= number_format($row['deduction_amount'], 2) ?></td>
									<td class="text-right"><?= number_format($row['net'],2) ?></td>
								</tr>
								<?php
									}
								?>
							</tbody>
		</table>
	</div>
</div>
</body>

</html>
<?php
	// $html = ob_get_clean();
    // $dompdf = new DOMPDF();
    // $dompdf->set_paper('letter', 'landscape');
	// $dompdf->load_html($html);
	// $dompdf->render();
	// //$dompdf->stream("sample.pdf");
	// $dompdf->stream("codexworld", array("Attachment" => 0));
?>