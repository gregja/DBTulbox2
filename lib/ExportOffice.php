<?php
/**
 * Description of class ExportOffice
 *
 * @author gregory
 */
abstract class ExportOffice {

    public static function excel($nom_fichier='') {
        self::vidageBuffer() ;    	
        $params = array();
        $params['Content-type'] = 'application/x-msexcel';
        $nom_fichier = trim($nom_fichier);
        if ($nom_fichier != '') {
            $params['Content-Disposition'] = "attachment; filename={$nom_fichier}.xls";
        }
        $params['Pragma'] = 'no-cache';
        $params['Cache-Control'] = 'no-store, no-cache, must-revalidate';
        $params['Expires'] = 'Sat, 26 Jul 1997 05:00:00 GMT';

        self::sendHeader($params);
    }

    public static function csv($nom_fichier='') {
        self::vidageBuffer() ;    	
        $params = array();
        $params['Content-type'] = 'application/vnd.ms-excel';
        $nom_fichier = trim($nom_fichier);
        if ($nom_fichier != '') {
            $params['Content-Disposition'] = "attachment; filename={$nom_fichier}.csv";
        }
        $params['Pragma'] = 'no-cache';
        $params['Cache-Control'] = 'no-store, no-cache, must-revalidate';
        $params['Expires'] = 'Sat, 26 Jul 1997 05:00:00 GMT';

        self::sendHeader($params);
    }
    
    public static function txt($nom_fichier='', $charset='') {
        self::vidageBuffer() ;    	
        $params = array();
        $format = 'text/plain;';
      	$charset = trim($charset);
        if ($charset != '') {
        	$format .= ' charset='.$charset ;
        } else {
        	$format .= ' charset=ISO-8859-1' ;
        }
        $params['Content-type'] = $format ;
        $nom_fichier = trim($nom_fichier);
        if ($nom_fichier != '') {
            $params['Content-Disposition'] = "attachment; filename={$nom_fichier}.txt";
        }
        $params['Pragma'] = 'no-cache';
        $params['Cache-Control'] = 'no-store, no-cache, must-revalidate';
        $params['Expires'] = 'Sat, 26 Jul 1997 05:00:00 GMT';

        self::sendHeader($params);
    }
    
    public static function word($nom_fichier='') {
    	self::vidageBuffer() ;
        $params = array();
        $params['Content-type'] = 'application/doc';
        $nom_fichier = trim($nom_fichier);
        if ($nom_fichier != '') {
            $params['Content-Disposition'] = "attachment; filename={$nom_fichier}.doc";
        }
        $params['Pragma'] = 'no-cache';
        $params['Cache-Control'] = 'no-store, no-cache, must-revalidate';
        $params['Expires'] = 'Sat, 26 Jul 1997 05:00:00 GMT';

        self::sendHeader($params);
    }

    /*
     * l'export PDF ne peut être réalisé aussi simplement que l'export XLS ou
     * DOC, mais l'initialisation de l'entête obéit au même principe
     */

    public static function pdf($nom_fichier='') {
        self::vidageBuffer() ;    	
        $params = array();
        $nom_fichier = trim($nom_fichier);
        if ($nom_fichier == '') {
            $nom_fichier = 'mypdf';
        }

        if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
            $params['Pragma'] = 'public';
            $params['Expires'] = 0;
            $params['Cache-Control'] = 'must-revalidate, post-check=0, pre-check=0';
            $params['Content-type'] = 'application-download';
            // $params['Content-Length'] = $size;
            $params['Content-Disposition'] = "attachment; filename={$nom_fichier}.pdf";
            $params['Content-Transfer-Encoding'] = 'binary';
        } else {
            // $params['Content-type'] = 'application/pdf';
            $params['Content-type'] = 'application-download';
            // $params['Content-Length'] = $size;
            $params['Content-Disposition'] = "attachment; filename={$nom_fichier}.pdf";
            $params['Pragma'] = 'no-cache';
            $params['Cache-Control'] = 'no-store, no-cache, must-revalidate';
            $params['Expires'] = 'Sat, 26 Jul 1997 05:00:00 GMT';
        }

        self::sendHeader($params);
    }
    
    private static function vidageBuffer() {
        /*
         * vidage des buffers avant d'effectuer l'export Excel ou Word
        */
        if (headers_sent()) {
            while (@ob_end_flush());
        }
		ob_end_clean();
    }
    
    private static function sendHeader($params) {
        foreach ($params as $key => $value) {
            header($key.': '. $value);
        }
    }
    
}
