<?php

namespace App\Http\Controllers;

use App\Enums\StatusChamado;
use App\Models\Chamado;
use App\Models\Responsavel;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $porStatus = Chamado::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $indicadores = [
            'total' => (int) $porStatus->sum(),
        ];

        foreach (StatusChamado::cases() as $status) {
            $indicadores[$status->value] = (int) ($porStatus[$status->value] ?? 0);
        }

        $carga = Responsavel::query()
            ->ativos()
            ->comCargaEmAberto()
            ->withCount('chamados')
            ->orderByDesc('chamados_em_aberto_count')
            ->orderBy('id')
            ->get()
            ->map(fn (Responsavel $responsavel) => [
                'id' => $responsavel->id,
                'nome' => $responsavel->nome,
                'em_aberto' => $responsavel->chamados_em_aberto_count,
                'total' => $responsavel->chamados_count,
            ]);

        return Inertia::render('Dashboard', [
            'indicadores' => $indicadores,
            'carga' => $carga,
        ]);
    }
}
