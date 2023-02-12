<?php
    namespace OpenNetworkTools\Node\ExtremeNetworks\BOSS;

    use OpenNetworkTools\Node\ExtremeNetworks\BOSS;

    class ERS4500 extends BOSS {

        public function __construct() {
            parent::__construct();
        }

        public function analyseConfigFile($configFile, $configReport){
            $configReport = parent::analyseConfigFile($configFile, $configReport);
            $configReport[0] = true;
            return $configReport;
        }

    }