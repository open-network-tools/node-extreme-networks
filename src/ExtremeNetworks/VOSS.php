<?php
    namespace OpenNetworkTools\Node\ExtremeNetworks;

    use OpenNetworkTools\Node\ExtremeNetworks;

    class VOSS extends ExtremeNetworks {

        public function __construct() {
            parent::__construct();
        }

        public function analyseConfigFile($configFile, $configReport){
            $configReport = parent::analyseConfigFile($configFile, $configReport);
            return $configReport;
        }

    }