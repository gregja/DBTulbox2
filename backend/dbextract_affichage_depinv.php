<?php
// TODO : brouillon à retravailler

if (array_key_exists ( 'schema', $_GET ) && array_key_exists ( 'table', $_GET )) {
	
	$schema = Sanitize::blinderGet ( 'schema');
	$table = Sanitize::blinderGet ( 'table');
	$sql = DB2Tools::extractTableInfo ();
	$data = $cnx_db01->selectOne ( $sql, array ($schema, $table ) );
	if ($data ['TABLE_TYPE'] == 'V') {
		$cet_objet_est_une_vue = true;
	} else {
		$cet_objet_est_une_vue = false;
	}
	
	$sql = DB2Tools::extractSysviewdepInverse();
	$data = $cnx_db01->selectBlock( $sql, array ($schema, $table ) );
	if (is_array($data) && count($data)>0) {
		echo '<div>'.PHP_EOL ;
		//echo '<h4 href="#">Liste des objets utilisateurs</h4>'.PHP_EOL;
		echo '<div class="container">'.PHP_EOL ;
	
		echo '<br/><fieldset><legend>Liste des Vues utilisant l\'objet '.$schema.'/'.$table.'</legend>'.PHP_EOL;
		echo '<table border="1" cellspacing="0" cellpadding="5" >'.PHP_EOL;
		echo '<tr class="header-row"><td>View name</td><td>View schema</td><td>View owner</td></tr>'.PHP_EOL;
		foreach($data as $key=>$value) {
            $get_params = 'schema='.trim($value ['VIEW_SCHEMA']).'&amp;table='.trim($value ['VIEW_NAME']);
			echo '<tr>';
			echo '<td><a href="dbextract_affichage.php?'.$get_params.'">' . trim($value ['VIEW_NAME']) . '</a></td>'.PHP_EOL;
			echo '<td>' . trim($value ['VIEW_SCHEMA']) . '</td>'.PHP_EOL;
			echo '<td>' . trim($value ['VIEW_OWNER']) . '</td>'.PHP_EOL;
			echo '<tr>' .PHP_EOL;
		}
		echo '</table>'.PHP_EOL;
		echo '</fieldset>'.PHP_EOL;
		echo '</div>'.PHP_EOL ;
		echo '</div>'.PHP_EOL ;
	}
	
	$sql = DB2Tools::extractSysroutinedepInverse();
	$data = $cnx_db01->selectBlock( $sql, array ( $table ) );
	if (is_array($data) && count($data)>0) {
		echo '<div>'.PHP_EOL ;
		//echo '<h4 href="#">Liste des routines utilisatrices</h4>'.PHP_EOL;
		echo '<div class="container">'.PHP_EOL ;
		
		echo '<br/><fieldset><legend>Liste des routines utilisant l\'objet '.$table.'</legend>'.PHP_EOL;
		echo 'Attention : cette liste est théorique, car la recherche est effectuée sur le nom de l\'objet considéré, sans la bibliothèque,<br/>';
		echo 'cette dernière n\'étant généralement pas "qualifiée" dans les routines (procédures et fonctions) référencées (Object schema = *LIBL).<br/><br/>'.PHP_EOL ; 
		echo '<table border="1" cellspacing="0" cellpadding="5" >'.PHP_EOL;
		echo '<tr class="header-row"><td>Routine name</td><td>Routine schema</td><td>Object Schema</td></tr>'.PHP_EOL;
		foreach($data as $key=>$value) {
            $get_params = 'schema='.trim($value ['SPECIFIC_SCHEMA']).'&amp;routine='.trim($value ['SPECIFIC_NAME']);
			echo '<tr>'.PHP_EOL;
			echo '<td><a href="dbroutine_affichage.php?'.$get_params.'">' . trim($value ['SPECIFIC_NAME']) . '</a></td>'.PHP_EOL;
			echo '<td>' . trim($value ['SPECIFIC_SCHEMA']) . '</td>'.PHP_EOL;
			echo '<td>' . trim($value ['OBJECT_SCHEMA']) . '</td>'.PHP_EOL;
			echo '<tr>' .PHP_EOL;
		}
		echo '</table>'.PHP_EOL;
		echo '</fieldset>'.PHP_EOL;
		echo '</div>'.PHP_EOL ;
		echo '</div>'.PHP_EOL ;
	}

}


