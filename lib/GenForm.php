<?php 
abstract class GenForm {
	
	public static function inputHidden ($nom_zone, $valeur) {
		return "<input id=\"{$nom_zone}\" name=\"{$nom_zone}\" type=\"hidden\" value=\"{$valeur}\" /><br/>";
		
	}
	
	public static function inputSelect ($nom_zone, $liste_valeurs, $valeur_choisie='', &$erreurs=array()) {
		$valeur_choisie = trim($valeur_choisie) ;
		$field = "<select id=\"$nom_zone\" name=\"$nom_zone\" class=\"custom-select custom-select-sm\">".PHP_EOL;
		foreach ($liste_valeurs as $key=>$value) {
			if (trim($key) == $valeur_choisie) {
				$temp_selected = "selected=\"selected\"";
			} else {
				$temp_selected = '';
			}
			$field .= "<option value=\"$key\" $temp_selected >$value</option>".PHP_EOL;
		}
		$field .= "</select>".PHP_EOL;
		return $field;
	}
	
}