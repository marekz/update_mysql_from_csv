<?php

namespace csvimport;

include_once './DBConnect.php';

class ImportFile {

    private $path;
    private $file;
    private $handler;
    private $name;
    private $status;
    
    private $host = '127.0.0.1';
    private $user = 'root';
    private $password = 'root';
    private $database = 'application';
    private $sqlQuery;
    private $newDbConnect;

    public function __construct($path, $file) {
        $this->file = $file;
        $this->path = $path;
        $this->newDbConnect = new DBConnect($this->host, $this->user, $this->password, $this->database);
        $this->ImporteCSVFile();
    }

    public function ImporteCSVFile() {
        $PathWithfile = $this->path . DIRECTORY_SEPARATOR . $this->file;
        $this->handler = fopen($PathWithfile, "r");
        if ($this->handler !== FALSE) {
            $this->getRowsCSVFile();
        }
    }

    private function getRowsCSVFile() {
        while (($data = fgetcsv($this->handler, 1000, ",")) !== FALSE) {
            if ($this->checkRowsElement($data)) {
                $this->setValueForPositionOrStatus($data);
                $dataSet = array($this->name, $this->status);
                $status = $this->checkRecordInDatabase($dataSet);
                $this->updateRowStatus($status);
            }
        }
        $this->newDbConnect->closeConnection();
        fclose($this->handler);
    }
    
    private function  updateRowStatus($status){
        if($status) {
            $this->sqlQuery = "UPDATE user_position set statistic_role_id = ". $this->status ." WHERE name=\"". $this->name ."\"";
            return $this->newDbConnect->execute($this->sqlQuery);
        } 
        return FALSE;
    }

    private function checkRowsElement($data) {
        if ($data[0] !== '' AND $data[1] !== '') {
            return true;
        } else {
            return false;
        }
    }

    private function setValueForPositionOrStatus($data) {
        $column = count($data);
        for ($c = 0; $c < $column; $c++) {
            if ($c === 0) {
                $this->name = $data[$c];
            }
            if ($c === 1) {
                $this->status = $data[$c];
            }
        }
    }
    
    private function checkRecordInDatabase($record){
        $result = $this->checkRecordStatusInDb($record);
        if($record[1] != $result['statistic_role_id']) {
            printf("Zmiana na stanowisku: %s. Nowy status dostępu do statystyk: %s<br />", $record[0], $record[1]);
            return true;
        }
        printf("<i>%s</i>, pozostaje bez zmian<br />", $record[0]);
        return false;
    }
    
    private function checkRecordStatusInDb($record){
        $this->sqlQuery = "Select name, statistic_role_id from user_position where name = \"" . $record[0] . "\";";
        return $this->newDbConnect->execute($this->sqlQuery);
    }
}
