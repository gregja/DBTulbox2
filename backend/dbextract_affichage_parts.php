<?php
if (array_key_exists ( 'schema', $_GET ) && array_key_exists ( 'table', $_GET )) {
	$cnxdb = $this->getDB();
	$schema = Sanitize::blinderGet('schema') ;
	$table  = Sanitize::blinderGet('table') ;
	
	$sql = DB2Tools::extractTableInfo();
	$data = $cnxdb->selectOne ( $sql, array ($schema, $table ) );
	$system_schema = trim($data['SYSTEM_TABLE_SCHEMA']) ;
	$system_table  = trim($data['SYSTEM_TABLE_NAME']) ;
	
	$cet_objet_est_une_vue = false;
	$cet_objet_est_un_index = false;
	if ($data ['TABLE_TYPE'] == 'V') {
		$cet_objet_est_une_vue = true;
	} else {
		if ($data ['TABLE_TYPE'] == 'L') {
			$cet_objet_est_un_index = true;
		}
	}
	$sql = DB2Tools::extractTableStruct(true);
	$datastructure = $cnxdb->selectBlock ( $sql, array ($system_schema, $system_table ) );
	if (count ( $datastructure ) <= 0) {
		echo 'Datastructure non trouvée pour la table '.$schema.'/'.$table . '<br/>';
	} else {
		$list_cols = array ();
		echo '<fieldset>';
		if ($cet_objet_est_une_vue) {
			echo '<legend><h6>Liste des colonnes renvoyées par la vue : '.$schema.'/'.$table . '</h6></legend>';
		} else {	
			if ($cet_objet_est_un_index) {
				echo '<legend><h6>Description de l\'index : '.$schema.'/'.$table . '</h6></legend>';
				$sql_dep = DB2Tools::extractDependanceInverse();
				$data_dep = $cnxdb->selectOne($sql_dep, array ($system_schema, $system_table ) ) ;
				echo 'Table sous-jacente : ' . trim($data_dep['DBFLIB']).'/'.$data_dep['DBFFIL'] ;
				
				$sql_inv = DB2Tools::findTableFromSsystemName();
				$data_inv = $cnxdb->selectOne($sql_inv, array (trim($data_dep['DBFLIB']), trim($data_dep['DBFFIL']) ) ) ;
				if ($data_inv) {
					echo ' => Nom long : '.HtmlToolbox::genHtmlLink('dbTableDisplay?schema=' . trim($data_inv ['TABLE_SCHEMA']) . 
					'&table=' . trim($data_inv ['TABLE_NAME']),
					 trim($data_inv ['TABLE_SCHEMA']).'/'.trim($data_inv ['TABLE_NAME']));
				}
				echo '<br>'.PHP_EOL;

				$sql_index = DB2Tools::extractSysindexkeys (true, 'NO');
				$datacols = $cnxdb->selectBlock ( $sql_index, array ($schema, $table) );
				$colons = array ();
				foreach ( $datacols as $datacol ) {
					$colonne = $datacol ['COLUMN_NAME'];
					if ($datacol ['ORDERING'] == 'D') {
						$colonne .= ' ( DESC ) ';
					}
					$colons [] = $colonne;
				}
				if (count($colons)>0) {
					echo 'Clés de l\'index : ' . implode ( ', ', $colons ). '<br/>';
				} else {
					echo 'Clés de l\'index : néant (index de type "surrogate")';
				}
				
			}	
		}
		
		/*
		* Tableaux pour stockage colonnes décimales et non décimales
		* permettra de générer des requêtes SQL "modèles" avec des 
		* SUM() sur les colonnes décimales et des GROUP BY sur les
		* autre colonnes
		*/
		$tab_col_dec = array();
		$tab_col_nodec = array();
                
		echo '<div>'.PHP_EOL ;
		echo '<div class="container">'.PHP_EOL ;
		
		echo 'Nombre de colonnes : ' . count ( $datastructure ) . '<br/><br/>'.PHP_EOL;
		echo '<table class="table table-striped table-sm table-bordered" >'.PHP_EOL;
		echo '<thead class="thead-dark">'.PHP_EOL;
		echo '<tr><th>Num.</th><th>Nom de colonne (long)</th><th>Nom court</th><th>Libell&eacute;</th><th>Type</th><th>Longueur</th><th>Pr&eacute;cision</th><th>CCSID</th><th>Null</th><th>Identit&eacute;</th></tr>'.PHP_EOL;
		echo '</thead>'.PHP_EOL.'<tbody>'.PHP_EOL;
		foreach ( $datastructure as $data ) {
			$list_cols [] = trim($data ['FIELD']);
			echo '<tr>';
			echo '<td align="right">' . $data ['ORDINAL_POSITION'] . '</td>';
                        $data ['FIELD'] = trim($data ['FIELD']) ;
			echo '<td>' . $data ['FIELD'] . '</td>';
			echo '<td>' . trim($data ['SYSTEM_COLUMN_NAME']) . '</td>';
			if (isset($data ['COLUMN_TEXT']) && trim($data ['COLUMN_TEXT']) == '') {
				echo '<td>' . trim($data ['COLUMN_HEADING']) . '</td>';
			} else {
				echo '<td>' . trim($data ['COLUMN_TEXT']) . '</td>';
			}
                        $data ['DATA_TYPE'] = trim($data ['DATA_TYPE']) ;
			if ($data ['DATA_TYPE'] == 'VARCHAR') {
				echo '<td><font color = "red">' . $data ['DATA_TYPE'] . '</font></td>';
			} else {
				echo '<td>' . $data ['DATA_TYPE'] . '</td>';
			}
			if (($data ['DATA_TYPE'] == 'DECIMAL' || $data ['DATA_TYPE'] == 'NUMERIC') && $data ['SCALE']!=0) {
				$tab_col_dec[] = 'SUM(' . $data ['FIELD'] .') as SOM_'.$data ['FIELD'] ;
			} else {
				$tab_col_nodec[] = $data ['FIELD'];
			}
			if (!is_null($data ['NUMERIC_PRECISION'])) {
				$longueur = $data ['NUMERIC_PRECISION'] ;
			} else {
				$longueur = $data ['LENGTH'] ;
			}
			echo '<td align="right">' . $longueur . '</td>';
			echo '<td align="right">' . $data ['SCALE'] . '</td>';                        
                        echo '<td align="center">' . $data ['COLUMN_CCSID'] . '</td>';
			echo '<td align="center">' . $data ['COLUMN_NULLABLE'] . '</td>';
			echo '<td align="center">' . $data ['IS_IDENTITY'] . '</td>';
			echo '</tr>'.PHP_EOL;
		}
		echo '</tbody>'.PHP_EOL;
		echo '</table>'.PHP_EOL;
		echo '</div>'.PHP_EOL ;
		echo '</div>'.PHP_EOL ;
		
		/*
		 * affichage de la liste des colonnes séparées par une virgule (pratique pour les INSERT)
		 */
		if (count ( $list_cols ) > 0) { 
			echo '<div>'.PHP_EOL ;
			echo '<h4 href="#">Liste des colonnes pour INSERT SQL</h4>'.PHP_EOL;
			echo '<div class="container">'.PHP_EOL ;

			echo '<br/>';
			echo '<fieldset><legend>Liste des colonnes (format optimisé pour INSERT SQL) : </legend>' . PHP_EOL;		
			echo implode ( ', ', $list_cols ) . '<br/>';
			echo '</fieldset>'.PHP_EOL;

			if (count($tab_col_dec)>0) {
				$tmp_col_nodec = implode(', ', $tab_col_nodec) ;
				echo '<br/>';
				echo '<fieldset><legend>Requête de cumul "type" : </legend>' . PHP_EOL;
				echo 'SELECT ' . $tmp_col_nodec . ', ' . 
						implode(', ', $tab_col_dec). '<br>' .PHP_EOL ;
				echo 'FROM '.$schema.'.'.$table. '<br>' .PHP_EOL ;
				echo 'GROUP BY '. $tmp_col_nodec . '<br>' . PHP_EOL ;
				echo '</fieldset>'.PHP_EOL;
			}
			echo '</div>'.PHP_EOL ;
			echo '</div>'.PHP_EOL ;
		}
	}	
}

