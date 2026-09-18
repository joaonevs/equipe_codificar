<?php

use App\Enums\Prioridade;
use App\Enums\StatusChamado;
use App\Models\Chamado;
use App\Models\Responsavel;

function dadosValidos(array $sobrescritas = []): array
{
    return array_merge([
        'titulo' => 'Notebook não liga',
        'descricao' => 'O equipamento parou de ligar após a atualização de ontem.',
        'prioridade' => Prioridade::Alta->value,
        'status' => StatusChamado::Aberto->value,
        'atribuicao_automatica' => false,
    ], $sobrescritas);
}

it('cria um chamado com atribuição manual', function () {
    $escolhido = Responsavel::factory()->create();
    Responsavel::factory()->create();

    $resposta = $this->post('/chamados', dadosValidos([
        'responsavel_id' => $escolhido->id,
    ]));

    $chamado = Chamado::sole();

    $resposta->assertRedirect("/chamados/{$chamado->id}");
    expect($chamado->responsavel_id)->toBe($escolhido->id)
        ->and($chamado->titulo)->toBe('Notebook não liga')
        ->and($chamado->status)->toBe(StatusChamado::Aberto)
        ->and($chamado->prioridade)->toBe(Prioridade::Alta)
        ->and($chamado->aberto_em)->not->toBeNull();
});

it('registra a data e a hora de abertura automaticamente', function () {
    $this->freezeTime();
    $responsavel = Responsavel::factory()->create();

    $this->post('/chamados', dadosValidos(['responsavel_id' => $responsavel->id]));

    expect(Chamado::sole()->aberto_em->timestamp)->toBe(now()->timestamp);
});

it('cria um chamado com atribuição automática para quem tem menos chamados em aberto', function () {
    $ocupado = Responsavel::factory()->create();
    $livre = Responsavel::factory()->create();

    Chamado::factory()->count(2)->emAberto()->para($ocupado)->create();

    $this->post('/chamados', dadosValidos([
        'atribuicao_automatica' => true,
        'responsavel_id' => $ocupado->id, // deve ser ignorado
    ]));

    expect(Chamado::latest('id')->sole()->responsavel_id)->toBe($livre->id);
});
