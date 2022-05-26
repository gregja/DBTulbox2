<?php
abstract class SQLTools {
	/**
	 * 
	 * Colorisation d'une requ�te SQL pour en faciliter la lecture (les mots r�serv�s SQL sont affich�s en bleu)
	 * @param string $sql
	 */
	public static function coloriseCode($sql) {
		$pos = strpos ( $sql, '{SEPARATOR}' );
		if ($pos !== false) {
			$sql = str_replace ( '{SEPARATOR}', '/', $sql );
		}
		
		$font_color = 'blue';
		$sql = nl2br($sql);
		$sql = str_replace ( "\t", "&nbsp;&nbsp;", $sql );
		
		// suppression des tabulations, et insertion d'un blanc apr�s chaque virgule
		$sql = str_ireplace ( array ("\t", ', ', ',' ), array (' ', ',', ', ' ), trim ( $sql ) );
		// d�finition des mots r�serv�s SQL coloris�s
		$sql_orig = array ('label ', 'alter ', 'drop ', 'table ', 'column ', 'set data type ', 'view ', 'create ', 'index ', 'delete ', 'update ', 'select ', 'from ', 'where ', 'group by ', 'order by ', 'having ', ' case ', 'when ', 'then ', ' else ', ' end', ' and ', ' or ', ' like ', ' left outer join ', ' inner join ', ' on ', ' as ', 'date(' );
		$nb_postes = count ( $sql_orig ) ;
		// pr�paration du tableau contenant les mots r�serv�s coloris�s (et forc�s en majuscule)
		$sql_dest = array ();
		for($i = 0; $i < $nb_postes; $i ++) {
			$sql_dest [] = '<font color = "' . $font_color . '">' . strtoupper ( $sql_orig [$i] ) . '</font>';
		}
		$sql = str_ireplace ( $sql_orig, $sql_dest, $sql );
		$sql = '<font face="Courier New" size="2">' . $sql . '</font>' . PHP_EOL;
		return self::clean_code($sql);
	}

	public static function clean_code($str) {
		return Sanitize::clean_code($str);
	}
	
}