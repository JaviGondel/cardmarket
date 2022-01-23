<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Card_Collection;
use App\Models\Collection;
use App\Models\Sale;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
            $deck = DB::table('collections')->where('name', $dataCard -> collection)->first();
            if($deck) {

                $card -> collection = $dataCard -> collection;
                $card -> collection_id = $deck->id;
    
                $card -> save();

                $answer['msg'] = "Card registered correctly";

            } else {

                $collection = new Collection();
                $collection -> name = $dataCard -> collection;
                $collection -> symbol = "defaultImage.jpg";
                $collection -> edition_date = date('Y-m-d');
                $collection -> first_card = $dataCard -> name;
                $collection -> save();


                $card -> collection = $dataCard -> collection;
                $card -> collection_id = $collection -> id;

                $card -> save();

                $deck = $collection;

                $answer['msg'] = "Card and collection registered correctly";
            }

            // Registro la carta y la colección en la tabla de Cards_Collection
            $cardCollection = new Card_Collection();

            $cardCollection -> card_id = $card->id;
            $cardCollection -> collection_id = $deck->id;
            $cardCollection->save();

        } catch(\Exception $e) {
            $answer['msg'] = $e -> getMessage();
            $answer['status'] = 0;
        }

        return response()-> json($answer);
    }

    public function cardsToSale (Request $req) {

        $answer = ['status' => 1, 'msg' => ''];
        
        $dataSale = $req -> getContent();
        $user = $req->user;

        // Valido los datos recibidos del json
        $dataSale = json_decode($dataSale);

        $card = DB::table('cards')->where('name', $dataSale -> name)->first();

        if ($card) {
            // Lo escribo en la base de datos
            try {
                // Creo una nueva compra con los datos correspondientes
                $sale = new Sale();

                $sale -> name = $dataSale -> name;
                $sale -> number_of_cards = $dataSale -> number_of_cards;
                $sale -> price = $dataSale -> price;

                $sale -> card_id = $card -> id;
                $sale -> user_id = $user -> id;

                $sale -> save();

                $answer['msg'] = "Sale created correctly";
                
            } catch(\Exception $e) {
                $answer['msg'] = $e -> getMessage();
                $answer['status'] = 0;
            }
            
        } else {
            $answer['msg'] = "No such card exists";
        }

        return response()-> json($answer);

    }

    public function searchCard(Request $req) {

        $answer = ['status' => 1, 'msg' => '', 'data' => ''];

        $card = $req->input('card');

        try {

            $answer['msg'] = "Aquí tienes la lista de las cartas solicitadas";

            // Ejecuto la consulta para mostrar 
            if ($card) {
                $answer['data'] = DB::table('cards')
                    ->where('cards.name' , 'like', '%'.$card.'%')
                    ->select(
                        'cards.id',
                        'cards.name',
                    )
                    ->get();
            } else {
                $answer['data'] = DB::table('cards')
                    ->select(
                        'cards.id',
                        'cards.name',
                    )
                    ->get();
            }

        } catch(\Exception $e) {
            $answer['msg'] = $e -> getMessage();
            $answer['status'] = 0;
        }

        return response()->json($answer);

    }

    ////// COLLECTIONS //////

    public function registerCollection (Request $req) {

        $answer = ['status' => 1, 'msg' => ''];
        
        $dataCollection = $req -> getContent();
        $user = $req->user;

        // Valido el campo del nombre para que no se pueda repetir y cree dos colecciones iguales.
        $validator = Validator::make(json_decode($dataCollection, true), [
            'name' => 'required|unique:collections'
        ]);

        if ($validator->fails()) {
            $answer['msg'] = "Ha ocurrido un error: " . $validator->errors()->first();
        } else {

            // Lo escribo en la base de datos
            try {

                // Valido los datos recibidos del json
                $dataCollection = json_decode($dataCollection);

                // Creo una nueva colección con los datos correspondientes
                $collection = new Collection();

                $collection -> name = $dataCollection -> name;
                $collection -> symbol = $dataCollection -> symbol;
                $collection -> edition_date = date('Y-m-d');

                // Compruebo que la carta que quiera añadir en la colección exista
                $card = DB::table('cards')->where('name', $dataCollection->first_card)->first();

                if ($card) {

                    $collection -> first_card = $dataCollection -> first_card;
                    $collection -> save();


                    $answer['msg'] = "Collection registered correctly";

                } else {

                    // Guardo la colección con el nombre de la carta creada
                    $collection -> first_card = $dataCollection -> first_card;
                    $collection -> save();

                    // Creo una nueva carta con los datos correspondientes
                    $card = new Card();

                    $card -> name = $dataCollection -> first_card;
                    $card -> description = "Esta es una carta creada automáticamente al crear una colección";
                    $card -> collection = $dataCollection -> name;
                    $card -> user_id = $user -> id;
                    $card -> collection_id = $collection -> id;

                    $card -> save();

                    $answer['msg'] = "Collection and card registered correctly";
                }

                // Registro la carta y la colección en la tabla de Cards_Collection
                $cardCollection = new Card_Collection();

                $cardCollection -> card_id = $card->id;
                $cardCollection -> collection_id = $collection->id;
                $cardCollection->save();
                

            } catch(\Exception $e) {
                $answer['msg'] = $e -> getMessage();
                $answer['status'] = 0;
            }
        }

        return response()-> json($answer);

    }

}
