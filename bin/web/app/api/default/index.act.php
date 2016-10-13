<?php

class Act_Index extends Page{

    public function __construct(){
        exit();
        Logger::i("REQUEST:".var_export($_REQUEST, TRUE));
        Logger::i("POST:".var_export($_POST, TRUE));
        Logger::i("GET:".var_export($_GET, TRUE));
    }

}
