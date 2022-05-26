<?php 

abstract class GenForm {
	
	public static function input_text($nom_zone, &$zones_form, &$erreurs=array()) {
		?>
<label for="<?php echo $nom_zone ;?>">Saisissez <?php echo $nom_zone; ?>:</label>
		<?php echo isset($erreurs[$nom_zone]) ? $erreurs[$nom_zone] : ''; ?>
<br />
<input
	id="<?php echo $nom_zone ;?>" name="<?php echo $nom_zone ;?>"
	type="text"
	maxlength="<?php echo $zones_form[$nom_zone]['long_max'] ;?>"
	value="<?php echo isset($_POST[$nom_zone]) ? $_POST[$nom_zone] : ''; ?>"
	size="<?php echo $zones_form[$nom_zone]['long_max'] ;?>" />
<br />
		<?php
	}

	public static function input_textarea($nom_zone, $cols, $rows, &$zones_form, &$erreurs=array()) {
		?>
<label for="<?php echo $nom_zone ;?>">Saisissez <?php echo $nom_zone; ?>:</label>
		<?php echo isset($erreurs[$nom_zone]) ? $erreurs[$nom_zone] : ''; ?>
<br />
<textarea id="<?php echo $nom_zone ;?>" name="<?php echo $nom_zone ;?>"
	cols="<?php echo $cols ;?>" rows="<?php echo $rows ;?>" /><?php echo isset($_POST[$nom_zone]) ? $_POST[$nom_zone] : ''; ?></textarea>
<br />
		<?php
	}

	public static function input_hidden ($nom_zone, $valeur) {
		echo "<input id=\"{$nom_zone}\" name=\"{$nom_zone}\" type=\"hidden\" value=\"{$valeur}\" /><br/>";
		
	}
	public static function input_select ($nom_zone, $liste_valeurs, $valeur_choisie='', &$erreurs=array()) {
		$valeur_choisie = trim($valeur_choisie) ;
		echo "<select id=\"$nom_zone\" name=\"$nom_zone\">".PHP_EOL;
		foreach ($liste_valeurs as $key=>$value) {
			if (trim($key) == $valeur_choisie) {
				$temp_selected = "selected=\"selected\"";
			} else {
				$temp_selected = '';
			}
			echo "<option value=\"$key\" $temp_selected >$value</option>".PHP_EOL;
		}
		echo "</select>".PHP_EOL;		
	}
	
	public static function open_form ($nom_form, $methode='post', $action='') {
		echo '<form id="'.$nom_form.'" name="'.$nom_form.'" method="'.$methode.'"';
		if ($action != '') {
			echo ' action="'.$action.'"';
			echo '>';
		}
	}

	public static function close_form () {
		echo '</form><br/>'.PHP_EOL ;
	}

	public static function button_send () {
		echo '<input name="send" id="send" type="submit" value="Valider" />';
	}
}