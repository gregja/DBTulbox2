# DBTulbox2
A modest Database Manager for DB2 for i

## DESCRIPTION

I developed this project to facilitate the maintenance of DB2 databases, and specially DB2 for i.
Because when you discover a new project that you must maintain, it's generally difficul to understand the structure of the database, I programmed some search functions, on tables (on short and long name), on columns, etc.

## ARCHITECTURE

To manage the connection to SQL DB2 for i, I used my own library (MacaronDB), which is embedded in the project. MacaronDB allows to connect to DB2 via : 
 * a PHP stack external to the IBM i and an ODBC connection, 
 * or a PHP stack on IBM i, and the extension ibm_db2

You can find the source code of MacaronDB, independently of DBTulbox2, in that repo :
https://github.com/gregja/macaronDB

The project DBTulbox2 has no PHP dependencies, it is self-sufficient.

For the front-end, I used the CSS framework Boostrap v4 which is embedded in the project.

For the MVC architecture, I used the Bones class, which is a minimalistic component (please read the Bones_license.txt and Bones_readme.md for more informations).

Bones is a simple component. It is relatively easy to adapt it to your own needs, for example to add some security layer, or other features...

## HOW TO START ?

Install the project into your PHP stack, and configure the script "context/config.php" with a user profile authorized to connect to your IBM i server. 
You can declare the variables $usr, $pwd and $ip in an external file, like in the example below, or declare the same variables directly in the script "context/config.php" if you want :

```PHP
/*
* Paramètres user, password, adresse IP placés en dehors du projet
*/
require_once '../config_external.php';
/*
 * profil de connexion DB2
*/
/*
$usr = 'ROGERRABBIT';           // TODO : à personnaliser
$pwd = 'ONRASEGRATIS';          // TODO : à personnaliser
$ip = 'dev.acmecompany.com';    // TODO : à personnaliser

$liste_servers = [];
$liste_servers[] = ['server' => $ip, 'lib' => ['MYPRECIOUS1', 'MYPRECIOUS2'] ];
$liste_servers[] = ['server' => 'rec.acmecompany.com', 'usr'=> 'XXX', 'pwd' => 'ZZZ', 'lib' => []];
$liste_servers[] = ['server' => 'prd.acmecompany.com', 'usr'=> 'XXX', 'pwd' => 'ZZZ', 'lib' => []];
*/
```
Warning : only the first item of the array $liste_servers is mandatory. This array is used for comparisons of databases based on one IBM i server (set only the first item), or for comparisons of datases based on multiple servers (in that case, you must set the item 2, or more...). 

## TODO LIST 

Shortlist of improvements I want to add :
 * replace, in macaronDB, the current pagination function with the new LIMIT clause of DB2
 * finalize the 2 options for Databases comparisons
 * implement CSV exports (code commented for the moment, because I need to adapt Bones for that feature)
 * and much more... :)


