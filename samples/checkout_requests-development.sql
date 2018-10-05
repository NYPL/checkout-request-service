--
-- PostgreSQL database dump
--

-- Dumped from database version 9.5.4
-- Dumped by pg_dump version 9.5.4

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


ALTER TABLE checkout_request_id_seq OWNER TO [pg_username];

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


ALTER TABLE checkout_request OWNER TO [pg_username];

--
-- Name: checkout_request_pkey; Type: CONSTRAINT; Schema: public; Owner:
--

ALTER TABLE ONLY checkout_request
    ADD CONSTRAINT checkout_request_pkey PRIMARY KEY (id);


--
-- Name: public; Type: ACL; Schema: -; Owner:
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM [pg_username];
GRANT ALL ON SCHEMA public TO [pg_username];
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- Name: checkout_request_id_seq; Type: ACL; Schema: public; Owner:
--

REVOKE ALL ON SEQUENCE checkout_request_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE checkout_request_id_seq FROM [pg_username];
GRANT ALL ON SEQUENCE checkout_request_id_seq TO [pg_username];
GRANT SELECT,USAGE ON SEQUENCE checkout_request_id_seq TO [pg_username];


--
-- Name: checkout_request; Type: ACL; Schema: public; Owner:
--

REVOKE ALL ON TABLE checkout_request FROM PUBLIC;
REVOKE ALL ON TABLE checkout_request FROM [pg_username];
GRANT ALL ON TABLE checkout_request TO [pg_username];
GRANT ALL ON TABLE checkout_request TO [pg_username];


--
-- PostgreSQL database dump complete
--
