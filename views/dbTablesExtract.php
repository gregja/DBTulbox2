<?php
$currentScript = 'dbTablesExtract';

// récupération des paramètres de $_GET
$fields = array ('nom_base', 'nom_table', 'type_objet', 'respect_casse', 'table_used_ref' );
$params = array ();
foreach ( $fields as $field ) {
	$params [$field] = Sanitize::blinderGet($field);
}

// récupération ou initialisation de l'offset 
$offset = isset ( $_GET ['offset'] ) ? Sanitize::blinderGet('offset', '', 'intval' ) : 1;
$params ['offset'] = $offset;
?>
<fieldset><legend>Structure des tables et vues DB2</legend>
<form id="extraction" name="extraction" method="get" action="">
<div class="form-group row">
	<label for="nom_base" class="col-sm-4 col-form-label">Saisissez une bibliothèque (saisie facultative) :</label>
	<div class="col-sm-3">
		<input type="text" name="nom_base" id="nom_base" class="form-control" size="20"
		value="<?php echo $params['nom_base']; ?>"/>
	</div>
	<div class="col-sm-3">
	<!--	<img src="images/search.gif" id="nom_base_icon"  onclick="$('#nom_base').focus();" alt="search" /> -->
		<img src="images/clear_left.png" id="nom_base_clear" border="0" onclick="$('#nom_base').val('');" alt="clear" />
	</div>
</div>
<div class="form-group row">
<label for="nom_table" class="col-sm-4 col-form-label">Saisissez une table SQL (saisie facultative) (*):</label>
	<div class="col-sm-3">
	<input type="text" name="nom_table" id="nom_table" class="form-control" size="20" 
	value="<?php echo $params['nom_table']; ?>" />
	</div>
	<div class="col-sm-3">
		<img src="images/clear_left.png" id="nom_table_clear" border="0" onclick="$('#nom_table').val('');" alt="clear" />	
	</div>
</div>
<div class="form-group row">
	<label for="type_objet" class="col-sm-4 col-form-label">Types d'objets </label>
	<div class="col-sm-3">
		<?php echo GenForm::inputSelect('type_objet', DB2Tools::getTypeObjetsDb2(), $params['type_objet']) ;?>
	</div>
</div>	
<div class="custom-control custom-checkbox">
	<input type="checkbox" name="respect_casse" id="respect_casse" value="ON" class="custom-control-input"
	<?php
	echo $params ['respect_casse'] == 'ON' ? ' checked="checked" ' : '';
	?> />
	<label class="custom-control-label col-sm-6" for="respect_casse">Respecter la casse</label> 
</div><br>
<p><h6><small>(*) : La recherche de table se fait à la fois sur nom long et sur nom court. 
	Il est possible de suffixer la saisie avec un %, ce qui déclenche une recherche de type "contient".</small></h6></p>
