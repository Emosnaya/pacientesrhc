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
        Schema::create('soporte_mensajes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('sender_id');
            $table->enum('sender_type', ['user', 'admin']);
            $table->text('mensaje');
            $table->timestamp('leido_at')->nullable();
            $table->timestamps();

            $table->foreign('ticket_id')->references('id')->on('soporte_tickets')->onDelete('cascade');
            $table->index(['ticket_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('soporte_mensajes');
    }
};
