<div class="modal" id="modal" tabindex="-1" role="dialog">
	<form class="form-auth-small" id="form-add" method="post" novalidate>
		<input type="hidden" name="id" id="id">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h6 class="modal-title" id="defaultModalLabel">Create Site</h6>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="row clearfix">
					    <div class="col-md-12">
							<div class="form-group">
								<label>Code</label>
								<input type="text" class="form-control" placeholder="Code" name="site_code" id="site_code" data-parsley-required-message="Code is required." required>
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">
								<label>Name</label>
								<input type="text" class="form-control" placeholder="Name" name="site_name" id="site_name" data-parsley-required-message="Name is required." required>
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">
								<label>Address</label>
								<textarea class="form-control" id="site_address" name="site_address" placeholder="Enter address" rows="3" required data-parsley-required-message="Address is required."></textarea>
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">
								<label>Cluster</label>
								<select id="cluster-select" class="form-control show-tick  select2" name="cluster_id" data-placeholder="Select cluster" data-parsley-required-message="Please select cluster." required>
									<option value="">Select a cluster</option>
									<?php
									$pos = $conn->query("SELECT * from clusters order by cluster asc");
									while ($row = $pos->fetch_assoc()) :
									?>
										<option class="opt" value="<?php echo $row['id'] ?>" ><?php echo $row['cluster'] ?></option>
									<?php endwhile; ?>
								</select>
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">
								<label>Timekeeper</label>
								<select id="timekeeper-select" class="form-control show-tick  select2" name="timekeeper_id" data-placeholder="Select timekeeper" data-parsley-required-message="Please select timekeeper." >
									<option value="">Select a timekeeper</option>
									<?php
									$pos = $conn->query("SELECT * from users WHERE  role = 5 order by name asc");
									while ($row = $pos->fetch_assoc()) :
									?>
										<option class="opt" value="<?php echo $row['id'] ?>" ><?php echo $row['name'] ?></option>
									<?php endwhile; ?>
								</select>
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">
								<label>PIC</label>
								<select id="pic-select" class="form-control show-tick  select2" name="pic" data-placeholder="Select timekeeper" data-parsley-required-message="Please select timekeeper." >
									<option value="">Select a PIC</option>
									<?php
									$pos = $conn->query("SELECT * from users WHERE  role = 6 order by name asc");
									while ($row = $pos->fetch_assoc()) :
									?>
										<option class="opt" value="<?php echo $row['id'] ?>" ><?php echo $row['name'] ?></option>
									<?php endwhile; ?>
								</select>
							</div>
						</div>
						<div class="col-md-12">
                            <div class="form-group">
                                <div class="form-check">
                                    <input  name="status" class="form-check-input" type="checkbox" id="status2">
                                    <label class="form-check-label" for="deferential">
                                        Active
                                    </label>
                                </div>
                            </div>
                        </div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-info submitbutton"> <i class="fa fa-spinner fa-spin fa-spinner-button"></i> Create</button>
				</div>
			</div>
		</div>
	</form>
</div>