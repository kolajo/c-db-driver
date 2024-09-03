# c-db-driver
 Collection of PHP database driver collection for multi-databases. Having a common function but communicating to many databases. (MySQL, Maria DB, Postgre, MS SQL, and Mongo DB). Relying on PHP PDO driver.

===== Database Exporter =======

Explanation:
************
Database Connection: connect($host, $dbname, $user, $pass) - Connects to the MySQL database using PDO.

Export Table Structure: exportTableStructure() - Exports the structure of all tables in the database.

Export Data: exportTableData() - Exports data in batches of 1000 rows and generates INSERT statements.

Export Views: exportViews() - Exports the structure of all views in the database.

Export Triggers: exportTriggers() - Exports the structure of all triggers in

===== Database Importer ======

Explanation:
************
Database Connection: The connect() method establishes a connection to the MySQL database using PDO.

Load SQL File: The loadSQLFile() method reads the content of the SQL file into memory.

Import Tables: The importTables() method extracts and executes SQL statements related to table creation.

Import Views: The importViews() method extracts and executes SQL statements related to view creation.

Import Data: The importData() method extracts and executes INSERT statements for data.

Import Triggers: The importTriggers() method extracts and executes SQL statements related to triggers.

Import Functions and Procedures: The importFunctionsAndProcedures() method extracts and executes SQL statements related to functions and stored procedures.

Import Events: The importEvents() method extracts and executes SQL statements related to events.

How It Works:
extractSections(): This utility method is used to find and extract sections of the SQL file that start with specific keywords (like CREATE TABLE, CREATE VIEW, etc.) and optionally include related DROP statements.

executeSQL(): This method executes the extracted SQL section against the connected database.