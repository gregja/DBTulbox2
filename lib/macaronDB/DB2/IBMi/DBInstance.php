<?php

/**
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   MacaronDB
 * @package    DB
 * @license    New BSD License
 * @version    DB/DB2/IBMi/DBInstance.php 2012-03-28 09:15:47
 */
require_once 'DBInstanceInterface.php';

class DB2_IBMi_DBInstance implements DBInstanceInterface {

    protected $_dbinstance = null;
    protected $_options = null;
    protected $_sql_separator = '.';
    protected $_system = null;
    protected $_user = null;
    protected $_profiler = false;
    protected $_autocommit = true;
    protected $_persistent = false;

    public function __construct($system, $user, $password, $options = array(), $persistent = false) {

        $this->_system = $system;
        $this->_user = $user;
        $this->_options = $options;
        if ($persistent === true) {
            $this->_persistent = true;
        } else {
            $this->_persistent = false;
        }

        // préparation du tableau des options au format attendu par le connecteur DB2
        //$options = DB2_IBMi_DBConnex::generate_config ( $options );

        $this->_dbinstance = DB2_IBMi_DBConnex::getInstance($system, $user, $password, $options, $this->_persistent);

        if (isset($options ['i5_naming']) && $options ['i5_naming'] == true) {
            $this->_sql_separator = '/';
        }
    }

    public function getResource() {
        return $this->_dbinstance;
    }

    /*
     * renvoie le séparateur SQL à utiliser en fonction du type de nommage déclaré
     * ( nommage SQL => "."  ; ou nommage Systéme IBM i => "/" )
     */

    public function getSqlSeparator() {
        return $this->_sql_separator;
    }

    public function getAutocommitMode() {
        return $this->_autocommit;
    }

    public function getPersistentMode() {
        return $this->_persistent;
    }

    public function selectOne($sql, $args = array(), $fetch_mode_num = false) {
        return DB2_IBMi_DBWrapper::selectOne($this, $sql, $args, $fetch_mode_num);
    }

    public function selectBlock($sql, $args = array()) {
        return DB2_IBMi_DBWrapper::selectBlock($this, $sql, $args);
    }

    public function selectKeyValuePairs($sql, $args = array()) {
        return DB2_IBMi_DBWrapper::selectKeyValuePairs($this, $sql, $args);
    }

    public function executeCommand($sql, $args = array(), $count_nb_rows = true) {
        return DB2_IBMi_DBWrapper::executeCommand($this, $sql, $args, $count_nb_rows);
    }

    public function executeSysCommand($cmd) {
        return DB2_IBMi_DBWrapper::executeSysCommand($this, $cmd);
    }

    public function callProcedure($proc_name, $proc_schema, &$args = array(), $return_resultset = false) {
        return DB2_IBMi_DBWrapper::callProcedure($this, $proc_name, $proc_schema, $args, $return_resultset);
    }

    public function getStatement($sql, $args = array()) {
        return DB2_IBMi_DBWrapper::getStatement($this, $sql, $args);
    }

    public function getFetchAssoc($st) {
        return DB2_IBMi_DBWrapper::getFetchAssoc($st);
    }

    public function getPagination($sql, $args, $offset, $nbl_by_page, $order_by = '') {
        return DB2_IBMi_DBWrapper::getPagination($this, $sql, $args, $offset, $nbl_by_page, $order_by);
    }

    public function getScrollCursor($sql, $args, $offset, $nbl_by_page, $order_by = '') {
        return DB2_IBMi_DBWrapper::getScrollCursor($this, $sql, $args, $offset, $nbl_by_page, $order_by);
    }

    public function export2CSV($sql, $args = array()) {
        return DB2_IBMi_DBWrapper::export2CSV($this, $sql, $args);
    }

    public function export2XML($sql, $args = array(), $tag_line = '', $gen_header = true) {
        return DB2_IBMi_DBWrapper::export2XML($this, $sql, $args, $tag_line, $gen_header);
    }

    public function export2insertSQL($sql, $args = array()) {
        return DB2_IBMi_DBWrapper::export2insertSQL($this, $sql, $args);
    }

    public function getLastInsertId($sequence = '') {
        return DB2_IBMi_DBWrapper::getLastInsertId($this, $sequence);
    }

    public function valueIsExisting($table, $nomcol, $valcol, $where_optionnel = '') {
        return DB2_IBMi_DBWrapper::valueIsExisting($this, $table, $nomcol, $valcol, $where_optionnel);
    }

    public function valueIsExistingOnOtherRecord($table, $nomcol, $valcol, $idencours, $where_optionnel = '') {
        return DB2_IBMi_DBWrapper::valueIsExistingOnOtherRecord($this, $table, $nomcol, $valcol, $idencours, $where_optionnel);
    }

    public function getInfoDatabase() {
        return DB2_IBMi_DBWrapper::getInfoDatabase($this);
    }

    public function countNbRowsFromTable($table, $schema = '') {
        return DB2_IBMi_DBWrapper::countNbRowsFromTable($this, $table, $schema);
    }

    public function countNbRowsFromSQL($sql, $args = array()) {
        return DB2_IBMi_DBWrapper::countNbRowsFromSQL($this, $sql, $args);
    }

    public function setProfilerOn() {
        $this->_profiler = true;
    }

    public function setProfilerOff() {
        $this->_profiler = false;
    }

    public function getProfilerStatus() {
        return $this->_profiler;
    }

}
