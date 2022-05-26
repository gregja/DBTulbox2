# DBTulbox2
Database Manager for DB2 

I developed this project to facilitate the maintenance of DB2 databases, and specially DB2 for i.
To manage the connection to SQL DB2 for i, I used my own library (MacaronDB). This library allows to connect to DB2 via : 
 * a PHP stack external to the IBM i and an ODBC connection, 
 * or a PHP stack on IBM i, and the extension ibm_db2

The project has no PHP dependencies, it is self-sufficient.
For the front-end, I used the CSS framework Boostrap 4 which is embedded in the project.

For the MVC architecture, I choosed the Bones class, which is a minimalistic component (please read the Bones_license.txt and Bones_readme.md). 
It's easy to adapt Bones to your own needs, to add some security features for example.
