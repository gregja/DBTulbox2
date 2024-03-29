<?php
$currentScript = 'dbRoutineDisplay';

if ($this->get_method() == 'GET' && array_key_exists ( 'schema', $_GET ) && array_key_exists ( 'routine', $_GET )) {
    $cnxdb = $this->getDB();
	$schema = Sanitize::blinderGet('schema') ;
	$routine  = Sanitize::blinderGet('routine') ;
    $type  = Sanitize::blinderGet('type') ;

    if ($type == 'PROCEDURE') {
        $type_objet = 'procédure stockée';
    } else {
        $type_objet = 'fonction';
    }
    echo '<h3>Description de la '.$type_objet.' : '.$schema.'/'.$routine . '</h3>';

    $menus = [
        ['desc'=> 'Définition', 'script' => 'backend/dbextract_affroutine_defn.php', 'load'=>'initial'],
        ['desc'=> 'Source SQL', 'load' => 'differed', 'script' => 'dbTableDisplaySourceProc' ],
        ['desc'=> 'Objets utilisés', 'load' => 'differed', 'script' => 'dbRoutineDisplayObjUsed'],
        ['desc'=> 'Objets utilisateurs', 'load' => 'differed', 'script' => 'dbRoutineDisplayObjUsers']
    ];

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