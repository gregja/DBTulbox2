<?php
// TODO : brouillon à retravailler

if (array_key_exists ( 'schema', $_GET ) && array_key_exists ( 'table', $_GET )) {
	$schema = Sanitize::blinderGet('schema') ;
	$table  = Sanitize::blinderGet('table') ;
	
	echo '<div>'.PHP_EOL ;
	echo '<h4>Cliquez sur les liens ci-dessous pour lancer les convertisseurs</h4>'.PHP_EOL;
	echo '<div class="container">'.PHP_EOL ;

	echo HtmlToolbox::genHtmlLink( 'dbconvert_db2i_2_db2win.php?schema=' . $schema . '&table=' . $table, 'Conversion DB2 i -> DB2 WinLnx' ) . '<br/>';
	echo HtmlToolbox::genHtmlLink( 'dbconvert_db2_2_mysql.php?schema=' . $schema . '&table=' . $table, 'Conversion DB2 i -> MySQL' ) . '<br/>';
	echo HtmlToolbox::genHtmlLink( 'dbconvert_db2_2_activerecord.php?schema=' . $schema . '&table=' . $table, 'Conversion -> ActiveRecord' ) . '<br/>';
	echo HtmlToolbox::genHtmlLink( 'dbconvert_db2_2_xml.php?schema=' . $schema . '&table=' . $table. '&crud_export_xml', 'Conversion -> XML' ) . '<br/>';
	
	echo '</div>'.PHP_EOL ;
	echo '</div>'.PHP_EOL ;		
}

