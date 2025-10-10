<div class="modal" id="modal" tabindex="-1" role="dialog">
	<form class="form-auth-small" id="form-add" method="post" novalidate>
    	<input type="hidden" name="id" id="id">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h6 class="modal-title" id="defaultModalLabel">Create Allowance</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
				</div>
				<div class="modal-body">
					<div class="row clearfix">
						<div class="col-md-12">
							<div class="form-group">   
								<label>Allowance</label>                                 
								<input type="text" class="form-control" placeholder="Name" name="allowance" id="allowance" data-parsley-required-message="Allowance is required." required>
							</div>
                            <div class="form-group">   
								<label>Description</label>      
                                <textarea  placeholder="Description"class="form-control" name="description" id="description" data-parsley-required-message="Description is required." required rows="5"></textarea>                           
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
                    <button type="submit" class="btn btn-info submitbutton" >Create</button>
				</div>
			</div>
		</div>
	</form>
</div>