<input type="submit" value="valider" name="crud_valid" id="crud_valid" class="btn btn-primary" /> 
<!--
<input type="submit" value="export_csv" name="crud_export_csv" id="crud_export_csv" class="btn btn-secondary"/>
<input type="submit" value="export_sql" name="crud_export_sql" id="crud_export_sql" class="btn btn-secondary"/>
-->
</form>
</fieldset>
<?php
if ($this->get_method() == 'GET' && array_key_exists('nom_table', $_GET)) {
	$params = $_GET;
	echo $this->form('nom_table');

	if (array_key_exists ( 'nom_base', $params ) && array_key_exists ( 'nom_table', $params )) {
	
		$base = trim ( $params ['nom_base'] );
		if (array_key_exists ( 'nom_table', $params )) {
			$table = trim ( $params ['nom_table'] );
		} else {
			$table = '';
		}
		if (!array_key_exists ( 'respect_casse', $params ) || $params ['respect_casse'] != 'ON') {
				$base = strtoupper ( $base );
				$table = strtoupper ( $table );
			}
		if ($base == '' && $table == '' ) {
			if (isset($_GET['nom_base'])) {
				echo 'Sélection invalide : saisir au moins une bibliothèque ou une table';
			}
		} else {
	
			$criteres = array ();
			$recherche_base = '';
			$recherche_table = '';
			
			if ($base != '') {
				$recherche_base = true;
				$criteres [] = $base;
			} else {
				$recherche_base = false;
			}
			
			if ($table != '') {
				$recherche_table = true;
				if (strpos($table, '%') !== false || strlen($table)<=10) {
					$criteres [] = $table;
					$criteres [] = $table;
				} else {
					$criteres [] = $table;
				}
			} else {
				$recherche_table = false;
			}
			
			if (array_key_exists ( 'varchar_only', $params ) && $params ['varchar_only'] == 'ON') {
				$recherche_varchar = true;
			} else {
				$recherche_varchar = false;
			}
			
			/*
			 * si demandé, on élimine de la liste les tables inutilisées par une recherche multi-sources
			 */
			if (array_key_exists ( 'table_used_ref', $params ) && $params ['table_used_ref'] == 'ON') {
				$ref_croisee = true;
			} else {
				$ref_croisee = false;
			}
			
			$sql = DB2Tools::getListeTables ( $recherche_base, $table, $recherche_varchar, $params ['type_objet'], $ref_croisee );
			$cnxdb = $this->getDB();
			$nb_lignes_total = $cnxdb->countNbRowsFromSQL ( $sql, $criteres );
			if (is_null($nb_lignes_total) || $nb_lignes_total <= 0) {
				echo 'pas de données trouvées';
			} else {
				
				if (array_key_exists ( 'crud_export_csv', $_GET )) {
					ob_clean ();
					ob_start ();
					
					ExportOffice::csv ( 'extract_tables_db2' );
					
					echo $cnxdb->export2CSV ( $sql, $criteres );
					
					ob_end_flush ();
					exit ();
				}
							
				if (array_key_exists ( 'crud_export_sql', $_GET )) {
					ob_clean ();
					ob_start ();
					
					ExportOffice::txt ( 'extract_tables_db2' );
					
					$datas = $cnxdb->selectBlock ( $sql, $criteres );
					foreach ( $datas as $data ) {
						$schema = trim($data ['TABLE_SCHEMA']);
						$table = trim($data ['TABLE_NAME']);
						if (trim($data ['TABLE_TYPE']) == 'V') {
							echo GenAlterSQL::reCreateView($cnxdb, $schema, $table);
						} else {
							echo GenAlterSQL::reCreateTable($cnxdb, $schema, $table);
						}						
					}				
					ob_end_flush ();
					exit ();
				} else {
					// ATTENTION : la technique du curseur scrollable ne fonctionne plus correctement sur DB2 en V7.3 
					//   avec PDO et le driver ODBC (pas vérifié si le problème se pose avec le driver ibm_db2). 
					//   Serait-ce un problème de driver ?
					//$datas = $cnxdb->getScrollCursor ($sql, $criteres, $offset, MAX_LINES_BY_PAGE, 'TABLE_NAME' );
					$datas = $cnxdb->getPagination ( $sql, $criteres, $offset, MAX_LINES_BY_PAGE, 'TABLE_NAME' );
					/*
					 * si demandé, on élimine de la liste les tables inutilisées par une recherche multi-sources
					 */
					if (array_key_exists ( 'table_used_src', $params ) && $params ['table_used_src'] == 'ON') {
						GenProcDb2::liste_DeleteTablesNotUsed ( $datas );
					}
					$lastRowNumber = 0;
					echo '<table class="table table-striped table-sm table-bordered" >'.PHP_EOL;
					echo '<thead class="thead-dark">'.PHP_EOL;
					echo '<tr><th>Schéma</th><th>Table (sqlname)</th><th>Table (sysname)</th>'.
						'<th>Type</th><th>Nb.Cols.</th><th>Buffer</th><th>Description</th><th>Propriétaire</th>'.PHP_EOL;
					if ($ref_croisee) {
						echo '<th>Bib.Proc.</th><th>Nom Proc.DB2.</th>'.PHP_EOL;
					}
					echo '</tr>'.PHP_EOL;
					echo '</thead>'.PHP_EOL;
					echo '<tbody>'.PHP_EOL;
					foreach ( $datas as $data ) {
						if (is_null($data ['TABLE_TEXT'] ) || trim ( $data ['TABLE_TEXT'] ) == '') {
							$data ['TABLE_TEXT'] = '&nbsp;';
						}
						echo '<tr>'.PHP_EOL;										
						echo '<td>' . $data ['TABLE_SCHEMA'] . '</td>'.PHP_EOL;
						echo '<td>' . HtmlToolbox::genHtmlLink ( 'dbTableDisplay?schema=' . trim ( $data ['TABLE_SCHEMA'] ) . '&amp;table=' . trim ( $data ['TABLE_NAME'] ), trim ( $data ['TABLE_NAME'] ) ) . '</td>'.PHP_EOL;
						echo '<td>' . $data ['SYSTEM_TABLE_NAME'] . '</td>'.PHP_EOL;
						echo '<td align="center">' . $data ['TABLE_TYPE'] . '</td>'.PHP_EOL;
						echo '<td align="right">' . $data ['COLUMN_COUNT'] . '</td>'.PHP_EOL;
						echo '<td align="right">' . $data ['ROW_LENGTH'] . '</td>'.PHP_EOL;
						echo '<td>' . $data ['TABLE_TEXT'] . '</td>'.PHP_EOL;
						echo '<td>' . $data ['TABLE_OWNER'] . '</td>'.PHP_EOL;
						if ($ref_croisee) {
							echo '<td>' . $data ['SPECIFIC_SCHEMA'] . '</td>'.PHP_EOL;
							echo '<td>' . $data ['SPECIFIC_NAME'] . '</td>'.PHP_EOL;
						}
						echo '</tr>'.PHP_EOL;
					}
					echo '</tbody>'.PHP_EOL;
					echo '</table>'.PHP_EOL;
					echo '<br/>'.PHP_EOL;
					// Appel de la fonction de pagination
					echo Pagination::pcIndexedLinks ( $nb_lignes_total, $offset, MAX_LINES_BY_PAGE, $currentScript, $params );
					echo '<br/>';
					echo "(Affichage " . $offset . " à " . ($offset + $lastRowNumber - 1) . " sur " . $nb_lignes_total . ")";
				}
			}
		}
	}

}
?>