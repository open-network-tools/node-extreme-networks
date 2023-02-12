<?php
    namespace OpenNetworkTools\Node;

    use OpenNetworkTools\Node\ExtremeNetworks\BOSS\ERS4800;
    use OpenNetworkTools\OpenNode;

    class Toolbox {

        static function determineModel($configFile){
            $model = "none";
            foreach ($configFile as $k => $v){
                if(preg_match("#^Switch Model: (.*)#", $v, $match)){
                    $match = str_replace(" ", "", $match);
                    if($match[1] == "4850GTS") $model = "extremenetworks-boss-4800";
                }
            }

            return $model;
        }

        static function initModel($model){
            if($model == "none") return new OpenNode();
            elseif($model == "extremenetworks-boss-4800") return new ERS4800();
            else return new OpenNode();
        }

    }