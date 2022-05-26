<?php

abstract class SpecifBusiness {

	public static function get_donnees_fonctionnelles_figees_sur_vues ($options=array()) {
            $sql = '' ;
			/*
            if (is_array($options) && count($options)>0) {
                foreach ($options as $option_key => $option_val ) {
                    if ($option_key == 'codsoc' && $option_val==true) {
        		$sql .= " and (VIEW_DEFINITION like '%CONO=%' or VIEW_DEFINITION like '%CONO =%')" ;
                    }
                }
            }
			*/
            return $sql ;
	}
	
	public static function get_listeSocietes() {
		$types = array () ;
		$types['*'] = 'Toutes' ;
		$types['001'] = 'xxx (à personnaliser)' ;
		$types['002'] = 'yyy (à personnaliser)' ;
		$types['003'] = 'zzz (à personnaliser)' ;
		return $types ;
	}
	
	public static function get_typePeriodes() {
		$types = array () ;
		$types['*'] = 'Toutes' ;
		$types['J'] = 'Journalière' ;
		$types['H'] = 'Hebdomadaire' ;
		$types['M'] = 'Mensuelle' ;		
		return $types ;
		
	}
	
}
 