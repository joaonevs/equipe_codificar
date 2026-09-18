<?php

namespace App\Enums;

enum StatusChamado: string
{
    case Aberto = 'aberto';
    case EmAndamento = 'em_andamento';
    case Resolvido = 'resolvido';
    case Fechado = 'fechado';

    public function rotulo(): string
    {
        return match ($this) {
            self::Aberto => 'Aberto',
            self::EmAndamento => 'Em andamento',
            self::Resolvido => 'Resolvido',
            self::Fechado => 'Fechado',
        };
    }

    /**
     * Um chamado "em aberto" ainda consome a capacidade de trabalho do responsável.
     * É essa definição que alimenta a distribuição automática.
     */
    public function emAberto(): bool
    {
        return in_array($this, self::emAbertoCases(), strict: true);
    }

    /**
     * @return array<int, self>
     */
    public static function emAbertoCases(): array
    {
        return [self::Aberto, self::EmAndamento];
    }

    /**
     * @return array<int, string>
     */
    public static function emAbertoValores(): array
    {
        return array_map(fn (self $caso) => $caso->value, self::emAbertoCases());
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
