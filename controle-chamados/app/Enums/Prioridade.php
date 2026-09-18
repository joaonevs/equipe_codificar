<?php

namespace App\Enums;

enum Prioridade: string
{
    case Baixa = 'baixa';
    case Media = 'media';
    case Alta = 'alta';

    public function rotulo(): string
    {
        return match ($this) {
            self::Baixa => 'Baixa',
            self::Media => 'Média',
            self::Alta => 'Alta',
        };
    }

    /**
     * Peso usado para ordenar a listagem da prioridade mais urgente para a menos urgente.
     */
    public function peso(): int
    {
        return match ($this) {
            self::Alta => 3,
            self::Media => 2,
            self::Baixa => 1,
        };
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function opcoes(): array
    {
        return array_map(
            fn (self $caso) => ['value' => $caso->value, 'label' => $caso->rotulo()],
            self::cases(),
        );
    }
}
