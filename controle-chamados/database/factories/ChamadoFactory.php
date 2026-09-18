<?php

namespace Database\Factories;

use App\Enums\Prioridade;
use App\Enums\StatusChamado;
use App\Models\Chamado;
use App\Models\Responsavel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Chamado>
 */
class ChamadoFactory extends Factory
{
    protected $model = Chamado::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $abertoEm = fake()->dateTimeBetween('-30 days', 'now');

        return [
            'titulo' => rtrim(fake()->sentence(6), '.'),
            'descricao' => fake()->paragraph(4),
            'prioridade' => fake()->randomElement(Prioridade::cases()),
            'status' => fake()->randomElement(StatusChamado::cases()),
            'responsavel_id' => Responsavel::factory(),
            'aberto_em' => $abertoEm,
            'created_at' => $abertoEm,
            'updated_at' => $abertoEm,
        ];
    }

    public function comStatus(StatusChamado $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }

    public function emAberto(): static
    {
        return $this->comStatus(StatusChamado::Aberto);
    }

    public function para(Responsavel $responsavel): static
    {
        return $this->state(fn () => ['responsavel_id' => $responsavel->id]);
    }
}
