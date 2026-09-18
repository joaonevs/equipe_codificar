<?php

namespace Database\Seeders;

use App\Models\Responsavel;
use Illuminate\Database\Seeder;

class ResponsavelSeeder extends Seeder
{
    public function run(): void
    {
        $responsaveis = [
            ['nome' => 'João Silva', 'email' => 'joao.silva@empresa.test'],
            ['nome' => 'Maria Souza', 'email' => 'maria.souza@empresa.test'],
            ['nome' => 'Carlos Oliveira', 'email' => 'carlos.oliveira@empresa.test'],
        ];

        foreach ($responsaveis as $responsavel) {
            Responsavel::updateOrCreate(
                ['email' => $responsavel['email']],
                $responsavel + ['ativo' => true],
            );
        }
    }
}
