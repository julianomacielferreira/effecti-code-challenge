<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContratoItensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contrato_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contrato_id')->constrained()->cascadeOnDelete();
            $table->foreignId('servico_id')->constrained('servicos')->restrictOnDelete();
            $table->unsignedInteger('quantidade');
            $table->decimal('valor_unitario', 10, 2);
            $table->timestamps();
            $table->unique(['contrato_id', 'servico_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contrato_itens');
    }
}
