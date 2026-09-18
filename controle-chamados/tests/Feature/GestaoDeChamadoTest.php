<?php

use App\Enums\Prioridade;
use App\Enums\StatusChamado;
use App\Models\Chamado;
use App\Models\Responsavel;

it('lista os chamados existentes', function () {
    $responsavel = Responsavel::factory()->create();
    Chamado::factory()->count(3)->para($responsavel)->create();

    $this->get('/chamados')
        ->assertOk()
        ->assertInertia(fn ($pagina) => $pagina
            ->component('Chamados/Index')
            ->has('chamados.data', 3));
});

it('filtra a listagem por status', function () {
    $responsavel = Responsavel::factory()->create();
    Chamado::factory()->count(2)->comStatus(StatusChamado::Aberto)->para($responsavel)->create();
    Chamado::factory()->comStatus(StatusChamado::Fechado)->para($responsavel)->create();

    $this->get('/chamados?status=fechado')
        ->assertOk()
        ->assertInertia(fn ($pagina) => $pagina->has('chamados.data', 1));
});

it('exibe o detalhe de um chamado', function () {
    $chamado = Chamado::factory()->para(Responsavel::factory()->create())->create();

    $this->get("/chamados/{$chamado->id}")
        ->assertOk()
        ->assertInertia(fn ($pagina) => $pagina
            ->component('Chamados/Show')
            ->where('chamado.id', $chamado->id));
});

it('atualiza um chamado existente', function () {
    $original = Responsavel::factory()->create();
    $novo = Responsavel::factory()->create();
    $chamado = Chamado::factory()->emAberto()->para($original)->create();

    $this->put("/chamados/{$chamado->id}", [
        'titulo' => 'Título atualizado',
        'descricao' => 'Descrição atualizada com detalhes suficientes.',
        'prioridade' => Prioridade::Baixa->value,
        'status' => StatusChamado::Resolvido->value,
        'responsavel_id' => $novo->id,
    ])->assertRedirect("/chamados/{$chamado->id}");

    expect($chamado->refresh())
        ->titulo->toBe('Título atualizado')
        ->prioridade->toBe(Prioridade::Baixa)
        ->status->toBe(StatusChamado::Resolvido)
        ->responsavel_id->toBe($novo->id);
});

it('redistribui um chamado para quem tem menor carga', function () {
    $ocupado = Responsavel::factory()->create();
    $livre = Responsavel::factory()->create();
    Chamado::factory()->count(3)->emAberto()->para($ocupado)->create();
    $chamado = Chamado::factory()->emAberto()->para($ocupado)->create();

    $this->post("/chamados/{$chamado->id}/redistribuir")->assertRedirect();

    expect($chamado->refresh()->responsavel_id)->toBe($livre->id);
});

it('exclui um chamado', function () {
    $chamado = Chamado::factory()->para(Responsavel::factory()->create())->create();

    $this->delete("/chamados/{$chamado->id}")->assertRedirect('/chamados');

    expect(Chamado::count())->toBe(0);
});
