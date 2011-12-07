CREATE TABLE sb_database_version
(
  actual_version integer NOT NULL DEFAULT 0,
  previous_version integer NOT NULL DEFAULT 0
)
WITH (
  OIDS=FALSE
);

CREATE TABLE sb_automatic_sequences
(
  sequence_name character varying NOT NULL,
  sequence_next_value integer NOT NULL DEFAULT 1,
  CONSTRAINT sb_automatic_sequences_pkey PRIMARY KEY (sequence_name, sequence_next_value)
)
WITH (
  OIDS=FALSE
);

CREATE LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION sb_get_next_id(table_name character varying)
  RETURNS integer AS
$BODY$
DECLARE
   nextid record;
BEGIN
	SELECT * INTO nextid FROM sb_automatic_sequences WHERE sequence_name = table_name;
	UPDATE sb_automatic_sequences SET sequence_next_value = (nextid.sequence_next_value +1) where sequence_name = table_name;

	RETURN nextid.sequence_next_value;
END;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE
  COST 100;