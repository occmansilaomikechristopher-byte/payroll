-- Safe foreign-key relationships for the payroll databases.
-- Policy: ON DELETE RESTRICT. Ambiguous IDs and columns with existing orphan rows
-- are intentionally excluded until their data is cleaned and their meaning confirmed.
-- Apply to both u109581358_payroll_db and payroll_management.

ALTER TABLE dtr ADD CONSTRAINT fk_dtr_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE users ADD CONSTRAINT fk_users_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE employee ADD CONSTRAINT fk_employee_department FOREIGN KEY (department_id) REFERENCES department(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE employee ADD CONSTRAINT fk_employee_position FOREIGN KEY (position_id) REFERENCES `position`(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE employee ADD CONSTRAINT fk_employee_loan FOREIGN KEY (loan_id) REFERENCES loans(loan_id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE employee_allowances ADD CONSTRAINT fk_employee_allowances_allowance FOREIGN KEY (allowance_id) REFERENCES allowances(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE employee_contributions ADD CONSTRAINT fk_employee_contributions_contribution FOREIGN KEY (contribution_id) REFERENCES contributions(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE employee_deductions ADD CONSTRAINT fk_employee_deductions_employee FOREIGN KEY (employee_id) REFERENCES employee(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE employee_deductions ADD CONSTRAINT fk_employee_deductions_deduction FOREIGN KEY (deduction_id) REFERENCES deductions(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE loans ADD CONSTRAINT fk_loans_employee FOREIGN KEY (employee_id) REFERENCES employee(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE loan_history ADD CONSTRAINT fk_loan_history_loan FOREIGN KEY (loan_id) REFERENCES loans(loan_id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE loan_history ADD CONSTRAINT fk_loan_history_payroll FOREIGN KEY (payroll_id) REFERENCES payroll(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE loan_history ADD CONSTRAINT fk_loan_history_employee FOREIGN KEY (employee_id) REFERENCES employee(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE notifications ADD CONSTRAINT fk_notifications_user FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE notifications ADD CONSTRAINT fk_notifications_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE owner_requisitions ADD CONSTRAINT fk_owner_requisitions_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE payroll_items ADD CONSTRAINT fk_payroll_items_employee FOREIGN KEY (employee_id) REFERENCES employee(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE `position` ADD CONSTRAINT fk_position_department FOREIGN KEY (department_id) REFERENCES department(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE products ADD CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE products ADD CONSTRAINT fk_products_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE pos_quotations ADD CONSTRAINT fk_pos_quotations_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE pos_quotation_items ADD CONSTRAINT fk_pos_quotation_items_quotation FOREIGN KEY (quotation_id) REFERENCES pos_quotations(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE pos_quotation_items ADD CONSTRAINT fk_pos_quotation_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE pos_sales ADD CONSTRAINT fk_pos_sales_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE pos_sales ADD CONSTRAINT fk_pos_sales_cashier FOREIGN KEY (cashier_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE pos_sale_items ADD CONSTRAINT fk_pos_sale_items_sale FOREIGN KEY (sale_id) REFERENCES pos_sales(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE pos_sale_items ADD CONSTRAINT fk_pos_sale_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE time_logs ADD CONSTRAINT fk_time_logs_employee FOREIGN KEY (employee_id) REFERENCES employee(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE damage_items ADD CONSTRAINT fk_damage_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE damage_items ADD CONSTRAINT fk_damage_items_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE cashier_notifications ADD CONSTRAINT fk_cashier_notifications_user FOREIGN KEY (mobile_user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE cashier_notifications ADD CONSTRAINT fk_cashier_notifications_sale FOREIGN KEY (sales_id) REFERENCES pos_sales(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE cashier_notifications MODIFY requisition_id INT UNSIGNED NULL;
ALTER TABLE cashier_notifications ADD CONSTRAINT fk_cashier_notifications_requisition FOREIGN KEY (requisition_id) REFERENCES owner_requisitions(id) ON DELETE RESTRICT ON UPDATE CASCADE;

-- Deliberately deferred: users.employer_id, employee_bio.employee_id,
-- employee_contributions.employee_id, payroll_items.payroll_id,
-- payroll_logs.payroll_id, payroll_logs.user_id, attendance.employee_id,
-- device_id/site_id/local_id, and polymorphic record_id.
