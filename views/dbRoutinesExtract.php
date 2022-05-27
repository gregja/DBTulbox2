<?php
$currentScript = 'dbRoutinesExtract';

// récupération des paramètres du $_GET si existent, sinon récupération des paramètres de $_GET
$fields = array ('nom_base', 'nom_routine', 'type_objet', 'table_used_ref', 'table_used_src' );
$params = array ();
foreach ( $fields as $field ) {
	$params [$field] = Sanitize::blinderGet($field);
}

// récupération ou initialisation de l'offset 
$offset = isset ( $_GET ['offset'] ) ? Sanitize::blinderGet('offset', '', 'intval' ) : 1;
$params ['offset'] = $offset;

?>
<fieldset><legend>Liste des routines DB2 (procédures stockées et fonctions)</legend>
<form id="extraction" name="extraction" method="get" action="" >
<p><label for="nom_base">Saisissez une bibliothèque (facultatif) :</label>
<input type="text" name="nom_base" id="nom_base"
	value="<?php
	echo array_key_exists ( 'nom_base', $params ) ? $params ['nom_base'] : '';
	?>"
	size="20" />
<img src="images/search.gif" id="nom_base_icon" border="0" onclick="$('#nom_base').focus();return false;" alt="search" />
<img src="images/clear_left.png" id="nom_base_clear" border="0" onclick="$('#nom_base').val('');return false;" alt="clear" />
</p>	
<p><label for="nom_routine">Saisissez une routine (facultatif) :</label>
<input type="text" name="nom_routine" id="nom_routine"
	value="<?php
	echo array_key_exists ( 'nom_routine', $params ) ? $params ['nom_routine'] : '';
	?>"
	size="20" />
<img src="images/clear_left.png" id="nom_routine_clear" border="0" onclick="$('#nom_routine').val('');return false;" alt="clear" />	
</p>
<?php 
	// fonctionnalité expérimentale accessible uniquement en mode "local" (inutile en production)
	if (!Misc::isIBMiPlatform()) {
	?><p><label for="table_used_ref">Tables utilisées uniquement (références croisées) :</label> 
<input type="checkbox" name="table_used_ref" id="table_used_ref" value="ON"
	<?php
	echo array_key_exists ( 'table_used_ref', $params ) && $params ['table_used_ref'] == 'ON' ? ' checked="checked" ' : '';
	?> /></p><p><label for="table_used_src">Tables utilisées uniquement (recherche multi-sources) :</label> 
<input type="checkbox" name="table_used_src" id="table_used_src" value="ON"
	<?php
	echo array_key_exists ( 'table_used_src', $params ) && $params ['table_used_src'] == 'ON' ? ' checked="checked" ' : '';
	?> /></p><?php 
	}
	?>
<input type="submit" value="valider" name="crud_valid" id="crud_valid" /> 
<!-- <input type="submit" value="export_csv" name="crud_export_csv" id="crud_export_csv" /> -->
</form>
</fieldset>
<?php

if (array_key_exists ( 'nom_base', $params ) && array_key_exists ( 'nom_routine', $params )) {
    $cnxdb = $this->getDB();
	$base = trim ( strtoupper ( $params ['nom_base'] ) );
	
	if (array_key_exists ( 'nom_routine', $params )) {
		$table = trim ( strtoupper ( $params ['nom_routine'] ) );
	} else {
		$table = '';
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
			$criteres [] = $table;
		} else {
			$recherche_table = false;
		}
				
		/*
		 * si demandé, on élimine de la liste les tables inutilisées par une recherche multi-sources
		 */
		if (array_key_exists ( 'table_used_ref', $params ) && $params ['table_used_ref'] == 'ON') {
			$ref_croisee = true;
		} else {
			$ref_croisee = false;
		}
		
		$sql = DB2Tools::extractAllsysroutines($params['nom_routine'], false, $ref_croisee);
		
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
			
			} else {
				$datas = $cnxdb->getPagination ( $sql, $criteres, $offset, MAX_LINES_BY_PAGE, 'ROUTINE_NAME' );
				
				/*
				 * si demandé, on élimine de la liste les tables inutilisées par une recherche multi-sources
				 */
				if (array_key_exists ( 'table_used_src', $params ) && $params ['table_used_src'] == 'ON') {
					GenProcDb2::liste_DeleteTablesNotUsed ( $datas );
				}
				$lastRowNumber = 0;
                echo '<table class="table table-striped table-sm table-bordered" >'.PHP_EOL;
                echo '<thead class="thead-dark">'.PHP_EOL;    
				echo '<tr>';
				echo '<th>Schéma</th><th>Routine</th><th>Type</th><th>Body</th><th>Déterministique</th><th align="right">Nb.parms.</th><th>Créée par</th><th align="center">Date création</th>';
				if ($ref_croisee) {
					echo '<th>Bib.Proc.</th><th>Nom Proc.DB2.</th>';
				}
				echo '</tr>';
                echo '</thead>'.PHP_EOL;
                echo '<tbody>'.PHP_EOL;
				foreach ( $datas as $data ) {
					echo '<tr>';					
					echo '<td>' . $data ['ROUTINE_SCHEMA'] . '</td>';
					echo '<td>' . HtmlToolbox::genHtmlLink ( 'dbRoutineDisplay?schema=' . trim ( $data ['ROUTINE_SCHEMA'] ) . '&routine=' . trim ( $data ['ROUTINE_NAME'] ), trim ( $data ['ROUTINE_NAME'] ) ) . '</td>';
					echo '<td>' . $data ['ROUTINE_TYPE'] . '</td>';
					echo '<td>' . $data ['ROUTINE_BODY'] . '</td>';
					echo '<td>' . $data ['IS_DETERMINISTIC'] . '</td>';
					echo '<td align="right">' . $data ['IN_PARMS'] . '</td>';		
					echo '<td>' . $data ['ROUTINE_DEFINER'] . '</td>';
					echo '<td align="center">' . substr($data ['ROUTINE_CREATED'], 0, 19) . '</td>';								
					if ($ref_croisee) {
						echo '<td>' . $data ['SPECIFIC_SCHEMA'] . '</td>';
						echo '<td>' . $data ['SPECIFIC_NAME'] . '</td>';
					}
					echo '</tr>';
				}
                echo '<tbody>'.PHP_EOL;
				echo '</table>';
				echo '<br/>';
				// Appel de la fonction de pagination
				Pagination::pcIndexedLinks ( $nb_lignes_total, $offset, MAX_LINES_BY_PAGE, $currentScript, $params );
				echo '<br/>';
				echo "(Affichage " . $offset . " à " . ($offset + $lastRowNumber - 1) . " sur " . $nb_lignes_total . ")";
			}
		}
	}
}

