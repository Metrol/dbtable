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
    onestring character varying(50),
    twostring character(5) NOT NULL,
    threestring text DEFAULT 'blah blah'::text,
    fourstring character varying(50)[],
    onenumber integer,
    twonumber numeric(8,4),
    threenumber bigint,
    fournumber smallint,
    date_range_1 daterange,
    date_range_2 "DateRange",
    yeahnay "YesNo",
    compo "DatesAnswer",
    trueorfalse boolean,
    onedate date,
    twodate timestamp without time zone,
    threedate timestamp with time zone,
    CONSTRAINT pgtable1_pkey PRIMARY KEY ("primaryKeyID")
)
WITH (
OIDS=FALSE
);
ALTER TABLE pgtable1
    OWNER TO testuser;
