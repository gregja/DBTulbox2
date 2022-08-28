<?php
$currentScript = 'dbTableDisplay';

if ($this->get_method() == 'GET' && array_key_exists ( 'schema', $_GET ) && array_key_exists ( 'table', $_GET )) {
    $cnxdb = $this->getDB();
	$schema = Sanitize::blinderGet('schema') ;
	$table  = Sanitize::blinderGet('table') ;
	
	$svg_refresh_btn = <<<SVG
	<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-repeat" viewBox="0 0 16 16">
		<path d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41zm-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9z"></path>
		<path fill-rule="evenodd" d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5.002 5.002 0 0 0 8 3zM3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9H3.1z"></path>
	</svg>    
SVG;

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
			['desc'=> 'Datastructure', 'script' => 'backend/dbextract_affichage_parts.php', 'load'=>'initial' ],
			['desc'=> 'Définition', 'script' => 'backend/dbextract_affichage_defn.php', 'load'=>'initial']
		];

		if ($type_objet != 'V') {
			$menus[] = ['desc'=> 'Statistiques', 'script' => 'backend/dbextract_affichage_stats.php', 'load'=>'initial'];
		}
		$menus[] = ['desc'=> 'Source SQL', 'script' => 'dbTableDisplaySource', 'load'=>'differed'];
		if ($type_objet != 'V' && $type_objet != 'L') {
			$menus[] = ['desc'=> 'Indexs', 'script' => 'dbTableDisplayIndexs', 'load'=>'differed'];
		}
		if ($type_objet == 'T' || $type_objet == 'P' ) {
			$menus[] = ['desc'=> 'Verrouillages', 'script' => 'dbTableDisplayLocks', 'load'=>'differed', 'refresh'=>true];
		}
		if ($type_objet == 'V' || $type_objet == 'M' ) {
			// pour vues et MQT seulement 
			// TODO : ajouter les procédures stockées ? si oui quel type d'objet ?
			$menus[] = ['desc'=> 'Objets utilisés', 'script' => 'dbTableDisplayObjUsed', 'load'=>'differed'];
		}
		if ($type_objet == 'T' || $type_objet == 'P' || $type_objet == 'V' || $type_objet == 'M'  ) {
			// pour tables, fichiers physiques, vues et MQT 
			// TODO : ajouter les procédures stockées ? si oui quel type d'objet ?
			$menus[] = ['desc'=> 'Objets utilisateurs', 'script' => 'dbTableDisplayObjUsers', 'load'=>'differed']; // pour vues et procédures stockées
		}
		if ($type_objet == 'T' || $type_objet == 'P' ) {
			$menus[] = ['desc'=> 'Triggers', 'script' => 'dbTableDisplayTriggers', 'load'=>'differed'];
		}
		/*
		 TODO : Fonctions à implémenter ultérieurement

		$menus[] = ['desc'=> 'Query', 'script' => ''];
		$menus[] = ['desc'=> 'Conversions', 'script' => ''];
		*/

		$tabs_menu = [];
		$tabs_content = [];

		foreach($menus as $ndx => $menu) {
			if ($menu['script'] != '') {
				$index = $ndx+1;
				$option = $menu['desc'];
				$tmp_link = '<li data-tab="'.$index.'" class="nav-item">' ;
				$tmp_link .= '<a class="nav-link" data-toggle="pill" href="#option'.$index.'"';
				if ($menu['load'] == 'differed') {
					$tmp_link .= ' data-url="'.$menu['script'].'"' ;
				}
				$tmp_link .= '>';
				if (isset($menu['refresh']) && $menu['refresh']) {
					$tmp_link .= $svg_refresh_btn . '&nbsp;';
				}
				$tmp_link .= $option.'</a></li>';
				$tabs_menu[] = $tmp_link ;
			}
		}
		$tabs_menu_out = implode(PHP_EOL, $tabs_menu);

		echo <<<BLOC
		<div class="container">
		<ul class="nav nav-pills" id="dynamictabs">
		{$tabs_menu_out}
		</ul>
		<div class="tab-content">
BLOC;

		foreach($menus as $ndx => $xmenu) {
			if (trim($xmenu['script']) != '') {
				$index = $ndx+1;
				echo '<div id="option'.$index.'" class="tab-pane fade ">'.PHP_EOL;
				if ($xmenu['load'] == 'initial') {
					require $xmenu['script'];
				}
				echo '</div>'.PHP_EOL;
			}

		}
		echo <<<BLOC
		</div>
BLOC;

		echo '<br/>'.PHP_EOL ;
	}
}