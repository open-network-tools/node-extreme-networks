<?php
    namespace OpenNetworkTools\Node\ExtremeNetworks\BOSS;

    use OpenNetworkTools\Node\ExtremeNetworks\BOSS;

    class ERS4800 extends BOSS {

        private $configReport;
        private $runningPortInterface = false;
        private $runningPortInterfaceName = null;

        public function __construct() {
            parent::__construct();
        }

        public function analyseConfigFile($configFile, $configReport){
            $this->configReport = parent::analyseConfigFile($configFile, $configReport);
            foreach ($configFile as $k => $v){
                $this->analyseRunningInterfaces($k, $v);
            }

            $configReport = $this->configReport;
            $this->configReport = null;
            return $configReport;
        }

        private function analyseRunningInterfaces($key, $line){
            if(preg_match("#^\*\*\*\*\*Port Interface\*\*\*\*\*#", $line, $match)){
                $this->runningPortInterface = true;

                $this->configReport[$key] = true;
            } elseif(preg_match("#^Unit 1$#", $line)){
                $this->runningPortInterface = false;
                $this->runningPortInterfaceName = null;

                $this->configReport[$key] = true;
            } elseif($this->runningPortInterface && preg_match("#^Unit\/Port:  ([0-9]+)\/([0-9]+)#", $line, $match)){
                $this->runningPortInterfaceName = [
                    'fpc'   => $match[1],
                    'pic'   => 0,
                    'port'  => $match[2]
                ];

                $this->configReport[$key] = true;
            } elseif($this->runningPortInterface && !is_null($this->runningPortInterfaceName)){
                if(preg_match("#^    Admin Status:  (.*)#", $line, $match)){
                    $match = str_replace(" ", "", $match);
                    if($match[1] == "Enable"){
                        $this->getOpenRunning()->getInterfaces()->addEthernet($this->runningPortInterfaceName['fpc'], $this->runningPortInterfaceName['pic'], $this->runningPortInterfaceName['port'])->setAdminStatus(true);
                    } else {
                        $this->getOpenRunning()->getInterfaces()->addEthernet($this->runningPortInterfaceName['fpc'], $this->runningPortInterfaceName['pic'], $this->runningPortInterfaceName['port'])->setAdminStatus(false);
                    }

                    $this->configReport[$key] = true;
                } elseif(preg_match("#^    Oper Status:  (.*)#", $line, $match)){
                    $match = str_replace(" ", "", $match);
                    if($match[1] == "Up"){
                        $this->getOpenRunning()->getInterfaces()->addEthernet($this->runningPortInterfaceName['fpc'], $this->runningPortInterfaceName['pic'], $this->runningPortInterfaceName['port'])->setOperStatus(true);
                    } else {
                        $this->getOpenRunning()->getInterfaces()->addEthernet($this->runningPortInterfaceName['fpc'], $this->runningPortInterfaceName['pic'], $this->runningPortInterfaceName['port'])->setOperStatus(false);
                    }

                    $this->configReport[$key] = true;
                }
            }
        }

    }