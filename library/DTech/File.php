<?php

/**
 * File Class is responsible
 * to retreive all the information about the
 * file or directory
 * @package DTEch Framework
 * @author Eng. Mohammed Yehia
 * @copyright Dahab TEchnology 2011
 * @version 1 Sunday 24th July 2011, 10:20 pm
 */
final class File
{

    /**
     * The file/directory name (without extension)
     * @access public
     * @var string
     */
    public $fileName;

    /**
     * The file/directory name (with extension if exists)
     * @access public
     * @var string
     */
    public $fileBaseName;

    /**
     * The directory that contains the file / directory
     * @access public
     * @var string
     */
    public $fileDir;

    /**
     * The file extension if exists
     * @access public
     * @var string
     */
    public $fileExtension;

    /**
     * The file/directory size
     * @access public
     * @var int
     */
    public $fileSize;

    /**
     * The file/directory type
     * @access public
     * @var string
     */
    public $fileType;

    /**
     * The file/directory permissions
     * @access public
     * @var array
     */
    public $filepermissions = array();

    /**
     * The file/directory last accessed time
     * @access public
     * @var string
     */
    public $fileLastAccessed;

    /**
     * The file/directory last changed time
     * @access public
     * @var string
     */
    public $fileLastChanged;

    /**
     * The file/directory last modified time
     * @access public
     * @var string
     */
    public $fileLastModified;

    /**
     * The file/directory Owner
     * @access public
     * @var string
     */
    public $fileOwner;

    /**
     * The file MIME Type
     * @access public
     * @var string
     */
    public $fileMimeType;

    /**
     * Class constructor builds a list of information
     * about the file / directory
     * @return void
     */
    function __construct ($file)
    {
        $this->_statFileFolder($file);
    }

    function __toString ()
    {
        $output = "<p><b>File:</b> " . $this->fileBaseName . "</p>";
        $output .= "<p><b>File Name:</b> " . $this->fileName . "</p>";
        $output .= "<p><b>Directory:</b> " . $this->fileDir . "</p>";
        $output .= "<p><b>Type:</b> " . $this->fileType . "</p>";
        $output .= "<p><b>Size:</b> " . $this->fileSize . "</p>";
        if ($this->fileType !== 'dir') {
            $output .= "<p><b>Extension:</b> " . $this->fileExtension . "</p>";
        }
        $output .= "<p><b>Permissions:</b> ";
        foreach ($this->filepermissions as $permission => $value) {
            if (true === $value) {
                $output .= ucfirst($permission) . " ";
            }
        }
        $output .= "</p>";
        $output .= "<p><b>Owner:</b> " . $this->fileOwner . "</p>";
        $output .= "<p><b>Last Accessed:</b> " .
         datetimeToText($this->fileLastAccessed) . "</p>";
        $output .= "<p><b>Last Changed:</b> " .
         datetimeToText($this->fileLastChanged) . "</p>";
        $output .= "<p><b>Last Modified:</b> " .
         datetimeToText($this->fileLastModified) . "</p>";
        if ($this->fileType === 'file') {
            $output .= "<p><b>MIME Type:</b> " . $this->fileMimeType . "</p>";
        }
        return $output;
    }

    /**
     * Retreives the most important file set
     * of information
     * @param file/directory $file
     * @return void
     */
    private function _statFileFolder ($file)
    {
        $fileInfo = $this->_getPathInfo($file);
        $this->fileName = $fileInfo['filename'];
        $this->fileBaseName = $fileInfo['basename'];
        $this->fileDir = realpath($fileInfo['dirname']);
        $this->fileType = $this->_getFileType($file);
        if ($this->fileType !== 'dir') {
            $this->fileExtension = $fileInfo['extension'];
        }
        if ($this->fileType === 'dir') {
            $this->fileSize = $this->_getDirSize($file);
        } else {
            $this->fileSize = $this->_getFileSize($file);
        }
        $permissions = array();
        if ($this->isReadable($file)) {
            $permissions['readable'] = $this->isReadable($file);
        }
        if ($this->isWritable($file)) {
            $permissions['writable'] = $this->isWritable($file);
        }
        if ($this->isExecutable($file)) {
            $permissions['executable'] = $this->isExecutable($file);
        }
        $this->filepermissions = $permissions;
        $this->fileOwner = $this->_getFileOwner($file);
        $this->fileLastAccessed = $this->_lastAccessed($file);
        $this->fileLastChanged = $this->_lastChanged($file);
        $this->fileLastModified = $this->_lastModified($file);
        if($this->fileType === 'file') {
            $this->fileMimeType = $this->_getFileMimeType($file);
        }
    }

