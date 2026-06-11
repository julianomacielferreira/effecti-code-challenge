<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateContratosValorView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            CREATE OR REPLACE VIEW contratos_valor_view AS
            SELECT 
                c.id AS contrato_id,
                c.cliente_id,
                c.status,
                COALESCE(SUM(ci.quantidade * ci.valor_unitario), 0) AS total_mensal
            FROM contratos c
            LEFT JOIN contrato_itens ci ON ci.contrato_id = c.id
            GROUP BY c.id, c.cliente_id, c.status
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS contratos_valor_view");
    }
}
