<?php
/*
 * Classe permettant de générer une barre de pagination
 * Créée à partir de fonctions extraites de cet excellent livre : 
 * PHP Cookbook (2nd Edition), par Adam Trachtenberg et David Sklar, O'Reilly (2006) 
 * Des modifications ont été apportée au code initial, telles que : 
 * - le regroupement de ces 2 fonctions dans une classe abstraite, sous forme de
 *   méthodes statiques, afin de renforcer la robustesse et de faciliter la
 *   réutilisation au sein de projets orientés objet 
 * - la possibilité de passer la page d'appel aux 2 méthodes, ceci afin de faciliter 
 *   la réutilisation de ces 2 méthodes sur différentes pages 
 * - il a été nécessaire d'ajouter un tableau $params permettant de transmettre d'une 
 *   page à l'autre des paramètres autres que l'offset, tels que les critéres de 
 *   sélection saisis sur le formulaire de recherche.
 * - le nombre de pages directement "appelables" a été limité à 5, des points de suspension
 *   sont ajoutés ensuite, et le lien vers la dernière page est ajouté en fin de barre de 
 *   pagination (la version initiale proposait un lien vers chaque page, ce qui
 *   donnait des résultats particulièrement laids sur des jeux de données de grande taille. 
 */

abstract class Pagination {
	
	public function __construct() {
		throw new Exception ( "Static class - instances not allowed." );
	}
	
	static public function pcPrintLink($inactive, $text, $offset, $current_page, $params_page) {
		// on prépare l'URL avec tous les paramètres sauf "offset"
		$output = '';
		if (! isset ( $offset ) or $offset == '' or $offset == '0') {
			$offset = '1';
		}
		$url = '';
		$params_page ['offset'] = $offset;
		$url = '?' . http_build_query ( $params_page );
		if ($inactive) {
			$output = "<li class=\"page-item active\"><a class=\"page-link\">$text</a></li>\n";
		} else {
			$output = "<li class=\"page-item\" aria-current=\"page\">" . 
			"<a class=\"page-link\" href='" . htmlentities ( $current_page ) . "$url'>$text</a></li>\n";
		}
		return $output;
	}
	
	static public function pcIndexedLinks($total, $offset, $per_page, $curpage, $parmpage) {
		$barre = "<nav><ul class=\"pagination\">";
		$barre .= self::pcPrintLink ( $offset == 1, '<< Pr&eacute;c.', $offset - $per_page, $curpage, $parmpage );
		
		$compteur = 0;
		$top_suspension = false;
		
		// affichage de tous les groupes à l'exception du dernier
		for($start = 1, $end = $per_page; $end < $total; $start += $per_page, $end += $per_page) {
			$compteur += 1;
			if ($compteur < 5) {
				$barre .= self::pcPrintLink ( $offset == $start, "$start-$end", $start, $curpage, $parmpage );
			} else {
				if (! $top_suspension) {
					$top_suspension = true;
					$barre .= "<li class=\"page-item disabled\"><a class=\"page-link\"> ... </a></li>";
				}
			}
		}
		
        $end = ($total > $start) ? '-'.$total : '';
		$barre .= self::pcPrintLink ( $offset == $start, "$start$end", $start, $curpage, $parmpage );		
		$barre .= self::pcPrintLink ( $offset == $start, 'Suiv. >>', $offset + $per_page, $curpage, $parmpage );
		$barre .= "</ul></nav>";
		return $barre;
	}
}
