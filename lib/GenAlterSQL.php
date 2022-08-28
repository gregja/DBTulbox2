<?php

abstract class GenAlterSQL {

    /**
     * 
     * Fonction de génération d'un ALTER TABLE pour remplacer les VARCHAR par des CHAR dans une table existante
     * @param string $schema
     * @param string $table
     * @param boolean $affichage_navigateur

     */
    public static function alterVarcharTable($db, $schema, $table, $affichage_navigateur = false) {
        $schema = trim($schema);
        $table = trim($table);
        $sql_gen = '';

        $sql = <<<BLOC_SQL
SELECT           
 COLUMN_NAME,
 DATA_TYPE,                             
 LENGTH AS LENGTH,                                     
 SCALE AS SCALE,                                       
 NULLS AS NULLABLE,                                           
 LOWER(SUBSTR( COLUMN_DEFAULT, 1, 20 )) AS DEFAULT ,  
 "CCSID" AS CCSID  
FROM QSYS2{SEPARATOR}SYSCOLUMNS                                                   
WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND DATA_TYPE = 'VARCHAR'  
ORDER BY ORDINAL_POSITION      
BLOC_SQL;

        $datas = DBWrapper::selectBlock($db, $sql, array($schema, $table));
        if (count($datas) > 0) {
            $schema_table = $schema . '/' . $table;
            $template_begin = <<<BLOC_SQL
-- Suppression des VARCHAR sur la table {$schema_table} 
DECLARE GLOBAL TEMPORARY TABLE SAX_ALTERTABLE AS (
SELECT * FROM {$schema_table} WITH NC
) WITH DATA WITH REPLACE NOT LOGGED ;
DELETE FROM {$schema_table} WITH NC ;
BLOC_SQL;

            $template_end = <<<BLOC_SQL
INSERT INTO {$schema_table}
SELECT * FROM QTEMP/SAX_ALTERTABLE WITH NC ;
BLOC_SQL;

            $sql_gen .= '-- Suppression des VARCHAR sur la table ' . $schema_table . PHP_EOL;
            $sql_gen .= 'DECLARE GLOBAL TEMPORARY TABLE SAX_ALTERTABLE AS ( ' . PHP_EOL;
            $sql_gen .= 'SELECT * FROM ' . $schema_table . ' WITH NC ' . PHP_EOL;
            $sql_gen .= ') WITH DATA WITH REPLACE NOT LOGGED ; ' . PHP_EOL;
            $sql_gen .= 'DELETE FROM ' . $schema_table . ' WITH NC ;' . PHP_EOL;
            $sql_gen .= 'ALTER TABLE ' . $schema . '/' . $table . PHP_EOL;
            foreach ($datas as $data) {
                $sql_gen .= ' ALTER COLUMN ' . trim($data ['COLUMN_NAME']) . ' SET DATA TYPE CHAR(' . $data ['LENGTH'] . ') CCSID 37 ' . PHP_EOL;
            }
            $sql_gen .= ';' . PHP_EOL;
            $sql_gen .= 'INSERT INTO ' . $schema_table . PHP_EOL;
            $sql_gen .= '( SELECT * FROM QTEMP/SAX_ALTERTABLE ) WITH NC ;' . PHP_EOL;
        }
        //if ($affichage_navigateur) {
        //	$sql_gen = nl2br($sql_gen);
        //}
        return $sql_gen;
    }

    /**
     * 
     * Fonction de génération d'un script de regénération d'une vue existante
     * @param string $schema
     * @param string $table
     * @param boolean $affichage_navigateur
     * @param boolean $remplacement_varchar
     * @param boolean $remplacement_concat
     */
    public static function reCreateView($db, $schema, $table, $affichage_navigateur = true, $remplacement_varchar = false, $remplacement_concat = true) {

        $column_headings = array();
        $column_texts = array();
        $view = '';

        $sql = DB2Tools::extractTableStruct(false);
        $datastructure = DBWrapper::selectBlock($db, $sql, array($schema, $table));
        $list_cols = array();
        if (is_array($datastructure)) {
            foreach ($datastructure as $data) {
                $list_cols [] = trim($data ['FIELD']);
                if ($data ['FIELD'] != $data ['COLUMN_HEADING']) {
                    $column_headings[$data ['FIELD']] = $data ['COLUMN_HEADING'];
                }
                if ($data ['FIELD'] != $data ['COLUMN_TEXT']) {
                    $column_texts[$data ['FIELD']] = $data ['COLUMN_TEXT'];
                }
            }

            $colons = PHP_EOL . '( ' . implode(', ', $list_cols) . ' ) ' . PHP_EOL;
        } else {
            $colons = '';
        }

        $sql = <<<BLOC_SQL
SELECT VIEW_DEFINITION                                                         
 FROM  QSYS2{SEPARATOR}SYSVIEWS          
WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? 
BLOC_SQL;
        $data = DBWrapper::selectOne($db, $sql, array($schema, $table));
        if (array_key_exists('VIEW_DEFINITION', $data)) {
            $view_def = trim($data ['VIEW_DEFINITION']);
            $view_def = DB2Tools::convertirCaracteresAccentues($view_def);

            $view = 'CREATE OR REPLACE VIEW ' . $schema . '.' . $table;
            $view .= $colons;
            $view .= ' AS ' . PHP_EOL;
            $view .= $view_def . ';' . PHP_EOL;

            // remplacement des VARCHAR par des CHAR et un peu de nettoyage complémentaire
            if ($remplacement_varchar) {
                $view = str_replace('VARCHAR', 'CHAR', $view);
            }
            if ($remplacement_concat) {
                $view = str_replace('!!', ' CONCAT ', $view);
                $view = str_replace('||', ' CONCAT ', $view);
            }

            $view .= self::create_label_on_db2_object($db, $schema, $table, true);
            $view .= self::create_label_on_db2_object($db, $schema, $table);
            $view .= self::create_label_on_columns($schema, $table, $column_headings);
            $view .= self::create_label_on_columns($schema, $table, $column_texts, 'texts');

            //if ($affichage_navigateur) {
            //	$view = nl2br($view );
            //	$view = str_replace ( "\t", "&nbsp;&nbsp;", $view );
            //}	
        } else {
            $view .= 'vue non trouvée dans QSYS2/SYSVIEWS';
        }
        return $view;
    }

