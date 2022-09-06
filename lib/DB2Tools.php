<?php

interface intDB2Tools {

    public static function convertirCaracteresAccentues($chaine);

    public static function extractDb2ObjectsFromLib($table_name_only = false, $table_name_include_short_name = false, $ref_croisee = false, $creator=false, $sql_include='');

    public static function getSchemas($search = '', $like = false);

    public static function getTypeObjetsDb2($tous = true);

    public static function extractIbmiObjectsFromLib($library, $tmp_table, $tmp_schema = '');

    public static function extractDspfdMbrlist($table, $schema, $tmp_table = '', $tmp_schema = '');

    public static function extractDspDbr($table, $schema, $tmp_table = '', $tmp_schema = '');
    
    public static function extractIndexKeys($index, $schema, $tmp_table = '', $tmp_schema = ''); 

    public static function findTableFromSsystemName();

    public static function checkIndexFromSystemName();

    public static function parseIbmiObjectsFromLib($tmp_table, $credat_comp = true, $typ_object = '', $typ_attrib = '');

    public static function compareStrings($chaine1, $chaine2, $troncat = false);

    public static function retrieveUserProfile($user = '', $tmp_table = '', $tmp_schema = '');

    public static function getIbmiProfilesWithFilters($filters = array(), $tmp_table = '', $tmp_schema = '');

    public static function getIbmiJobdprfusers($tmp_table = '', $tmp_schema = '');

    public static function extractTableStruct($data_from_system_table = false, $columns_only = false);

    public static function extractTableInfo($data_from_system_table = false) ;

    public static function extractDependanceInverse();

    public static function extractSysindexs($data_from_system_table = false);

    public static function extractSysindexkeys($data_from_system_table = false, $index_sql = 'YES');

    public static function extractAllsysviews($view_name = '', $data_from_system_table = false, $ref_croisee = false, $param_optionnels=array());

    public static function extractSysviewdep();

    public static function extractSystabledep();
    
    public static function extractSysviewdepInverse();

    public static function extractAllsysroutines($routine_name = '', $data_from_system_table = false, $ref_croisee = false);

    public static function extractSysroutine();

    public static function extractSysroutinedep();

    public static function extractSysroutinedepInverse();

    public static function getTablesByColumn($nom_colonne, $nom_schema, $nom_table);

    public static function extractTableStat($nom_table, $nom_schema);

    /**
     *
     * L'objectif de cette méthode est de générer une requête SQL qui permettra d'obtenir
     * les valeurs mini, maxi et la longueur maxi de chaque colonne d'une table
     * Le résultat de cette requête pourra être transmis au 2ème paramètre de la méthode analyseTableStruct()
     * @param string $schema
     * @param string $table
     * @param array $colonnes  => tableau contenant la liste des colonnes de la table
     */
    public static function extractColumnsMinmax($schema, $table, $colonnes);

    /**
     *
     * L'objectif de cette méthode est de produire un tableau à peu près équivalent à celui
     * que l'on peut obtenir sous MySQL avec la requête suivante :
     *       SELECT * FROM `table` PROCEDURE ANALYSE ( ) ;
     * Cette méthode a besoin de la structure de la table
     * @param array $datastructure   => données fournies par la requête générée par la méthode extractTableStruct()
     * @param array $tabminmax       => données fournies par la requête générée par la méthode extractColumnsMinmax()
     */
    public static function analyseTableStruct($datastructure, $tabminmax);

    public static function getListeTables($recherche_base, $recherche_table, $recherche_varchar, $type_objet, $ref_croisee);

    public static function getRefCroiseesSysroutines($table_name_from_a='');

    public static function genAltertables($zone_util = false, $prefix_tables = '', $prefix_procs = '');

    public static function getSqlStates();

    public static function getSqlVerbs();

    public static function changeJobSortSequence();
    
    /**
     * Récupération de la version d'OS IBMi (exemple : V7R1, V7R2, ...)
     */
    public static function getOsVersion () ;

    /**
     * Génère une requête permettant de vérifier l'existence d'un objet DB2
     * en fonction du type transmis en paramètre (TABLE, VIEW, INDEX, ROUTINE)
     * @param string $object_type
     */
    public static function checkObjectExists($object_type) ;
    
    /**
     * Fonction utilisable uniquement à partir de la V7R1
     * @param array (renvoie un tableau contenant la bibliothèque et le
     *               nom de la procédure système de regénération de
     *               source SQL)
     */
    public static function procGenerateSQL () ;

}

abstract class DB2Tools implements intDB2Tools {

    const BIB_SYS = 'QSYS2';

    /*
     * renvoi de la bib de référence BIB_REF_DTA (pour l'instant stockée en constante),
     * mais si on décide de modifier la souce de cette information, il suffira de modifier
     * cette méthode
     */

    private static function get_bib_ref_dta() {
        return BIB_REF_DTA;
    }

    /*
     * renvoi de la bib de référence BIB_REF_PGM (pour l'instant stockée en constante),
     * mais si on décide de modifier la souce de cette information, il suffira de modifier
     * cette méthode
     */

    private static function get_bib_ref_pgm() {
        return BIB_REF_PGM;
    }

    public static function convertirCaracteresAccentues($chaine) {
        $dirty = array('{', '}', '@');
        $clean = array('é', 'è', 'à');
        return str_replace($dirty, $clean, $chaine);
    }

    private static function defineTypeTableName($data_from_system_table = false, $prefix = '') {
        $prefix = trim($prefix);
        if ($prefix != '') {
            $prefix .= '.';
        }
        if ($data_from_system_table === true || $data_from_system_table === false) {
            if ($data_from_system_table) {
                $from_table = " {$prefix}SYSTEM_TABLE_SCHEMA = ? AND {$prefix}SYSTEM_TABLE_NAME = ?";
            } else {
                $from_table = " {$prefix}TABLE_SCHEMA = ? AND {$prefix}TABLE_NAME = ?";
            }
        } else {
            switch ($data_from_system_table) {
                case 'V':
                case 'v': {
                        $from_table = " {$prefix}VIEW_SCHEMA = ? AND {$prefix}VIEW_NAME = ?";
                        break;
                    }
                case 'R':
                case 'r': {
                        $from_table = " {$prefix}ROUTINE_SCHEMA = ? AND {$prefix}ROUTINE_NAME = ?";
                        break;
                    }
                default: {
                        $from_table = " {$prefix}TABLE_SCHEMA = ? AND {$prefix}TABLE_NAME = ?";
                    }
            }
        }
        return $from_table;
    }

