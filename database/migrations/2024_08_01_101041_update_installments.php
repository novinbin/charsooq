<?php

use App\Enums\InstallmentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('installments', function (Blueprint $table) {
            $table->foreignId('factor_id')->constrained('factors');
            $table->decimal('amount', 10, 0);
            $table->date('due_date');
            $table->tinyInteger('profit_rate', false, true)->default(0);
            $table->enum('status', InstallmentStatus::values())->default(InstallmentStatus::InDue);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