    /**
     * 
     * Fonction de génération d'un script de regénération d'une vue existante
     * @param string $schema
     * @param string $table
     * @param boolean $affichage_navigateur
     * @param boolean $remplacement_varchar
     * @param boolean $remplacement_concat
     */
    public static function reCreateTable($db, $schema, $table, $affichage_navigateur = true, $remplacement_varchar = false) {

        $table = trim($table);
        $schema = trim($schema);

        $sql = DB2Tools::get_table_structure_entete($schema, $table);
        $datatab = DBWrapper::selectOne($db, $sql, array($schema, $table));
        $is_MQT = trim($datatab['TABLE_TYPE']) == 'M' ? true : false;

        $column_headings = array();
        $column_texts = array();
        
        $nom_court = $datatab['SYSTEM_TABLE_NAME'] ;

        $sql = DB2Tools::extractTableStruct(false);

        $datastructure = DBWrapper::selectBlock($db, $sql, array($schema, $table));
        $list_cols = array();
        if (is_array($datastructure)) {
            if ($is_MQT) {
                foreach ($datastructure as $data) {
                    $data ['FIELD'] = trim($data ['FIELD']);
                    $data ['SYSTEM_COLUMN_NAME'] = trim($data ['SYSTEM_COLUMN_NAME']);
                    if ($data['FIELD'] != $data ['SYSTEM_COLUMN_NAME']) {
                        $list_cols [] = $data ['FIELD'] . ' FOR COLUMN ' . 
                                $data ['SYSTEM_COLUMN_NAME'];
                    } else {
                        $list_cols [] = $data ['FIELD'];
                    }
                }
            } else {
                $constraints = array();
                foreach ($datastructure as $data) {
                    $field_name = trim($data ['FIELD']);
                    $data ['DATA_TYPE'] = trim($data ['DATA_TYPE']);
                    $data ['COLUMN_HEADING'] = trim($data ['COLUMN_HEADING']);
                    $data ['COLUMN_TEXT'] = trim($data ['COLUMN_TEXT']);
                    $data ['SYSTEM_COLUMN_NAME'] = trim($data['SYSTEM_COLUMN_NAME']);

                    $pos = self::check_blanks_and_accents($field_name);
                    if ($pos === false) {
                        $field = $field_name;
                    } else {
                        $field = '"'. $field_name . '"';
                    }
                                       
                    if ($field_name != $data['SYSTEM_COLUMN_NAME']) {
                    	$field .= ' FOR COLUMN '. $data['SYSTEM_COLUMN_NAME'] ;
                    }

                    $field .= ' ' . $data ['DATA_TYPE'];
                    if ($data ['DATA_TYPE'] == 'DECIMAL' ||
                            $data ['DATA_TYPE'] == 'NUMERIC' ||
                            $data ['DATA_TYPE'] == 'CHAR' ||
                            $data ['DATA_TYPE'] == 'VARCHAR' ||
                            $data ['DATA_TYPE'] == 'GRAPHIC' ||
                            $data ['DATA_TYPE'] == 'VARG' ||
                            $data ['DATA_TYPE'] == 'VARGRAPHIC'
                    ) {
                        $field .= '(' . $data ['LENGTH'];
                        if (!is_null($data ['SCALE'])) {
                            $field .= ', ' . trim($data ['SCALE']);
                        }
                        $field .= ')';
                        if (!is_null($data ['ALLOCATE'])) {
                            $field .= ' ALLOCATE(' . trim($data ['ALLOCATE'] . ')');
                        }
                    }

                    if ($data ['DATA_TYPE'] == 'CHAR' || $data ['DATA_TYPE'] == 'VARCHAR' || $data ['DATA_TYPE'] == 'GRAPHIC' || $data ['DATA_TYPE'] == 'VARGRAPHIC') {
                        if (!is_null($data ['COLUMN_CCSID'])) {
                            $field .= ' CCSID ' . $data ['COLUMN_CCSID'];
                        }
                    }

                    if (!is_null($data ['CONSTRAINT_TYPE'])) {
                        $constraints[$data['CONSTRAINT_TYPE']][$data['CONSTRAINT_COLSEQ']] = $data['FIELD'];
                    }

                    if (trim($data ['COLUMN_NULLABLE']) == 'Y') {
                        $field .= ' DEFAULT NULL';
                    } else {
                        $field .= ' NOT NULL';
                        if (trim($data ['HAS_DEFAULT']) == 'Y') {
                            $defaut = trim($data ['COLUMN_DEFAULT']);
                            if ($defaut == "N''") {
                                $defaut = "''";
                            }
                            $field .= ' DEFAULT ' . $defaut . ' ';
                        }
                    }

                    if (trim($data['IS_IDENTITY']) == 'YES') {
                        $field .= PHP_EOL . ' GENERATED ' . $data['IDENTITY_GENERATION'] . ' AS IDENTITY (';
                        $field_part = array();
                        $field_part[] = ' START WITH ' . $data['IDENTITY_START'];
                        $field_part[] = ' INCREMENT BY ' . $data['IDENTITY_INCREMENT'];
                        $field_part[] = ' MINVALUE ' . $data['IDENTITY_MINIMUM'];
                        if (is_null($data['IDENTITY_MAXIMUM'])) {
                            $field_part[] = ' NO MAXVALUE ';
                        } else {
                            $field_part[] = ' MAXVALUE ' . $data['IDENTITY_MAXIMUM'];
                        }
                        if ($data['IDENTITY_CACHE'] == 0) {
                            $field_part[] = ' NO CACHE';
                        } else {
                            $field_part[] = ' CACHE ' . $data['IDENTITY_CACHE'];
                        }
                        if (trim($data['IDENTITY_CYCLE']) == 'NO') {
                            $field_part[] = ' NO CYCLE';
                        } else {
                            $field_part[] = ' CYCLE ' . $data['IDENTITY_CYCLE'];
                        }
                        $field .= implode(', ', $field_part) . ')';
                    }

                    $list_cols [] = $field;
                    //if ($field_name != $data ['COLUMN_HEADING'] && $data ['COLUMN_HEADING'] != '') {
                    //    $column_headings[$field_name] = $data ['COLUMN_HEADING'];
                    //}
                    if ($field_name != $data ['COLUMN_TEXT'] && $data ['COLUMN_TEXT'] != '') {
                        $column_texts[$field_name] = $data ['COLUMN_TEXT'];
                    }
                }
                foreach ($constraints as $key => $values) {
                    $contrainte = $key . ' ( ';
                    ksort($values);
                    $cols = array();
                    foreach ($values as $seq => $col) {
                        $cols[] = $col;
                    }
                    $contrainte .= implode(', ', $cols) . ' ) ';
                    $list_cols [] = $contrainte;
                }
            }
            $colons = implode(",\n ", $list_cols) . PHP_EOL;
        } else {
            $colons = '';
        }

        $create = '-- DROP TABLE ' . $schema . '.' . $table . ' ;' . PHP_EOL;
        $create .= 'CREATE TABLE ' . $schema . '.' . $table . ' FOR SYSTEM NAME ' . $nom_court . '(' . PHP_EOL;
        $create .= $colons;
        $create .= ') ' . PHP_EOL;
              
        if ($is_MQT) {
            $create .= ' AS (' . PHP_EOL . trim($datatab['WARNING_DEFINITION']) .
                    PHP_EOL . trim($datatab['MQT_DEFINITION']) .
                    PHP_EOL . ')' . PHP_EOL;
            $tmp_create = <<<BLOC_TMP_MQT
DATA INITIALLY {$datatab['REFRESH']}
REFRESH {$datatab['REFRESH']} 
MAINTAINED BY {$datatab['MAINTENANCE']}
ENABLE QUERY OPTIMIZATION 
BLOC_TMP_MQT;
            $create .= $tmp_create . PHP_EOL;
        }

        $create .= ' RCDFMT ' . $nom_court . PHP_EOL; 

        $create .= ' ;' . PHP_EOL;
       
        // remplacement des VARCHAR par des CHAR et un peu de nettoyage complémentaire
        if ($remplacement_varchar) {
            $create = str_replace('VARCHAR', 'CHAR', $create);
        }

        $create .= self::create_label_on_db2_object($db, $schema, $table, true);
        $create .= self::create_label_on_db2_object($db, $schema, $table);
        // $create .= self::create_label_on_columns($schema, $table, $column_headings);
        $create .= self::create_label_on_columns($schema, $table, $column_texts, 'texts');

        //if ($affichage_navigateur) {
        //$create = nl2br($create );
        //$create = str_replace ( "\t", "&nbsp;&nbsp;", $create );
        //}	
        return $create;
    }