    public static function extractDb2ObjectsFromLib($table_name_only = false, 
            $table_name_include_short_name = false, $ref_croisee = false, 
            $creator=false, $sql_include='') {
        $sql = '';
        if ($ref_croisee === true) {
            $ref_croisee = self::getRefCroiseesSysroutines('A.DBXLFI');
            $ref_croisee_sql = $ref_croisee['sql'];
            $ref_croisee_colons = $ref_croisee['colons'];
        } else {
            $ref_croisee_sql = '';
            $ref_croisee_colons = '';
        }

        $sql_include = trim($sql_include) ;
        if ($sql_include != '') {
            $sql_include = ' AND A.DBXLFI IN ('.$sql_include.') ' ;
        }
        if ($table_name_only === true) {
            // renvoi des seuls noms d'objets, incluant éventuellement les noms courts
            if ($table_name_include_short_name === true) {
                $sql = <<<BLOC_SQL
SELECT distinct A.DBXLFI, A.DBXFIL
FROM QSYS{SEPARATOR}QADBXREF A {$ref_croisee_sql}
WHERE A.DBXLIB = ? {$sql_include}
ORDER BY A.DBXLFI         
BLOC_SQL;
            } else {
                $sql = <<<BLOC_SQL
SELECT distinct A.DBXLFI 
FROM QSYS{SEPARATOR}QADBXREF A {$ref_croisee_sql}
WHERE A.DBXLIB = ? {$sql_include}
ORDER BY A.DBXLFI         
BLOC_SQL;
            }
        } else {
            // structure plus complète pour analyse détaillée
            if ($creator==true) {
                $definer = ', A.DBXDEFINER' ;
            } else {
                $definer = '' ;
            }
            $sql = <<<BLOC_SQL
SELECT distinct A.DBXLFI, A.DBXFIL, A.DBXATR, A.DBXTYP, A.DBXNFL, A.DBXNKF, A.DBXRDL{$definer} 
FROM QSYS{SEPARATOR}QADBXREF A {$ref_croisee_sql}
WHERE A.DBXLIB = ? AND A.DBXATR <> 'IX' {$sql_include}
ORDER BY A.DBXLFI         
BLOC_SQL;
        }
        return $sql;
    }

    public static function getSchemas($search = '', $like = false) {
        // $sql = 'SELECT SCHEMA_NAME FROM QSYS2{SEPARATOR}SYSSCHEMAS ';
        $sql = 'SELECT DISTINCT DBXLIB FROM QSYS{SEPARATOR}QADBXREF ';
        $search = trim($search);
        if ($search != '') {
            $sql .= ' WHERE DBXLIB ';
            if ($like === true) {
                $sql .= " LIKE '" . $search . "%'";
            } else {
                $sql .= " = '" . $search . '"';
            }
        }
        return $sql;
    }

    public static function getTypeObjetsDb2($tous = true) {
        $liste = array();
        if ($tous === true) {
            $liste['*'] = 'Tous';
        }
        $liste['T'] = 'Tables SQL';
        $liste['P'] = 'Fichiers physiques';
        $liste['V'] = 'Vues';
        $liste['M'] = 'Materialized Query Tables (MQT)';
        return $liste;
    }

    public static function extractIbmiObjectsFromLib($library, $tmp_table, $tmp_schema = '') {

        $tmp_table = trim($tmp_table);
        $tmp_schema = trim($tmp_schema);
        if ($tmp_schema == '') {
            $tmp_schema = 'QTEMP';
        }
        $library = trim($library);
        return 'DSPOBJD OBJ(' . $library . '/*ALL) OBJTYPE(*ALL) DETAIL(*BASIC) OUTPUT(*OUTFILE) OUTFILE(' . $tmp_schema . '/' . $tmp_table . ') OUTMBR(*FIRST *REPLACE)';
    }

    public static function extractDspfdMbrlist($table, $schema, $tmp_table = '', $tmp_schema = '') {
        $tmp_table = trim($tmp_table);
        if ($tmp_table == '') {
            $tmp_table = 'TMPDSPFDML';
        }
        $tmp_schema = trim($tmp_schema);
        if ($tmp_schema == '') {
            $tmp_schema = 'QTEMP';
        }
        $table = trim($table);
        $schema = trim($schema);
        $cmd = 'DSPFD FILE(' . $schema . '/' . $table . ') TYPE(*MBRLIST) OUTPUT(*OUTFILE) OUTFILE(' . 
            $tmp_schema . '/' . $tmp_table . ')';
        $sql = 'SELECT MLFATR, MLSYSN, MLASP, MLNOMB, MLNAME, MLNRCD, MLNDTR, MLCHGC, MLCHGD, MLCHGT, MLUCEN, MLUDAT, MLUCNT FROM ';
        $sql .= $tmp_schema . '{SEPARATOR}' . $tmp_table . '';
        return array($cmd, $sql);
    }

    public static function extractIndexKeys($index, $schema, $tmp_table = '', $tmp_schema = '') {
        $tmp_table = trim($tmp_table);
        if ($tmp_table == '') {
            $tmp_table = 'TMPINDEX';
        }
        $tmp_schema = trim($tmp_schema);
        if ($tmp_schema == '') {
            $tmp_schema = 'QTEMP';
        }
        $index = trim($index);
        $schema = trim($schema);
        $cmd = 'DSPFD FILE(' . $schema . '/' . $index . ') TYPE(*ACCPTH) OUTPUT(*OUTFILE) OUTFILE(' .
            $tmp_schema . '/' . $tmp_table . ')';
        $cmd .= ' FILEATR(*ALL) OUTMBR(*FIRST *REPLACE)';
        $sql = 'SELECT APKEYF AS KEY, APKSEQ AS SENS, APUNIQ AS UNIQUE_KEY  FROM ';
        $sql .= $tmp_schema . '{SEPARATOR}' . $tmp_table . '';
        return array($cmd, $sql);
    }

    public static function extractDspDbr($table, $schema, $tmp_table = '', $tmp_schema = '') {
        $tmp_table = trim($tmp_table);
        if ($tmp_table == '') {
            $tmp_table = 'TMPDSPDBRX';
        }
        $tmp_schema = trim($tmp_schema);
        if ($tmp_schema == '') {
            $tmp_schema = 'QTEMP';
        }
        $table = trim($table);
        $schema = trim($schema);
        $cmd = 'DSPDBR FILE(' . $schema . '/' . $table . ') OUTPUT(*OUTFILE) OUTFILE(' . $tmp_schema . '/' . $tmp_table . ') OUTMBR(*FIRST *REPLACE)';
        $sql = 'SELECT WHREFI as file, WHRELI as library, WHDTM FROM ';
        $sql .= $tmp_schema . '{SEPARATOR}' . $tmp_table . '';
        return array($cmd, $sql);
    }

    public static function extractTableStat($nom_table, $nom_schema) {
        $bib_sys = self::BIB_SYS;
        $nom_schema = trim($nom_schema);
        $nom_table = trim($nom_table);
        $wheres = array();

        if ($nom_schema != '') {
            $wheres [] = 'TABLE_SCHEMA LIKE ?';
        }

        if ($nom_table != '') {
            $wheres [] = 'TABLE_NAME LIKE ?';
        }

        if (count($wheres) > 0) {
            $criteres_sql = ' WHERE ' . implode(' AND ', $wheres);
        } else {
            $criteres_sql = '';
        }
        $sql = <<<BLOC_SQL
SELECT TABLE_SCHEMA, TABLE_NAME, SYSTEM_TABLE_SCHEMA, SYSTEM_TABLE_NAME
    PARTITION_TYPE, NUMBER_PARTITIONS, 
    NUMBER_DISTRIBUTED_PARTITIONS, NUMBER_ROWS, NUMBER_ROW_PAGES, NUMBER_PAGES,
    NUMBER_DELETED_ROWS, DATA_SIZE,            
    OVERFLOW, COLUMN_STATS_SIZE, DAYS_USED_COUNT,
    MAINTAINED_TEMPORARY_INDEX_SIZE, NUMBER_DISTINCT_INDEXES, 
    OPEN_OPERATIONS, CLOSE_OPERATIONS, INSERT_OPERATIONS, UPDATE_OPERATIONS, 
    DELETE_OPERATIONS, CLEAR_OPERATIONS, COPY_OPERATIONS, REORGANIZE_OPERATIONS, 
    INDEX_BUILDS, LOGICAL_READS, PHYSICAL_READS, SEQUENTIAL_READS, 
    RANDOM_READS, 
        NUMBER_PARTITIONING_KEYS 
FROM {$bib_sys}{SEPARATOR}SYSTABLESTAT
{$criteres_sql} 
BLOC_SQL;

        /*
        * mots clés retirés du SELECT de la requête SQL ci-dessus car jugés peu utiles :
            CLUSTERED, ACTIVE_BLOCKS, VARIABLE_LENGTH_SIZE, FIXED_LENGTH_EXTENTS, VARIABLE_LENGTH_EXTENTS
        */
        return $sql;
    }
    
