<?php include 'db_connect.php'; ?>
<div class="container-fluid">
    <?php $title='Time Logs'; include 'includes/breadcrumb.php'; ?>
	<div class="row clearfix">
		<div class="col-lg-12">
	    	<div class="card">
				<div class="body">
					<div class="row">
						<div class="col-sm-6">
					    	<!-- <form id="navbar-search" class="navbar-form search-form">
							    <input id="search-input" class="form-control" placeholder="Search here..." type="text" >
						    	<button type="button" class="btn btn-default"><i class="icon-magnifier"></i></button>
					    	</form> -->
						</div>
						<div class="col-sm-6 text-right">
						   <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modal"> <span class="icon-plus"></span> Log Time </button>
						</div>
					</div>
				</div>
			</div>
			<div class="card">
				<div class="header">
					<h2>Time Logs List</h2>
				</div>
				<div class="body">
					<div class="table-responsive">
						<table id="data-table" class="table table-hover  dataTable table-custom table-striped m-b-0 c_list">
							<thead class="thead-dark">
								<tr>
									<th>Employee</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Total Hours</th>
                                    <th>Memo</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$query = $conn->query("SELECT *,time_logs.id AS time_log_id from time_logs INNER JOIN employee ON employee.id = time_logs.employee_id  order by time_logs.id  desc");
									while($row=$query->fetch_assoc()){
										
							?>
								<tr>
									<td>
                                       <?php echo $row['firstname']?>
										<?php echo $row['middlename']?>.
										<?php echo $row['lastname']?>
                                    </td>
                                    <td> <?= date("M d, Y h:i A", strtotime($row['start_date']))?></td>
                                    <td> <?= date("M d, Y h:i A", strtotime($row['end_date']))?></td>
                                    <td class="text-center"> <?= $row['total_hours']?></td>
                                    <td> <?= $row['memo']?></td>
									<td class="text-center" width="100">
									    <button type="button" class="btn btn-sm btn-outline-danger remove_timelog" title="Delete"  data-id="<?= intval($row['time_log_id'])?>" ><i class="fa fa-trash"></i></button>
									</td>
								</tr>
								<?php
									}
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php include 'component/add_timelog.php'; ?>