    private static function create_label_on_db2_object($db, $schema, $table, $comment = false) {
        $label = '';
        $sql = DB2Tools::get_table_structure_entete($schema, $table);
        $data = DBWrapper::selectOne($db, $sql, array($schema, $table));
        if (is_array($data) && trim($data['TABLE_TEXT']) != '') {
            $data['TABLE_TEXT'] = trim($data['TABLE_TEXT']);
            // il est impératif de doubler les apostrophes dans les libellés, quand il y en a
            $data['TABLE_TEXT'] = str_replace("'", "''", $data['TABLE_TEXT']);
            if ($comment === true) {
                $label = 'COMMENT';
            } else {
                $label = 'LABEL';
            }
            $label .= ' ON TABLE ' . $schema . '.' . $table . PHP_EOL;
            $label .= " IS '" . $data['TABLE_TEXT'] . "';" . PHP_EOL;
        }
        return $label . PHP_EOL;
        ;
    }

    private static function create_label_on_columns($schema, $table, $columns, $type_label = 'headings') {
        $label = '';
        $nb_cols = 0;
        if (is_array($columns) && count($columns) > 0) {
            $label = 'LABEL ON COLUMN ' . $schema . '.' . $table . ' (' . PHP_EOL;
            $tab_label = array();
            if (strtolower($type_label) == 'headings') {
                $is_type = 'IS';
            } else {
                $is_type = 'TEXT IS';
            }
            foreach ($columns as $key => $value) {
                $value = trim($value);
                if ($value != '') {
                    // il est impératif de doubler les apostrophes dans les libellés, quand il y en a
                    $value = str_replace("'", "''", $value);
                    $tab_label [] = $key . ' ' . $is_type . " '" . $value . "'";
                    $nb_cols++;
                }
            }
            $label .= implode(', ' . PHP_EOL, $tab_label) . ' ) ;' . PHP_EOL;
        }
        if ($nb_cols > 0) {
            return $label . PHP_EOL;
        } else {
            return '';
        }
    }

