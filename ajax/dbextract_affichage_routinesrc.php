<?php
if (array_key_exists ( 'schema', $_GET ) && array_key_exists ( 'routine', $_GET ) && array_key_exists('type', $_GET)) {
	$cnxdb = $this->getDB();	
	$schema = Sanitize::blinderGet ( 'schema');
	$routine = Sanitize::blinderGet ( 'routine');
	$type = Sanitize::blinderGet ( 'type');

	/*
	* On vérifie si la procédure système GENERATE_SQL est disponible, 
	* auquel cas on l'utilise pour générer le source SQL de l'objet 
	* considéré (dans le cas contraire, on génèrera le code source via 
	* les fonctions GenAlterSQL::recreateTable ou GenAlterSQL::recreateView)
	*/
	list($routine_schema, $routine_name) = DB2Tools::procGenerateSQL() ;
	$check_routine = GenAlterSQL::checkObjectExists($cnxdb, $routine_schema, $routine_name, 'PROCEDURE') ;

	if ($check_routine) {
	
		list($routine_schema, $routine_name) = DB2Tools::procGenerateSQL() ;
		$datas = GenAlterSQL::generateSQLObject($cnxdb, $schema, $routine, $type) ;

		if ($type == 'PROCEDURE') {
			$type_objet = 'procédure stockée';
		} else {
			$type_objet = 'fonction';
		}
		if (!$datas || count($datas) == 0) {
			echo '<br><br>'.PHP_EOL;
			echo '<h4>Une anomalie s\'est produite pendant la génération du code source</h4>'.PHP_EOL;
		} else {
			echo '<div>'.PHP_EOL ; 
			echo '<div class="container">'.PHP_EOL ;
			echo '<br/>'.PHP_EOL;
			echo '<fieldset><legend><h6>Code source de la '.$type_objet.' DB2</h6></legend>'.PHP_EOL;
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
	} else {
		echo '<br><br>'.PHP_EOL;
		echo '<h4>Procédure GENERATE_SQL non disponible sur cet IBM i</h4>'.PHP_EOL;
	}

}
