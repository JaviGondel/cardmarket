<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{
    public function register (Request $req) {

        $answer = ['status' => 1, 'msg' => ''];
        
        $dataUser = $req -> getContent();

        // Valido los campos para que tengan un formato válido
        
        $validator = Validator::make(json_decode($dataUser, true), [
            'name' => 'required|max:255',
            'role' => 'required|in:particular,profesional,admin',
            'email' => 'required|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix|unique:users|max:255',
            'password' => 'required|regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{6,}/',
        ]);

        // Lo escribo en la base de datos
        try {
            // Valido los datos recibidos del json
            $dataUser = json_decode($dataUser);
        
            if ($validator->fails()) {
                $answer['msg'] = "Ha ocurrido un error: " . $validator->errors()->first();
            } else {
                // Creo un nuevo usuario con los datos correspondientes
                $user = new User();

                $user -> name = $dataUser -> name;
                $user -> role = $dataUser -> role;
                $user -> email = $dataUser -> email;
                $user -> password = Hash::make($dataUser -> password); 

                $user -> save();

                $answer['msg'] = "User registered correctly";
            }

        } catch(\Exception $e) {
            $answer['msg'] = $e -> getMessage();
            $answer['status'] = 0;
        }

        return response()-> json($answer); 
    }

    public function login(Request $req) {

        $answer = ['status' => 1, 'msg' => '', 'alert' => ''];

        $user = "";

        // Compruebo que el usuario introduzca el nombre de usuario y una contraseña
        if ($req->input('name') != "") {
            $user = User::where ('name', $req->input ('name'))->first();
        } else{
            $answer['alert'] = "Introduce an user name to continue";
        }

        if ($req->input('password') == "") {
            $answer['alert'] = "Introduce a password to continue";
        }


        // Si la contraseña es igual a la que tenemos en la bbdd
        if ($user) {
            if (Hash::check($req->input('password'), $user->password)) {

                try {

                    // Genero un api_token cuando el usuario se logea
                    $token = Hash::make(now().$user->id);

                    $user->api_token = $token;
                    $user->save();

                    $answer['msg']= "Login code: ". $user->api_token;

                }catch(\Exception $e){
                    $answer['msg'] = $e -> getMessage();
                    $answer['status'] = 0;
                }

            } else{
                $answer['error'] = 'Incorrect password';
            }
        } else{
            $answer['alert'] .= ', User not found';
        }

        return response()->json($answer);

    }

    public function recoveryPassword(Request $req) {

        $answer = ['status' => 1, 'msg' => '', 'alert' => ''];

        $email = $req->input('email');

        $Pass_pattern = '/^\S*(?=\S{6,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/';


        try {

            if($req->has('email')) {

                $user = User::where('email', $email)->first();

                if ($user) {

                    do {
                        $newPass = Str::random(6);
                    } while(!preg_match($Pass_pattern, $newPass));

                    $user->password = Hash::make($newPass);
                    $user->save();

                    $answer['password'] = "This is your new password: ".$newPass;

                } else {
                    $answer['msg'] = "Please enter an email address.";
                }
            }

        } catch (\Exception $e){
            $answer['status'] = 0;
            $answer['msg'] = "Se ha producido un error al generar la contraseña. ".$e->getMessage();
        }

        return response()->json($answer);

    }

}
