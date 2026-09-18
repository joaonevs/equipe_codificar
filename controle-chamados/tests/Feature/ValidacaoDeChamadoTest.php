<?php

use App\Models\Chamado;
use App\Models\Responsavel;

it('recusa a criação sem os campos obrigatórios', function () {
    $this->post('/chamados', [])
        ->assertSessionHasErrors(['titulo', 'descricao', 'prioridade', 'status']);

    expect(Chamado::count())->toBe(0);
});

it('recusa prioridade e status fora dos valores permitidos', function () {
    $responsavel = Responsavel::factory()->create();

    $this->post('/chamados', [
        'titulo' => 'Título válido',
        'descricao' => 'Descrição suficientemente longa.',
        'prioridade' => 'urgentissima',
        'status' => 'pendente',
        'atribuicao_automatica' => false,
        'responsavel_id' => $responsavel->id,
    ])->assertSessionHasErrors(['prioridade', 'status']);

    expect(Chamado::count())->toBe(0);
});

it('exige um responsável quando a atribuição automática está desligada', function () {
    $this->post('/chamados', [
        'titulo' => 'Título válido',
        'descricao' => 'Descrição suficientemente longa.',
        'prioridade' => 'alta',
        'status' => 'aberto',
        'atribuicao_automatica' => false,
    ])->assertSessionHasErrors('responsavel_id');
});

it('recusa um responsável inexistente ou inativo', function () {
    $inativo = Responsavel::factory()->inativo()->create();

    $this->post('/chamados', [
        'titulo' => 'Título válido',
        'descricao' => 'Descrição suficientemente longa.',
        'prioridade' => 'alta',
        'status' => 'aberto',
        'atribuicao_automatica' => false,
        'responsavel_id' => $inativo->id,
    ])->assertSessionHasErrors('responsavel_id');

    $this->post('/chamados', [
        'titulo' => 'Título válido',
        'descricao' => 'Descrição suficientemente longa.',
        'prioridade' => 'alta',
        'status' => 'aberto',
        'atribuicao_automatica' => false,
        'responsavel_id' => 9999,
    ])->assertSessionHasErrors('responsavel_id');
});
