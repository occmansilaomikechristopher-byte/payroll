<div class="modal" id="addemployee" tabindex="-1" role="dialog">
	<form class="form-auth-small" id="form-add" method="post" novalidate>
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h6 class="modal-title" id="defaultModalLabel">Add Contact</h6>
				</div>
				<div class="modal-body">
					<div class="row clearfix">
						<div class="col-md-12">
							<div class="form-group">   
								<label>First Name</label>                                 
								<input type="text" class="form-control" placeholder="First Name" name="firstname" data-parsley-required-message="First name is required." required>
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">   
								<label>Middle Name</label>                                 
								<input type="text" class="form-control" placeholder="Middle Name" name="middlename" data-parsley-required-message="Middle name is required." required>
							</div>
						</div>
						
						<div class="col-md-12">
							<div class="form-group">   
								<label>First Name</label>                                 
								<input type="text" class="form-control" placeholder="Last Name" name="lastname" data-parsley-required-message="Last name is required." required>
							</div>
						</div>
						<div class="col-12">
							<div class="form-group">    
								<label>Department</label>                                 
								<select class="form-control" name="department_id" data-parsley-required-message="First name is required." required>
										<option value="">--Select department--</option>
									<?php
									$dept = $conn->query("SELECT * from department order by name asc");
									while($row=$dept->fetch_assoc()):
									?>
										<option value="<?php echo $row['id'] ?>" <?php echo isset($department_id) && $department_id == $row['id'] ? "selected" :"" ?>><?php echo $row['name'] ?></option>
									<?php endwhile; ?>
								</select>
							</div>
						</div>    
						<div class="col-12">
							<div class="form-group">    
								<label>Position</label>                                 
								<select class="form-control" name="position_id">
										<option value="">--Select position--</option>
									<?php
									$pos = $conn->query("SELECT * from position order by name asc");
									while($row=$pos->fetch_assoc()):
									?>
										<option value="<?php echo $row['id'] ?>" data-did="<?php echo $row['department_id'] ?>" <?php echo isset($department_id) && $department_id == $row['department_id'] ? '' :"" ?> <?php echo isset($position_id) && $position_id == $row['id'] ? " selected" : '' ?> ><?php echo $row['name'] ?></option>
									<?php endwhile; ?>
								</select>
							</div>
						</div>    
						<div class="col-md-12">
							<div class="form-group">   
								<label>Monthly Salary</label>                                 
								<input type="text" class="form-control" placeholder="Enter amount" name="salary">
							</div>
						</div>
						<div class="col-12">
							<div class="form-group">                                            
								<input type="file" class="form-control-file" id="exampleInputFile" aria-describedby="fileHelp">
							</div>
							<hr>
						</div>
						
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary">Add</button>
					<button type="button" class="btn btn-secondary" data-dismiss="modal">CLOSE</button>
				</div>
			</div>
		</div>
	</form>
</div>