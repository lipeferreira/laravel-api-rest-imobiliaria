<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Api\ApiMessages; //importa a customização de mesnsagens, que deve ser criada manualmente nesse namespace App/Api
use Illuminate\Support\Facades\Validator; //import do validator, pra validação dos dados do perfil

class UserController extends Controller
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = $this->user->paginate('10');
        return response()->json($users, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();

        if (!$request->has('password') || !$request->get('password')){
            $message = new ApiMessages('É necessário informar uma senha');
            return response()->json($message->getMessage(), 401);
        }

        
        Validator::make($data, [    //validação do profile
            'phone' => 'required',
            'mobile_phone' => 'required'
        ])->validate();
        

        try {
            $data['password'] = bcrypt($data['password']);
            $user = $this->user->create($data); // mass assignment, depende de ter o fillable no model
            $user->profile()->create([                //salva o perfil
                'phone' => $data['phone'],
                'mobile_phone' => $data['mobile_phone']
            ]);
            return response()->json([
                'data' => [
                    'msg' => 'Usuario cadastrado com sucesso!'
                ]
            ], 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());
            return response()->json(['error' => $message->getMessage()], 401);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $user = $this->user->with('profile')->findOrFail($id); // mass assignment, depende de ter o fillable no model
            $user->profile->social_networks = unserialize($user->profile->social_networks); //transforma a strung de volta em array
            return response()->json([
                'data' =>  $user
            ], 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());
            return response()->json(['error' => $message->getMessage()], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();

        if ($request->has('password') && $request->get('password')){
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']); //remove do update o password
        }

        Validator::make($data, [    //validação do profile
            'profile.phone' => 'required',
            'profile.mobile_phone' => 'required'
        ])->validate();

        try {
            $profile = $data['profile'];
            $profile['social_networks'] = serialize($profile['social_networks']); //transforma o array de redes em uma unica string
            $user = $this->user->findOrFail($id); // procura pelo id, se não achar lança uma exception
            $user->update($data);
            $user->profile->update($profile);
            return response()->json([
                'data' => [
                    'msg' => 'Usuario atualizado com sucesso!'
                ]
            ], 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());
            return response()->json(['error' => $message->getMessage()], 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $user = $this->user->findOrFail($id);
            $user->delete();
            return response()->json([
                'data' => [
                    'msg' => 'Usuario removido com sucesso!'
                ]
            ], 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());
            return response()->json(['error' => $message->getMessage()], 401);
        }
    }
}