    /**
     * 
     * ReCréation des index liés à une table DB2
     * avec possibilité de renvoyer la liste soit sous forme d'un tableau,
     * soit sous forme SQL, soit les deux
     * @param ressource $db
     * @param string $schema
     * @param string $table
     * @param string $type_retrieve : "both" = sql & array , "sql" = sql only, "array" = array only
     * @param boolean $affichage_navigateur (true par défaut)
     * @param boolean $reorg_index_names (false par défaut) : si true alors recodification des noms d'indexs (ménage)
     */
    public static function reCreateIndexs($db, $schema, $table, $type_retrieve = 'both', $affichage_navigateur = true, $reorg_index_names = false) {
        $sql_tab_info = DB2Tools::extractTableInfo();
        $data = DBWrapper::selectOne($db, $sql_tab_info, array($schema, $table));
        $system_schema = trim($data ['SYSTEM_TABLE_SCHEMA']);
        $system_table = trim($data ['SYSTEM_TABLE_NAME']);

        $liste_indexs = array();
        $type_retrieve = trim(strtolower($type_retrieve));
        if ($type_retrieve == '') {
            $type_retrieve = 'both';
        }
        $sql = DB2Tools::extractSysindexs(true);
        $dataindexs = DBWrapper::selectBlock($db, $sql, array($system_schema, $system_table));
        if (count($dataindexs) > 0) {
            foreach ($dataindexs as $dataindex) {
                $sql2 = DB2Tools::extractSysindexkeys(true, $dataindex ['INDEX_SQL']);
                $datacols = DBWrapper::selectBlock($db, $sql2, array(trim($dataindex ['SYSTEM_INDEX_SCHEMA']), trim($dataindex ['SYSTEM_INDEX_NAME'], false)));
                $colons = array();
                foreach ($datacols as $datacol) {
                    $colonne = trim($datacol ['COLUMN_NAME']);
                    if ($datacol ['ORDERING'] == 'D') {
                        $colonne .= ' DESC ';
                    }
                    $colons [] = $colonne;
                }
                switch ($dataindex ['INDEX_TYPE']) {
                    case 'D': {
                            $type_dep_aff = 'Non unique';
                            $type_dep_sql = '';
                            break;
                        }
                    case 'E': {
                            $type_dep_aff = 'Encoded Vector Index';
                            $type_dep_sql = 'ENCODED VECTOR';
                            break;
                        }
                    case 'U': {
                            $type_dep_aff = 'Unique';
                            $type_dep_sql = 'UNIQUE';
                            break;
                        }
                    case 'V': {
                            $type_dep_aff = 'Vue SQL';
                            $type_dep_sql = 'VIEW';
                            break;
                        }

                    default : {
                            // cas qui ne devraient pas se produire, mais sait-on jamais
                            $type_dep_aff = 'indéfini ?'; // type non reconnu ???
                            $type_dep_sql = 'undefined'; // garantit que le code SQL ne sera pas compilable
                            break;
                        }
                }

                $liste_indexs [] = array(
                    'INDEX_SCHEMA' => trim($dataindex ['INDEX_SCHEMA']),
                    'INDEX_NAME' => trim($dataindex ['INDEX_NAME']),
                    'SYSTEM_INDEX_SCHEMA' => trim($dataindex ['SYSTEM_INDEX_SCHEMA']),
                    'SYSTEM_INDEX_NAME' => trim($dataindex ['SYSTEM_INDEX_NAME']),
                    'INDEX_SQL' => trim($dataindex ['INDEX_SQL']),
                    'INDEX_TYPE' => trim($dataindex ['INDEX_TYPE']),
                    'EVI_DISTINCT_VALUES' => trim($dataindex ['EVI_DISTINCT_VALUES']),
                    'DEP_TYPE_AFF' => $type_dep_aff,
                    'DEP_TYPE_SQL' => $type_dep_sql,
                    'COLONS' => implode(', ', $colons)
                );
            }
        }
        $return_info = array();
        if ($type_retrieve == 'both' || $type_retrieve == 'sql') {
            $table_de_base = $schema . '.' . $table;
            $code_sql = '';
            $compteur_indexs = 0;
            if (count($liste_indexs) > 99) {
                $format_idx_name = '%03s';
            } else {
                $format_idx_name = '%02s';
            }
            foreach ($liste_indexs as $index) {
                $compteur_indexs++;
                if ($reorg_index_names === true) {
                    $index_name = $table_de_base . '_IDX' . sprintf($format_idx_name, $compteur_indexs);
                } else {
                    $index_name = $index['INDEX_SCHEMA'] . '.' . $index['INDEX_NAME'];
                }
                $dep_type_sql = trim($index['DEP_TYPE_SQL']);
                if ($dep_type_sql != '') {
                    $code_sql .= 'CREATE ' . $dep_type_sql . ' INDEX ';
                } else {
                    $code_sql .= 'CREATE INDEX ';
                }
                $code_sql .= $index_name . ' ON ' .
                        $table_de_base . ' (' . $index['COLONS'] . ')';
                if ($index['INDEX_TYPE'] == 'E') {
                    $code_sql .= ' WITH ' . $index['EVI_DISTINCT_VALUES'] . ' DISTINCT VALUES ';
                }
                $code_sql .= ';' . PHP_EOL;
            }
            $return_info [] = $code_sql;
        }
        if ($type_retrieve == 'both' || $type_retrieve == 'array') {
            $return_info [] = $liste_indexs;
        }
        return $return_info;
    }

