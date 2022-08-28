<?php
$currentScript = 'dbTriggerDisplay';

if ($this->get_method() == 'GET' && array_key_exists ( 'schema', $_GET ) && array_key_exists ( 'trigger', $_GET )) {
    $cnxdb = $this->getDB();
	$schema = Sanitize::blinderGet('schema') ;
	$trigger  = Sanitize::blinderGet('trigger') ;

    echo '<h3>Description du trigger : '.$schema.'/'.$trigger . '</h3>';

    $menus = [
        ['desc'=> 'Définition', 'script' => 'backend/dbextract_afftrigger_defn.php', 'load'=>'initial'],
        ['desc'=> 'Source SQL', 'load' => 'differed', 'script' => 'dbTriggerDisplaySource' ],
        ['desc'=> 'Objets utilisés', 'load' => 'differed', 'script' => 'dbTriggerDisplayObjUsed']
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