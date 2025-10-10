<?php include 'db_connect.php'; ?>
<div class="container-fluid">
    <?php $title='Allowance'; include 'includes/breadcrumb.php'; ?>
	<div class="row clearfix">
		<div class="col-lg-12">
		   <div class="card">
				<div class="body">
					<div class="row">
						<div class="col-sm-6">
					    	<form id="navbar-search" class="navbar-form search-form">
							    <input id="search-input" class="form-control" placeholder="Search here..." type="text" >
						    	<button type="button" class="btn btn-default"><i class="icon-magnifier"></i></button>
					    	</form>
						</div>
						<div class="col-sm-6 text-right">
						   <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modal"> <span class="icon-plus"></span> Create Allowance </button>
						</div>
					</div>
				</div>
			</div>
			<div class="card">
				<div class="header">
					<h2>Allowance List</h2>
				</div>
				<div class="body">
					<div class="table-responsive">
						<table id="data-table" class="table table-hover dataTable table-custom table-striped m-b-0 c_list">
							<thead class="thead-dark">
								<tr>
									<th>Allowance</th>
									<th>Description</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
							<?php
								$query = $conn->query("SELECT * FROM allowances order by id asc");
									while($row=$query->fetch_assoc()){
										
							?>
								<tr>
									<td><?php echo $row['allowance']?></td>
									<td><?php echo $row['description']?></td>
									<td class="text-center" width="100">
									    <button type="button" class="btn btn-sm btn-outline-secondary" title="Edit"  id="<?=$row['id']?>" name="<?=$row['allowance']?>" description="<?=$row['description']?>" onclick="edit_function(this)"><i class="fa fa-edit"></i></button>
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
<?php include 'component/add_allowances.php'; ?>

