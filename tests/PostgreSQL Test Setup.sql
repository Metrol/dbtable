-- A rough structure of the tables and types that I put in place into a
-- stand along database to fully exercise the ability to dig this information
-- out.  Unit testing must have a Database connection to work, as that's the
-- only really interesting parts of this library.


CREATE ROLE testuser LOGIN
ENCRYPTED PASSWORD 'md5549982ed419d060f70c7d7be55086993'
NOSUPERUSER INHERIT NOCREATEDB NOCREATEROLE NOREPLICATION;
-- That password is 'testuserpass'

CREATE DATABASE testdb
WITH OWNER = testuser
ENCODING = 'UTF8'
TABLESPACE = pg_default
LC_COLLATE = 'en_US.UTF-8'
LC_CTYPE = 'en_US.UTF-8'
CONNECTION LIMIT = -1;

CREATE TYPE "DateRange" AS RANGE
(SUBTYPE=date,
SUBTYPE_OPCLASS=date_ops);
ALTER TYPE "DateRange"
OWNER TO testuser;

CREATE TYPE "DatesAnswer" AS
(dt_rng "DateRange",
 ans "YesNo",
 affected integer[]);
ALTER TYPE "DatesAnswer"
OWNER TO testuser;

CREATE TYPE "YesNo" AS ENUM
('Yes',
    'No');
ALTER TYPE "YesNo"
OWNER TO testuser;

CREATE TABLE pgtable1
(
    "primaryKeyID" serial NOT NULL,
    stringone character varying(50),
    stringtwo character(5) NOT NULL DEFAULT 'ABCDE'::bpchar,
    stringthree text DEFAULT 'blah blah'::text,
    numberone integer,
    numbertwo numeric(8,4),
    numberthree bigint,
    numberfour smallint,
    numberfive double precision,
    numbersix money,
    numberseven real,
    dateone date,
    datetwo timestamp without time zone,
    datethree timestamp with time zone,
    timeone time without time zone,
    timetwo time with time zone,
    jsonone json,
    xmarkuplang xml,
    yeahnay "YesNo",
    trueorfalse boolean,
    CONSTRAINT pgtable1_pkey PRIMARY KEY ("primaryKeyID")
)
WITH (
OIDS=FALSE
);
ALTER TABLE pgtable1
    OWNER TO testuser;

