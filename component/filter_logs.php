<div class="modal" id="modal-filter-add" tabindex="-1" role="dialog">
	<form class="form-auth-small" id="form-filter" method="post" novalidate>
		<input type="hidden" name="id" id="id">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h6 class="modal-title" id="defaultModalLabel">Filter Logs</h6>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body" style="min-height: 500px;">
					<div class="row clearfix">
						<div class="col-md-12">
							<div class="form-group">
								<label>Sites</label>
								<select id="site-select" class="form-control select2" name="site_id" data-parsley-required-message="Please select  site." required>
									<option value="">Select a site...</option>
									<?php
									$user_forms = $conn->query("SELECT * from sites WHERE status = 1 order by site_name asc");
									while ($row_data_form = $user_forms->fetch_assoc()) :
									?>
										<option value="<?php echo $row_data_form['id'] ?>"><?php echo $row_data_form['site_name'] ?>(<?php echo $row_data_form['site_address'] ?>)</option>
									<?php endwhile; ?>
								</select>
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">
								<label>From</label>
								<input name="from" id="from" class="form-control datetimepicker" autocomplete="off" data-parsley-required-message="Please select  date." required>
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">
								<label>To</label>
								<input name="to" id="to" class="form-control datetimepicker" autocomplete="off" data-parsley-required-message="Please select  date." required>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-info">Filter</button>
				</div>
			</div>
		</div>
	</form>
</div>