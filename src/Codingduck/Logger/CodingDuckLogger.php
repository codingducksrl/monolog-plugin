<?php

namespace Codingduck\Logger;

use Monolog\Logger;

class CodingDuckLogger {

    public function __invoke(array $config): Logger {

        $logger = new Logger('mainLogger');

        $clientCredentials = json_decode(file_get_contents($config["credentials"]),true);

        $autoSession = isset($config["autoSession"]) ? $config["autoSession"] : false;

        try {
            $logger->pushProcessor(new CodingDuckProcessor($clientCredentials["clientID"], $clientCredentials["clientSecret"], $config["projectRoot"], $autoSession));

            $host = $config["host"];
            $port = intval("".$config["port"]);

            $logger->pushHandler(new CodingDuckHandler("tls://$host:$port", [
                "ca" => $config["ca"],
                "cert" => $config["cert"],
                "key" => $config["key"]
            ]));


        } catch (\Exception $e) {
            error_log("Err ".$e->getMessage()." ".$e->getLine());
        }
        return $logger;
    }
}
