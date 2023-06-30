<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth',['except'=>['index','show']]);
    }
    public function __invoke(){

    }
    //Metodos REST 
    //GET
    public function index(){
        $data=Category::all();
        $response=array(
            'status'=>200,
            'message'=>'Consulta completada satisfactoriamente',
            'data'=>$data
        );
        return response()->json($response,200);
    }
    public function show($id){
        $data=Category::find($id);
        if(is_object($data)){
            $data=$data->load('posts');
            $response=array(
                'status'=>200,                
                'data'=>$data
            );
        }else{
            $response=array(
                'status'=>404,                
                'message'=>'Recurso no encontrado'
            );
        }
        return response()->json($response,$response['status']);
    }
    public function store(Request $request){
        $data_input=$request->input('data',null);
        $data=json_decode($data_input,true);
        if(!empty($data)){
            $data=array_map('trim',$data);
            $rules=[
                'name'=>'required|alpha'
            ];
            $validate=\validator($data,$rules);
            if(!($validate->fails())){
                $category=new Category();
                $category->name=$data['name'];
                $category->save();
                $response=array(
                    'status'=>201,
                    'message'=>'Datos guardados correctamente'
                );
            }else{
                $response=array(
                    'status'=>406,
                    'message'=>'Error de validación, datos incorrectos',
                    'errors'=>$validate->errors()
                );
            }
        }else{
            $response=array(
                'status'=>400,
                'message'=>'Faltan parametros'
            );
        }
        return response()->json($response,$response['status']);
    }
    public function update(Request $request){
        $dataInput=$request->input('data',null);
        $data=json_decode($dataInput,true);
        if(!empty($data)){
            $data=array_map('trim',$data);
            $rules=[
                'id'=>'required',
                'name'=>'required|alpha'
            ];
            $validate=\validator($data,$rules);
            if($validate->fails()){
                $response=array(                    
                    'status'=>406,
                    'message'=>'Los datos enviados son incorrectos',
                    'errors'=>$validate->errors()
                );
            }else{
                $id=$data['id'];
                unset($data['id']);        //[name,updated_at]
                unset($data['created_at']);
                $updated=Category::where('id',$id)->update($data);
                if($updated>0){
                    $response=array(
                        'status'=>200,                        
                        'message'=>'Datos actualizados exitosamente'
                    );
                }else{
                    $response=array(
                        'status'=>400,
                        'message'=>'No se pudo actualizar los datos'
                    );
                }
            }
        }else{
            $response=array(
                'status'=>400,
                'message'=>'Faltan parametros'
            );
        }
        return response()->json($response,$response['status']);
    }
    //destroy --> Elimina un elemento   DELETE
    public function destroy($id){
        if(isset($id)){
            $deleted=Category::where('id',$id)->delete();
            if($deleted){
                $response=array(
                    'status'=>200,
                    'message'=>'Eliminado correctamente'
                );
            }else{
                $response=array(
                    'status'=>400,
                    'message'=>'Problemas al eleminar el recurso, puede ser que el recurso no exista'
                );
            }
        }else{
            $response=array(
                'status'=>400,
                'message'=>'Falta el identificador del recurso'
            );
        }
        return response()->json($response,$response['status']);
    }
}
