<?php
if (array_key_exists ( 'schema', $_GET ) && array_key_exists ( 'table', $_GET )) {
	$cnxdb = $this->getDB();
	$schema = Sanitize::blinderGet ( 'schema');
	$table = Sanitize::blinderGet ( 'table');
	$sql = DB2Tools::extractTableInfo ();
	$data = $cnxdb->selectOne ( $sql, array ($schema, $table ) );
	if ($data ['TABLE_TYPE'] == 'V') {
		$cet_objet_est_une_vue = true;
	} else {
		$cet_objet_est_une_vue = false;
	}
	$system_table_name = $data ['SYSTEM_TABLE_NAME'];
	$system_table_schema = $data ['SYSTEM_TABLE_SCHEMA'];
	
	/*
	* dans le cas où on a affaire à une table, affichage de la liste des indexs associés à cette table s'il y en a  
	*/
	if (! $cet_objet_est_une_vue) {
		
		echo '<br/><fieldset><legend><h6>Liste des indexs SQL de la table ' . $schema . '/' . $table . ' </h6></legend>' . PHP_EOL;
		list ( $sql_index_code, $dataindexs ) = GenAlterSQL::reCreateIndexs ( $cnxdb, $schema, $table );
		if (!is_array($dataindexs) || count ( $dataindexs ) <= 0) {
			echo 'Pas d\'indexs SQL définis sur cette table.<br/>';
		} else {
			echo 'Nombre d\'indexs dépendants :' . count ( $dataindexs ) . '<br/>';
			echo '<table class="table table-striped table-sm table-bordered" >'.PHP_EOL;
			echo '<thead class="thead-dark">'.PHP_EOL;
			echo '<tr class="header-row">';
			echo '<th>Schéma</th><th>Nom Index</th><th>Nom Index Système</th><th>Index SQL</th><th>Type Dép.</th><th>Keys</th>';
			echo '</tr>'.PHP_EOL ;
			echo '</thead>'.PHP_EOL.'<tbody>'.PHP_EOL;
			foreach ( $dataindexs as $dataindex ) {
				echo '<tr>';
				echo '<td>' . $dataindex ['INDEX_SCHEMA'] . '</td>';
				echo '<td>' . $dataindex ['INDEX_NAME'] . '</td>';
				echo '<td>' . $dataindex ['SYSTEM_INDEX_NAME'] . '</td>';
				echo '<td>' . $dataindex ['INDEX_SQL'] . '</td>';
				echo '<td>' . $dataindex ['DEP_TYPE_AFF'] . '</td>';
				echo '<td>' . $dataindex ['COLONS'] . '</td>';
				echo '</tr>';
			}
			echo '</tbody>';
			echo '</table>';
		}
		echo '</fieldset>';
		if ($sql_index_code != '') { 
			echo '<br/><fieldset><legend><h6>Liste des indexs en syntaxe SQL</h6></legend>' . PHP_EOL;
			echo SQLTools::coloriseCode ( $sql_index_code );
			echo '</fieldset>';
		}

		echo '<br/><fieldset><legend><h6>Liste des indexs DDS de la table ' . $system_table_schema . '/' . $system_table_name . ' </h6></legend>' . PHP_EOL;

		// recherche des dépendances en se basant sur le nom court de l'objet DB2 principal
		list($cmd, $sql) = DB2Tools::extractDspDbr($system_table_name, $system_table_schema);
        $flag = $cnxdb->executeSysCommand($cmd);
        $indexeslist = $cnxdb->selectBlock($sql);

		if (!$indexeslist || count ( $indexeslist ) == 0 ) {
			echo 'Pas d\'indexs DDS définis sur cette table.<br/>';
		} else {
			if (count ( $indexeslist ) == 1 && trim($indexeslist[0]['FILE']) == '' ) {
				echo 'Pas d\'indexs DDS définis sur cette table.<br/>';
			} else {
				echo 'Nombre d\'indexs dépendants détectés par la commande DSPDBR : ' . count ( $indexeslist ) . '<br/>';
				echo '<table class="table table-striped table-sm table-bordered" >'.PHP_EOL;
				echo '<thead class="thead-dark">'.PHP_EOL;
				echo '<tr class="header-row">';
				echo '<th>Schéma</th><th>Index</th><th>Keys</th>';
				echo '</tr>'.PHP_EOL ;
				echo '</thead>'.PHP_EOL.'<tbody>'.PHP_EOL;
				foreach ( $indexeslist as $dataindex ) {
					list($cmdx, $sqlx) = DB2Tools::extractIndexKeys($dataindex ['FILE'], $dataindex ['LIBRARY']);
					$cnxdb->executeSysCommand($cmdx);
					$indexKeys = $cnxdb->selectBlock($sqlx);
					$tmpkeys = [];
					foreach($indexKeys as $dtax) {
						if (trim($dtax['KEY']) != '') {
							$sens = '';
							if ($dtax['SENS'] == 'D') $sens = ' (DESC)';
							$tmpkeys [] = $dtax['KEY'] . $sens;
						}
					}
					if (count($tmpkeys) > 0) {
						$keys = implode(', ', $tmpkeys);
					} else {
						$keys = 'Index de type "surrogate"';
					}
					echo '<tr>';
					echo '<td>' . $dataindex ['LIBRARY'] . '</td>';
					echo '<td>' . $dataindex ['FILE'] . '</td>';
					echo '<td>' . $keys . '</td>';
					echo '</tr>';
				}
				echo '</tbody>';
				echo '</table>';
			}
		}

		echo '</fieldset>';
	
	}

}
