<?php

namespace App\Models;

use App\Enums\StatusChamado;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Responsavel extends Model
{
    /** @use HasFactory<\Database\Factories\ResponsavelFactory> */
    use HasFactory;

    protected $table = 'responsaveis';

    protected $fillable = ['nome', 'email', 'ativo'];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }

    /**
     * @return HasMany<Chamado, $this>
     */
    public function chamados(): HasMany
    {
        return $this->hasMany(Chamado::class);
    }

    /**
     * @param  Builder<Responsavel>  $query
     */
    public function scopeAtivos(Builder $query): void
    {
        $query->where('ativo', true);
    }

    /**
     * Carrega a contagem de chamados em aberto como atributo `chamados_em_aberto_count`.
     *
     * @param  Builder<Responsavel>  $query
     */
    public function scopeComCargaEmAberto(Builder $query): void
    {
        $query->withCount([
            'chamados as chamados_em_aberto_count' => fn (Builder $chamados) => $chamados
                ->whereIn('status', StatusChamado::emAbertoValores()),
        ]);
    }
}
