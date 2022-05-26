<?php

if (array_key_exists ( 'schema', $_GET ) && array_key_exists ( 'table', $_GET )) {
	$cnxdb = $this->getDB();	
	$schema = Sanitize::blinderGet ( 'schema');
	$table = Sanitize::blinderGet ( 'table');
	$sql = DB2Tools::extractTableInfo ();
	$data = $cnxdb->selectOne ( $sql, array ($schema, $table ) );
        
	$type_objet = $data ['TABLE_TYPE'] ;
	
	/*
	* On vérifie si la procédure système GENERATE_SQL est disponible, 
	* auquel cas on propose un lien permettant de l'utiliser pour 
	* générer un source SQL 
	*/
	list($routine_schema, $routine_name) = DB2Tools::procGenerateSQL() ;
	$check_routine = GenAlterSQL::checkObjectExists($cnxdb, $routine_schema, $routine_name, 'PROCEDURE') ;

	if ($check_routine) {
		$sql = DB2Tools::extractTableInfo ();
		$data = $cnxdb->selectOne ( $sql, array ($schema, $table ) );
			
		$type_objet = $data ['TABLE_TYPE'] ;
		$nom_objet = 'table' ;
		$type = 'TABLE';
		if ($type_objet == 'V') {
			$nom_objet = 'vue' ;
			$type = 'VIEW';
		}
		
		list($routine_schema, $routine_name) = DB2Tools::procGenerateSQL() ;
		$datas = GenAlterSQL::generateSQLObject($cnxdb, $schema, $table, $type) ;

		echo '<div>'.PHP_EOL ; 

		echo '<div class="container">'.PHP_EOL ;

		echo '<br/>'.PHP_EOL;
		echo '<fieldset><legend>Code source de la '.$nom_objet.' DB2</legend>'.PHP_EOL;
		echo '<pre>' ;
		foreach ($datas as $data) {
			if (!is_null($data['SRCDTA'])) {
				$code = rtrim($data['SRCDTA']) ;
				if ($code != '') {
					echo SQLTools::coloriseCode($code);
				}
			}
		}
		echo '</pre>' ;
		echo '</fieldset>'.PHP_EOL;
		echo '</div>'.PHP_EOL ;
		echo '</div>'.PHP_EOL ;
	} else {
		// pour les systèmes plus anciens qui ne disposent de la fonction DB2 GENERATE_SQL
		/*
		* dans le cas où on a affaire à une table, affichage de la liste des indexs associés à cette table s'il y en a  
		*/
		if ($type_objet != 'V') {
			echo '<div>'.PHP_EOL ;
									
			//echo '<h4 href="#">Code source de la table</h4>'.PHP_EOL;
			echo '<div class="container">'.PHP_EOL ;
			
			echo '<br/>'.PHP_EOL;	
			echo '<fieldset><legend>Code source de la table DB2</legend>'.PHP_EOL;
			echo SQLTools::coloriseCode(GenAlterSQL::reCreateTable($cnxdb, $schema, $table));
			echo '</fieldset>'.PHP_EOL;
			echo '<br/>'.PHP_EOL;
			echo '</div>'.PHP_EOL ;
			echo '</div>'.PHP_EOL ;
			
			$alter_varchar = GenAlterSQL::alterVarcharTable($cnxdb, $schema, $table, true) ;
			if ($alter_varchar != '') {
				echo '<div>'.PHP_EOL ;
				echo '<h4 href="#">Suppression des VARCHAR</h4>'.PHP_EOL;
				echo '<div class="container">'.PHP_EOL ;
					
				echo '<fieldset><legend>Génération des ALTER TABLE si présence de VARCHAR</legend>'.PHP_EOL;
				echo SQLTools::coloriseCode($alter_varchar);
				echo '</fieldset>'.PHP_EOL;
				echo '</div>'.PHP_EOL ;
				echo '</div>'.PHP_EOL ;
			}
		
		} else {
			echo '<div>'.PHP_EOL ; 

			echo '<div class="container">'.PHP_EOL ;
			
			echo '<br/>'.PHP_EOL;
			echo '<fieldset><legend>Code source de la vue DB2</legend>'.PHP_EOL;
			echo SQLTools::coloriseCode(GenAlterSQL::reCreateView($cnxdb, $schema, $table));
			echo '</fieldset>'.PHP_EOL;
			echo '</div>'.PHP_EOL ;
			echo '</div>'.PHP_EOL ;
			
		}

	}
}


