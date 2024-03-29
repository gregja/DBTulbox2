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
<div class="form-group row">
	<label for="nom_base" class="col-sm-4 col-form-label">Saisissez une bibliothèque (facultatif) :</label>
    <div class="col-sm-3">
		<input type="text" name="nom_base" id="nom_base" class="form-control" size="20"
		value="<?php echo array_key_exists ( 'nom_base', $params ) ? $params ['nom_base'] : ''; ?>"/>
    </div>
    <div class="col-sm-3">
	<!--	<img src="images/search.gif" id="nom_base_icon" onclick="$('#nom_base').focus();return false;" alt="search" /> -->
		<img src="images/clear_left.png" id="nom_base_clear" onclick="$('#nom_base').val('');return false;" alt="clear" />
	</div>
  </div>	
<div class="form-group row">
	<label for="nom_routine" class="col-sm-4 col-form-label">Saisissez une routine (facultatif) :</label>
	<div class="col-sm-3">
	<input type="text" name="nom_routine" id="nom_routine" class="form-control" size="20" 
	value="<?php echo array_key_exists ( 'nom_routine', $params ) ? $params ['nom_routine'] : ''; ?>"/>
	</div>
	<div class="col-sm-3">
		<img src="images/clear_left.png" id="nom_routine_clear"  onclick="$('#nom_routine').val('');return false;" alt="clear" />	
	</div>
</div>
<?php 
	$blocked_option = false;
	if ($blocked_option) {  // fonctions mises en sommeil temporairement TODO : à réactiver après une phase de test
	// fonctionnalité expérimentale accessible uniquement en mode "local" (inutile en production)
	//if (!Misc::isIBMiPlatform()) {
	?>
	<div class="custom-control custom-checkbox">
		<input type="checkbox" name="table_used_ref" id="table_used_ref" value="ON" class="custom-control-input"
	<?php
	echo array_key_exists ( 'table_used_ref', $params ) && $params ['table_used_ref'] == 'ON' ? ' checked="checked" ' : '';
	?> />
		<label for="table_used_ref" class="custom-control-label col-sm-6">Tables utilisées uniquement (références croisées)</label> 
	</div>
	<div class="custom-control custom-checkbox">
		<input type="checkbox" name="table_used_src" id="table_used_src" value="ON" class="custom-control-input"
	<?php
	echo array_key_exists ( 'table_used_src', $params ) && $params ['table_used_src'] == 'ON' ? ' checked="checked" ' : '';
	?> />
		<label for="table_used_src" class="custom-control-label col-sm-6" >Tables utilisées uniquement (recherche multi-sources)</label> 
	</div><br><?php 
	}
	?>
<input type="submit" value="valider" name="crud_valid" id="crud_valid" class="btn btn-primary"  /> 
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
				echo '<th>Schéma</th><th>Routine</th><th>Type</th><th>Body</th><th>Déterministe</th><th align="right">Nb.parms.</th><th>Créée par</th><th align="center">Date création</th>';
				if ($ref_croisee) {
					echo '<th>Bib.Proc.</th><th>Nom Proc.DB2.</th>';
				}
				echo '</tr>';
                echo '</thead>'.PHP_EOL;
                echo '<tbody>'.PHP_EOL;
				foreach ( $datas as $data ) {
					echo '<tr>';					
					echo '<td>' . $data ['ROUTINE_SCHEMA'] . '</td>';
					echo '<td>' . HtmlToolbox::genHtmlLink ( 'dbRoutineDisplay?schema=' . trim ( $data ['ROUTINE_SCHEMA'] ) .
					 '&routine=' . trim ( $data ['ROUTINE_NAME'] ) .
					 '&type=' . trim ( $data ['ROUTINE_TYPE'] ), trim ( $data ['ROUTINE_NAME'] ) ) . '</td>';
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
					$lastRowNumber++;
				}
                echo '<tbody>'.PHP_EOL;
				echo '</table>';
				echo '<br/>';
				// Appel de la fonction de pagination
				echo Pagination::pcIndexedLinks ( $nb_lignes_total, $offset, MAX_LINES_BY_PAGE, $currentScript, $params );
				echo '<br/>';
				echo "(Affichage " . $offset . " à " . ($offset + $lastRowNumber - 1) . " sur " . $nb_lignes_total . ")";
			}
		}
	}
}

