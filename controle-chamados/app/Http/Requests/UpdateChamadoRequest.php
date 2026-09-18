<?php

namespace App\Http\Requests;

use App\Enums\Prioridade;
use App\Enums\StatusChamado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateChamadoRequest extends FormRequest
{
    /**
     * Na edição o responsável é sempre explícito: a atribuição automática é
     * uma decisão do momento da abertura e fica disponível como ação própria
     * na tela de detalhe do chamado.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'min:3', 'max:150'],
            'descricao' => ['required', 'string', 'min:5', 'max:5000'],
            'prioridade' => ['required', Rule::enum(Prioridade::class)],
            'status' => ['required', Rule::enum(StatusChamado::class)],
            'responsavel_id' => [
                'required',
                'integer',
                Rule::exists('responsaveis', 'id')->where('ativo', true),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'responsavel_id.required' => 'Selecione um responsável.',
            'responsavel_id.exists' => 'O responsável selecionado não existe ou está inativo.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'titulo' => 'título',
            'descricao' => 'descrição',
            'responsavel_id' => 'responsável',
        ];
    }
}
