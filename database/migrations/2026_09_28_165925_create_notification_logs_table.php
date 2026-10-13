<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();

            // Who received the notification
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Channel: whatsapp, email, sms
            $table->string('channel', 30)->default('whatsapp');

            // Tipe notifikasi
            $table->string('type', 60);

            // Destination (phone number or email)
            $table->string('recipient', 100)->nullable();

            // The message body sent
            $table->text('message');

            // Status: sent, failed, pending
            $table->string('status', 20)->default('pending');

            // Error message if failed
            $table->text('error')->nullable();

            // Subjek polymorphic
            $table->nullableMorphs('notifiable');

            // Provider response reference
            $table->string('provider_reference', 100)->nullable();

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
