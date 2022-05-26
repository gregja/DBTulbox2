<?php
$currentScript = 'dbTableDisplay';
$display_options_test = true;

if ($this->get_method() == 'GET' && array_key_exists ( 'schema', $_GET ) && array_key_exists ( 'table', $_GET )) {
    $cnxdb = $this->getDB();
	echo '<br/>Cliquez sur les liens ci-dessous pour afficher les informations correspondantes.<br/><br/>'.PHP_EOL ;
	$schema = Sanitize::blinderGet('schema') ;
	$table  = Sanitize::blinderGet('table') ;
	
	$sql = DB2Tools::extractTableInfo();
	$data = $cnxdb->selectOne ( $sql, array ($schema, $table ) );
	$system_schema = trim($data['SYSTEM_TABLE_SCHEMA']) ;
	$system_table  = trim($data['SYSTEM_TABLE_NAME']) ;

        $type_objet = $data ['TABLE_TYPE'] ;
	if ($type_objet == 'V') {
		$cet_objet_est_une_vue = true;
		echo '<h3>Description de la vue : '.$schema.'/'.$table . '</h3>';
	} else {
		if ($type_objet == 'L') {
			echo '<h3>Description de l\'index : '.$schema.'/'.$table . '</h3>';
		} else {
			if ($type_objet == 'M') {
				echo '<h3>Description de la MQT : '.$schema.'/'.$table . '</h3>';                        
            } else {
				echo '<h3>Description de la table : '.$schema.'/'.$table . '</h3>';
            }
		}
	}
	$sql = DB2Tools::extractTableStruct(true);
	$datastructure = $cnxdb->selectBlock ( $sql, array ($system_schema, $system_table ) );
	if (count ( $datastructure ) <= 0) {
		echo 'Datastructure non trouvée pour la table '.$schema.'/'.$table . '<br/>';
	} else {
echo <<<BLOC
<div class="container">
  <ul class="nav nav-pills">
    <li data-tab="1" class="nav-item active"><a class="nav-link" data-toggle="pill" href="#option1">Datastructure</a></li>
    <li data-tab="2" class="nav-item"><a class="nav-link" data-toggle="pill" href="#option2">Définition</a></li>
    <li data-tab="3" class="nav-item"><a class="nav-link" data-toggle="pill" href="#option3">Statistiques</a></li>
	<li data-tab="4" class="nav-item"><a class="nav-link" data-toggle="pill" href="#option4">Source SQL</a></li>
	<li data-tab="5" class="nav-item"><a class="nav-link" data-toggle="pill" href="#option5">Indexs</a></li>
<!--
	<li data-tab="6" class="nav-item"><a class="nav-link" data-toggle="pill" href="#option6">Objets utilisateurs</a></li>
	<li data-tab="7" class="nav-item"><a class="nav-link" data-toggle="pill" href="#option7">Verrouillages</a></li> 
	<li data-tab="8" class="nav-item"><a class="nav-link" data-toggle="pill" href="#option8">Query</a></li>
	<li data-tab="9" class="nav-item"><a class="nav-link" data-toggle="pill" href="#option9">Conversions</a></li>
-->
  </ul>
BLOC;
echo <<<BLOC
  <div class="tab-content">
    <div id="option1" class="tab-pane fade in active">
BLOC;
require 'backend/dbextract_affichage_parts.php'; 
echo <<<BLOC
    </div>
    <div id="option2" class="tab-pane fade">
BLOC;
require 'backend/dbextract_affichage_defn.php'; 
echo <<<BLOC
	</div>
    <div id="option3" class="tab-pane fade">
BLOC;
require 'backend/dbextract_affichage_stats.php'; 
echo <<<BLOC
	</div>
    <div id="option4" class="tab-pane fade">
BLOC;
require 'backend/dbextract_affichage_source.php'; 
echo <<<BLOC
	</div>
    <div id="option5" class="tab-pane fade">
BLOC;
require 'backend/dbextract_affichage_indexs.php'; 
echo <<<BLOC
	</div>
    <div id="option6" class="tab-pane fade">
	à implémenter
	</div>
    <div id="option7" class="tab-pane fade">
	à implémenter
	</div>
    <div id="option8" class="tab-pane fade">
	à implémenter
	</div>
    <div id="option9" class="tab-pane fade">
	à implémenter
	</div>
  </div>
</div>
BLOC;
	}
}
echo '<br/>'.PHP_EOL ;
