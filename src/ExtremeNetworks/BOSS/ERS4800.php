<?php
    namespace OpenNetworkTools\Node\ExtremeNetworks\BOSS;

    use OpenNetworkTools\Node\ExtremeNetworks\BOSS;

    class ERS4800 extends BOSS {

        private $configReport;
        private $runningPortInterface = false;
        private $runningPortInterfaceName = null;
        private $runningSystemInfo = false;
        private $runningSystemUnit = null;
        private $runningSystemUptime = false;

        public function __construct() {
            parent::__construct();
        }

        public function analyseConfigFile($configFile, $configReport){
            $this->configReport = parent::analyseConfigFile($configFile, $configReport);
            foreach ($configFile as $k => $v){
                $this->analyseRunningInterfaces($k, $v);
                $this->analyseRunningSystem($k, $v);
            }

            $configReport = $this->configReport;
            $this->configReport = null;
            return $configReport;
        }

        private function analyseRunningInterfaces($key, $line){
            if(preg_match("#^\*\*\*\*\*Port Interface\*\*\*\*\*#", $line, $match)){
                $this->runningPortInterface = true;
                $this->configReport[$key] = true;
            } elseif($this->runningPortInterface && preg_match("#^Unit 1$#", $line)){
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
            } elseif($this->runningPortInterface && preg_match("#^Port:  ([0-9]+)#", $line, $match)){
                $this->runningPortInterfaceName = [
                    'fpc'   => 1,
                    'pic'   => 0,
                    'port'  => $match[1]
                ];
                $this->configReport[$key] = true;
            } elseif($this->runningPortInterface && !is_null($this->runningPortInterfaceName)){
                if(preg_match("#Admin Status:  (.*)#", $line, $match)){
                    preg_match("#([a-zA-Z]+)#", $match[1], $match);
                    if($match[1] == "Enable"){
                        $this->getOpenRunning()->getInterfaces()->addEthernet($this->runningPortInterfaceName['fpc'], $this->runningPortInterfaceName['pic'], $this->runningPortInterfaceName['port'])->setAdminStatus(true);
                    } else {
                        $this->getOpenRunning()->getInterfaces()->addEthernet($this->runningPortInterfaceName['fpc'], $this->runningPortInterfaceName['pic'], $this->runningPortInterfaceName['port'])->setAdminStatus(false);
                    }

                    $this->configReport[$key] = true;
                } elseif(preg_match("#^    Oper Status:  (.*)#", $line, $match)){
                    preg_match("#([a-zA-Z]+)#", $match[1], $match);
                    if($match[1] == "Up"){
                        $this->getOpenRunning()->getInterfaces()->addEthernet($this->runningPortInterfaceName['fpc'], $this->runningPortInterfaceName['pic'], $this->runningPortInterfaceName['port'])->setOperStatus(true);
                    } else {
                        $this->getOpenRunning()->getInterfaces()->addEthernet($this->runningPortInterfaceName['fpc'], $this->runningPortInterfaceName['pic'], $this->runningPortInterfaceName['port'])->setOperStatus(false);
                    }

                    $this->configReport[$key] = true;
                }
            }
        }

        private function analyseRunningSystem($key, $line){
            if(preg_match("#^\*\*\*\*\*SYS Info\*\*\*\*\*#", $line, $match)){
                $this->runningSystemInfo = true;
                $this->configReport[$key] = true;
            } elseif($this->runningSystemInfo && preg_match("#^Unit \#([0-9]+)#", $line, $match)){
                $this->getOpenRunning()->getSystem()->addStackUnit($match[1]);
                $this->runningSystemUnit = $match[1];
                $this->configReport[$key] = true;
            } elseif($this->runningSystemInfo && preg_match('#^Unit\# Switch Model     Unit UpTime#', $line)){
                $this->runningSystemUnit = null;
                $this->runningSystemUptime = true;
                $this->configReport[$key] = true;
            } elseif($this->runningSystemUnit && $this->runningSystemUnit){
                if(preg_match("#Serial Number:        (.*)#", $line, $match)){
                    $this->getOpenRunning()->getSystem()->addStackUnit($this->runningSystemUnit)->setSerialNumber($match[1]);
                    $this->configReport[$key] = true;
                } elseif(preg_match("#Power Status:         (.*)#", $line, $match)){
                    $this->getOpenRunning()->getSystem()->addStackUnit($this->runningSystemUnit)->setPowerStatus($match[1]);
                    $this->configReport[$key] = true;
                } elseif(preg_match("#Switch Model:         (.*)#", $line, $match)){
                    $this->getOpenRunning()->getSystem()->addStackUnit($this->runningSystemUnit)->setSwitchModel($match[1]);
                    $this->configReport[$key] = true;
                } elseif(preg_match("#Firmware Version:     (.*)#", $line, $match)){
                    $this->getOpenRunning()->getSystem()->addStackUnit($this->runningSystemUnit)->setVersionFirmware($match[1]);
                    $this->configReport[$key] = true;
                } elseif(preg_match("#Software Version:     (.*)#", $line, $match)){
                    $this->getOpenRunning()->getSystem()->addStackUnit($this->runningSystemUnit)->setVersionSoftware($match[1]);
                    $this->configReport[$key] = true;
                } elseif(preg_match("#Fan \#([0-9]) Status:        (.*)#", $line, $match)){
                    $this->getOpenRunning()->getSystem()->addStackUnit($this->runningSystemUnit)->addFanStatus($match[1], $match[2]);
                    $this->configReport[$key] = true;
                }
            } elseif($this->runningSystemInfo && $this->runningSystemUptime) {
                if(preg_match("#^$#", $line, $match)){
                    $this->runningSystemUptime = false;
                    $this->configReport[$key] = true;
                } elseif(preg_match("#^([0-9]) (.*) ([0-9]+ days, ([0-9\:]+))#", $line, $match)){
                    $this->getOpenRunning()->getSystem()->addStackUnit($match[1])->setSwitchUptime($match[3]);
                    $this->configReport[$key] = true;
                }
            } elseif($this->runningSystemInfo) {
                if(preg_match("#^\*\*\*\*\*MEMORY INFORMATION\*\*\*\*\*#", $line)){
                    $this->runningSystemInfo = false;
                    $this->configReport[$key] = true;
                } elseif(preg_match("#Operation Mode:        Switch#", $line)){
                    $this->getOpenRunning()->getSystem()->setSizeStack(1);
                } elseif(preg_match("#Size Of Stack:        ([0-9]+)#", $line, $match)){
                    $this->getOpenRunning()->getSystem()->setSizeStack($match[1]);
                    $this->configReport[$key] = true;
                } elseif(preg_match("#Base Unit:            ([0-9]+)#", $line, $match)){
                    $this->getOpenRunning()->getSystem()->setBaseUnitStack($match[1]);
                    $this->configReport[$key] = true;
                } elseif(preg_match("#sysName:              (.*)#", $line, $match)){
                    $this->getOpenRunning()->getSystem()->setSysName($match[1]);
                    $this->configReport[$key] = true;
                } elseif(preg_match("#Installed license:     (.*)#", $line, $match)){
                    $this->getOpenRunning()->getSystem()->addInstalledLicence($match[1]);
                    $this->configReport[$key] = true;
                } elseif(preg_match("#sysUpTime:            (.*)#", $line, $match)){
                    $this->getOpenRunning()->getSystem()->setSwitchUptime($match[1]);
                    if($this->getOpenRunning()->getSystem()->getSizeStack() == 1) $this->getOpenRunning()->getSystem()->addStackUnit(1)->setSwitchUptime($match[1]);
                    $this->configReport[$key] = true;
                }
            }
            if(preg_match("#^ ([0-9])\/ ([0-9]) ([0-9\.]+)#", $line, $match)){
                $this->getOpenRunning()->getSystem()->setMgmtIp($match[3]);
                $this->configReport[$key] = true;
            }
        }

    }