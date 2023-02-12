<?php
    namespace OpenNetworkTools\Node;

    use OpenNetworkTools\OpenNode;

    class ExtremeNetworks extends OpenNode {

        public function __construct() {
            parent::__construct();
        }

        public function analyseConfigFile($configFile, $configReport){
            $configReport = parent::analyseConfigFile($configFile, $configReport);
            return $configReport;
        }

    }