    public static function retrieveUserProfile($user = '', $tmp_table = '', $tmp_schema = '') {

        $user = strtoupper(trim($user));
        $tmp_table = strtoupper(trim($tmp_table));
        $tmp_schema = strtoupper(trim($tmp_schema));
        if ($tmp_table == '') {
            $tmp_table = 'TMPUSRPRF';
        }
        if ($tmp_schema == '') {
            $tmp_schema = 'QTEMP';
        }

        if ($user == '') {
            $sys_cmd = 'DSPUSRPRF USRPRF(*ALL) OUTPUT(*OUTFILE) OUTFILE(' . $tmp_schema . '/' . $tmp_table . ') OUTMBR(*FIRST *REPLACE)';
        } else {
            $sys_cmd = 'DSPUSRPRF USRPRF(' . $user . ') OUTPUT(*OUTFILE) OUTFILE(' . $tmp_schema . '/' . $tmp_table . ') OUTMBR(*FIRST *REPLACE)';
        }
        return $sys_cmd;
    }

    public static function parseIbmiObjectsFromLib($tmp_table, $credat_comp = true, $typ_object = '', $typ_attrib = '') {
        $tmp_table = strtoupper(trim($tmp_table));
        if ($credat_comp === true) {
            $sql = 'SELECT ODOBNM, ODOBTP, ODOBAT, ODCDAT, ODCTIM, ODCRTS, ODCVRM, ODOBOW FROM QTEMP{SEPARATOR}';
        } else {
            $sql = 'SELECT ODOBNM, ODOBTP, ODOBAT, ODCRTS, ODCVRM, ODOBOW FROM QTEMP{SEPARATOR}';
        }
        $sql .= $tmp_table;
        $where = '';
        $typ_object = trim($typ_object);
        $typ_attrib = trim($typ_attrib);
        if ($typ_object != '' && $typ_object != '*ALL') {
            $where = "ODOBTP = '{$typ_object}'";
        }
        if ($typ_attrib != '' && $typ_attrib != '*ALL') {
            if ($where != '')
                $where .= ' AND ';
            $where .= " ODOBAT = '{$typ_attrib}'";
        }
        if ($where != '') {
            $sql .= ' WHERE ' . $where;
        }
        $sql .= ' order by ODOBTP, ODOBAT, ODOBNM ';
        return $sql;
    }

    public static function compareStrings($chaine1, $chaine2, $troncat = false) {
        $chaine1 = trim($chaine1);
        $chaine2 = trim($chaine2);
        $troncat = is_bool($troncat) ? $troncat : false;
        /*
         * on veille à ce que la comparaison se fasse sur des chaînes de même longueur
         * car la comparaison de 2 chaînes de longueur différente renverra un résultat
         * non conforme à nos attentes 
         */
        if ($troncat && strlen($chaine1) != strlen($chaine2)) {
            if (strlen($chaine1) > strlen($chaine2)) {
                // chaine1 tronquée à la longueur de chaine2
                $chaine1 = substr($chaine1, 0, strlen($chaine2));
            } else {
                // chaine2 tronquée à la longueur de chaine1
                $chaine2 = substr($chaine2, 0, strlen($chaine1));
            }
        }
        $sql = <<<BLOC_SQL
SELECT CASE WHEN '{$chaine1}' > '{$chaine2}' THEN 1 ELSE  
         CASE WHEN '{$chaine1}' < '{$chaine2}' THEN -1 ELSE 0 END 
       END as RESULT                                                 
FROM SYSIBM{SEPARATOR}SYSDUMMY1 
BLOC_SQL;

        return $sql;
    }

    public static function extractTableStruct($data_from_system_table = false, $columns_only = false) {
        $bib_sys = self::BIB_SYS;
        if ($columns_only) {
            $from_table = self::defineTypeTableName($data_from_system_table, 'c');
            $sql = <<<BLOC_SQL
SELECT TRIM(c.COLUMN_NAME) AS FIELD
FROM {$bib_sys}{SEPARATOR}SYSCOLUMNS c                                                   
WHERE {$from_table} 
ORDER BY c.ORDINAL_POSITION      
BLOC_SQL;
        } else {
            $from_table = self::defineTypeTableName($data_from_system_table, 'a');
            $sql = <<<BLOC_SQL
SELECT c.*,
 tc.CONSTRAINT_TYPE, k.COLSEQ AS CONSTRAINT_COLSEQ
FROM (
SELECT 
 a.TABLE_SCHEMA,
 a.TABLE_NAME,
 c.ORDINAL_POSITION, 
 c.COLUMN_NAME AS FIELD,
 case when c.DATA_TYPE = 'TIMESTMP' then 'TIMESTAMP' else (       
   case when c.DATA_TYPE = 'VARC' then 'VARCHAR' else  (
       case when c.DATA_TYPE = 'VARG' then 'VARGRAPHIC' else c.DATA_TYPE  end
 ) end ) end as DATA_TYPE, 
 c.DATA_TYPE_LENGTH as LENGTH, 
 c.NUMERIC_SCALE as SCALE, 
 c.NUMERIC_PRECISION,  
 c.IS_NULLABLE AS COLUMN_NULLABLE, 
 c."CCSID" as COLUMN_CCSID, 
 c.SYSTEM_COLUMN_NAME,
 c.COLUMN_HEADING,
 c.COLUMN_TEXT,
 c.HAS_DEFAULT, 
 c.COLUMN_DEFAULT,
 c.ALLOCATE,
 c.IS_IDENTITY, c.IDENTITY_GENERATION, c.IDENTITY_START, 
 c.IDENTITY_INCREMENT, c.IDENTITY_MINIMUM, c.IDENTITY_MAXIMUM,
 c.IDENTITY_CYCLE, c.IDENTITY_CACHE, c.IDENTITY_ORDER 
FROM {$bib_sys}{SEPARATOR}systables A, 
   TABLE ({$bib_sys}{SEPARATOR}QSQSYSCOL2(A.system_table_schema, A.system_table_name) ) AS c 
WHERE {$from_table} 
) c 
  LEFT JOIN ({$bib_sys}{SEPARATOR}SYSKEYCST k JOIN {$bib_sys}{SEPARATOR}SYSCST tc
     ON (k.TABLE_SCHEMA = tc.TABLE_SCHEMA
       AND k.TABLE_NAME = tc.TABLE_NAME
       AND LEFT(tc.type,1) = 'P'))
     ON (C.TABLE_SCHEMA = k.TABLE_SCHEMA
       AND C.TABLE_NAME = k.TABLE_NAME
       AND C.FIELD = k.COLUMN_NAME)
BLOC_SQL;
        }
        return $sql;
    }

