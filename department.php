<?php include 'db_connect.php'; ?>
<div class="container-fluid">
    <?php $title='Department'; include 'includes/breadcrumb.php'; ?>
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
						   <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modal"> <span class="icon-plus"></span> Create Deparment </button>
						</div>
					</div>
				</div>
			</div>
			<div class="card">
				<div class="header">
					<h2>Department List</h2>
				</div>
				<div class="body">
					<div class="table-responsive">
						<table id="data-table" class="table table-hover  dataTable table-custom table-striped m-b-0 c_list">
							<thead class="thead-dark">
								<tr>
									<th>Name</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$query = $conn->query("SELECT * from department order by name asc");
									while($row=$query->fetch_assoc()){
										
							?>
								<tr>
									<td><?php echo $row['name']?></td>
									<td class="text-center" width="100">
									    <button type="button" class="btn btn-sm btn-outline-secondary" title="Edit"  id="<?=$row['id']?>" name="<?=$row['name']?>" onclick="edit_function(this)"><i class="fa fa-edit"></i></button>
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
<?php include 'component/add_department_form.php'; ?>

