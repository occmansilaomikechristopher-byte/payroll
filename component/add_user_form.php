<div class="modal" id="modal" tabindex="-1" role="dialog">
    <form class="form-auth-small" id="form-add" method="post" novalidate>
        <input type="hidden" name="id" id="id">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="defaultModalLabel">Create User</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row clearfix">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Employers</label>
                                <select id="employer-select" class="form-control select2" name="employer_id" data-placeholder="Select employer" data-parsley-required-message="Please select employer." required>
                                    <option value="">Select a Employer...</option>
                                    <?php
                                    $user_forms = $conn->query("SELECT * from employers ");
                                    while ($row_data_form = $user_forms->fetch_assoc()) :
                                    ?>
                                        <option value="<?php echo $row_data_form['id'] ?>"><?php echo $row_data_form['employer_name'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Role</label>
                                <select class="form-control select2" id="role" name="role" data-parsley-required-message="Please select role." required>
                                    <option value="">Select a role...</option>
                                    <!-- <option value="2">Staff</option>
                                    <option value="3">Auditor</option> -->
                                    <option value="4">Payroll Clerk</option>
                                    <option value="5">Timekeeper</option>
                                    <!-- <option value="6">PIC</option> -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" class="form-control" placeholder="Name" name="name" id="name" data-parsley-required-message="Name is required." required>
                            </div>
                        </div>
                        <!-- <div class="col-md-12" id="site-wrapper">
                            <div class="form-group">
                                <label>Sites</label>
                                <select id="site-select" class="form-control select2" name="site_id" data-placeholder="Select site" data-parsley-required-message="Please select site." required>
                                    <option value="">Select a site...</option>
                                    <?php
                                    $user_forms = $conn->query("SELECT * from sites WHERE status = 1 order by site_name asc");
                                    while ($row_data_form = $user_forms->fetch_assoc()) :
                                    ?>
                                        <option value="<?php echo $row_data_form['id'] ?>"><?php echo $row_data_form['site_name'] ?>(<?php echo $row_data_form['site_address'] ?>)</option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div> -->
                        <div class="col-md-12" id="username-wrapper">
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" class="form-control" placeholder="Username" name="username" id="username" data-parsley-required-message="Username is required." required>
                            </div>
                        </div>
                        <div class="col-md-12" id="password-wrapper">
                            <div class="form-group">
                                <label>Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" data-parsley-required-message="Password is required." placeholder="Enter password" required>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fa fa-eye-slash" aria-hidden="true"></i>
                                        </button>
                                    </div>
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