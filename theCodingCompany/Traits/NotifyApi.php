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

namespace theCodingCompany\Traits;

use theCodingCompany\HttpRequest;

trait NotifyApi
{
    /**
     *
     * @global string $mail_from Via Global
     * @global string $mail_to Via Global
     * @param type $filepath Path to the file without leading /
     * @param type $filename Filename
     * @param type $ip IP of this server
     * @param type $token Your API token
     */
    public function notify()
    {
        $r          = new HttpRequest("/", $this->api_url);
        $headers    = array(
            "Content-Type" => "application/json",
            "Authorization" => $this->api_token
        );
        $parameters = [
            "ipaddress" => $this->ip_address,
            "filepath"  => $this->backup_path,
            "filename"  => $this->filename,
            "filesize"  => filesize($this->backup_path."/".$this->filename),
            "filehash"  => hash_file("sha256", $this->backup_path."/".$this->filename)
        ];

        $data = $r->Post("backup", $headers, $parameters);
        if ($data["status"] === TRUE || $data["status"] === 1) {

            $this->log("API successfull.");
            return true;
        }

        $this->log("Server did not received our status update.");

        $body = "Backup API did not successfull process our request.\r\n"
            .print_r($data, true);

        @mail(
            $this->settings["mail_to"],
            "Backup success, API failed",
            $body,
            null,
            "-f{$this->settings["mail_from"]}"
        );

        return false;
    }
}