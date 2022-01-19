<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\User;
use Illuminate\Http\Request;

class CardsAndCollectionsController extends Controller
{
    
    public function register (Request $req) {

        $answer = ['status' => 1, 'msg' => ''];
        
        $dataCard = $req -> getContent();

        // Lo escribo en la base de datos
        try {

            // Valido los datos recibidos del json
            $dataCard = json_decode($dataCard);

            // Creo una nueva carta con los datos correspondientes
            $card = new Card();

            $card -> name = $dataCard -> name;
            $card -> description = $dataCard -> description;
            $card -> collection = $dataCard -> collection;

            $card -> save();

            $answer['msg'] = "Card registered correctly";
            }

        catch(\Exception $e) {
            $answer['msg'] = $e -> getMessage();
            $answer['status'] = 0;
        }

        return response()-> json($answer); 
    }

}
