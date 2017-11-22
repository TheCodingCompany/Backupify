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

use theCodingCompany\TarGZ;

class Backupify
{
    use Traits\NotifyApi;
    use Traits\SystemLog;

    /**
     * The URL to the Backupify API
     * @var string
     */
    private $api_url = "http://backup.thecodingcompany.se/api";

    /**
     * Location of the backup
     * @var string
     */
    private $backup_path = null;

    /**
     * Date and time
     * @var string
     */
    private $datetime = null;

    /**
     * Filename of the backup file
     * @var string
     */
    private $filename = null;

    /**
     * Settings from .env file in the root folder
     * @var string
     */
    private $settings = [];

    /**
     * The public IP address of the current machine
     * @var string
     */
    private $ip_address = null;

    /**
     * API Token for accessing api.backupify.io
     * @var string
     */
    private $api_token = null;

    /**
     * MySQL password
     * @var string
     */
    private $mysql_password = null;

    /**
     * MySQL user
     * @var string
     */
    private $mysql_user = "root";

    /**
     * Construct new class
     * @param string $ipaddress Valid public IP of the current machine
     * @param string $api_token Backupify API token
     * @param string $backup_path Where to store the backup
     */
    public function __construct($ipaddress = "", $backup_path = ".")
    {        
        if (filter_var($ipaddress, FILTER_VALIDATE_IP)) {
            $this->ip_address = $ipaddress;
        } else {
            throw new \Exception("Invalid IP address.");
        }

        $this->ini_settings();

        $this->filename     = "MySQL_{$this->datetime}_";
        $this->backup_path  = $backup_path;
        
        if(isset($this->settings["api_token"])){
            $this->api_token = $this->settings["api_token"];
        }else{
            throw new \Exception("No API token found.");
        }
    }

    /**
     * Set MySQL database password
     * @param string $password MySQL DB password
     * @return \theCodingCompany\Backupify
     */
    public function setMySQLPassword($password = "")
    {
        $this->mysql_password = $password;

        return $this;
    }

    /**
     * Set MySQL user
     * @param string $user
     * @return \theCodingCompany\Backupify
     */
    public function setMySQLUser($user = "")
    {
        $this->mysql_user = $user;

        return $this;
    }

    /**
     * Backup MySQL database
     * @param string $database The name of the database or null for all
     */
    public function backupMySQL($database = null)
    {
        if(is_array($database)){
            $database_list = join(" ", $database);
            $this->filename .= "multi-dbs.sql";
        }elseif(!empty($database)){
            $database_list = $database;
            $this->filename .= "{$database}.sql";
        }else{
            $database_list = "-A"; //All databases
            $this->filename .= "ALL.sql";
        }

        $res = $this->system_exec("mysqldump -u{$this->mysql_user} -p{$this->mysql_password} --add-drop-database --add-drop-table --opt -r {$this->backup_path}/{$this->filename} {$database_list} 2>&1");
        if(!$res){
            $this->log("Error while creating backup.");
            
            throw new \Exception("Error while creating backup.");
        }

        return $this;
    }

    /**
     * Compress the backup with Tar and GZ
     * @return boolean|\theCodingCompany\Backupify
     */
    public function compressBackup()
    {
        $targz = new TarGZ("{$this->backup_path}/{$this->filename}");
        $gz_filename = $targz->compress();
        if($gz_filename !== FALSE){

            if(!unlink($this->backup_path."/".$this->filename)){
                $this->log("Unable to remove file {$this->filename}.");
            }
            $this->filename = $gz_filename;

            return $this;
        }
        
        return false;
    }

    /**
     * Execute shell command
     * @param type $command
     */
    private function system_exec($command = "")
    {
        exec($command, $output, $retval);        
        if ($retval === 0) {
            return true;
        } else {
            $this->log("Error while executing command. {$command} ".print_r($output, true));

            throw new \Exception("Error while executing command. ".print_r($output, true));
        }
    }

    /**
     * Init settings file
     * @return boolean
     * @throws \Exception
     */
    private function ini_settings()
    {
        if (file_exists(".env")) {

            $this->settings = parse_ini_file(".env");
            $this->datetime = date("YmdHis");

            return true;
        }
        throw new \Exception(".env file missing.");
    }

    /**
     * Destruct class
     */
    public function __destruct()
    {
        $this->api_token        = null;
        $this->mysql_password   = null;
        $this->mysql_user       = null;
    }
}
