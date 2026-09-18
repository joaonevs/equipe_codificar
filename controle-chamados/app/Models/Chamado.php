<?php

namespace App\Models;

use App\Enums\Prioridade;
use App\Enums\StatusChamado;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chamado extends Model
{
    /** @use HasFactory<\Database\Factories\ChamadoFactory> */
    use HasFactory;

    protected $fillable = [
        'titulo',
        'descricao',
        'prioridade',
        'status',
        'responsavel_id',
        'aberto_em',
    ];

    protected function casts(): array
    {
        return [
            'prioridade' => Prioridade::class,
            'status' => StatusChamado::class,
            'aberto_em' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Responsavel, $this>
     */
    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(Responsavel::class);
    }

    /**
     * @param  Builder<Chamado>  $query
     */
    public function scopeEmAberto(Builder $query): void
    {
        $query->whereIn('status', StatusChamado::emAbertoValores());
    }

    /**
     * Busca livre por título ou descrição.
     *
     * @param  Builder<Chamado>  $query
     */
    public function scopeBusca(Builder $query, ?string $termo): void
    {
        $query->when(filled($termo), function (Builder $query) use ($termo) {
            $query->where(function (Builder $query) use ($termo) {
                $query->where('titulo', 'like', "%{$termo}%")
                    ->orWhere('descricao', 'like', "%{$termo}%");
            });
        });
    }
}
