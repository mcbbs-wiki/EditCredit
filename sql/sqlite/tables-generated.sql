-- This file is automatically generated using maintenance/generateSchemaSql.php.
-- Source: ./sql/tables.json
-- Do not modify this file directly.
-- See https://www.mediawiki.org/wiki/Manual:Schema_changes
CREATE TABLE /*_*/user_editcredit (
  ue_id INTEGER UNSIGNED NOT NULL,
  ue_credit INTEGER UNSIGNED NOT NULL,
  PRIMARY KEY(ue_id)
);

CREATE INDEX ue_credit ON /*_*/user_editcredit (ue_credit);