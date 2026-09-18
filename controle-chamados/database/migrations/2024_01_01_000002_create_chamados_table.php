<?php

use App\Enums\Prioridade;
use App\Enums\StatusChamado;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chamados', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 150);
            $table->text('descricao');
            $table->string('prioridade', 20)->default(Prioridade::Media->value);
            $table->string('status', 20)->default(StatusChamado::Aberto->value);
            $table->foreignId('responsavel_id')
                ->nullable()
                ->constrained('responsaveis')
                ->nullOnDelete();
            $table->timestamp('aberto_em')->useCurrent();
            $table->timestamps();

            // A listagem filtra por status/prioridade e a distribuição automática
            // conta chamados em aberto por responsável.
            $table->index('status');
            $table->index('prioridade');
            $table->index(['responsavel_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chamados');
    }
};