    public static function findTableFromSystemName() {
        $bib_sys = self::BIB_SYS ;
        $sql = <<<BLOC_SQL
SELECT A.TABLE_SCHEMA, A.TABLE_NAME,  
  A.TABLE_OWNER, A.TABLE_TYPE, A.COLUMN_COUNT, A.ROW_LENGTH, 
  ifnull(A.TABLE_TEXT, '') as TABLE_TEXT              
FROM {$bib_sys}{SEPARATOR}SYSTABLES A 
WHERE rtrim(A.SYSTEM_TABLE_SCHEMA) = ? AND rtrim(A.SYSTEM_TABLE_NAME) = ?
BLOC_SQL;
        return $sql;
    }

    public static function checkIndexFromSystemName() {
        $bib_sys = self::BIB_SYS ;
        $sql = <<<BLOC_SQL
SELECT COUNT(*) AS FOUND           
FROM {$bib_sys}{SEPARATOR}SYSINDEXES A 
WHERE A.SYSTEM_INDEX_SCHEMA = ? AND A.SYSTEM_INDEX_NAME = ?
BLOC_SQL;
        return $sql;
    }

    public static function extractTableInfo($data_from_system_table = false) {

        $from_table = self::defineTypeTableName($data_from_system_table, 'A');
        $bib_sys = self::BIB_SYS ;

        $sql = <<<BLOC_SQL
SELECT A.TABLE_SCHEMA, A.TABLE_NAME, A.SYSTEM_TABLE_SCHEMA, A.SYSTEM_TABLE_NAME, 
  A.TABLE_OWNER, A.TABLE_TYPE, A.COLUMN_COUNT, A.ROW_LENGTH, 
  ifnull(A.TABLE_TEXT, '') as TABLE_TEXT, ifnull(A.LONG_COMMENT, '') as LONG_COMMENT, 
  A.FILE_TYPE, A.SELECT_OMIT, A.IS_INSERTABLE_INTO, A.IASP_NUMBER,
  A.LAST_ALTERED_TIMESTAMP, B.LAST_CHANGE_TIMESTAMP, B.LAST_SAVE_TIMESTAMP, 
  B.LAST_RESTORE_TIMESTAMP, B.LAST_USED_TIMESTAMP, B.LAST_RESET_TIMESTAMP                
FROM {$bib_sys}{SEPARATOR}SYSTABLES A 
LEFT OUTER JOIN {$bib_sys}{SEPARATOR}SYSTABLESTAT B
  ON A.TABLE_SCHEMA = B.TABLE_SCHEMA AND A.TABLE_NAME = B.TABLE_NAME
WHERE {$from_table} 
BLOC_SQL;
        
        return $sql;
    }
    
    public static function extractDependanceInverse() {

        $sql = 'SELECT DBFFIL, DBFLIB, DBFTDP, DBFRDP FROM QSYS{SEPARATOR}QADBFDEP WHERE DBFLDP = ? AND DBFFDP = ? ';

        return $sql;
    }

    public static function extractSysindexs($data_from_system_table = false) {

        $from_table = self::defineTypeTableName($data_from_system_table, 'X');
        $bib_sys = self::BIB_SYS;

        $sql = <<<BLOC_SQL
SELECT X.INDEX_NAME, X.INDEX_SCHEMA, 
X.SYSTEM_INDEX_NAME, X.SYSTEM_INDEX_SCHEMA, 
'YES' as INDEX_SQL, 
IS_UNIQUE AS INDEX_TYPE,
0 AS EVI_DISTINCT_VALUES 
FROM {$bib_sys}{SEPARATOR}SYSINDEXES X 
WHERE {$from_table}
BLOC_SQL;

        return $sql;
    }

    public static function extractSysindexkeys($data_from_system_table = false, $index_sql = 'YES') {

        if ($index_sql == 'YES') {
            if ($data_from_system_table) {
                $from_table = ' SYSTEM_INDEX_SCHEMA = ? AND SYSTEM_INDEX_NAME = ?  ';
            } else {
                $from_table = ' INDEX_SCHEMA = ? AND INDEX_NAME = ?  ';
            }

            $sql = 'SELECT SUBSTR(COLUMN_NAME, 1, 30) AS COLUMN_NAME, ORDERING FROM ' . self::BIB_SYS . '{SEPARATOR}SYSKEYS WHERE ' . $from_table . ' ORDER BY ORDINAL_POSITION ';
        } else {
            $sql = 'SELECT DBKFLD AS COLUMN_NAME, DBKORD AS ORDERING FROM QSYS{SEPARATOR}QADBKFLD WHERE DBKLIB = ? and DBKFIL = ? ORDER BY DBKPOS ';
        }
        return $sql;
    }

    public static function extractSysview($data_from_system_table = false) {

        $bib_sys = self::BIB_SYS;
        $from_table = self::defineTypeTableName($data_from_system_table);

        $sql = <<<BLOC_SQL
SELECT A.TABLE_NAME, A.VIEW_DEFINITION 
FROM {$bib_sys}{SEPARATOR}SYSVIEWS A 
WHERE {$from_table}
BLOC_SQL;

        return $sql;
    }

    public static function extractSysviewdep() {

        $bib_sys = self::BIB_SYS;
        $from_table = self::defineTypeTableName('V');

        $sql = <<<BLOC_SQL
SELECT VIEW_NAME, VIEW_OWNER, OBJECT_NAME, OBJECT_SCHEMA, OBJECT_TYPE, 
    VIEW_SCHEMA, SYSTEM_VIEW_NAME, SYSTEM_VIEW_SCHEMA, SYSTEM_TABLE_NAME, 
    SYSTEM_TABLE_SCHEMA, TABLE_NAME, TABLE_OWNER, TABLE_SCHEMA, 
    TABLE_TYPE, IASP_NUMBER, PARM_SIGNATURE            
FROM {$bib_sys}{SEPARATOR}SYSVIEWDEP 
WHERE {$from_table}
BLOC_SQL;

        return $sql;
    }

    public static function extractSystabledep() {

        $bib_sys = self::BIB_SYS;
        $from_table = self::defineTypeTableName('T');

        $sql = <<<BLOC_SQL
SELECT TABLE_SCHEMA, TABLE_NAME, OBJECT_SCHEMA, OBJECT_NAME, OBJECT_TYPE, 
    IASP_NUMBER, SYSTEM_TABLE_SCHEMA, SYSTEM_TABLE_NAME, PARM_SIGNATURE
FROM {$bib_sys}{SEPARATOR}SYSTABLEDEP
WHERE {$from_table}
BLOC_SQL;

        return $sql;
    }
    
    public static function extractSysviewdepInverse() {

        $bib_sys = self::BIB_SYS;

        $sql = <<<BLOC_SQL
SELECT VIEW_NAME, VIEW_OWNER, VIEW_SCHEMA
FROM {$bib_sys}{SEPARATOR}SYSVIEWDEP
WHERE OBJECT_SCHEMA = ? AND OBJECT_NAME = ? 
BLOC_SQL;

        return $sql;
    }

