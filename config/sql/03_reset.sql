-- PostgreSQL reset for ROFlow.
-- Drops all app tables. Use during development to start clean.

DROP TABLE IF EXISTS inspection_findings CASCADE;
DROP TABLE IF EXISTS repair_orders CASCADE;
DROP TABLE IF EXISTS vehicles CASCADE;
DROP TABLE IF EXISTS customers CASCADE;
DROP TABLE IF EXISTS users CASCADE;
