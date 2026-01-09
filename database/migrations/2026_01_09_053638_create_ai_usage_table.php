<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ai_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('feature_type'); // 'chat', 'transcribe', 'autocomplete', 'summarize'
            $table->integer('tokens_used')->default(0);
            $table->text('prompt')->nullable();
            $table->text('response')->nullable();
            $table->string('model_used')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index('feature_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ai_usage');
    }
};
