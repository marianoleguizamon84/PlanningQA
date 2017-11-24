-- $Id: pgsql.sql 1281 2009-12-09 22:37:17Z jberanek $
--
-- Add a column to record entry status code.   The default is 1
-- which is a confirmed booking.  The status codes are defined
-- in systemdefaults.inc.php

ALTER TABLE %DB_TBL_PREFIX%entry
ADD COLUMN status            smallint DEFAULT 1 NOT NULL;
