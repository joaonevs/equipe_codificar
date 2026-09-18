<?php

namespace App\Http\Requests;

use App\Enums\Prioridade;
use App\Enums\StatusChamado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChamadoRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'min:3', 'max:150'],
            'descricao' => ['required', 'string', 'min:5', 'max:5000'],
            'prioridade' => ['required', Rule::enum(Prioridade::class)],
            'status' => ['required', Rule::enum(StatusChamado::class)],
            'atribuicao_automatica' => ['required', 'boolean'],
            'responsavel_id' => [
                Rule::requiredIf(fn () => ! $this->boolean('atribuicao_automatica')),
                'nullable',
                'integer',
                Rule::exists('responsaveis', 'id')->where('ativo', true),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'atribuicao_automatica' => $this->boolean('atribuicao_automatica'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'responsavel_id.required' => 'Selecione um responsável ou ative a atribuição automática.',
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
