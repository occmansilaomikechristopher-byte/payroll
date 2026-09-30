-- Sample POS sales transactions for June 2026.
-- Run this in the payroll database (for example, phpMyAdmin).
-- This script does not change product stock.
-- It uses the first active branch, cashier, and five active products already in the database.

START TRANSACTION;

SET @branch_id = (SELECT id FROM branches WHERE status = 1 ORDER BY id LIMIT 1);
SET @cashier_id = (SELECT id FROM users WHERE role = 9 AND status = 1 AND (branch_id = @branch_id OR branch_id IS NULL) ORDER BY id LIMIT 1);

SET @p1 = (SELECT id FROM products WHERE status = 1 AND branch_id = @branch_id ORDER BY id LIMIT 1 OFFSET 0);
SET @p2 = (SELECT id FROM products WHERE status = 1 AND branch_id = @branch_id ORDER BY id LIMIT 1 OFFSET 1);
SET @p3 = (SELECT id FROM products WHERE status = 1 AND branch_id = @branch_id ORDER BY id LIMIT 1 OFFSET 2);
SET @p4 = (SELECT id FROM products WHERE status = 1 AND branch_id = @branch_id ORDER BY id LIMIT 1 OFFSET 3);
SET @p5 = (SELECT id FROM products WHERE status = 1 AND branch_id = @branch_id ORDER BY id LIMIT 1 OFFSET 4);

-- Prevent duplicate seed data when this script is run again.
DELETE FROM pos_sale_items
WHERE sale_id IN (
    SELECT id FROM pos_sales
    WHERE invoice_no LIKE 'INV-202606%'
       OR invoice_no LIKE 'JUNE26-%'
);
DELETE FROM pos_sales
WHERE invoice_no LIKE 'INV-202606%'
   OR invoice_no LIKE 'JUNE26-%';

INSERT INTO pos_sales
    (invoice_no, branch_id, cashier_id, subtotal, discount, total, payment, change_due, created_at)
VALUES
    ('INV-20260601-A1B2C3', @branch_id, @cashier_id,  850.00,  0.00,  850.00, 1000.00, 150.00, '2026-06-01 09:15:00'),
    ('INV-20260603-D4E5F6', @branch_id, @cashier_id, 1320.00, 20.00, 1300.00, 1500.00, 200.00, '2026-06-03 10:42:00'),
    ('INV-20260605-G7H8J9', @branch_id, @cashier_id,  675.00,  0.00,  675.00,  700.00,  25.00, '2026-06-05 13:08:00'),
    ('INV-20260608-K1L2M3', @branch_id, @cashier_id, 2180.00, 80.00, 2100.00, 2500.00, 400.00, '2026-06-08 14:26:00'),
    ('INV-20260610-N4P5Q6', @branch_id, @cashier_id,  990.00, 40.00,  950.00, 1000.00,  50.00, '2026-06-10 11:17:00'),
    ('INV-20260612-R7S8T9', @branch_id, @cashier_id, 1540.00,  0.00, 1540.00, 2000.00, 460.00, '2026-06-12 15:35:00'),
    ('INV-20260615-U1V2W3', @branch_id, @cashier_id, 2875.00, 75.00, 2800.00, 3000.00, 200.00, '2026-06-15 09:50:00'),
    ('INV-20260618-X4Y5Z6', @branch_id, @cashier_id, 1125.00, 25.00, 1100.00, 1200.00, 100.00, '2026-06-18 16:12:00'),
    ('INV-20260622-B7C8D9', @branch_id, @cashier_id, 1960.00, 60.00, 1900.00, 2000.00, 100.00, '2026-06-22 12:44:00'),
    ('INV-20260626-E1F2G3', @branch_id, @cashier_id, 3450.00, 150.00, 3300.00, 3500.00, 200.00, '2026-06-26 17:20:00');

CREATE TEMPORARY TABLE tmp_june_sales (
    invoice_no VARCHAR(50) PRIMARY KEY,
    sale_id INT NOT NULL
);

INSERT INTO tmp_june_sales (invoice_no, sale_id)
SELECT invoice_no, id
FROM pos_sales
WHERE invoice_no LIKE 'INV-202606%';

-- Two line items per transaction, using real products from the selected branch.
INSERT INTO pos_sale_items
    (sale_id, product_id, product_name, price, qty, line_total)
SELECT t.sale_id, @p1, p.product_name, p.unit_price, 1, p.unit_price
FROM tmp_june_sales t
JOIN products p ON p.id = @p1
WHERE t.invoice_no IN ('INV-20260601-A1B2C3', 'INV-20260605-G7H8J9', 'INV-20260610-N4P5Q6', 'INV-20260615-U1V2W3', 'INV-20260622-B7C8D9')
UNION ALL
SELECT t.sale_id, @p2, p.product_name, p.unit_price, 2, p.unit_price * 2
FROM tmp_june_sales t
JOIN products p ON p.id = @p2
WHERE t.invoice_no IN ('INV-20260601-A1B2C3', 'INV-20260605-G7H8J9', 'INV-20260610-N4P5Q6', 'INV-20260615-U1V2W3', 'INV-20260622-B7C8D9')
UNION ALL
SELECT t.sale_id, @p3, p.product_name, p.unit_price, 2, p.unit_price * 2
FROM tmp_june_sales t
JOIN products p ON p.id = @p3
WHERE t.invoice_no IN ('INV-20260603-D4E5F6', 'INV-20260608-K1L2M3', 'INV-20260612-R7S8T9', 'INV-20260618-X4Y5Z6', 'INV-20260626-E1F2G3')
UNION ALL
SELECT t.sale_id, @p4, p.product_name, p.unit_price, 1, p.unit_price
FROM tmp_june_sales t
JOIN products p ON p.id = @p4
WHERE t.invoice_no IN ('INV-20260603-D4E5F6', 'INV-20260608-K1L2M3', 'INV-20260612-R7S8T9', 'INV-20260618-X4Y5Z6', 'INV-20260626-E1F2G3');

-- Keep sale headers consistent with the actual line-item prices in the catalog.
UPDATE pos_sales s
JOIN (
    SELECT sale_id, SUM(line_total) AS calculated_subtotal
    FROM pos_sale_items
    GROUP BY sale_id
) i ON i.sale_id = s.id
SET s.subtotal = i.calculated_subtotal,
    s.total = GREATEST(i.calculated_subtotal - s.discount, 0),
    s.change_due = GREATEST(s.payment - GREATEST(i.calculated_subtotal - s.discount, 0), 0)
WHERE s.invoice_no LIKE 'INV-202606%';

DROP TEMPORARY TABLE tmp_june_sales;
COMMIT;

-- Example query for the June 2026 sales report:
SELECT
    DATE(s.created_at) AS sale_date,
    COUNT(*) AS transaction_count,
    SUM(s.subtotal) AS subtotal,
    SUM(s.discount) AS discount,
    SUM(s.total) AS total_sales,
    SUM(s.payment) AS total_payment
FROM pos_sales s
WHERE s.created_at >= '2026-06-01 00:00:00'
  AND s.created_at <  '2026-07-01 00:00:00'
GROUP BY DATE(s.created_at)
ORDER BY sale_date;