    /**
     * 
     * Fonction de génération d'une vue "par dessus" une table ou vue existante
     * avec transformation des GRAPHIC et VARGRAPHIC en CHAR et VARCHAR et
     * réencodage selon le CCSID souhaité (transmis en paramètre)
     * @param string $schema
     * @param string $table
     * @param boolean $affichage_navigateur
     * @param boolean $remplacement_varchar
     * @param boolean $remplacement_concat
     */
    public static function createViewOnDB2Object($db, $schema, $table, $ccsid) {

        $sql = DB2Tools::extractTableStruct(false);
        $datastructure = DBWrapper::selectBlock($db, $sql, array($schema, $table));
        $list_cols_ori = array();
        $list_cols_des = array();
        if (is_array($datastructure)) {
            foreach ($datastructure as $data) {
                $col_name = trim($data ['FIELD']) ;
                $list_cols_ori [] = $col_name;
                //var_dump($data) ;
                if ($data['COLUMN_CCSID'] != null && $data['COLUMN_CCSID'] != $ccsid) {
                    $data_type = trim($data ['DATA_TYPE']) ;
                    switch ($data_type) {
                        case 'GRAPHIC' : {
                            $data_type = 'CHAR';
                            break;
                        }
                        case 'VARGRAPHIC' : {
                            $data_type = 'VARCHAR';
                            break;
                        }
                    }
                    $length = trim($data['LENGTH']) ;
                    $list_cols_des [] = "CAST($col_name as $data_type($length) CCSID $ccsid ) as $col_name" ;
                } else {
                    $list_cols_des [] = $col_name;
                }
                
            }
            $colons_ori = PHP_EOL . implode(', '.PHP_EOL, $list_cols_ori) . PHP_EOL;
            $colons_des = PHP_EOL . implode(', '.PHP_EOL, $list_cols_des) . PHP_EOL;
        } else {
            $colons_ori = '';
            $colons_des = '';
        }
        if ($colons_ori != '') {
            $view = 'CREATE OR REPLACE VIEW ' . $schema . '.VUE_' . $table . ' AS ( ';
            $view .= 'SELECT ' . $colons_des . ' FROM ' . $schema . '.' . $table . ' ) ;'. PHP_EOL ; 
        } else {
            $view = 'impossible de constituer la vue';
        }
        return $view;
    }    
    
