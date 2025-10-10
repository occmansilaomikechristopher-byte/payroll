<div class="modal" id="modal" tabindex="-1" role="dialog">
    <form class="form-auth-small" id="form-submit" method="post" novalidate>
        <input type="hidden" name="id" id="id">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="defaultModalLabel">Create Payroll</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="min-height: 500px;">
                    <input type="hidden" name="p2" value="<?php echo (isset($_GET['p2']) && $_GET['p2'] === 'true') ? 'yes' : 'no'; ?>">
                    <div class="row clearfix">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Employers</label>
                                <select id="employer-select" class="form-select" name="employer_id" data-placeholder="Select employer" data-parsley-required-message="Please select employer." required>
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
                                <label>Date From</label>
                                <input type="text" name="date_from" class="form-control datetimepicker" autocomplete="off" data-parsley-required-message="Please select  date." required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Date To</label>
                                <input type="text" name="date_to" class="form-control datetimepicker" autocomplete="off" data-parsley-required-message="Please select  date." required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Payroll Type</label>
                                <select id="type" name="type" class="form-select" data-placeholder="Payroll Type" data-parsley-required-message="Please select type." required>
                                    <option value="">Select Type</option>
                                    <option value="1">Week 1</option>
                                    <option value="2">Week 2</option>
                                    <option value="3">Week 3</option>
                                    <option value="4">Week 4</option>
                                    <option value="5">Monthly</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Category <small>(Select Sites/ Select Cluster)</small></label>
                                <select id="category-select" class="form-select" name="category_id" data-placeholder="Select category" data-parsley-required-message="Please select category." required>
                                    <option value="">Select a category...</option>
                                    <option value="0">Select Sites</option>
                                    <?php
                                    $user_forms = $conn->query("SELECT * from clusters ");
                                    while ($row_data_form = $user_forms->fetch_assoc()) :
                                    ?>
                                        <option value="<?php echo $row_data_form['id'] ?>">Cluster - <?php echo $row_data_form['cluster'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <!-- <div class="col-md-12">
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="deferential" value="" id="deferential">
                                    <label class="form-check-label" for="deferential">
                                        Deferential
                                    </label>
                                </div>
                            </div>
                        </div> -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info submitbutton">Submit</button>
                </div>
            </div>
        </div>
    </form>
</div>



<div class="modal" id="modal-settings" tabindex="-1" role="dialog">
    <form class="form-auth-small" id="form-settings" method="post" novalidate>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="defaultModalLabel">Settings</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="min-height: 500px;">
                    <input type="hidden" name="id" id="settings-id">
                    <h5>Contributions</h5>
                    <table class="table table-hover  table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Select</th>
                                <th>Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = $conn->query("SELECT * FROM contributions order by id asc");
                            while ($row = $query->fetch_assoc()) {

                            ?>
                                <tr>
                                    <td width="120"><input id="contributions-<?= $row['id']  ?>" type="checkbox" name="contributions[]" value="<?= $row['id']  ?>"></td>
                                    <td><?php echo $row['contribution'] ?></td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                    <hr>
                    <h5>Deduction</h5>
                    <table class="table table-hover  table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Select</th>
                                <th>Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = $conn->query("SELECT * FROM deductions order by id asc");
                            while ($row = $query->fetch_assoc()) {

                            ?>
                                <tr>
                                    <td width="120"><input id="deductions-<?= $row['id']  ?>" type="checkbox" name="deductions[]" value="<?= $row['id']  ?>"></td>
                                    <td><?php echo $row['deduction'] ?></td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                    <hr>
                    <h5>Loans</h5>
                    <table class="table table-hover  table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Select</th>
                                <th>Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = $conn->query("SELECT * FROM contribution_loan_types order by clt_id asc");
                            while ($row = $query->fetch_assoc()) {

                            ?>
                                <tr>
                                    <td width="120"><input id="loan-<?= $row['clt_id']  ?>" type="checkbox" name="loans[]" value="<?= $row['clt_id']  ?>"></td>
                                    <td><?php echo $row['loan_type'] ?></td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                    <hr>
                    <h5>Refunds</h5>
                    <table class="table table-hover  table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Select</th>
                                <th>Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = $conn->query("SELECT * FROM refunds order by id asc");
                            while ($row = $query->fetch_assoc()) {

                            ?>
                                <tr>
                                    <td width="120"><input id="refund-<?= $row['id']  ?>" type="checkbox" name="refunds[]" value="<?= $row['id']  ?>"></td>
                                    <td><?php echo $row['refunds'] ?></td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info submitbutton">Save Changes</button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="modal" id="modal-payroll-history" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="defaultModalLabel">Payroll History</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="loanHistoryDiv"></div>
            </div>

        </div>
    </div>
</div>

<div class="modal" id="modal-sites" tabindex="-1" role="dialog">
    <form class="form-auth-small" id="form-add" method="post" novalidate>
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="title" id="defaultModalLabel">Select Sites</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="min-height: 500px;">
                    <div class="row" id="show-sites">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info submitbutton">Create</button>
                </div>
            </div>
        </div>
    </form>
</div>

