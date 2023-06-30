<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;
use App\Helpers\JwtAuth;

class UserController extends Controller
{
    public function __construct(){
        $this->middleware('api.auth',['except'=>['index','store','show','login','getImage']]);
    }
    public function __invoke(){

    }
    public function index(){
        $data=User::all();
        if($data){
            $data->load('posts');
        }        
        $response=array(
            "status"=>200,
            "message"=>"Consulta generada exitosamente",
            "data"=>$data
        );
        return response()->json($response,200);

    }
    public function show($id){
        $user=User::find($id);
        if(is_object($user)){
            $response=array(
                'status'=>200,
                'data'=>$user
            );
        }else{
            $response=array(
                'status'=>404,
                'message'=>'Usuario no encontrado'
            );
        }
        return response()->json($response,$response['status']);
    }
    public function store(Request $request){
        $dataInput=$request->input('data',null);
        $data=json_decode($dataInput,true);
        $data=array_map('trim',$data);
        $rules=[
            'name'=>'required|alpha',
            'last_name'=>'required',
            'email'=>'required|email|unique:users',
            'password'=>'required',
            'role'=>'required'
        ];
        $valid=\validator($data,$rules);
        if(!$valid->fails()){
            $user=new User();
            $user->name=$data['name'];
            $user->last_name=$data['last_name'];
            $user->email=$data['email'];
            $user->password=hash('sha256',$data['password']);
            $user->role=$data['role'];
            $user->save();
            $response=array(
                'status'=>200,
                'message'=>'Datos guardados exitosamente'
            );
        }else{
            $response=array(
                'status'=>406,
                'message'=>'Error en la validación de los datos',
                'errors'=>$valid->errors(),
            );
        }
        return response()->json($response,$response['status']);
    }
    public function update(Request $request){
        $dataInput=$request->input('data',null);
        $data=json_decode($dataInput,true);        
        $data=array_map('trim',$data);
        $rules=[
            'name'=>'required|alpha',
            'last_name'=>'required',
            'email'=>'required|email',
            'password'=>'required',
            'role'=>'required'
        ];
        $valid=\validator($data,$rules);
        if($valid->fails()){
            $response=array(
                'status'=>406,
                'message'=>'Datos enviados no cumplen con las reglas establecidas',
                'errors'=>$valid->errors()
            );
        }else{
            $email=$data['email'];
            unset($data['email']);
            unset($data['id']);
            unset($data['created_at']);
            unset($data['remember_token']);
            $updated=User::where('email',$email)->update($data);
            if($updated>0){
                $response=array(
                    'status'=>200,
                    'message'=>'Datos actualizados satisfactoriamente'
                );
            }else{
                $response=array(
                    'status'=>400,
                    'message'=>'No se pudo actualizar el usuario, puede ser que no exista'
                );
            }
        }
        return response()->json($response,$response['status']);
    }
    public function destroy($id){
        if(isset($id)){
            $deleted=User::where('id',$id)->delete();
            if($deleted){
                $response=array(
                    'status'=>200,
                    'message'=>'Usuario eliminado correctamente'
                );
            }else{
                $response=array(
                    'status'=>400,
                    'message'=>'No se pudo eliminar el recurso'
                );
            }
        }else{
            $response=array(
                'status'=>400,
                'message'=>'Falta el identificador del recurso a eliminar'
            );
        }
        return response()->json($response,$response['status']);
    }
    public function uploadImage(Request $request){        
        $valid=\Validator::make($request->all(),['file0'=>'required|image|mimes:jpg,png']);
        if(!$valid->fails()){
            $image=$request->file('file0');
            $filename=time().$image->getClientOriginalName();
            \Storage::disk('users')->put($filename,\File::get($image));
            $response=array(
                'status'=>200,
                'message'=>'Imagen guardada exitosamente',
                'image_name'=>$filename
            );

        }else{
            $response=array(
                'status'=>406,
                'message'=>'Error al subir el archivo',
                'errors'=>$valid->errors(),
            );
        }
        return response()->json($response,$response['status']);
    }
    public function getImage($filename){
        if(isset($filename)){
            $exist=\Storage::disk('users')->exists($filename);
            if($exist){
                $file=\Storage::disk('users')->get($filename);
                return new Response($file,200);
            }else{
                $response=array(
                    'status'=>404,
                    'message'=>'Imagen no encontrada',
                );
            }
        }else{
            $response=array(
                'status'=>404,
                'message'=>'No se definió correctamente el nombre de la imagen',
            );
        }
        return response()->json($response,404);
    }
    public function login(Request $request){
        $jwtAuth=new JwtAuth();
        $dataInput=$request->input('data',null);
        $data=json_decode($dataInput,true);
        $data=array_map('trim',$data);
        $rules=['email'=>'required','password'=>'required'];
        $valid=\validator($data,$rules);
        if(!$valid->fails()){
            $response=$jwtAuth->getToken($data['email'],$data['password']);
            return response()->json($response);
        }else{
            $response=array(
                'status'=>406,
                'message'=>'Error en la validación de los datos',
                'errors'=>$valid->errors(),
            );
            return response()->json($response,406);
        }
    }
    public function getIdentity(Request $request){
        $jwtAuth=new JwtAuth();
        $token=$request->header('beartoken');
        if(isset($token)){
            $response=$jwtAuth->checkToken($token,true);
        }else{
            $response=array(
                'status'=>404,
                'message'=> 'Token (beartoken) no encontrado'
            );
        }
        return response()->json($response);
    }
}
