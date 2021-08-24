<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Api\ApiMessages;
use App\Models\RealStatePhoto;
use Illuminate\Support\Facades\Storage;

class RealStatePhotoController extends Controller
{
    private $realStatePhoto;

    public function __construct(RealStatePhoto $realStatePhoto)
    {
        $this->realStatePhoto = $realStatePhoto;
    }

    public function setThumb($photoId, $realStateId)
    {
        try {
            $photo = $this->realStatePhoto->where('real_state_id', $realStateId)->where('is_thumb', true); //busca as imagens que são thumb no imóvel
            if ($photo->count()) $photo->first()->update(['is_thumb' => false]); //se tiver uma imagem marcada como thumb, desmarca ela

            $photo = $this->realStatePhoto->find($photoId); //seleciona a foto enviada
            $photo->update(['is_thumb' => true]); //seta a foto enviada como thumb

            return response()->json([
                'data' => [
                    'msg' => 'Thumb atualizada com sucesso'
                ]
            ], 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());
            return response()->json(['error' => $message->getMessage()], 401);
        }
    }

    public function remove($photoId)
    {
        try {
            $photo = $this->realStatePhoto->find($photoId); //seleciona a foto enviada

            if($photo->is_thumb) {
                $message = new ApiMessages('Não é possível remover a thumb, selecione outra imagem como thumb para poder remover esta');
                return response()->json(['error' => $message->getMessage()], 401);
            }

            if($photo) {
                Storage::disk('public')->delete($photo->photo);
                $photo->delete();
            }

            return response()->json([
                'data' => [
                    'msg' => 'Imagem deletada com sucesso'
                ]
            ], 200);
        } catch (\Exception $e) {
            $message = new ApiMessages($e->getMessage());
            return response()->json(['error' => $message->getMessage()], 401);
        }
    }
}