    public static function extractSystabdepInverse() {

        $bib_sys = self::BIB_SYS;

        $sql = <<<BLOC_SQL
SELECT TABLE_SCHEMA, TABLE_NAME
FROM {$bib_sys}{SEPARATOR}SYSTABDEP
WHERE OBJECT_SCHEMA = ? AND OBJECT_NAME = ? 
BLOC_SQL;

        return $sql;
    }

    public static function extractAllsysviews($view_name = '', $data_from_system_table = false, $ref_croisee = false, $param_optionnels=array()) {
        $bib_sys = self::BIB_SYS;
        if ($data_from_system_table) {
            $from_table = ' A.SYSTEM_TABLE_SCHEMA = ?';
        } else {
            $from_table = ' A.TABLE_SCHEMA = ?';
        }
        $view_name = trim($view_name);
        if ($view_name != '') {
            if (strpos($view_name, '%') !== false) {
                $from_table .= ' AND A.TABLE_NAME LIKE ?';
            } else {
                $from_table .= ' AND A.TABLE_NAME = ?';
            }
        }
        if ($ref_croisee === true) {
            $ref_croisee = self::getRefCroiseesSysroutines();
            $ref_croisee_sql = $ref_croisee['sql'];
            $ref_croisee_colons = $ref_croisee['colons'];
        } else {
            $ref_croisee_sql = '';
            $ref_croisee_colons = '';
        }

        $sql = <<<BLOC_SQL
SELECT A.TABLE_NAME, A.VIEW_DEFINITION {$ref_croisee_colons}
FROM {$bib_sys}{SEPARATOR}SYSVIEWS A {$ref_croisee_sql}
WHERE {$from_table}
BLOC_SQL;

        if (is_array($param_optionnels) && count($param_optionnels)>0) {
            $sql .= ' ' . SpecifBusiness::get_donnees_fonctionnelles_figees_sur_vues($param_optionnels);
        }

        return $sql;
    }

    public static function extractAllsysroutines($routine_name = '', $data_from_system_table = false, $ref_croisee = false) {
        $bib_sys = self::BIB_SYS;
        if ($data_from_system_table) {
            $from_table = ' A.SPECIFIC_SCHEMA = ?';
        } else {
            $from_table = ' A.ROUTINE_SCHEMA = ?';
        }
        $routine_name = trim($routine_name);
        if ($routine_name != '') {
            if (strpos($routine_name, '%') !== false) {
                $from_table .= ' AND A.ROUTINE_NAME LIKE ?';
            } else {
                $from_table .= ' AND A.ROUTINE_NAME = ?';
            }
        }
        if ($ref_croisee === true) {
            $ref_croisee = self::getRefCroiseesSysroutines();
            $ref_croisee_sql = $ref_croisee['sql'];
            $ref_croisee_colons = $ref_croisee['colons'];
        } else {
            $ref_croisee_sql = '';
            $ref_croisee_colons = '';
        }

        $sql = <<<BLOC_SQL
SELECT A.* {$ref_croisee_colons}
FROM {$bib_sys}{SEPARATOR}SYSROUTINE A {$ref_croisee_sql}
WHERE {$from_table}
BLOC_SQL;

        return $sql;
    }

    public static function extractSysroutine() {

        $bib_sys = self::BIB_SYS;
        $from_table = self::defineTypeTableName('R');

        $sql = <<<BLOC_SQL
SELECT 
  SPECIFIC_SCHEMA, SPECIFIC_NAME, ROUTINE_SCHEMA, ROUTINE_NAME, ROUTINE_TYPE, 
  ROUTINE_CREATED, ROUTINE_DEFINER, ROUTINE_BODY, EXTERNAL_NAME, EXTERNAL_LANGUAGE, 
  PARAMETER_STYLE, IS_DETERMINISTIC, SQL_DATA_ACCESS, SQL_PATH, PARM_SIGNATURE, 
  NUMBER_OF_RESULTS, MAX_DYNAMIC_RESULT_SETS, IN_PARMS, OUT_PARMS, INOUT_PARMS, 
  cast(ROUTINE_DEFINITION as VARGRAPHIC(16000)) as ROUTINE_DEFINITION,
  CASE WHEN length(ROUTINE_DEFINITION) > 16000 THEN 'WARNING : DEFINITION > 16000 CHARS., SOURCE TRUNCATED' ELSE '' END  as warning_definition
FROM {$bib_sys}{SEPARATOR}SYSROUTINE A
WHERE {$from_table}
BLOC_SQL;

        return $sql;
    }

    public static function getRoutineSpecificName() {
        $bib_sys = self::BIB_SYS;

        $sql = <<<BLOC_SQL
SELECT specific_schema, specific_name 
FROM {$bib_sys}{SEPARATOR}SYSROUTINE 
WHERE routine_schema = ? and routine_name = ?
BLOC_SQL;
        return $sql;
    }

    public static function extractSysroutinedep() {

        $bib_sys = self::BIB_SYS;

        $sql = <<<BLOC_SQL
SELECT distinct OBJECT_NAME, OBJECT_SCHEMA, OBJECT_TYPE
FROM {$bib_sys}{SEPARATOR}SYSROUTINEDEP
WHERE SPECIFIC_SCHEMA = ? AND SPECIFIC_NAME = ? 
ORDER BY OBJECT_TYPE DESC, OBJECT_NAME
BLOC_SQL;

        return $sql;
    }

    public static function extractSysroutinedepInverse() {

        $bib_sys = self::BIB_SYS;

        $sql = <<<BLOC_SQL
SELECT distinct A.SPECIFIC_SCHEMA, A.SPECIFIC_NAME, A.OBJECT_SCHEMA, B.ROUTINE_SCHEMA, B.ROUTINE_NAME,
    B.ROUTINE_TYPE
FROM {$bib_sys}{SEPARATOR}SYSROUTINEDEP A
INNER JOIN {$bib_sys}{SEPARATOR}SYSROUTINES B
  ON A.SPECIFIC_SCHEMA = B.SPECIFIC_SCHEMA AND A.SPECIFIC_NAME = B.SPECIFIC_NAME
WHERE A.OBJECT_NAME = ?
ORDER BY A.SPECIFIC_SCHEMA, A.SPECIFIC_NAME
BLOC_SQL;

        return $sql;
    }

    public static function extractColumnsMinmax($schema, $table, $colonnes) {

        $sql_colonne = array();

        foreach ($colonnes as $colonne) {
            $colonne = trim(strtoupper($colonne));
            $sql_colonne [] = <<<BLOC_SQL
max({$colonne}) as MAXVAL_{$colonne}, 
min({$colonne}) as MINVAL_{$colonne},  
max(length({$colonne})) as MAXBYT_{$colonne}                                       
BLOC_SQL;
        }
        $sql = 'SELECT ' . PHP_EOL . implode(', ', $sql_colonne) . PHP_EOL . ' FROM ' . trim($schema) . '{SEPARATOR}' . trim($table);
        return $sql;
    }

