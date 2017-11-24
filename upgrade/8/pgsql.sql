-- $Id: pgsql.sql 1231 2009-10-27 16:52:17Z cimorrison $

ALTER TABLE %DB_TBL_PREFIX%room
ADD COLUMN sort_key varchar(25) DEFAULT '' NOT NULL;

create index idxSortKey on mrbs_room(sort_key);

