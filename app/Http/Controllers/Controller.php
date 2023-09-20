<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function setHeader(){
          // Envoi de l'en-tête Content-Type pour spécifier que la réponse est une chaîne JSON
          header('Content-Type: application/json');
          header('Access-Control-Allow-Origin: *');
  
    }
}