    public static function analyseTableStruct($datastructure, $tabminmax) {
        $tab_optimize = array();
        foreach ($datastructure as $data) {
            $field = trim($data ['FIELD']);
            $tab_optimize [$field] = array(
                'FIELD' => $field,
                'ORDINAL_POSITION' => $data ['ORDINAL_POSITION'],
                'DATA_TYPE' => $data ['DATA_TYPE'],
                'LENGTH' => $data ['LENGTH'],
                'SCALE' => $data ['SCALE'],
                'NULLABLE' => isset($data ['COLUMN_NULLABLE']) ? $data ['COLUMN_NULLABLE'] : false,
                'IDENTITY' => isset($data ['IS_IDENTITY']) ? $data ['IS_IDENTITY'] : false,
                'VAL_MIN' => $tabminmax['MINVAL_' . $field],
                'VAL_MAX' => $tabminmax['MAXVAL_' . $field],
                'LEN_BYT' => $tabminmax['MAXBYT_' . $field],
                'LEN_MIN' => strlen(trim($tabminmax['MINVAL_' . $field])),
                'LEN_MAX' => strlen($tabminmax['MAXVAL_' . $field])
            );
        }
        return $tab_optimize;
    }

    public static function get_table_structure_entete($schema, $table, $nom_court = false) {
        if ($nom_court === true) {
            $where = ' A.SYSTEM_TABLE_NAME = ? ';
        } else {
            $where = ' A.TABLE_NAME = ? ';
        }
        $bib_sys = self::BIB_SYS;

        $sql = <<<BLOC_SQL
SELECT  
A.TABLE_NAME,
A.SYSTEM_TABLE_NAME,
A.TABLE_TYPE,
A.COLUMN_COUNT,
A.ROW_LENGTH,
A.TABLE_TEXT,
A.IS_INSERTABLE_INTO,
A.TABLE_OWNER,
A.TABLE_SCHEMA,
A.ENABLED, A.MAINTENANCE, A.REFRESH, A.REFRESH_TIME, A.ISOLATION, A.MQT_RESTORE_DEFERRED, 
cast(A.MQT_DEFINITION as VARGRAPHIC(16000)) as MQT_DEFINITION,
CASE WHEN length(MQT_DEFINITION) > 16000 THEN 'WARNING : DEFINITION > 16000 CHARS., SOURCE TRUNCATED' ELSE '' END  as warning_definition
FROM {$bib_sys}{SEPARATOR}SYSTABLES A 
WHERE A.TABLE_SCHEMA = ? AND                       
{$where}
BLOC_SQL;
        return $sql;
    }

    public static function getListeTables($recherche_base, $recherche_table, $recherche_varchar, $type_objet, $ref_croisee) {

        $wheres = array();
        $bib_sys = self::BIB_SYS;
        if ($recherche_base) {
            $wheres [] = 'A.TABLE_SCHEMA = ?';
        }

        $recherche_table = trim($recherche_table);
        if (strlen($recherche_table) > 0) {
            if (strpos($recherche_table, '%') !== false) {
                $wheres [] = ' (A.TABLE_NAME LIKE ? OR A.SYSTEM_TABLE_NAME LIKE ?)  ';
            } else {
                if (strlen($recherche_table) > 10) {
                    /*
                     * Cela n'aurait aucun sens de rechercher un libellé de plus 10 caractères dans une chaîne de 10 caractères,
                     * en plus cela a pour effet de déclencher une erreur SQL de type :
                     * SQLSTATE[HY010]: Function sequence error: 0 [Microsoft][Gestionnaire de pilotes ODBC] Erreur de séquence de la fonction (SQLExecute[0] at ...
                     */
                    $wheres [] = ' A.TABLE_NAME = ?';
                } else {
                    $wheres [] = ' (A.TABLE_NAME = ? OR A.SYSTEM_TABLE_NAME = ?)';
                }
            }
        }

        if ($recherche_varchar) {
            $wheres [] = " exists ( select 1 from " . $bib_sys . "{SEPARATOR}SYSCOLUMNS B where A.TABLE_NAME = B.TABLE_NAME AND A.TABLE_SCHEMA = B.TABLE_SCHEMA and B.DATA_TYPE = 'VARCHAR') ";
        }

        if ($type_objet != '*' && $type_objet != '') {
            $type_objet = trim($type_objet);
            $wheres [] = "A.TABLE_TYPE = '{$type_objet}'";
        }

        if (count($wheres) > 0) {
            $criteres_sql = ' WHERE ' . implode(' AND ', $wheres);
        } else {
            $criteres_sql = '';
        }

        if ($ref_croisee === true) {
            $ref_croisee = self::getRefCroiseesSysroutines();
            $ref_croisee_sql = $ref_croisee['sql'];
            $ref_croisee_colons = $ref_croisee['colons'];
        } else {
            $ref_croisee_sql = '';
            $ref_croisee_colons = '';
        }

        $sql = <<<BLOC_SQL
SELECT  
A.TABLE_NAME,
A.SYSTEM_TABLE_NAME,
A.TABLE_TYPE,
A.COLUMN_COUNT,
A.ROW_LENGTH,
A.TABLE_TEXT,
A.IS_INSERTABLE_INTO,
A.TABLE_OWNER,
A.TABLE_SCHEMA {$ref_croisee_colons}
FROM {$bib_sys}{SEPARATOR}SYSTABLES A {$ref_croisee_sql}
{$criteres_sql}
BLOC_SQL;

        return $sql;
    }

    public static function getTablesByColumn($nom_colonne, $nom_schema, $nom_table) {
        $wheres = array();
        $bib_sys = self::BIB_SYS;

        $nom_colonne = trim($nom_colonne);
        $nom_schema = trim($nom_schema);
        $nom_table = trim($nom_table);

        if ($nom_colonne != '') {
            $wheres [] = '(A.COLUMN_NAME LIKE ? OR A.SYSTEM_COLUMN_NAME LIKE ?)';
        }

        if ($nom_schema != '') {
            $wheres [] = 'A.TABLE_SCHEMA LIKE ?';
        }

        if ($nom_table != '') {
            $wheres [] = 'A.TABLE_NAME LIKE ?';
        }

        if (count($wheres) > 0) {
            $criteres_sql = ' WHERE ' . implode(' AND ', $wheres);
        } else {
            $criteres_sql = '';
        }

        $sql = <<<BLOC_SQL
SELECT
 ORDINAL_POSITION,         
 COLUMN_NAME,
 TABLE_NAME, TABLE_SCHEMA, 
 DATA_TYPE,                             
 LENGTH,                                     
 SCALE,    
 NUMERIC_PRECISION,                                   
 NULLS AS COLUMN_NULLABLE,                                            
 "CCSID" as COLUMN_CCSID, 
 SYSTEM_COLUMN_NAME,
 COLUMN_HEADING,
 COLUMN_TEXT,
 HAS_DEFAULT, 
 COLUMN_DEFAULT,
 IS_IDENTITY                                
FROM {$bib_sys}{SEPARATOR}SYSCOLUMNS A 
{$criteres_sql}
BLOC_SQL;

        return $sql;
    }

    public static function getRefCroiseesSysroutines($table_name_from_a='') {
        $table_name_from_a = trim($table_name_from_a) ;
        if ($table_name_from_a == '') {
            $table_name_from_a = 'A.TABLE_NAME' ;
        }
        $bib_sys = self::BIB_SYS;
        $ref_croisee = array();
        $ref_croisee['sql'] = "INNER JOIN " . $bib_sys . "{SEPARATOR}SYSROUTINEDEP C ON {$table_name_from_a} = C.OBJECT_NAME AND C.SPECIFIC_SCHEMA = '" . self::get_bib_ref_pgm() . "' ";
        $ref_croisee['colons'] = ', C.SPECIFIC_SCHEMA, C.SPECIFIC_NAME ';
        return $ref_croisee;
    }

