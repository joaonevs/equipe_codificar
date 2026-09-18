<?php

use App\Enums\StatusChamado;
use App\Models\Chamado;
use App\Models\Responsavel;
use App\Services\DistribuicaoAutomatica;

beforeEach(function () {
    $this->distribuicao = new DistribuicaoAutomatica();
});

it('escolhe o responsável com menos chamados em aberto', function () {
    $ocupado = Responsavel::factory()->create(['nome' => 'Ocupado']);
    $livre = Responsavel::factory()->create(['nome' => 'Livre']);

    Chamado::factory()->count(3)->emAberto()->para($ocupado)->create();
    Chamado::factory()->emAberto()->para($livre)->create();

    expect($this->distribuicao->proximoResponsavel()->id)->toBe($livre->id);
});

it('não conta chamados resolvidos nem fechados como em aberto', function () {
    $comHistorico = Responsavel::factory()->create();
    $comUmAberto = Responsavel::factory()->create();

    Chamado::factory()->count(5)->comStatus(StatusChamado::Resolvido)->para($comHistorico)->create();
    Chamado::factory()->count(5)->comStatus(StatusChamado::Fechado)->para($comHistorico)->create();
    Chamado::factory()->emAberto()->para($comUmAberto)->create();

    expect($this->distribuicao->proximoResponsavel()->id)->toBe($comHistorico->id);
});

it('desempata pelo menor id quando a carga é igual', function () {
    $primeiro = Responsavel::factory()->create();
    $segundo = Responsavel::factory()->create();
    $terceiro = Responsavel::factory()->create();

    foreach ([$primeiro, $segundo, $terceiro] as $responsavel) {
        Chamado::factory()->count(2)->emAberto()->para($responsavel)->create();
    }

    expect($this->distribuicao->proximoResponsavel()->id)->toBe($primeiro->id);
});

it('ignora responsáveis inativos', function () {
    $inativoSemCarga = Responsavel::factory()->inativo()->create();
    $ativo = Responsavel::factory()->create();

    Chamado::factory()->count(4)->emAberto()->para($ativo)->create();

    expect($this->distribuicao->proximoResponsavel()->id)
        ->toBe($ativo->id)
        ->not->toBe($inativoSemCarga->id);
});

it('falha de forma explícita quando não há responsáveis ativos', function () {
    Responsavel::factory()->inativo()->create();

    $this->distribuicao->proximoResponsavel();
})->throws(RuntimeException::class);
