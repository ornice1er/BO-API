<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['bjpay', 'fedapay', 'kkiapay']);
            $table->string('api_key')->nullable();
            $table->string('secret_key')->nullable();
            $table->string('webhook_url')->nullable();
            $table->enum('env', ['test', 'production'])->default('test');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('prestations', function (Blueprint $table) {
            $table->boolean('is_payant')->default(false)->after('has_document_circuit');
            $table->decimal('montant', 12, 2)->nullable()->after('is_payant');
            $table->foreignId('payment_account_id')->nullable()->constrained('payment_accounts')->nullOnDelete()->after('montant');
        });
    }

    public function down(): void
    {
        Schema::table('prestations', function (Blueprint $table) {
            $table->dropForeign(['payment_account_id']);
            $table->dropColumn(['is_payant', 'montant', 'payment_account_id']);
        });
        Schema::dropIfExists('payment_accounts');
    }
};
