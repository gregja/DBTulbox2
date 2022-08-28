<?php
if (array_key_exists ( 'schema', $_GET ) && array_key_exists ( 'trigger', $_GET ) ) {
	$cnxdb = $this->getDB();	
	$schema = Sanitize::blinderGet ( 'schema');
	$trigger = Sanitize::blinderGet ( 'trigger');

	list($routine_schema, $routine_name) = DB2Tools::procGenerateSQL() ;
	$check_routine = GenAlterSQL::checkObjectExists($cnxdb, $routine_schema, $routine_name, 'PROCEDURE') ;

	if ($check_routine) {
		$datas = GenAlterSQL::generateSQLObject($cnxdb, $schema, $trigger, 'TRIGGER') ;

		if (!$datas || count($datas) == 0) {
			echo '<br><br>'.PHP_EOL;
			echo '<h4>Une anomalie s\'est produite pendant la génération du code source</h4>'.PHP_EOL;
		} else {
			echo '<div>'.PHP_EOL ; 
			echo '<div class="container">'.PHP_EOL ;
			echo '<br/>'.PHP_EOL;
			echo '<fieldset><legend><h6>Code source du trigger DB2</h6></legend>'.PHP_EOL;
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
		// si procédure GENERATE_SQL non disponible (cas d'un IBM i trop ancien) alors
		// version "lite" du code source du trigger extrait à partir de la table SYSTRIGGERS 
		$trig = $cnxdb->selectOne( DB2Tools::getTriggerDesc(), array ( $schema, $trigger ) );
		$source = explode(PHP_EOL, $trig['ACTION_STATEMENT']);
		if (!$source || count($source) == 0) {
			echo '<br><br>'.PHP_EOL;
			echo '<h4>Une anomalie s\'est produite pendant la génération du code source</h4>'.PHP_EOL;
		} else {
			echo '<div>'.PHP_EOL ; 
			echo '<div class="container">'.PHP_EOL ;
			echo '<br/>'.PHP_EOL;
			echo '<fieldset><legend><h6>Code source du trigger DB2</h6></legend>'.PHP_EOL;
			echo '<pre>' ;
			foreach ($source as $code) {
				echo SQLTools::coloriseCode($code);
			}
			echo '</pre>' ;
			echo '</fieldset>'.PHP_EOL;
			echo '</div>'.PHP_EOL ;
			echo '</div>'.PHP_EOL ;
		}
	
	}

}