    public static function dropTable($table, $schema) {
        $table = trim($table);
        $schema = trim($schema);
        error_log('Warning : suppression de la table DB2 ' . $schema . '/' . $table);
        return 'DROP TABLE ' . $schema . '{SEPARATOR}' . $table;
    }

 
    public static function genAltertables($zone_util = false, $prefix_tables = '', $prefix_procs = '') {

        $sql_prefix_tables = '';
        if (trim($prefix_tables) != '') {
            $sql_prefix_tables = " AND A.TABLE_NAME LIKE '" . trim($prefix_tables) . "%'";
        }

        $sql_prefix_procs = '';
        if (trim($prefix_procs) != '') {
            $sql_prefix_procs = " AND C.SPECIFIC_NAME LIKE '" . trim($prefix_procs) . "%'";
        }

        $bib_sys = self::BIB_SYS;
        $sql = <<<BLOC_SQL
SELECT A.TABLE_NAME 
  FROM {$bib_sys}{SEPARATOR}SYSTABLES A
 WHERE A.TABLE_SCHEMA = ? AND A.TABLE_TYPE = 'T'
   AND NOT ( A.TABLE_NAME LIKE 'PRC_TRACE%' ) {$sql_prefix_tables}
   AND EXISTS 
        (SELECT 1 FROM {$bib_sys}{SEPARATOR}SYSCOLUMNS B 
             WHERE A.TABLE_SCHEMA = B.TABLE_SCHEMA AND A.TABLE_NAME = B.TABLE_NAME AND B.DATA_TYPE = 'GRAPHIC'
        )	
BLOC_SQL;
        if ($zone_util) {
            /*
             * prendre en compte uniquement les tables utilisées dans les procédures stockées 
             * d'alimentation du datawarehouse
             */
            $bib_pgm = self::get_bib_ref_pgm();
            $sql2 = <<<BLOC_SQL
   AND EXISTS 
		(SELECT 1 FROM {$bib_sys}{SEPARATOR}SYSROUTDEP C 
             WHERE A.TABLE_NAME = C.OBJECT_NAME {$sql_prefix_procs}
               AND (C.SPECIFIC_SCHEMA = '{$bib_pgm}')
		)
BLOC_SQL;
            $sql .= $sql2;
        }
        return $sql;
    }

    public static function getIbmiProfilesWithFilters($filters = array(), $tmp_table = '', $tmp_schema = '') {
        $fields_scr = array(
            'prf_user' => 'UPUPRF',
            'user_name' => 'UPTEXT',
            'class_user' => 'UPUSCL',
            'status_user' => 'UPSTAT',
            'jobd_user' => 'UPJBDL/UPJBDS'
        );
        $tmp_table = strtoupper(trim($tmp_table));
        $tmp_schema = strtoupper(trim($tmp_schema));
        if ($tmp_table == '') {
            $tmp_table = 'TMPUSRPRF';
        }
        if ($tmp_schema == '') {
            $tmp_schema = 'QTEMP';
        }

        $sql = "SELECT UPUPRF, UPUSCL, UPTEXT, UPSTAT, UPJBDS, UPJBDL, UPOWNR FROM $tmp_schema{SEPARATOR}$tmp_table";
        $where_sql = array();
        $where_prm = array();
        foreach ($fields_scr as $key => $coldb) {
            if (array_key_exists($key, $filters) && $filters[$key] != '' && $filters[$key] != '*ALL') {
                $value = $filters[$key];
                if ($key == 'prf_user' && strlen($value) < 10) {
                    $where_sql [] = $coldb . ' LIKE ?';
                    $where_prm [] = $value . '%';
                } else {
                    if ($key == 'user_name') {
                        $where_sql [] = $coldb . ' LIKE ?';
                        $where_prm [] = '%' . $value . '%';
                    } else {
                        if ($key == 'jobd_user') {
                            $pieces = explode('/', $coldb);
                            $where_sql [] = $pieces[0] . ' = ?';
                            $where_sql [] = $pieces[1] . ' = ?';
                            $pieces = explode('/', $value);
                            $where_prm [] = $pieces[0];
                            $where_prm [] = $pieces[1];
                        } else {
                            $where_sql [] = $coldb . ' = ?';
                            $where_prm [] = $value;
                        }
                    }
                }
            }
        }
        if (count($where_sql) > 0) {
            $sql .= ' WHERE ' . implode(' AND ', $where_sql);
        }
        return array($sql, $where_prm);
    }

    public static function getIbmiJobdprfusers($tmp_table = '', $tmp_schema = '') {
        $tmp_table = strtoupper(trim($tmp_table));
        $tmp_schema = strtoupper(trim($tmp_schema));
        if ($tmp_table == '') {
            $tmp_table = 'TMPUSRPRF';
        }
        if ($tmp_schema == '') {
            $tmp_schema = 'QTEMP';
        }

        return <<<BLOC_SQL
SELECT '*ALL' AS JOBD FROM SYSIBM{SEPARATOR}SYSDUMMY1	
UNION
SELECT trim(UPJBDL) concat '/' concat UPJBDS as JOBD FROM $tmp_schema{SEPARATOR}$tmp_table GROUP BY UPJBDS, UPJBDL
BLOC_SQL;
    }

    public static function getSqlStates() {
        $sqlstates = array();
        $sqlstates ['01003'] = 'Null values were eliminated from the argument of a column function.';
        $sqlstates ['01004'] = 'The value of a string was truncated when assigned to another string data type with a shorter length.';
        $sqlstates ['01503'] = 'The number of result columns is larger than the number of host variables provided.';
        $sqlstates ['01504'] = 'The UPDATE or DELETE statement does not include a WHERE clause.';
        $sqlstates ['01506'] = 'An adjustment was made to a DATE or TIMESTAMP value to correct an invalid date resulting from an arithmetic operation.';
        $sqlstates ['01517'] = 'A character that cannot be converted was replaced with a substitute character.';
        $sqlstates ['01526'] = 'Isolation level has been escalated.';
        $sqlstates ['02000'] = 'Nombre de lignes impactées = zéro';
        $sqlstates ['42802'] = 'The number of insert or update values is not the same as the number of columns.';
        $sqlstates ['01545'] = 'An unqualified column name has been interpreted as a correlated reference.';
        $sqlstates ['01532'] = 'An undefined object name was detected.';

        return $sqlstates;
    }