    /**
     * 
     * Fonction de génération d'un script de conversion d'une table DB2 au format MySQL
     * @param string $schema
     * @param string $table
     * @param boolean $affichage_navigateur
     * @param boolean $remplacement_varchar
     * @param boolean $remplacement_concat
     */
    public static function convertTable_from_db2_to_mysql($db, $schema, $table, $affichage_navigateur = true, $remplacement_varchar = false) {

        $table = trim($table);
        $schema = trim($schema);
        $sql = DB2Tools::extractTableStruct(false);
        $datastructure = DBWrapper::selectBlock($db, $sql, array($schema, $table));
        $list_cols = array();
        if (is_array($datastructure)) {
            $constraints = array();
            foreach ($datastructure as $data) {
                $field_name_ori = strtolower(trim($data ['FIELD']));
                $pos = self::check_blanks_and_accents($field_name_ori);
                if ($pos === false) {
                    $field = $field_name_ori;
                } else {
                    $field = '"'. $field_name_ori . '"';
                }                   

                $data ['DATA_TYPE'] = strtolower(trim($data ['DATA_TYPE']));

                if ($data ['DATA_TYPE'] == 'graphic') {
                    $data ['DATA_TYPE'] = 'varchar';
                }

                if ($data ['DATA_TYPE'] == 'date' ||
                        $data ['DATA_TYPE'] == 'time' ||
                        $data ['DATA_TYPE'] == 'datetime' ||
                        $data ['DATA_TYPE'] == 'timestamp') {
                    // conversion de format(s) non reconnu(s) par DB2
                    if ($data ['DATA_TYPE'] == 'datetime') {
                        $data ['DATA_TYPE'] = 'timestamp';
                    }
                    $field .= ' ' . $data ['DATA_TYPE'];
                } else {
                    if ($data ['DATA_TYPE'] == 'integer') {
                        $field .= ' ' . $data ['DATA_TYPE'];
                    } else {
                        $field .= ' ' . $data ['DATA_TYPE'];
                        if (!is_null($data ['NUMERIC_PRECISION'])) {
                            $longueur = $data ['NUMERIC_PRECISION'];
                        } else {
                            $longueur = $data ['LENGTH'];
                        }
                        $field .= '(' . $longueur;
                        if ($data ['DATA_TYPE'] != 'INTEGER' && !is_null($data ['SCALE'])) {
                            $field .= ', ' . trim($data ['SCALE']);
                        }
                        $field .= ')';
                    }
                }

                if (!is_null($data ['CONSTRAINT_TYPE'])) {
                    $constraints[strtolower($data['CONSTRAINT_TYPE'])][$data['CONSTRAINT_COLSEQ']] = $data['FIELD'];
                }

                if (trim($data ['COLUMN_NULLABLE']) == 'Y') {
                    $field .= ' default null';
                } else {
                    $field .= ' not null';
                    if (trim($data ['HAS_DEFAULT']) == 'Y') {
                        $field .= ' default ' . trim($data ['COLUMN_DEFAULT']);
                    }
                }

                if (trim($data['IS_IDENTITY']) == 'YES') {
                    $field .= ' auto_increment';
                }

                $data['COLUMN_TEXT'] = trim($data['COLUMN_TEXT']);
                if ($data['COLUMN_TEXT'] != '' && $data['COLUMN_TEXT'] != $field_name_ori) {
                    $field .= " comment '" . $data['COLUMN_TEXT'] . "'";
                } else {
                    $data['COLUMN_HEADING'] = trim($data['COLUMN_HEADING']);
                    if ($data['COLUMN_HEADING'] != '' && $data['COLUMN_HEADING'] != $field_name_ori) {
                        $field .= " comment '" . $data['COLUMN_HEADING'] . "'";
                    }
                }

                $list_cols [] = $field;
            }
            foreach ($constraints as $key => $values) {
                $contrainte = $key . ' ( ';
                ksort($values);
                $cols = array();
                foreach ($values as $seq => $col) {
                    $cols[] = $col;
                }
                $contrainte .= implode(', ', $cols) . ' ) ';
                $list_cols [] = $contrainte;
            }

            $colons = implode(",\n ", $list_cols) . PHP_EOL;
        } else {
            $colons = '';
        }

        $table = strtolower($table);
        $create = 'CREATE OR REPLACE TABLE ' . $table . ' (' . PHP_EOL;
        $create .= $colons;
        $create .= ') ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1 ;' . PHP_EOL;

        // remplacement des VARCHAR par des CHAR et un peu de nettoyage complémentaire
        if ($remplacement_varchar) {
            $create = str_replace('VARCHAR', 'CHAR', $create);
        }

        //if ($affichage_navigateur) {
        //	$create = nl2br($create );
        //	$create = str_replace ( "\t", "&nbsp;&nbsp;", $create );
        //}	
        return $create;
    }

    private static function check_blanks_and_accents($field) {
        $pos = strpbrk($field, " éééééééééé");
        if ($pos === false) {
            return false ;
        } else {
            return true ;
        }
    }
    
