-- This file is automatically generated using maintenance/generateSchemaSql.php.
-- Source: ./sql/tables.json
-- Do not modify this file directly.
-- See https://www.mediawiki.org/wiki/Manual:Schema_changes
CREATE TABLE /*_*/user_editcredit (
  ue_id INT UNSIGNED NOT NULL,
  ue_credit INT UNSIGNED NOT NULL,
  INDEX ue_credit (ue_credit),
  PRIMARY KEY(ue_id)
) /*$wgDBTableOptions*/;
