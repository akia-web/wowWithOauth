<?php

namespace App\Entity;

class TokenBnet{
    public $token2;

    public function __construct($token2){
      $this->$token2=$token2;
    }

}