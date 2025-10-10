<div class="modal" id="modal" tabindex="-1" role="dialog">
	<form class="form-auth-small" id="form-add" method="post" novalidate>
    	<input type="hidden" name="id" id="id">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h6 class="modal-title" id="defaultModalLabel">Create Time Log</h6>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
				</div>
				<div class="modal-body">
					<div class="row clearfix">
						<div class="col-md-12">
							<div class="form-group">   
								<label>Employee</label>                                 
								<select id="employee_id" name="employee_id" class="form-control show-tick ms select2" data-placeholder="Select employee" data-parsley-required-message="Please select employee." required>
                                    <option value=""></option>
                                    <?php 
                                    $employee = $conn->query("SELECT *,concat(lastname,', ',firstname,' ',middlename) as ename FROM employee order by concat(lastname,', ',firstname,' ',middlename) asc");
                                    while($row = $employee->fetch_assoc()):
                                    ?>
                                        <option value="<?php echo $row['id'] ?>"><?php echo $row['ename'] . ' | '. $row['employee_no'] ?></option>
                                    <?php endwhile; ?>
                                </select>
							</div>
						</div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="id_start_datetime">Start Date</label>
                                <div  id="id_1">
                                    <input value="<?php echo  date("Y-m-d h:m") ?>" type="text" name="start_date" id="start_date" class="form-control" required/>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label >End Date</label>
                                <div id="id_2">
                                    <input value="<?php echo  date("Y-m-d h:m") ?>" type="text" name="end_date" id="end_date" class="form-control" data-parsley-required-message="Please select  date." required/>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                           <div class="form-group">   
								<label>Total Hours</label>   
                                <p id="time-log-display">0 Hrs</p>
								<!-- <input name="total_hours" id="total_hours"  class="form-control" autocomplete="off" readonly="">                               -->
							</div>
						</div>
                        <div class="col-md-12">
                            <div class="form-group">   
                                <label>Memo</label>                                 
                                <textarea  rows="4" type="text" class="form-control" placeholder="Enter memo" name="memo" id="memo" data-parsley-required-message="Memo is required." required></textarea>
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
