<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RealState; //importa o model
use App\Http\Requests\RealStateRequest; //importa o request customizado
use App\Api\ApiMessages; //importa a customização de mesnsagens, que deve ser criada manualmente nesse namespace App/Api

class RealStateController extends Controller
{
    private $realState;

    public function __construct(RealState $realState)
    {
        $this->realState = $realState;
    }

    public function index()
    {
        $realState = auth('api')->user()->real_state(); //mostrando só os imóveis do usuario
        // $realState = $this->realState->paginate('10');
        // return response()->json($realState, 200);
        return response()->json($realState->paginate('10'), 200);
    }

    public function show($id)
    {
        try {
            //$realState = $this->realState->with('photos')->findOrFail($id); // mass assignment, depende de ter o fillable no model
            $realState = auth('api')->user()->real_state()->with('photos')->findOrFail($id)->makeHidden('thumb'); //mostrando só os imóveis do usuario

            return response()->json([
                'data' =>  $realState
            ], 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());
            return response()->json(['error' => $message->getMessage()], 401);
        }
    }

    public function store(RealStateRequest $request) // substituir o Request pelo request custmoizado
    {
        $data = $request->all();
        $images = $request->file('images'); //recebe as imagens enviadas pelo form
        try {
            $data['user_id'] = auth('api')->user()->id;
            $realState = $this->realState->create($data); // mass assignment, depende de ter o fillable no model

            if (isset($data['categories']) && count($data['categories'])) {
                $realState->categories()->sync($data['categories']);   //sincroniza a tabela de imoveis_categorias
            }

            if($images) {  //verifica se tem imagens recebidas
                $imagesUploaded = [];
                foreach ($images as $image) {
                    $path = $image->store('images', 'public'); //armazena as imagens em storage/app/public/images
                    $imagesUploaded[] = [
                        'photo' => $path,
                        'is_thumb' => false
                    ];
                }

                $realStatePhoto = $realState->photos()->createMany($imagesUploaded); //faz o relacionamento das tabelas
            }

            return response()->json([
                'data' => [
                    'msg' => 'Imóvel cadastrado com sucesso!'
                ]
            ], 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());
            return response()->json(['error' => $message->getMessage()], 401);
        }
    }

    public function update($id, RealStateRequest $request) //para conseguir enviar o formulario com textos e binarios é preciso enviar o formulario como POST, passando o campo _method com o valor put (dica para o front-end)
    {
        $data = $request->all();
        $images = $request->file('images');
        try {
            $realState = $this->realState->findOrFail($id); // procura pelo id, se não achar lança uma exception
            $realState->update($data);

            if (isset($data['categories']) && count($data['categories'])) {
                $realState->categories()->sync($data['categories']);   //sincroniza a tabela de imoveis_categorias
            }

            if($images) {  //verifica se tem imagens recebidas
                $imagesUploaded = [];
                foreach ($images as $image) {
                    $path = $image->store('images', 'public'); //armazena as imagens em storage/app/public/images
                    $imagesUploaded[] = [
                        'photo' => $path,
                        'is_thumb' => false
                    ];
                }

                $realStatePhoto = $realState->photos()->createMany($imagesUploaded); //faz o relacionamento das tabelas
            }

            return response()->json([
                'data' => [
                    'msg' => 'Imóvel atualizado com sucesso!'
                ]
            ], 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());
            return response()->json(['error' => $message->getMessage()], 401);
        }
    }

    public function destroy($id)
    {
        try {
            $realState = $this->realState->findOrFail($id);
            $realState->delete();
            return response()->json([
                'data' => [
                    'msg' => 'Imóvel removido com sucesso!'
                ]
            ], 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());
            return response()->json(['error' => $message->getMessage()], 401);
        }
    }
}
