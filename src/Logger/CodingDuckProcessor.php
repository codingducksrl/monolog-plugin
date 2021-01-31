<?php

namespace Codingduck\Logger;
use Monolog\Processor\ProcessorInterface;
use Ramsey\Uuid\Uuid;

class CodingDuckProcessor implements ProcessorInterface{

    private string $clientID;
    private string $clientSecret;
    private string $projectRoot;
    private string $transaction;
    private $session ;

    public function __construct($clientID,$clientSecret,$projectRoot,$autoSession) {
        $this->clientID = $clientID;
        $this->clientSecret = $clientSecret;
        $this->projectRoot = $projectRoot;
        $this->transaction = Uuid::uuid4()->toString();
        $this->session = null;
        if($autoSession){
            session_name("coding_duck_logger_session");
            session_start();
            if(!isset($_SESSION["sid"])){
                $_SESSION["sid"] = \Ramsey\Uuid\Uuid::uuid4()->toString();
            }
            session_write_close();
            $this->session = $_SESSION["sid"];
        }
    }

    public function __invoke(array $record) {

        $raw = $record["context"];

        $raw["level"] = $record["level_name"];
        $raw["message"] = $record["message"];
        $raw["timestamp"] = $record["datetime"];//.format(DateTimeInterface::RFC3339_EXTENDED);
        $raw["id"] = Uuid::uuid4()->toString();
        $raw["transaction"] = $this->transaction;

        if(isset($raw["session"])){
            $this->session = $raw["session"];
        }elseif ($this->session != null){
            $raw["session"] = $this->session;
        }

        if(isset($raw["errorObject"]) && $raw["errorObject"] instanceof \Throwable){
            $error = $raw["errorObject"];
            $trace = $error->getTrace();
            $final = [];
            try{

                foreach ($trace as $line){
                    $final[] = [
                        "filename" => str_replace($this->projectRoot,"",$line["file"]),
                        "function" => $line["function"],
                        "line" => $line["line"],
                        "class" => $line["class"],
                        "column" => 0
                    ];
                }
            }catch (\Exception $e){

            }

            $raw["errorObject"] = [
                "message" => $error->getMessage(),
                "code" => $error->getCode()
            ];

            $raw["location"] = [
                "filename" => str_replace($this->projectRoot,"",$error->getFile()),
                "line" => $error->getLine(),
                "column" => 0,
                "fullstack" => $final
            ];

            //unset($raw["errorObject"]);
        }else{
            $trace = debug_backtrace();

            $final = [];

            $register = false;

            foreach ($trace as $item) {
                if($register && isset($item["file"],$item["function"],$item["line"],$item["class"])){
                    $final[] = [
                        "filename" => str_replace($this->projectRoot,"",$item["file"]),
                        "function" => $item["function"],
                        "line" => $item["line"],
                        "class" => $item["class"],
                        "column" => 0
                    ];
                }elseif (isset($item["file"]) && strpos($item["file"],"vendor/illuminate/support/Facades/Facade.php") !== false){
                    $register = true;
                }


            }
            if(count($final) > 0){
                $raw["location"] = [
                    "filename" => $final[0]["filename"],
                    "line" => $final[0]["line"],
                    "column" => $final[0]["column"],
                    "fullstack" => $final
                ];
            }

        }

        return [
            "log" => base64_encode(json_encode($raw)),
            "authentication" => [
                "logID" => $raw["id"],
                "clientID" => $this->clientID,
                "clientSecret" => hash("sha512",$raw["id"].$this->clientSecret )
            ],
            "level" => $record["level"],
            "extra" => [],
            "context" => []
        ];

    }
}
