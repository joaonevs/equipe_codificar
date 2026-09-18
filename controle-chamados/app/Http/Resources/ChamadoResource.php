<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Chamado
 */
class ChamadoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'prioridade' => $this->prioridade->value,
            'prioridade_rotulo' => $this->prioridade->rotulo(),
            'status' => $this->status->value,
            'status_rotulo' => $this->status->rotulo(),
            'em_aberto' => $this->status->emAberto(),
            'responsavel_id' => $this->responsavel_id,
            'responsavel' => ResponsavelResource::make($this->whenLoaded('responsavel')),
            'aberto_em' => $this->aberto_em?->format('d/m/Y H:i'),
            'atualizado_em' => $this->updated_at?->format('d/m/Y H:i'),
        ];
    }
}
