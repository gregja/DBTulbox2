<?php

abstract class HtmlToolbox {

    public static function genHtmlLink($option, $titre, $externe = false) {
        if ($externe) {
            $target = 'target="_blank"';
        } else {
            $target = '';
        }
        return '<a href="' . $option . '" ' . $target . '>' . $titre . '</a>';
    }

    public static function genIconeLink($image, $action = '', $titre = '') {

        $titre = trim($titre);
        $action = trim($action);

        $link = '<img src="' . $image . '"';
        if ($titre != '') {
            $link .= ' alt="' . htmlentities($titre) . '"';
        }
        if ($action != '') {
            $link .= ' onclick="' . $action . ';return false;"';
        }
        $link .= ' />';

        return $link;
    }

    public static function genHrefLink($image, $action = '', $titre = '') {

        $titre = trim($titre);
        $action = trim($action);
        $link = '<a href="' . $action . '" class="modal">';
        $link .= '<img src="' . $image . '"';
        if ($titre != '') {
            $link .= ' alt="' . htmlentities($titre) . '"';
        }
        $link .= ' />';
        $link .= '</a>';

        return $link;
    }

    public static function genCrudIconeLink($option, $id, $profondeur = '',
            $titre = '', $image = '') {
        $profondeur = self::genpath_profondeur($profondeur);

        $option = strtolower(trim($option));
        $id = trim($id);
        $titre = trim($titre);
        $action = '';
        $image = trim($image);

        switch ($option) {
            case 'upd': {
                    if ($titre == '') {
                        $titre = 'Mettre à jour';
                    }
                    if ($image == '') {
                        $image = $profondeur . 'images/b_edit.png';
                    }
                    $action = $option . '_item';
                    break;
                }
            case 'del': {
                    if ($titre == '') {
                        $titre = 'Supprimer';
                    }
                    if ($image == '') {
                        $image = $profondeur . 'images/b_drop.png';
                    }
                    $action = $option . '_item';
                    break;
                }
            case 'rtv': {
                    if ($titre == '') {
                        $titre = 'Afficher';
                    }
                    if ($image == '') {
                        $image = $profondeur . 'images/b_select.png';
                    }
                    $action = $option . '_item';
                    break;
                }
            case 'ins': {
                    if ($titre == '') {
                        $titre = 'Créer';
                    }
                    if ($image == '') {
                        $image = $profondeur . 'images/b_edit.png';
                    }
                    $action = $option . '_item';
                    break;
                }
            default : {
                    $option = '***';
                    $id = '';
                }
        }
        if ($action != '') {
            //$action .= '?crud_ope='.$option.'&crud_id='.$id ;
            $action .= "('" . $id . "')";
        }

        return self::genIconeLink($image, $action, $titre);
    }

    public static function genCrudLink($script_called, $option, $id,
            $profondeur = '', $titre = '', $image = '') {
        $profondeur = self::genpath_profondeur($profondeur);

        $option = strtolower(trim($option));
        $id = trim($id);
        $titre = trim($titre);
        $action = '';
        $image = trim($image);
        $script_called = trim($script_called);

        switch ($option) {
            case 'upd': {
                    if ($titre == '') {
                        $titre = 'Mettre à jour';
                    }
                    if ($image == '') {
                        $image = $profondeur . 'images/b_edit.png';
                    }
                    $action = "{$script_called}?crud_ope={$option}&crud_id={$id}";
                    break;
                }
            case 'del': {
                    if ($titre == '') {
                        $titre = 'Supprimer';
                    }
                    if ($image == '') {
                        $image = $profondeur . 'images/b_drop.png';
                    }
                    $action = "{$script_called}?crud_ope={$option}&crud_id={$id}";
                    break;
                }
            case 'rtv': {
                    if ($titre == '') {
                        $titre = 'Afficher';
                    }
                    if ($image == '') {
                        $image = $profondeur . 'images/b_select.png';
                    }
                    $action = "{$script_called}?crud_ope={$option}&crud_id={$id}";
                    break;
                }
            case 'ins': {
                    if ($titre == '') {
                        $titre = 'Créer';
                    }
                    if ($image == '') {
                        $image = $profondeur . 'images/b_edit.png';
                    }
                    $action = "{$script_called}?crud_ope={$option}";
                    break;
                }
            default : {
                    $action = '#';
                    $option = '***';
                    $id = '';
                }
        }
        $action .= '&modal=true';
        return self::genHrefLink($image, $action, $titre);
    }

    public static function genTreeLink($option_menu, $profondeur = '') {
        if (isset($option_menu ['externe']) && $option_menu ['externe']) {
            $externe = true;
            $link_menu = $option_menu ['option'];
        } else {
            $externe = false;
            $link_menu = $profondeur . $option_menu ['option'];
        }
        return '<li>' . self::genHtmlLink($link_menu, $option_menu ['title'],
                $externe) . '</li>' . PHP_EOL;
    }

    public static function genMenu($profondeur = '') {

        $profondeur = self::genpath_profondeur($profondeur);

        $liste_menus = MenuApp::initMenu();
        $menu = '<div id="div_navigation" >' . PHP_EOL;
        if (defined('TYP_MENU_APP') && TYP_MENU_APP == '*dropdown') {
            $menu .= '<ul class="dropdown">' . PHP_EOL;
        } else {
            $menu .= '<ul id="menuapp">' . PHP_EOL;
        }

        foreach ($liste_menus as $option_menu) {
            $top_affiche = false;
            // les options en "test" ne s'affichent que si on force l'URL avec le paramètre "test"
            if (isset($option_menu['test'])) {
                if (isset($GLOBALS['display_options_test'])
                        && $GLOBALS['display_options_test'] === true) {
                    $top_affiche = true;
                } else {
                    if (isset($_GET['test']) && $option_menu['test']) {
                        $top_affiche = true;
                    }
                }
            } else {
                // options qui ne peuvent s'afficher que sur une plateforme particulière
                if (isset($option_menu['platform'])) {
                    if ($option_menu['platform'] == 'i5_only' && Misc::isIBMiPlatform()) {
                        $top_affiche = true;
                    }
                } else {
                    $top_affiche = true;
                }
            }
            if ($top_affiche) {
                $menu .= self::genSousMenu($option_menu, $profondeur);
            }
        }
        $menu .= '</ul>' . PHP_EOL;
        $menu .= '</div>' . PHP_EOL;
        return $menu;
    }

    public static function genSousMenu($option_menu, $profondeur) {

        if (defined('TYP_MENU_APP') && TYP_MENU_APP == '*dropdown') {
            $li = '<li class="dropdown_trigger">' ;
        } else {
            $li = '<li>' ;
        }

        $menu = '';
        if (is_array($option_menu) && is_array($option_menu['option'])) {
            $menu .= $li . $option_menu['title'] . PHP_EOL;
            $menu .= '<ul>' . PHP_EOL;
            foreach ($option_menu['option'] as $option_ss_menu) {
                if (is_array($option_ss_menu)) {
                    $menu .= self::genSousMenu($option_ss_menu, $profondeur);
                } else {
                    $menu .= self::genTreeLink($option_menu, $profondeur);
                }
            }
            $menu .= '</ul>' . PHP_EOL;
            $menu .= '</li>' . PHP_EOL;
        } else {
            $menu .= self::genTreeLink($option_menu, $profondeur);
        }
        return $menu;
    }

}