    /**
     * Get all the file/directory permissions
     * @param file/directory $file
     * @return int
     */
    public function getFilePermissions ($file)
    {
        return fileperms($file);
    }

    /**
     * Check if the file/directory is readable and exists
     * @param file/directory $file
     * @return boolean
     */
    private function isReadable ($file)
    {
        return is_readable($file);
    }

    /**
     * Check if the file/directory is writable and exists
     * @param file/directory $file
     * @return boolean
     */
    private function isWritable ($file)
    {
        return is_writable($file);
    }

    /**
     * Check if the file/directory is executable and exists
     * @param file/directory $file
     * @return boolean
     */
    private function isExecutable ($file)
    {
        return is_executable($file);
    }

    /**
     * Returns the last time the file was accessed
     * @param file/directory $file
     */
    private function _lastAccessed ($file)
    {
        return fileatime($file);
    }

    /**
     * Returns the last time the file was changed
     * @param file/directory $file
     */
    private function _lastChanged ($file)
    {
        return filectime($file);
    }

    /**
     * Returns the last time the file was changed
     * @param file/directory $file
     */
    private function _lastModified ($file)
    {
        return filemtime($file);
    }

    /**
     * A file/directory array of information
     * @param file/directory $file
     * @return array
     */
    private function _getPathInfo ($file)
    {
        return pathinfo($file);
    }

    /**
     * Get file/directory type
     * @param file/directory $file
     * @return string
     */
    private function _getFileType ($file)
    {
        return filetype($file);
    }

    /**
     * Get file/directory MIME type
     * @param file/directory $file
     * @return string
     */
    private function _getFileMimeType($file)
    {
        return mime_content_type($file);
    }

    /**
     * Get file/directory Owner
     * @param file/directory $file
     * @return string
     */
    private function _getFileOwner($file)
    {
        $fileOwnerArray = posix_getpwuid(fileowner($file));
        return $fileOwnerArray['name'];
    }

    /**
     * Get file size
     * @param file $file
     * @return string
     */
    private function _getFileSize ($file)
    {
        $size = filesize($file);
        return $this->_calculateSize($size);
    }

    /**
     * Get directory size
     * @param directory $dir
     * @return string
     */
    private function _getDirSize ($dir)
    {
        $size = 0;
        if (is_dir($dir)) {
            $handle = opendir($dir);
            if ($handle) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
                        if (is_file($dir . DS . $file)) {
                            $size += filesize($dir . DS . $file);
                        }
                        // Recursion starts here
                        if (is_dir($dir . DS . $file)) {
                            $size += $this->_getDirSize($file);
                        }
                    }
                }
            } else {
                throw new Exception("Cannot open the directory.");
            }
        } else {
            throw new Exception("This is not a dirctory.");
        }
        return $this->_calculateSize($size);
    }

    /**
     * Calculate the size in (Bytes/KiloBytes/MegaBytes)
     * @param int $size
     * @return string $size
     */
    private function _calculateSize ($size)
    {
        if ($size < 1024) {
            $size = $size . " Bytes";
        } elseif ($size < 1048576) {
            $sizeInKB = $size / 1024;
            $size = round($sizeInKB, 1) . " KB";
        } else {
            $sizeInMB = $size / (1024 * 1024);
            $size = round($sizeInMB, 1) . " Mb";
        }
        return $size;
    }
}
?>