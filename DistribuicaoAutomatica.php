<?php

namespace App\Services;

use App\Models\Responsavel;
use RuntimeException;

/**
 * Regra de negócio central do sistema: escolher o responsável que receberá um
 * chamado quando o usuário opta pela atribuição automática.
 *
 * Critério:
 *  1. considera apenas responsáveis ativos;
 *  2. escolhe quem tem a MENOR quantidade de chamados em aberto
 *     (status `aberto` e `em_andamento` — ver App\Enums\StatusChamado::emAbertoCases());
 *  3. em caso de empate, vence o menor `id` (comportamento determinístico).
 */
class DistribuicaoAutomatica
{
    public function proximoResponsavel(): Responsavel
    {
        $responsavel = Responsavel::query()
            ->ativos()
            ->comCargaEmAberto()
            ->orderBy('chamados_em_aberto_count')
            ->orderBy('id')
            ->first();

        if (! $responsavel) {
            throw new RuntimeException(
                'Não há responsáveis ativos para receber a atribuição automática. Execute os seeders.'
            );
        }

        return $responsavel;
    }
}
