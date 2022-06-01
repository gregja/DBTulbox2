<?php
$currentScript = 'dbRoutineDisplay';

if ($this->get_method() == 'GET' && array_key_exists ( 'schema', $_GET ) && array_key_exists ( 'routine', $_GET )) {
    $cnxdb = $this->getDB();
	$schema = Sanitize::blinderGet('schema') ;
	$routine  = Sanitize::blinderGet('routine') ;

	$sql = DB2Tools::extractSysroutine();
	$data = $cnxdb->selectOne( $sql, array ($schema, $routine ) );
    $flag_routine_ok = true;
    $specific_name = '';
    $specific_schema = '';
	if (is_array($data)) {
		$routine_type = trim($data ['ROUTINE_TYPE']) ;
		if ($routine_type == 'FUNCTION') {
			$routine_type = 'Fonction' ;
		} else {
			$routine_type = 'Procédure' ;
		}
		$routine_body = trim($data ['ROUTINE_BODY']) ;		
		$routine_definition = trim($data['WARNING_DEFINITION']). PHP_EOL .
            trim(SQLTools::clean_code($data['ROUTINE_DEFINITION'])) ;
		$routine_deterministic = trim($data ['IS_DETERMINISTIC']) ;
        $specific_name = trim($data['SPECIFIC_NAME']);
        $specific_schema = trim($data['SPECIFIC_SCHEMA']);
	} else {
        $flag_routine_ok = false;
		$routine_definition = 'impossible d\'extraire le source de cette routine';
	}
	
	echo $routine_type . ' DB2 : '. $schema.'/'.$routine ;
	echo '<br/><br/>';
	if ($routine_deterministic == 'YES') {
		echo 'Cette '.strtolower($routine_type).' est de type "deterministic"<br/>'.PHP_EOL;
	} else {
		echo 'Cette '.strtolower($routine_type).' n\'est pas de type "deterministic"<br/>'.PHP_EOL;
	}
	echo 'Cette '.strtolower($routine_type).' est de type '.$routine_body.'<br/>'.PHP_EOL;

	echo '<br/>';

	if ($flag_routine_ok) {
		$menus = [
            ['desc'=> 'Source SQL' ],
			['desc'=> 'Liste des objets utilisés'],
            ['desc'=> 'Liste des objets utilisateurs']
		];
	
		$tabs_menu = [];
		$tabs_content = [];

		foreach($menus as $ndx => $menu) {
            $index = $ndx+1;
            $option = $menu['desc'];
            $tabs_menu[] = '<li data-tab="'.$index.'" class="nav-item"><a class="nav-link" data-toggle="pill" href="#option'.$index.'">'.$option.'</a></li>';
		}
		$tabs_menu_out = implode(PHP_EOL, $tabs_menu);

		echo <<<BLOC
		<div class="container">
		<ul class="nav nav-pills">
		{$tabs_menu_out}
		</ul>
		<div class="tab-content">
BLOC;
        /* ====== AFFICHAGE CODE SOURCE ======= */
        echo '<div id="option1" class="tab-pane fade ">'.PHP_EOL;
        echo '<fieldset><legend><h6>Code source de la '.$routine_type.' DB2 '.$routine.' </h6></legend>';
        echo '<pre>' ;
            
            /*
             * On vérifie si la procédure système GENERATE_SQL est disponible, 
             * auquel cas on propose un lien permettant de l'utiliser pour 
             * générer un source SQL 
             */
            list($routine_schema, $routine_name) = DB2Tools::procGenerateSQL() ;
            $check_routine = GenAlterSQL::checkObjectExists($cnxdb, $routine_schema, $routine_name, 'PROCEDURE') ;
            if ($check_routine) {
                $datas = GenAlterSQL::generateSQLObject($cnxdb, $schema, $routine, 'PROCEDURE') ;
                foreach ($datas as $data) {
                    if ($data['SRCDTA']) {
                        $code = rtrim($data['SRCDTA']) ;
                        if ($code != '') {
                            echo $code . PHP_EOL ;
                        }
                    }
                }
            } else {
                echo $routine_definition ;
            }
        echo '</pre>' ;
        echo '</fieldset>';
        echo '</div>'.PHP_EOL;

        /* ====== AFFICHAGE OBJETS UTILISES ======= */
        echo '<div id="option2" class="tab-pane fade ">'.PHP_EOL;
        echo '<fieldset><legend><h6>Liste des objets utilisés par la '.$routine_type.' ' .$routine.' </h6></legend>';
        $sql = DB2Tools::extractSysroutinedep();
        $data = $cnxdb->selectBlock( $sql, array ($specific_schema, $specific_name ) );
        if (is_array($data) && count($data)>0) {
            echo '<table class="table table-striped table-sm table-bordered" >'.PHP_EOL;
            echo '<thead class="thead-dark">'.PHP_EOL;
            echo '<tr><th>Object name</th><th>Object schema</th><th>Object type</th></tr>'.PHP_EOL;	
            echo '</thead>'.PHP_EOL;
            echo '<tbody>'.PHP_EOL;		
            foreach($data as $key=>$value) {
                echo '<tr>';
                echo '<td>' . trim($value ['OBJECT_NAME']) . '</td>';
                echo '<td>' . trim($value ['OBJECT_SCHEMA']) . '</td>';
                echo '<td>' . trim($value ['OBJECT_TYPE']) . '</td>';
                echo '<tr>' ;
            }
            echo '</tbody>'.PHP_EOL;		
            echo '</table>'.PHP_EOL;		
        } else {
            echo '<br/>'.PHP_EOL;		
            echo 'Néant'.PHP_EOL;		
            echo '<br/>'.PHP_EOL;		
        }
        echo '</fieldset>'.PHP_EOL;		
        echo '</div>'.PHP_EOL ;        

        /* ====== AFFICHAGE OBJETS UTILISATEURS ======= */
        echo '<div id="option3" class="tab-pane fade ">'.PHP_EOL;
        $sql = DB2Tools::extractSysroutinedepInverse();
        $data = $cnxdb->selectBlock( $sql, array ( $routine ) );
        if (is_array($data) && count($data)>0) {
            echo '<div>'.PHP_EOL ;
            echo '<h4 href="#">>Liste des objets utilisateurs</h4>'.PHP_EOL;
            echo '<div class="container">'.PHP_EOL ;
            
            echo '<br/><fieldset><legend><h6>Liste des objets utilisant la '.$routine_type . ' ' .$routine.'</h6></legend>'.PHP_EOL;
            echo '<table class="table table-striped table-sm table-bordered" >'.PHP_EOL;
            echo '<thead class="thead-dark">'.PHP_EOL;
            echo '<tr><th>Routine name</th><th>Routine schema</th><th>Object Schema</th></tr>'.PHP_EOL;
            echo '</thead>'.PHP_EOL;
            echo '<tbody>'.PHP_EOL;		
            foreach($data as $key=>$value) {
                echo '<tr>';
                echo '<td>' . trim($value ['SPECIFIC_NAME']) . '</td>';
                echo '<td>' . trim($value ['SPECIFIC_SCHEMA']) . '</td>';
                echo '<td>' . trim($value ['OBJECT_SCHEMA']) . '</td>';
                echo '<tr>' .PHP_EOL;
            }
            echo '</tbody>'.PHP_EOL;	
            echo '</table>'.PHP_EOL;
            echo '</fieldset>'.PHP_EOL;
            echo '</div>'.PHP_EOL ;
            echo '</div>'.PHP_EOL ;
        }
        echo '</div>'.PHP_EOL;
        
		echo '<br/>'.PHP_EOL ;
	}
}