    /**
     * 
     * Fonction de génération d'un script de conversion d'une table DB2 for i au format DB2 for Win/Lnx
     * @param string $schema
     * @param string $table
     * @param boolean $affichage_navigateur
     * @param boolean $remplacement_varchar
     * @param boolean $remplacement_concat
     */
    public static function convertTable_from_db2i_to_db2win($db, $schema, $table, $affichage_navigateur = true, $remplacement_varchar = false) {

        $table = trim($table);
        $schema = trim($schema);

        $sql = DB2Tools::extractTableStruct(false);
        $datastructure = DBWrapper::selectBlock($db, $sql, array($schema, $table));
        $list_cols = array();
        if (is_array($datastructure)) {
            $constraints = array();
            foreach ($datastructure as $data) {
                $field_name = trim($data ['FIELD']);
                $data ['DATA_TYPE'] = trim($data ['DATA_TYPE']);
                $data ['COLUMN_HEADING'] = trim($data ['COLUMN_HEADING']);
                $data ['COLUMN_TEXT'] = trim($data ['COLUMN_TEXT']);

                $pos = self::check_blanks_and_accents($field_name);
                if ($pos === false) {
                    $field = $field_name;
                } else {
                    $field = '"'. $field_name . '"';
                }                

                $field .= ' ' . $data ['DATA_TYPE'];
                if ($data ['DATA_TYPE'] == 'DECIMAL' || $data ['DATA_TYPE'] == 'CHAR' || $data ['DATA_TYPE'] == 'VARCHAR' || $data ['DATA_TYPE'] == 'GRAPHIC' || $data ['DATA_TYPE'] == 'VARGRAPHIC') {
                    $field .= '(' . $data ['LENGTH'];
                    if (!is_null($data ['SCALE'])) {
                        $field .= ', ' . trim($data ['SCALE']);
                    }
                    $field .= ')';
                }

                if (!is_null($data ['CONSTRAINT_TYPE'])) {
                    $constraints[$data['CONSTRAINT_TYPE']][$data['CONSTRAINT_COLSEQ']] = $data['FIELD'];
                }

                if (trim($data ['COLUMN_NULLABLE']) == 'Y') {
                    $field .= ' DEFAULT NULL';
                } else {
                    $field .= ' NOT NULL';
                    if (trim($data ['HAS_DEFAULT']) == 'Y') {
                        $field .= ' DEFAULT ' . trim($data ['COLUMN_DEFAULT']);
                    }
                }

                if (trim($data['IS_IDENTITY']) == 'YES') {
                    $field .= PHP_EOL . ' GENERATED ' . $data['IDENTITY_GENERATION'] . ' AS IDENTITY (';
                    $field_part = array();
                    $field_part[] = ' START WITH ' . $data['IDENTITY_START'];
                    $field_part[] = ' INCREMENT BY ' . $data['IDENTITY_INCREMENT'];
                    $field_part[] = ' MINVALUE ' . $data['IDENTITY_MINIMUM'];
                    if (is_null($data['IDENTITY_MAXIMUM'])) {
                        $field_part[] = ' NO MAXVALUE ';
                    } else {
                        $field_part[] = ' MAXVALUE ' . $data['IDENTITY_MAXIMUM'];
                    }
                    if ($data['IDENTITY_CACHE'] == 0) {
                        $field_part[] = ' NO CACHE';
                    } else {
                        $field_part[] = ' CACHE ' . $data['IDENTITY_CACHE'];
                    }
                    if (trim($data['IDENTITY_CYCLE']) == 'NO') {
                        $field_part[] = ' NO CYCLE';
                    } else {
                        $field_part[] = ' CYCLE ' . $data['IDENTITY_CYCLE'];
                    }
                    $field .= implode(', ', $field_part) . ')';
                }

                $list_cols [] = $field;
            }
            foreach ($constraints as $key => $values) {
                $contrainte = $key . ' ( ';
                ksort($values);
                $cols = array();
                foreach ($values as $seq => $col) {
                    $cols[] = $col;
                }
                $contrainte .= implode(', ', $cols) . ' ) ';
                $list_cols [] = $contrainte;
            }

            $colons = implode(",\n ", $list_cols) . PHP_EOL;
        } else {
            $colons = '';
        }

        $create = 'CREATE OR REPLACE TABLE ' . $schema . '.' . $table . ' (' . PHP_EOL;
        $create .= $colons;
        $create .= ') ;' . PHP_EOL;

        // remplacement des VARCHAR par des CHAR si demandé
        if ($remplacement_varchar) {
            $create = str_replace('VARCHAR', 'CHAR', $create);
        }

        $create .= self::create_label_on_db2_object($db, $schema, $table, true);

        return $create;
    }

