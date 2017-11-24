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

require_once "autoload.php";

$backup = new theCodingCompany\Backupify("my-public-ip", "/home/backups");

/*
$backup->setMySQLPassword("SuperSecretRootPassword")
    ->backupMySQL() //Or a single DB
    ->compressBackup() //Tar and GZIP compress
    ->notify(); //Send 'ready' notification to the Backupify API
*/

$backup->addPath("/home/path/to/folder1")
    ->addPath("/home/path/to/folder2")
    ->backup("MyFilesBackup.tar")
    ->notify();