    public static function getSqlVerbs() {
        return array(
            ' ALTER SEQUENCE ',
            ' ALTER TABLE ',
            ' CALL ',
            ' COMMENT ON ',
            ' COMMIT ',
            ' CREATE ALIAS ',
            ' CREATE COLLECTION ',
            ' CREATE DISTINCT TYPE ',
            ' CREATE FUNCTION ',
            ' CREATE INDEX ',
            ' CREATE PROCEDURE ',
            ' CREATE SEQUENCE ',
            ' CREATE TABLE ',
            ' CREATE TRIGGER ',
            ' CREATE VIEW ',
            ' CREATE OR REPLACE ALIAS ',
            ' CREATE OR REPLACE COLLECTION ',
            ' CREATE OR REPLACE DISTINCT TYPE ',
            ' CREATE OR REPLACE FUNCTION ',
            ' CREATE OR REPLACE INDEX ',
            ' CREATE OR REPLACE PROCEDURE ',
            ' CREATE OR REPLACE SEQUENCE ',
            ' CREATE OR REPLACE TABLE ',
            ' CREATE OR REPLACE TRIGGER ',
            ' CREATE OR REPLACE VIEW ',
            ' DECLARE GLOBAL TEMPORARY TABLE ',
            ' DELETE ',
            ' DROP ALIAS ',
            ' DROP COLLECTION ',
            ' DROP DISTINCT TYPE ',
            ' DROP FUNCTION ',
            ' DROP INDEX ',
            ' DROP PACKAGE ',
            ' DROP PROCEDURE ',
            ' DROP ROUTINE ',
            ' DROP TABLE ',
            ' DROP TRIGGER ',
            ' DROP VIEW ',
            ' GRANT ',
            ' INSERT ',
            ' LABEL ON ',
            ' LOCK TABLE ',
            ' REFRESH TABLE ',
            ' RELEASE SAVEPOINT ',
            ' RENAME ',
            ' REVOKE ',
            ' ROLLBACK ',
            ' SAVEPOINT ',
            ' SELECT ',
            ' SET CURRENT DEGREE ',
            ' SET ENCRYPTION PASSWORD ',
            ' SET PATH ',
            ' SET SCHEMA ',
            ' SET TRANSACTION ',
            ' UPDATE '
        );
    }

    public static function changeJobSortSequence() {
        /*
         * La syntaxe ci-dessous fonctionne dans les programmes RPG et les procédures stockées DB2, mais
         * on ne peut pas l'utiliser en SQL dynamique à l'intérieur d'un script PHP, on doit donc
         * recourir à un CHGJOB :
         *   $sql_tri = 'SET OPTION SRTSEQ = *LANGIDUNQ  , LANGID = *JOB' ;
         *
         * Paramètres possibles :
         * - Utilisation du "shared-weight sort sequence"
         *    CHGJOB SRTSEQ(*LANGIDSHR) LANGID(FRA) CNTRYID(FR) CCSID(297)
         * - Utilisation du "unique-weight sort sequence"
         *    CHGJOB SRTSEQ(*LANGIDUNQ) LANGID(FRA) CNTRYID(FR) CCSID(297)
         * - Utilisation d'une table de translation (très mauvaises perfs. constatées à l'usage) :
         *    CHGJOB SRTSEQ(FR_FR)
         * A noter :
         *   A l'usage, la saisie ou l'absence des paramètrs LANGID, CNTRYID et CCSID n'a pas
         *   semblé faire de différence sur les SRTSEQ(*LANGIDSHR) et SRTSEQ(*LANGIDUNQ)
         *
         */
        /*
         * tentative de modifier le tri DB2 (par défaut en *HEX)
         * aucune des solutions ci-dessous n'a donné satisfaction
         */
        $sys_cmd = 'CHGJOB SRTSEQ(*LANGIDUNQ) LANGID(FRA) CNTRYID(FR) CCSID(297)';

        return $sys_cmd;
    }

    public static function getOsVersion () {
        return "SELECT 'V' concat OS_VERSION concat 'R' concat OS_RELEASE as version_os FROM SYSIBMADM.ENV_SYS_INFO" ;
    }
            
    public static function checkObjectExists($object_type) {
        $bib_sys = self::BIB_SYS;
        $object_type = trim(strtoupper($object_type)) ;
        switch ($object_type) {
            case 'TABLE' :  {
                $table_check = 'systables' ;
                $col_schema = 'TABLE_SCHEMA' ;
                $col_table  = 'TABLE_NAME' ;
                break;
            }
            case 'VIEW' :  {
                $table_check = 'sysviews' ;
                $col_schema = 'TABLE_SCHEMA' ;
                $col_table  = 'TABLE_NAME' ;
                break;
            }
            case 'INDEX' :  {
                $table_check = 'sysindexes' ;
                $col_schema = 'INDEX_SCHEMA' ;
                $col_table  = 'INDEX_NAME' ;                
                break;
            }
            case 'PROCEDURE' :
            case 'FUNCTION' : {
                $table_check = 'sysroutines' ;
                $col_schema = 'ROUTINE_SCHEMA' ;
                $col_table  = 'ROUTINE_NAME' ;                
                break;
            }
            default :  {
                $table_check = 'systables' ;
                $col_schema = 'TABLE_SCHEMA' ;
                $col_table  = 'TABLE_NAME' ;                
                break;
            }           
        }
        
        return "select count(*) as found from {$bib_sys}{SEPARATOR}{$table_check} where {$col_schema} = ? and {$col_table} = ?" ;

    }
    
    public static function procGenerateSQL () {
        return array('QSYS2' , 'GENERATE_SQL') ; 
    }

    public static function getTableLocks() {
        $bib_sys = self::BIB_SYS;
        return <<<SQL
        SELECT *
        FROM {$bib_sys}{SEPARATOR}RECORD_LOCK_INFO 
        WHERE SYSTEM_TABLE_NAME = ?
          AND SYSTEM_TABLE_SCHEMA = ?
SQL;
    }


    public static function getTableTriggers() {
        $bib_sys = self::BIB_SYS;
        return <<<SQL
SELECT TRIGGER_SCHEMA, TRIGGER_NAME, EVENT_MANIPULATION, ACTION_ORDER, ACTION_CONDITION, 
       ACTION_ORIENTATION, ACTION_TIMING, TRIGGER_MODE, 
       DATE(CREATED) as CREADATE, TRIGGER_PROGRAM_NAME, TRIGGER_PROGRAM_LIBRARY
FROM {$bib_sys}{SEPARATOR}SYSTRIGGERS
WHERE EVENT_OBJECT_SCHEMA = ? AND EVENT_OBJECT_TABLE = ?
SQL;
    }

    public static function getTriggerDesc() {
        $bib_sys = self::BIB_SYS;
        return <<<SQL
SELECT * FROM {$bib_sys}{SEPARATOR}SYSTRIGGERS
WHERE TRIGGER_SCHEMA = ? AND TRIGGER_NAME = ?
SQL;
    }

    public static function extractSystrigdep() {

        $bib_sys = self::BIB_SYS;

        $sql = <<<BLOC_SQL
SELECT distinct OBJECT_NAME, OBJECT_SCHEMA, OBJECT_TYPE
FROM {$bib_sys}{SEPARATOR}SYSTRIGDEP
WHERE TRIGGER_SCHEMA = ? AND TRIGGER_NAME = ? 
ORDER BY OBJECT_TYPE DESC, OBJECT_NAME
BLOC_SQL;

        return $sql;
    }

    public static function extractSystrigdepInverse() {

        $bib_sys = self::BIB_SYS;

        $sql = <<<BLOC_SQL
SELECT TRIGGER_NAME, TRIGGER_SCHEMA
FROM {$bib_sys}{SEPARATOR}SYSTRIGDEP
WHERE OBJECT_SCHEMA = ? AND OBJECT_NAME = ? 
ORDER BY TRIGGER_NAME, TRIGGER_SCHEMA
BLOC_SQL;

        return $sql;
    }
}


