-- Legacy sites table removal.
-- Back up the database before running this migration.
-- Historical DTR/biometric/payroll rows keep their existing site_id values;
-- current lookups use branches.id instead.

START TRANSACTION;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `sites`;
SET FOREIGN_KEY_CHECKS = 1;

COMMIT;