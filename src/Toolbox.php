<?php
    namespace OpenNetworkTools\Node;

    use OpenNetworkTools\Node\ExtremeNetworks\BOSS\ERS3500;
    use OpenNetworkTools\Node\ExtremeNetworks\BOSS\ERS4500;
    use OpenNetworkTools\Node\ExtremeNetworks\BOSS\ERS4800;
    use OpenNetworkTools\Node\ExtremeNetworks\VOSS\VSP4450;
    use OpenNetworkTools\Node\ExtremeNetworks\VOSS\VSP7200;
    use OpenNetworkTools\OpenNode;

    class Toolbox {

        static function determineModel($configFile){
            $model = "none";
            foreach ($configFile as $k => $v){
                if(preg_match("#Switch Model: (.*)#", $v, $match)){
                    $match = str_replace(" ", "", $match);
                    preg_match("#([a-zA-Z0-9\-\+]+)#", $match[1], $match);

                    if($match[1] == "3510GT-PWR+") $model = "extremenetworks-boss-3500";
                    elseif($match[1] == "3524GT-PWR+") $model = "extremenetworks-boss-3500";
                    elseif($match[1] == "4548GT-PWR") $model = "extremenetworks-boss-4500";
                    elseif($match[1] == "4826GTS-PWR+") $model = "extremenetworks-boss-4800";
                    elseif($match[1] == "4850GTS") $model = "extremenetworks-boss-4800";
                    elseif($match[1] == "4850GTS-PWR+") $model = "extremenetworks-boss-4800";
                } elseif(preg_match("#^	ModelName          : (.*)#", $v, $match)){
                    $match = str_replace(" ", "", $match);
                    if($match[1] == "4450GSX-PWR+") $model = "extremenetworks-voss-4450";
                    elseif($match[1] == "7254XTQ") $model = "extremenetworks-voss-7200";
                    elseif($match[1] == "7254XSQ") $model = "extremenetworks-voss-7200";
                }
            }

            return $model;
        }

        static function initModel($model){
            if($model == "none") return new OpenNode();
            elseif($model == "extremenetworks-boss-3500") return new ERS3500();
            elseif($model == "extremenetworks-boss-4500") return new ERS4500();
            elseif($model == "extremenetworks-boss-4800") return new ERS4800();
            elseif($model == "extremenetworks-voss-4450") return new VSP4450();
            elseif($model == "extremenetworks-voss-7200") return new VSP7200();
            else return new OpenNode();
        }

    }