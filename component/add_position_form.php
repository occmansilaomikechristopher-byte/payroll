<div class="modal" id="modal" tabindex="-1" role="dialog">
	<form class="form-auth-small" id="form-add" method="post" novalidate>
    	<input type="hidden" name="id" id="id">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h6 class="modal-title" id="defaultModalLabel">Create Position</h6>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="row clearfix">
						<div class="col-md-12">
							<div class="form-group">   
								<label>Position</label>                                 
								<input type="text" class="form-control" placeholder="Position" name="name" id="name" data-parsley-required-message="Name is required." required>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-info submitbutton" > Create</button>
				</div>
			</div>
		</div>
	</form>
</div>