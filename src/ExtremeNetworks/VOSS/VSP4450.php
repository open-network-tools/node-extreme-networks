<?php
    namespace OpenNetworkTools\Node\ExtremeNetworks\VOSS;

    use OpenNetworkTools\Node\ExtremeNetworks\VOSS;

    class VSP4450 extends VOSS {

        private $configReport;
        private $showAutotopology = false;
        private $showSoftware = false;
        private $showSpanningTreeBPDUGuard = false;
        private $showSysInfo = false;

        public function __construct() {
            parent::__construct();
        }

        public function analyseConfigFile($configFile, $configReport){
            $this->configReport = parent::analyseConfigFile($configFile, $configReport);
            foreach ($configFile as $k => $v){
                $this->analyseShowAutotopology($k, $v);
                $this->analyseShowSpanningTreeBPDUGuard($k, $v);
                $this->analyseShowSysInfo($k, $v);
            }

            $configReport = $this->configReport;
            $this->configReport = null;
            return $configReport;
        }

        private function analyseShowAutotopology($key, $line){
            if(preg_match("#^Command:(.*) show autotopology nmm-table#", $line, $match)){
                $this->showAutotopology = true;
                $this->configReport[$key] = true;
            } elseif(preg_match("#^Command:#", $line, $match)){
                $this->showAutotopology = false;
                $this->configReport[$key] = true;
            } elseif($this->showAutotopology){
                if(preg_match("#^0/0      ([0-9\.]+)#", $line, $match)){
                    $this->getOpenRunning()->getSystem()->setMgmtIp($match[1]);
                    $this->configReport[$key] = true;
                } elseif(preg_match("#^1/([0-9]+)([\ ]+)([0-9\.]+)([\ ]+)([a-z0-9]+)([\ ]+)([a-z0-9]+)([\ ]+)([a-zA-Z0-9\-\+]+)#", $line, $match)){
                    $this->getOpenRunning()->getInterfaces()->addTopology($match[9]);
                    $this->configReport[$key] = true;
                }
            }
        }

        private function analyseShowSysInfo($key, $line){
            if(preg_match("#^Command:(.*) show sys-info#", $line, $match)){
                $this->showSysInfo = true;
                $this->configReport[$key] = true;
            } elseif(preg_match("#^Command:(.*) show software#", $line, $match)){
                $this->showSoftware = true;
                $this->configReport[$key] = true;
            } elseif(preg_match("#^Command:#", $line, $match)){
                $this->showSoftware = false;
                $this->showSysInfo = false;
                $this->configReport[$key] = true;
            } elseif($this->showSoftware){
                if(preg_match("#(.*) \(Primary Release\)#", $line, $match)){
                    $this->getOpenRunning()->getSystem()->addStackUnit(1)->setVersionSoftware($match[1]);
                    $this->getOpenRunning()->getSystem()->addStackUnit(1)->setVersionFirmware("-");
                    $this->configReport[$key] = true;
                }
            } elseif($this->showSysInfo){
                if(preg_match("#^	Serial\#            : (.*)#", $line, $match)){
                    $this->getOpenRunning()->getSystem()->setBaseUnitStack(1);
                    $this->getOpenRunning()->getSystem()->setSizeStack(1);
                    $this->getOpenRunning()->getSystem()->addStackUnit(1)->setSerialNumber($match[1]);
                    $this->configReport[$key] = true;
                } elseif(preg_match("#	SysUpTime    : (.*)#",$line, $match)){
                    $this->getOpenRunning()->getSystem()->setSwitchUptime($match[1]);
                    $this->getOpenRunning()->getSystem()->addStackUnit(1)->setSwitchUptime($match[1]);
                    $this->configReport[$key] = true;
                } elseif(preg_match("#	SysName      : (.*)#",$line, $match)){
                    $this->getOpenRunning()->getSystem()->setSysName($match[1]);
                    $this->configReport[$key] = true;
                } elseif(preg_match("#	Chassis            : (.*)#",$line, $match)){
                    $this->getOpenRunning()->getSystem()->addStackUnit(1)->setSwitchModel($match[1]);
                    $this->configReport[$key] = true;
                }
            }
        }

        private function analyseShowSpanningTreeBPDUGuard($key, $line){
            if(preg_match("#^Command:(.*) show spanning-tree bpduguard#", $line, $match)){
                $this->showSpanningTreeBPDUGuard = true;
                $this->configReport[$key] = true;
            } elseif(preg_match("#^Command:#", $line, $match)){
                $this->showSpanningTreeBPDUGuard = false;
                $this->configReport[$key] = true;
            } elseif($this->showSpanningTreeBPDUGuard){
                if(preg_match("#^([0-9]+)\/([0-9]+)(.*)(Up|Down)(.*)(Up|Down)#", $line, $match)){
                    $this->getOpenRunning()->getInterfaces()->addEthernet($match[1], 0, $match[2])->setAdminStatus($match[4] == "Up" ? true : false);
                    $this->getOpenRunning()->getInterfaces()->addEthernet($match[1], 0, $match[2])->setOperStatus($match[6] == "Up" ? true : false);
                }
            }
        }

    }