<?php

namespace App\Http\Controllers;

use App\Enums\Prioridade;
use App\Enums\StatusChamado;
use App\Http\Requests\StoreChamadoRequest;
use App\Http\Requests\UpdateChamadoRequest;
use App\Http\Resources\ChamadoResource;
use App\Http\Resources\ResponsavelResource;
use App\Models\Chamado;
use App\Models\Responsavel;
use App\Services\DistribuicaoAutomatica;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ChamadoController extends Controller
{
    /**
     * Colunas liberadas para ordenação. Evita que o parâmetro da URL
     * chegue cru ao banco.
     */
    private const ORDENACOES = ['aberto_em', 'titulo', 'prioridade', 'status'];

    public function index(Request $request): Response
    {
        $ordenarPor = in_array($request->string('ordenar_por')->toString(), self::ORDENACOES, true)
            ? $request->string('ordenar_por')->toString()
            : 'aberto_em';

        $direcao = $request->string('direcao')->toString() === 'asc' ? 'asc' : 'desc';

        $chamados = Chamado::query()
            ->with('responsavel')
            ->busca($request->string('busca')->toString())
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('prioridade'), fn ($query) => $query->where('prioridade', $request->string('prioridade')->toString()))
            ->when($request->filled('responsavel_id'), fn ($query) => $query->where('responsavel_id', $request->integer('responsavel_id')))
            ->when(
                $ordenarPor === 'prioridade',
                fn ($query) => $query->orderByRaw(self::ordenacaoPorPrioridade($direcao)),
                fn ($query) => $query->orderBy($ordenarPor, $direcao),
            )
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Chamados/Index', [
            'chamados' => ChamadoResource::collection($chamados),
            'responsaveis' => ResponsavelResource::collection(self::responsaveisAtivos())->resolve(),
            'filtros' => $request->only(['busca', 'status', 'prioridade', 'responsavel_id', 'ordenar_por', 'direcao']),
            'opcoes' => self::opcoes(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Chamados/Create', [
            'responsaveis' => ResponsavelResource::collection(self::responsaveisAtivos())->resolve(),
            'opcoes' => self::opcoes(),
        ]);
    }

    public function store(StoreChamadoRequest $request, DistribuicaoAutomatica $distribuicao): RedirectResponse
    {
        $dados = $request->validated();
        $automatica = (bool) $dados['atribuicao_automatica'];

        $chamado = DB::transaction(function () use ($dados, $automatica, $distribuicao) {
            $responsavelId = $automatica
                ? $distribuicao->proximoResponsavel()->id
                : $dados['responsavel_id'];

            return Chamado::create([
                'titulo' => $dados['titulo'],
                'descricao' => $dados['descricao'],
                'prioridade' => $dados['prioridade'],
                'status' => $dados['status'],
                'responsavel_id' => $responsavelId,
                'aberto_em' => now(),
            ]);
        });

        return redirect()
            ->route('chamados.show', $chamado)
            ->with('sucesso', "Chamado #{$chamado->id} criado.");
    }

    public function show(Chamado $chamado): Response
    {
        $chamado->load('responsavel');

        return Inertia::render('Chamados/Show', [
            'chamado' => ChamadoResource::make($chamado)->resolve(),
        ]);
    }

    public function edit(Chamado $chamado): Response
    {
        $chamado->load('responsavel');

        return Inertia::render('Chamados/Edit', [
            'chamado' => ChamadoResource::make($chamado)->resolve(),
            'responsaveis' => ResponsavelResource::collection(self::responsaveisAtivos())->resolve(),
            'opcoes' => self::opcoes(),
        ]);
    }

    public function update(UpdateChamadoRequest $request, Chamado $chamado): RedirectResponse
    {
        $chamado->update($request->validated());

        return redirect()
            ->route('chamados.show', $chamado)
            ->with('sucesso', "Chamado #{$chamado->id} atualizado.");
    }

    public function destroy(Chamado $chamado): RedirectResponse
    {
        $id = $chamado->id;
        $chamado->delete();

        return redirect()
            ->route('chamados.index')
            ->with('sucesso', "Chamado #{$id} excluído.");
    }

    /**
     * Reatribui o chamado ao responsável com menor carga em aberto.
     */
    public function redistribuir(Chamado $chamado, DistribuicaoAutomatica $distribuicao): RedirectResponse
    {
        $responsavel = $distribuicao->proximoResponsavel();
        $chamado->update(['responsavel_id' => $responsavel->id]);

        return back()->with('sucesso', "Chamado atribuído automaticamente a {$responsavel->nome}.");
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Responsavel>
     */
    private static function responsaveisAtivos()
    {
        return Responsavel::query()
            ->ativos()
            ->comCargaEmAberto()
            ->orderBy('nome')
            ->get();
    }

    /**
     * Ordena pela urgência real (alta > média > baixa) em vez da ordem alfabética
     * do valor gravado no banco. A direção já vem validada pela lista branca.
     */
    private static function ordenacaoPorPrioridade(string $direcao): string
    {
        $casos = '';

        foreach (Prioridade::cases() as $prioridade) {
            $casos .= sprintf(" WHEN '%s' THEN %d", $prioridade->value, $prioridade->peso());
        }

        return sprintf('CASE prioridade%s END %s', $casos, $direcao);
    }

    /**
     * @return array<string, mixed>
     */
    private static function opcoes(): array
    {
        return [
            'prioridades' => Prioridade::opcoes(),
            'status' => StatusChamado::opcoes(),
        ];
    }
}
