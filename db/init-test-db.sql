SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;


SET search_path = public, pg_catalog;

--
-- Name: checkout_request_id_seq; Type: SEQUENCE; Schema: public; Owner:
--

CREATE SEQUENCE checkout_request_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: checkout_request; Type: TABLE; Schema: public; Owner:
--

CREATE TABLE checkout_request (
    id integer DEFAULT nextval('checkout_request_id_seq'::regclass) NOT NULL,
    cancel_request_id integer,
    job_id text,
    patron_barcode text,
    item_barcode text,
    owning_institution_id text,
    success boolean,
    created_date text,
    updated_date text,
    desired_date_due text
);


--
-- Name: checkout_request_pkey; Type: CONSTRAINT; Schema: public; Owner:
--

ALTER TABLE ONLY checkout_request
    ADD CONSTRAINT checkout_request_pkey PRIMARY KEY (id);


