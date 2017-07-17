<?php

    namespace csvimport;
    
    include_once './ImportFile.php';
    
    $path = '/home/mzdybel/Dokumenty/path/to/document';
    $file = 'test.csv';
    
    $importFile = new ImportFile($path, $file);
