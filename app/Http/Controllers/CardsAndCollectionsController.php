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

        // Lo escribo en la base de datos
        try {

            // Valido los datos recibidos del json
            $dataCard = json_decode($dataCard);

            // Para guardar la colección compruebo que previamente exista, si no existe creo una nueva colección
            $deck = DB::table('collections')->where('id', $dataCard -> collection_id)->first();
            
            if($deck) {

                // Creo una nueva carta con los datos correspondientes
                $card = new Card();
                $card -> name = $dataCard -> name;
                $card -> description = $dataCard -> description;
                $card -> save();

                // Registro la carta y la colección en la tabla de Cards_Collection
                $cardCollection = new Card_Collection();
                $cardCollection -> card_id = $card->id;
                $cardCollection -> collection_id = $deck->id;
                $cardCollection->save();

                $answer['msg'] = "Card registered correctly";

            } else {

                $answer['msg'] = "The selected collection doesn´t exist";
            }
            
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

        $card = DB::table('cards')->where('id', $dataSale -> card_id)->first();

        if ($card) {
            // Lo escribo en la base de datos
            try {
                // Creo una nueva compra con los datos correspondientes
                $sale = new Sale();

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


    ////// COLLECTIONS //////

    public function registerCollection (Request $req) {

        $answer = ['status' => 1, 'msg' => ''];
        
        $dataCollection = $req -> getContent();

        // Valido el campo del nombre para que no se pueda repetir y cree dos colecciones iguales.
        $validator = Validator::make(json_decode($dataCollection, true), [
            'name' => 'required|unique:collections'
        ]);

        if ($validator->fails()) {
            $answer['msg'] = "Error: " . $validator->errors()->first();
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
                $collection -> save();

                $answer['msg'] = "Collection registered correctly";

                // Compruebo si la carta introducida existe
                $cardExist = DB::table('cards')->where('id', $dataCollection -> card_id)->first();

                if ($cardExist) {

                // Registro la carta y la colección en la tabla de Cards_Collection (Añado la carta a la colección)
                $cardCollection = new Card_Collection();
                $cardCollection -> card_id = $cardExist->id;
                $cardCollection -> collection_id = $collection->id;
                $cardCollection->save();

                } else {

                    // Creo una nueva carta default con los datos correspondientes ya que la colección no puede estar vacía
                    $card = new Card();
                    $card -> name = "Default card";
                    $card -> description = "Esta es una carta creada automáticamente al crear una colección";
                    $card -> save();

                    $answer['msg'] = "Collection and card registered correctly";

                    // Registro la carta y la colección en la tabla de Cards_Collection
                    $cardCollection = new Card_Collection();
                    $cardCollection -> card_id = $card->id;
                    $cardCollection -> collection_id = $collection->id;
                    $cardCollection->save();

                }

            } catch(\Exception $e) {
                $answer['msg'] = $e -> getMessage();
                $answer['status'] = 0;
            }
        }

        return response()-> json($answer);

    }

    public function addCardToCollection (Request $req) {
        
        $answer = ['status' => 1, 'msg' => ''];
        
        $dataToAdd = $req -> getContent();
        $dataToAdd= json_decode($dataToAdd);

        try {

            $tryCardId = $dataToAdd->card_id;
            $tryCollectionId = $dataToAdd->collection_id;

            // Busco si existe tanto la carta como la colección
            $card = DB::table('cards')->where('id', $tryCardId)->first();
            $collection = DB::table('collections')->where('id', $tryCollectionId)->first();

            $idCard = $card->id;
            $idCollection = $collection->id;

            // Compruebo si esta carta ya está añadida a esa misma colección
            $addedCardToCollection = DB::table('cards_collections')
                                    ->select('card_id' , 'collection_id')
                                    ->where('card_id', $idCard)
                                    ->where('collection_id', $idCollection)
                                    ->first();

            if($addedCardToCollection) {

                $answer['msg'] = "The card is already added to this collection.";

            } else {

                if ($card) {

                    if ($collection) {
        
                    // Si la carta y la colección existen, registro la carta y la colección en la tabla de Cards_Collection
                    $cardCollection = new Card_Collection();
        
                    $cardCollection -> card_id = $idCard;
                    $cardCollection -> collection_id = $idCollection;
                    $cardCollection->save();
        
                    $answer['msg'] = "The card has been added correctly";
        
        
                    } else {
                        $answer['msg'] = "The collection to which you want to add the card doesn´t exist.";
                    }
        
                } else {
                    $answer['msg'] = "The card you want to add does not exist";
                }
            }


        } catch(\Exception $e) {
            $answer['msg'] = $e -> getMessage();
            $answer['status'] = 0;
        }

        return response()-> json($answer);

    }


    ////// Filters //////

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
                
                $answer['msg'] = "No existen datos con la carta solicitada";

            }

        } catch(\Exception $e) {
            $answer['msg'] = $e -> getMessage();
            $answer['status'] = 0;
        }

        return response()->json($answer);

    }

    public function searchToBuy(Request $req) {

        $answer = ['status' => 1, 'msg' => '', 'data' => ''];

        $card = $req->input('card');

        try {

            $answer['msg'] = "Aquí tienes la lista de las cartas solicitadas";

            // Ejecuto la consulta para mostrar 
            if ($card) {
                $answer['data'] = DB::table('cards')
                    ->join('sales', 'sales.card_id', '=', 'cards.id')
                    ->join('users', 'sales.user_id', '=', 'users.id')
                    ->where('cards.name' , 'like', '%'.$card.'%')
                    ->select(
                        'cards.name',
                        'sales.number_of_cards',
                        'sales.price',
                        'users.name as user'
                    )
                    ->orderBy('price', 'asc')
                    ->get();
            } else {
                
                $answer['msg'] = "Introduzca una carta a buscar";

            }

        } catch(\Exception $e) {
            $answer['msg'] = $e -> getMessage();
            $answer['status'] = 0;
        }

        return response()->json($answer);

    }

}
