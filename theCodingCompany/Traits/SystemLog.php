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

trait SystemLog
{

    /**
     * Log message to the systemlog
     * @param string $message
     * @return boolean
     */
    private function log($message = "")
    {
        return syslog(LOG_INFO, $message);
    }
}