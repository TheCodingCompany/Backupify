<?php
/**
 * Intellectual Property of Svensk Coding Company AB - Sweden All rights reserved.
 * 
 * @copyright (c) 2016, Svensk Coding Company AB
 * @author V.A. (Victor) Angelier <victor@thecodingcompany.se>
 * @version 1.0
 * @license http://www.apache.org/licenses/GPL-compatibility.html GPL
 * 
 */

namespace theCodingCompany;

class TarGZ
{
    /**
     * The file we work with
     * @var type
     */
    protected $thefile = null;
    
    /**
     * File info
     * @var type 
     */
    protected $finfo = null;

    public function __construct($file)
    {
        $this->thefile  = $file;
        $this->finfo    = pathinfo($this->thefile);
    }

    /**
     * Tar/GZ file
     * @return boolean
     */
    public function compress(){
        if($this->tar() !== FALSE){
            $filename = $this->gzip();
            if($filename !== FALSE){
                return $filename;
            }
        }

        return false;
    }

    /**
     * Get the filename without extension
     * @return type
     */
    private function stripname(){
        return $this->finfo["filename"];
    }

    /**
     * Return the file path
     * @return type
     */
    private function filepath(){
        return $this->finfo["dirname"];
    }

    /**
     * Get the filename with extension
     * @return type
     */
    private function filename(){
        return $this->finfo["basename"];
    }

    /**
     * Log messages to system log
     * @param type $data
     */
    private function log($data){
        syslog(LOG_INFO, $data);
    }

    /**
     * Tar the file
     */
    private function tar(){
        exec("tar -cf {$this->filepath()}/{$this->stripname()}.tar -C {$this->filepath()} {$this->filename()} 2>&1", $output, $retval);
        if($retval === 0){
            return $this;
        }

        $this->log("Failed to create TAR archive. " . print_r($output, true));
        return false;
    }

    /**
     * GZIP archive
     */
    private function gzip(){
        exec("gzip -9 {$this->filepath()}/{$this->stripname()}.tar 2>&1", $output, $retval);
        if($retval === 0){
            return $this->stripname().".tar.gz";
        }

        $this->log("Failed to create GZ archive. " . print_r($output, true));
        return false;
    }
}