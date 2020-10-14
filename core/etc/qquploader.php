<?php

/**
 * Handle file uploads via XMLHttpRequest
 */
class qqUploadedFileXhr {
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {    
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);
        
        if ($realSize != $this->getSize()){            
            return false;
        }
        
        $target = fopen($path, "w");        
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);
        
        return true;
    }
    function getName() {
        return $_GET['qqfile'];
    }
    function getSize() {
        if (isset($_SERVER["CONTENT_LENGTH"])){
            return (int)$_SERVER["CONTENT_LENGTH"];            
        } else {
            throw new Exception('Getting content length is not supported.');
        }      
    }   
}

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class qqUploadedFileForm {  
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {
        if(!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path)){
            return false;
        }
        return true;
    }
    function getName() {
        return $_FILES['qqfile']['name'];
    }
    function getSize() {
        return $_FILES['qqfile']['size'];
    }
}

class qqFileUploader {
    private $allowedExtensions = array();
    private $sizeLimit = 104857600;
    private $file;

    function __construct(array $allowedExtensions = array(), $sizeLimit = 104857600){        
        $allowedExtensions = array_map("strtolower", $allowedExtensions);
            
        $this->allowedExtensions = $allowedExtensions;        
        $this->sizeLimit = $sizeLimit;
        
        $this->checkServerSettings();       

        if (isset($_GET['qqfile'])) {
            $this->file = new qqUploadedFileXhr();
        } elseif (isset($_FILES['qqfile'])) {
            $this->file = new qqUploadedFileForm();
        } else {
            $this->file = false; 
        }
    }
    
    private function checkServerSettings(){        
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));        
        
        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit){
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';             
            die("{'error':'Измените значение post_max_size и upload_max_filesize до $size'}");    
        }        
    }
    
    private function toBytes($str){
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;        
        }
        return $val;
    }
    
    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    function handleUpload($uploadDirectory, $uploadFilename, $uploadExt = false){
        if (!is_writable($uploadDirectory)){
            return array('error' => "Серверная ошибка. У директории загрузки нет прав на запись.");
        }
        
        if (!$this->file){
            return array('error' => 'Ни один файл не загружен.');
        }
        
        $size = $this->file->getSize();
        
        if ($size == 0) {
            return array('error' => 'Файл пуст.');
        }
        
        if ($size > $this->sizeLimit) {
            return array('error' => 'Размер файла больше допустимого размера.');
        }
        
        $pathinfo = pathinfo($this->file->getName());
        $filename = $uploadFilename;
        $ext = $uploadExt ? $uploadExt : $pathinfo['extension'];

        if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)){
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'Недопустимый формат файла. Домустимые форматы: '. $these . '. Загружаемый формат: ' . $ext);
        }
        
        if ($this->file->save($uploadDirectory . $filename . '.' . $ext)){
            return array('success'=>true, 'dir'=>$uploadDirectory, 'file'=>$filename, 'ext'=>$ext);
        } else {
            return array('error'=> 'Файл не загружен.' .
                'Загрузка отменена, или возникла ошибка.');
        }
        
    }    
}
