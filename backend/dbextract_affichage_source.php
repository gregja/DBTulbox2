<?php
if (array_key_exists ( 'schema', $_GET ) && array_key_exists ( 'table', $_GET )) {
	$cnxdb = $this->getDB();	
	$schema = Sanitize::blinderGet ( 'schema');
	$table = Sanitize::blinderGet ( 'table');
	$sql = DB2Tools::extractTableInfo ();
	$data = $cnxdb->selectOne ( $sql, array ($schema, $table ) );
        
	$type_objet = $data ['TABLE_TYPE'];
	
	/*
	* On vérifie si la procédure système GENERATE_SQL est disponible, 
	* auquel cas on l'utilise pour générer le source SQL de l'objet 
	* considéré (dans le cas contraire, on génèrera le code source via 
	* les fonctions GenAlterSQL::recreateTable ou GenAlterSQL::recreateView)
	*/
	list($routine_schema, $routine_name) = DB2Tools::procGenerateSQL() ;
	$check_routine = GenAlterSQL::checkObjectExists($cnxdb, $routine_schema, $routine_name, 'PROCEDURE') ;

	$flag_sysroutine_ok = false;
	if ($check_routine) {
		$flag_sysroutine_ok = true;
		$sql = DB2Tools::extractTableInfo ();
		$data = $cnxdb->selectOne ( $sql, array ($schema, $table ) );
			
		$type_objet = trim($data ['TABLE_TYPE']) ;
		$nom_objet = 'table' ;
		$type = 'TABLE';
		if ($type_objet == 'V') {
			$nom_objet = 'vue' ;
			$type = 'VIEW';
		}

		if ($type_objet == 'L') {
			// On check si l'index existe dans SYSINDEXES, car si ce n'est pas le cas
			//  alors il s'agit d'un index de type DDS, ce qui va avoir pour effet de faire 
			//  planter la procédure stockée QSYS2.GENERATE_SQL
			$sqlchkidx = DB2Tools::checkIndexFromSystemName();
			$dtaidx = $cnxdb->selectOne ( $sqlchkidx, array ($schema, $table ) );
			if ($dtaidx && count($dtaidx) == 1 && $dtaidx['FOUND'] == '0') {
				$flag_sysroutine_ok = false;
				error_log('Génération source SQL impossible pour index '. $table);	
			}
		}
		
		$datas = [];
		if ($flag_sysroutine_ok) {
			list($routine_schema, $routine_name) = DB2Tools::procGenerateSQL() ;
			$datas = GenAlterSQL::generateSQLObject($cnxdb, $schema, $table, $type) ;
		}

		if (!$datas || count($datas) == 0) {
			$flag_sysroutine_ok = false;
		} else {
			echo '<div>'.PHP_EOL ; 
			echo '<div class="container">'.PHP_EOL ;
			echo '<br/>'.PHP_EOL;
			echo '<fieldset><legend><h6>Code source de la '.$nom_objet.' DB2</h6></legend>'.PHP_EOL;
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
		}
	}
	if (!$flag_sysroutine_ok) {
		/*
		 * Le bloc ci-dessous est destiné aux OS plus anciens qui ne disposent de la fonction DB2 GENERATE_SQL
		*/
		if ($type_objet == 'V' ) {
			echo '<div>'.PHP_EOL ; 
			echo '<div class="container">'.PHP_EOL ;
			echo '<br/>'.PHP_EOL;
			echo '<fieldset><legend><h6>Code source de la vue DB2</h6></legend>'.PHP_EOL;
			echo SQLTools::coloriseCode(GenAlterSQL::reCreateView($cnxdb, $schema, $table));
			echo '</fieldset>'.PHP_EOL;
			echo '</div>'.PHP_EOL ;
			echo '</div>'.PHP_EOL ;
		} else {
			if ($type_objet == 'L') {
				echo '<div>Source SQL non disponible pour ce type d\'index</div>';
			} else {
				echo '<div>'.PHP_EOL ;
				echo '<div class="container">'.PHP_EOL ;			
				echo '<br/>'.PHP_EOL;	
				echo '<fieldset><legend><h6>Code source de la table DB2</h6></legend>'.PHP_EOL;
				echo SQLTools::coloriseCode(GenAlterSQL::reCreateTable($cnxdb, $schema, $table));
				echo '</fieldset>'.PHP_EOL;
				echo '<br/>'.PHP_EOL;
				echo '</div>'.PHP_EOL ;
				echo '</div>'.PHP_EOL ;
			}
		}

	}
}


