<?php
$currentScript = 'dbTableDisplay';

if ($this->get_method() == 'GET' && array_key_exists ( 'schema', $_GET ) && array_key_exists ( 'table', $_GET )) {
    $cnxdb = $this->getDB();
	echo '<br/>Cliquez sur les liens ci-dessous pour afficher les informations correspondantes.<br/><br/>'.PHP_EOL ;
	$schema = Sanitize::blinderGet('schema') ;
	$table  = Sanitize::blinderGet('table') ;
	
	$system_schema = '';
	$system_table = '';
	$type_objet = 'X';
	$sql = DB2Tools::extractTableInfo();
	$data = $cnxdb->selectOne ( $sql, array ($schema, $table ) );
	if ($data) {
		$system_schema = trim($data['SYSTEM_TABLE_SCHEMA']) ;
		$system_table  = trim($data['SYSTEM_TABLE_NAME']) ;
		$type_objet    = trim($data ['TABLE_TYPE']) ;
	}

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
				if ($type_objet == 'X') {
					echo '<h3>Description non trouvée pour la table : '.$schema.'/'.$table . '</h3>';
				} else {
					echo '<h3>Description de la table : '.$schema.'/'.$table . '</h3>';
				}
            }
		}
	}
	$sql = DB2Tools::extractTableStruct(true);
	$datastructure = $cnxdb->selectBlock ( $sql, array ($system_schema, $system_table ) );
	if (count ( $datastructure ) <= 0) {
		echo 'Datastructure non trouvée pour la table '.$schema.'/'.$table . '<br/>';
	} else {
		$menus = [
			['desc'=> 'Datastructure', 'script' => 'backend/dbextract_affichage_parts.php'],
			['desc'=> 'Définition', 'script' => 'backend/dbextract_affichage_defn.php']
		];

		if ($type_objet != 'V') {
			$menus[] = ['desc'=> 'Statistiques', 'script' => 'backend/dbextract_affichage_stats.php'];
		}
		$menus[] = ['desc'=> 'Source SQL', 'script' => 'backend/dbextract_affichage_source.php'];
		if ($type_objet != 'V' && $type_objet != 'L') {
			$menus[] = ['desc'=> 'Indexs', 'script' => 'backend/dbextract_affichage_indexs.php'];
		}
		$menus[] = ['desc'=> 'Objets utilisateurs', 'script' => ''];
		$menus[] = ['desc'=> 'Verrouillages', 'script' => ''];
		$menus[] = ['desc'=> 'Query', 'script' => ''];
		$menus[] = ['desc'=> 'Conversions', 'script' => ''];
		

		$tabs_menu = [];
		$tabs_content = [];

		foreach($menus as $ndx => $menu) {
			if ($menu['script'] != '') {
				$index = $ndx+1;
				$option = $menu['desc'];
				$tabs_menu[] = '<li data-tab="'.$index.'" class="nav-item"><a class="nav-link" data-toggle="pill" href="#option'.$index.'">'.$option.'</a></li>';
			}
		}
		$tabs_menu_out = implode(PHP_EOL, $tabs_menu);

		echo <<<BLOC
		<div class="container">
		<ul class="nav nav-pills">
		{$tabs_menu_out}
		</ul>
		<div class="tab-content">
BLOC;

		foreach($menus as $ndx => $xmenu) {
			if (trim($xmenu['script']) != '') {
				$index = $ndx+1;
				echo '<div id="option'.$index.'" class="tab-pane fade ">'.PHP_EOL;
				require $xmenu['script'];
				echo '</div>'.PHP_EOL;
			}

		}
		echo <<<BLOC
		</div>
BLOC;

		echo '<br/>'.PHP_EOL ;
	}
}