    /**
     *
     * Fonction de génération d'un script de conversion d'une table DB2 au format Active Record
     * @param string $schema
     * @param string $table
     */
    public static function convertTable_from_db2_to_activerecord($db, $schema, $table) {

        $form_elements = array();
        $table = trim($table);
        $schema = trim($schema);
        $sql = DB2Tools::extractTableStruct(false);
        $datastructure = DBWrapper::selectBlock($db, $sql, array($schema, $table));
        $list_cols = array();
        if (is_array($datastructure)) {
            $constraints = array();

            foreach ($datastructure as $data) {
                $field_name = strtolower(trim($data ['FIELD']));
                $data_type = strtolower(trim($data ['DATA_TYPE']));

                $form_element = array();

                if (trim($data['IS_IDENTITY']) == 'YES') {
                    $form_element ['key'] = true;
                }

                $label = $field_name;
                $data['COLUMN_TEXT'] = trim($data['COLUMN_TEXT']);
                if ($data['COLUMN_TEXT'] != '' && strtolower($data['COLUMN_TEXT']) != $field_name) {
                    $label = $data['COLUMN_TEXT'];
                } else {
                    $data['COLUMN_HEADING'] = trim($data['COLUMN_HEADING']);
                    if ($data['COLUMN_HEADING'] != '' && strtolower($data['COLUMN_HEADING']) != $field_name) {
                        $label = $data['COLUMN_HEADING'];
                    }
                }
                $form_element ['label'] = $label;

                $form_element ['type'] = 'text';
                $form_element ['db_type'] = $data ['DATA_TYPE'];

                $form_element ['attributes'] = array();
                $form_element ['filters'] = array();
                $form_element ['rules'] = array();

                switch ($data_type) {
                    case 'char' :
                    case 'varchar': {
                            $longueur = $data ['LENGTH'];
                            if ($longueur > 80) {
                                $form_element ['type'] = 'textarea';
                                $form_element ['attributes']['cols'] = 80;
                                $form_element ['attributes']['rows'] = ceil($longueur / 80);
                            } else {
                                $form_element ['attributes']['size'] = $longueur;
                            }
                            $form_element ['attributes']['maxlength'] = $data ['LENGTH'];
                            $form_element ['rules']['maxlength'] = $data ['LENGTH'];
                            break;
                        }
                    case 'date': {
                            $longueur = 10;
                            $form_element ['attributes']['size'] = $longueur;
                            $form_element ['attributes']['maxlength'] = $longueur;
                            $form_element ['rules']['maxlength'] = $longueur;
                            $form_element ['rules']['crud_date'] = true;
                            break;
                        }
                    case 'time': {
                            $longueur = 8;
                            $form_element ['attributes']['size'] = $longueur;
                            $form_element ['attributes']['maxlength'] = $longueur;
                            $form_element ['rules']['maxlength'] = $longueur;
                            $form_element ['rules']['crud_time'] = true;
                            break;
                        }
                    case 'timestamp': {
                            $longueur = 25;
                            $form_element ['attributes']['size'] = $longueur;
                            $form_element ['attributes']['maxlength'] = $longueur;
                            $form_element ['rules']['maxlength'] = $longueur;
                            $form_element ['rules']['crud_timestamp'] = true;
                            break;
                        }
                    case 'integer': {
                            $longueur = 12;
                            $form_element ['attributes']['size'] = $longueur;
                            $form_element ['attributes']['maxlength'] = $longueur;
                            $form_element ['rules']['maxlength'] = $longueur;
                            $form_element ['rules']['crud_integer'] = true;
                            break;
                        }
                    case 'numeric':
                    case 'decimal': {
                            $longueur = !is_null($data ['NUMERIC_PRECISION']) ? intval($data ['NUMERIC_PRECISION']) + 1 : 0;
                            if ($longueur > 0) {
                                $form_element ['attributes']['size'] = $longueur;
                                $form_element ['attributes']['maxlength'] = $longueur;
                                $form_element ['rules']['maxlength'] = $longueur;
                                $form_element ['rules']['numeric'] = true;
                            } else {
                                $form_element ['attributes']['size'] = 10;
                            }
                            break;
                        }
                    default: {
                            $form_element ['attributes']['size'] = 10;
                        }
                }

                if (!is_null($data ['CONSTRAINT_TYPE'])) {
                    $form_element ['constraint'] = array('type' => strtolower($data['CONSTRAINT_TYPE']), 'cols' => $data['CONSTRAINT_COLSEQ']);
                }

                if (trim($data ['COLUMN_NULLABLE']) != 'Y') {
                    $form_element ['rules']['required'] = true;
                }
                $form_elements[$field_name] = $form_element;
            }
        }

        return $form_elements;
    }

    /**
     * Contréle existence d'un objet DB2 en fonction de son type (TABLE, VIEW, INDEX, ROUTINE)
     * @param type $db
     * @param type $schema
     * @param type $table
     * @param type $type
     * @return boolean
     */
    public static function checkObjectExists($db, $schema, $table, $type) {
        $table = trim($table);
        $schema = trim($schema);
        $type = trim(strtoupper($type)) ;
        $sql = DB2Tools::checkObjectExists($type);
        $data = DBWrapper::selectOne($db, $sql, array($schema, $table));
        if (isset($data['FOUND']) && $data['FOUND'] > 0) {
            return true ;
        } else {
            return false ;
        }
    }

    /**
     * Appel de la procédure de regénération d'un source SQL
     * @param type $db
     * @param type $schema
     * @param type $table
     * @param type $type
     * @return array 
     */
    public static function generateSQLObject ($db, $schema, $table, $type) {
        $table = trim($table);
        $schema = trim($schema);
        $type = trim(strtoupper($type)) ;        
        //$parms = array($table, $schema, $type) ;
        $parms = array(
        'OBJECT_NAME' => array('value'=>$table, 'type'=>'in'), 
        'OBJECT_LIBRARY'=>array('value'=>$schema, 'type'=>'in'),
        'OBJECT_TYPE'=>array('value'=>$type, 'type'=>'in')
        ) ;

        list($routine_schema, $routine_name) = DB2Tools::procGenerateSQL() ;
        try {
            return $db->callProcedure ($routine_name, $routine_schema, $parms, true );
        } catch (Exception $e) {
			error_log($e->getMessage());
            return [];
		}
    }
        
}
