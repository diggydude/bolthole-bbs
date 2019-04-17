<?php

  class Uploader
  {

    protected static

      $errors = array(
                  0 => 'Upload succeeded.',
                  1 => 'File exceeded system maximum allowed size.',
                  2 => 'File exceeded application maximum allowed size.',
                  3 => 'Partial file received.',
                  4 => 'No file was uploaded.',
                  6 => 'Temporary directory missing.',
                  7 => 'Disk write error.',
                  8 => 'Aborted by system.'
                );

    protected

      $uploadDir,
      $allowedTypes,
      $lastError;

    public function __construct($params)
    {
      if (!is_dir($params->directory)) {
        $this->lastError =  "\"" . $params->directory . "\" is not a directory.";
      }
      $this->uploadDir    = $params->directory;
      $this->allowedTypes = (property_exists($params, 'allowedTypes')) ? $params->allowedTypes : array();
    } // __construct

    public function uploadFile($file)
    {
      $error = intval($file['error']);
      if ($error > 0) {
        $this->lastError = self::$errors[$error];
        @unlink($file['tmp_name']);
        return false;
      }
      if (!is_uploaded_file($file['tmp_name'])) {
        $this->lastError = "\"" . $file['tmp_name'] . "\" is not an uploaded file.";
        @unlink($file['tmp_name']);
        return false;
      }
      $finfo    = new finfo(FILEINFO_MIME_TYPE);
      $mimeType = strtok($finfo->file($file['tmp_name']), ';');
      if (!in_array($mimeType, $this->allowedTypes)) {
        $this->lastError = "Type \"$mimeType\" not allowed.";
        @unlink($file['tmp_name']);
        return false;
      }
      $filename = basename($file['name']);
      $path     = $this->uploadDir . "/" . $filename;
      if (@move_uploaded_file($file['tmp_name'], $path) === false) {
        $this->lastError = "The file could not be moved.";
        @unlink($file['tmp_name']);
        return false;
      }
      $hash = md5_file($path);
      $dest = $this->uploadDir . "/" . $hash;
      rename($path, $dest);
      return (object) array(
               'filename' => $filename,
               'mimeType' => $mimeType,
               'size'     => $file['size'],
               'hash'     => $hash,
               'path'     => $dest
             );
    } // uploadFile

    public function getLastError()
    {
      $message = $this->lastError;
      $this->lastError = "";
      return $message;
    } // getLastError

  } // Uploader

?>