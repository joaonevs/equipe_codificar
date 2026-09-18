<?php

namespace Database\Seeders;

use App\Enums\Prioridade;
use App\Enums\StatusChamado;
use App\Models\Chamado;
use App\Models\Responsavel;
use Illuminate\Database\Seeder;

class ChamadoSeeder extends Seeder
{
    public function run(): void
    {
        $responsaveis = Responsavel::query()->ativos()->orderBy('id')->get();

        if ($responsaveis->isEmpty()) {
            return;
        }

        // Alguns chamados realistas para o avaliador ver a listagem já povoada.
        $exemplos = [
            ['Notebook não liga após atualização', StatusChamado::Aberto, Prioridade::Alta],
            ['Solicitação de acesso ao ERP', StatusChamado::Aberto, Prioridade::Media],
            ['Impressora do 2º andar sem toner', StatusChamado::EmAndamento, Prioridade::Baixa],
            ['E-mails corporativos chegando na caixa de spam', StatusChamado::EmAndamento, Prioridade::Alta],
            ['Troca de monitor com defeito', StatusChamado::Resolvido, Prioridade::Media],
            ['Instalação do pacote Office', StatusChamado::Fechado, Prioridade::Baixa],
        ];

        foreach ($exemplos as $indice => [$titulo, $status, $prioridade]) {
            Chamado::factory()->create([
                'titulo' => $titulo,
                'status' => $status,
                'prioridade' => $prioridade,
                'responsavel_id' => $responsaveis[$indice % $responsaveis->count()]->id,
            ]);
        }

        // Volume adicional para testar filtros, ordenação e paginação.
        Chamado::factory()
            ->count(18)
            ->sequence(fn ($sequence) => [
                'responsavel_id' => $responsaveis[$sequence->index % $responsaveis->count()]->id,
            ])
            ->create();
    }
}
