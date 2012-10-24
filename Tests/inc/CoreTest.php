<?php
define("APPLICATION_PATH", "/usr/local/www/svirel.akmain/htdocs");
define("DS", "/");
require "/usr/local/www/svirel.akmain/htdocs/conf/config.php";
require "/usr/local/www/svirel.akmain/htdocs/inc/Core.php";
	class CoreTest extends PHPUnit_Framework_TestCase {

        public function testGetTop100() {

            $this->assertArrayHasKey("response",Core::GetTop100());
        }
        public function testSearch() {

            $this->assertArrayHasKey("response",Core::GetTop100());
        }
        /*public function testSearch() {

            $this->assertArrayHasKey("response",Core::GetTop100());
        }*/
        public function testTakeToken{

        }

    }