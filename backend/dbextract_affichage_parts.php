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
		$list_cols_nomcourts = array();
		echo '<fieldset>';
		if ($cet_objet_est_une_vue) {
			echo '<legend><h6>Liste des colonnes renvoyées par la vue : '.$schema.'/'.$table . '</h6></legend>';
		} else {	
			if ($cet_objet_est_un_index) {
				echo '<legend><h6>Description de l\'index : '.$schema.'/'.$table . '</h6></legend>';
				if (defined('SPECIFIC_VIEWS') && SPECIFIC_VIEWS == true && defined('SPECIFIC_LIB_VIEWS')) {
					$sql_dep = DB2Tools::extractDependanceInverse(SPECIFIC_VIEWS, SPECIFIC_LIB_VIEWS);
				} else {
					$sql_dep = DB2Tools::extractDependanceInverse();
				}
				$data_dep = $cnxdb->selectOne($sql_dep, array ($system_schema, $system_table ) ) ;
				echo 'Table sous-jacente : ';
				if ($data_dep) {
					echo trim($data_dep['DBFLIB']).'/'.$data_dep['DBFFIL'] ;

					$sql_inv = DB2Tools::findTableFromSystemName();
					$data_inv = $cnxdb->selectOne($sql_inv, array (trim($data_dep['DBFLIB']), trim($data_dep['DBFFIL']) ) ) ;
					if ($data_inv) {
						echo ' => Nom long : '.HtmlToolbox::genHtmlLink('dbTableDisplay?schema=' . trim($data_inv ['TABLE_SCHEMA']) . '&table=' 
						. trim($data_inv ['TABLE_NAME']),
						 trim($data_inv ['TABLE_SCHEMA']).'/'.trim($data_inv ['TABLE_NAME']));
					} else {
						echo ' => Nom long : donnée indisponible';
					}
				} else {
					echo 'donnée indisponible (problème de droits sur QADBFDEP)';		
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
		echo '<tr><th>Num.</th><th>Nom de colonne (long)</th>';
		if (!$cet_objet_est_une_vue) {
			echo '<th>Nom court</th>';
		}
		echo '<th>Libell&eacute;</th><th>Type</th><th>Longueur</th><th>Pr&eacute;cision</th><th>CCSID</th><th>Null</th><th>Identit&eacute;</th></tr>'.PHP_EOL;
		echo '</thead>'.PHP_EOL.'<tbody>'.PHP_EOL;
		foreach ( $datastructure as $data ) {
			$list_cols [] = trim($data ['FIELD']);
			$list_cols_nomcourts [] = trim($data ['SYSTEM_COLUMN_NAME']);
			echo '<tr>';
			echo '<td align="right">' . $data ['ORDINAL_POSITION'] . '</td>';
                        $data ['FIELD'] = trim($data ['FIELD']) ;
			echo '<td>' . $data ['FIELD'] . '</td>';
			if (!$cet_objet_est_une_vue) {
				echo '<td>' . trim($data ['SYSTEM_COLUMN_NAME']) . '</td>';
			}
			if (isset($data ['COLUMN_TEXT']) && trim($data ['COLUMN_TEXT']) != '') {
				echo '<td>' . trim($data ['COLUMN_TEXT']) . '</td>';
			} else {
				if (isset($data ['COLUMN_HEADING']) && trim($data ['COLUMN_HEADING']) != '') {
					echo '<td>' . trim($data ['COLUMN_HEADING']) . '</td>';
				} else {
					echo '<td>&nbsp;</td>';
				}
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
		 * Affichages complémentaires (pour faciliter l'écriture de différents types de requêtes)
		 */
		if (count ( $list_cols ) > 0) { 
			$tmp_list_cols = implode ( ', ', $list_cols ) . '<br/>';
			$tmp_list_shorts = implode ( ', ', $list_cols_nomcourts ) . '<br/>';
			$tmp_list_shorts2 = [];
			$tmp_camel_cols = [];
			foreach($list_cols as $idx=>$col) {
				$tmp_alias = '"' . Misc::dashesToCamelCase($col) . '"';
				$tmp_camel_cols []= $col . ' AS ' . $tmp_alias ;
				$tmp_nom_court = $list_cols_nomcourts[$idx];
				$tmp_list_shorts2 []= $tmp_nom_court . ' AS ' . $tmp_alias;
			}
			$final_camel_cols = implode ( ', ', $tmp_camel_cols ) . '<br/>';
			$tmp_list_shorts2 = implode ( ', ', $tmp_list_shorts2 ) . '<br/>';

			$tmp_query_sum = '';
			$button_query_sum = '';
			if (count($tab_col_dec)>0) {
				$tmp_col_nodec = implode(', ', $tab_col_nodec) ;
				$tmp_query_sum .= '<p><h4>Requête de cumul : </h4></p>' . PHP_EOL;
				$tmp_query_sum .= 'SELECT ' . $tmp_col_nodec . ', ' . 
						implode(', ', $tab_col_dec). '<br>' .PHP_EOL ;
				$tmp_query_sum .= 'FROM '.$schema.'.'.$table. '<br>' .PHP_EOL ;
				$tmp_query_sum .= 'GROUP BY '. $tmp_col_nodec . '<br>' . PHP_EOL ;
				$button_query_sum = <<<BLOC_BTN
	<br><br><button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseQuerySum" aria-expanded="false" aria-controls="collapseExample">
	Requête de cumul (pour SELECT)
	</button>
BLOC_BTN;
			}

			echo <<<BLOC_HTML
			<p>
			<button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseListColumns" aria-expanded="false" aria-controls="collapseExample">
			Liste des colonnes (noms longs)
			</button>
			<button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseListCamelColumns" aria-expanded="false" aria-controls="collapseExample">
			Liste des colonnes (noms longs, alias en "Camel Case") 
			</button>
BLOC_HTML;
			if (!$cet_objet_est_une_vue) {
			echo <<<BLOC_HTML
			<br><br>
			<button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseListColumnsShorts" aria-expanded="false" aria-controls="collapseExample">
			Liste des colonnes (noms courts)
			</button>
			<button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseListColumnsShorts2" aria-expanded="false" aria-controls="collapseExample">
			Liste des colonnes (noms courts, alias sur noms longs en "camel case")
			</button>
BLOC_HTML;
			}
			echo <<<BLOC_HTML
			{$button_query_sum}
		</p>
		<div class="collapse" id="collapseListColumns">
			<div class="card card-body">
			<p><h4>Liste des colonnes avec noms longs, pour SELECT ou INSERT</h4></p>
			{$tmp_list_cols}
			</div>
		</div>
		<div class="collapse" id="collapseListColumnsShorts">
			<div class="card card-body">
			<p><h4>Liste des colonnes avec noms courts, pour SELECT ou INSERT</h4></p>
			{$tmp_list_shorts}
			</div>
		</div>
		<div class="collapse" id="collapseListColumnsShorts2">
			<div class="card card-body">
			<p><h4>Liste des colonnes (noms courts,  alias sur noms longs en "camel case")</h4></p>
			{$tmp_list_shorts2}
			</div>
		</div>		
		<div class="collapse" id="collapseListCamelColumns">
			<div class="card card-body">
			<p><h4>Liste des colonnes (noms longs, alias en "Camel Case", pour SELECT)</h4></p>
			{$final_camel_cols}
			</div>
		</div>
		<div class="collapse" id="collapseQuerySum">
			<div class="card card-body">
			{$tmp_query_sum}
			</div>
		</div>		
BLOC_HTML;
		}
	}	
}

