<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Collection;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class CardsAndCollectionsController extends Controller
{
    
    /////// CARDS ///////

    public function register (Request $req) {

        $answer = ['status' => 1, 'msg' => ''];
        
        $dataCard = $req -> getContent();
        $user = $req->user;

        // Lo escribo en la base de datos
        try {

            // Valido los datos recibidos del json
            $dataCard = json_decode($dataCard);

            // Creo una nueva carta con los datos correspondientes
            $card = new Card();

            $card -> name = $dataCard -> name;
            $card -> description = $dataCard -> description;
            
            // Para introducir el id del usuario que pone a la venta la carta, lo cojo directamente del propio usuario logeado
            $card -> user_id = $user->id;
            
            // Para guardar la colección compruebo que previamente exista, si no existe creo una nueva colección
            if(Collection::where('name', $dataCard -> collection)->first()) {
    
                $deck = DB::table('collections')->where('name', $dataCard -> collection)->first();
                $card -> collection = $dataCard -> collection;
                $card -> collection_id = $deck->id;
    
                $card -> save();


                $answer['msg'] = "Card registered correctly";
            } else {

                $collection = new Collection();
                $collection -> name = $dataCard -> collection;
                $collection -> symbol = "defaultImage.jpg";
                $collection -> edition_date = date('Y-m-d');
                $collection -> save();


                $card -> collection = $dataCard -> collection;
                $card -> collection_id = $collection -> id;

                $card -> save();


                $answer['msg'] = "Card and collection registered correctly";
            }

        } catch(\Exception $e) {
            $answer['msg'] = $e -> getMessage();
            $answer['status'] = 0;
        }

        return response()-> json($answer);
    }



    ////// COLLECTIONS //////

    public function registerCollection (Request $req) {

        $answer = ['status' => 1, 'msg' => ''];
        
        $dataCollection = $req -> getContent();


        // Lo escribo en la base de datos
        try {

            // Valido los datos recibidos del json
            $dataCollection = json_decode($dataCollection);

            // Creo una nueva colección con los datos correspondientes
            $collection = new Collection();

            $collection -> name = $dataCollection -> name;
            $collection -> symbol = $dataCollection -> symbol;
            $collection -> edition_date = date('Y-m-d');

            $collection -> save();


            $answer['msg'] = "Collection registered correctly";
            

        } catch(\Exception $e) {
            $answer['msg'] = $e -> getMessage();
            $answer['status'] = 0;
        }

        return response()-> json($answer);

    }

}
