<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ---- Roles + suspension on users (plain columns, no FKs: safe on SQLite and MySQL) ----
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('student')->index();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('suspended_until')->nullable();
            $table->string('suspension_reason')->nullable();
            $table->timestamp('admin_seen_at')->nullable(); // notifications read marker
        });

        // ---- Pinning + moderator removal on posts ----
        Schema::table('posts', function (Blueprint $table) {
            $table->boolean('is_pinned')->default(false)->index();
            $table->unsignedBigInteger('removed_by')->nullable(); // set only when a moderator removes it
            $table->string('removal_reason')->nullable();
        });

        // ---- Student reports of posts ----
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // the reporter
            $table->string('reason', 30);
            $table->string('details', 500)->nullable();
            $table->string('status', 20)->default('open')->index(); // open | dismissed | actioned
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['post_id', 'user_id']);
        });

        // ---- Announcements shown to every student ----
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->text('body');
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // ---- Memory capsules ----
        Schema::create('memories', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->text('body');
            $table->string('image_path')->nullable();
            $table->dateTime('unlock_at');
            $table->timestamp('opened_at')->nullable(); // set when an admin opens it early
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // ---- Mysteries, official clues and student theories ----
        Schema::create('mysteries', function (Blueprint $table) {
            $table->id();
            $table->string('title', 140);
            $table->text('summary');
            $table->string('location', 120)->nullable();
            $table->string('status', 20)->default('open')->index(); // open | solved | debunked
            $table->text('resolution')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('mystery_clues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mystery_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('mystery_theories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mystery_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->boolean('is_accepted')->default(false);
            $table->timestamps();
        });

        // ---- Audit trail ----
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index(); // who did it (null = guest/system)
            $table->string('action', 40)->index();                     // e.g. post.created, user.suspended
            $table->string('description');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->boolean('notify')->default(false)->index();        // shows up in admin notifications
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('mystery_theories');
        Schema::dropIfExists('mystery_clues');
        Schema::dropIfExists('mysteries');
        Schema::dropIfExists('memories');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('reports');

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['is_pinned', 'removed_by', 'removal_reason']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'suspended_at', 'suspended_until', 'suspension_reason', 'admin_seen_at']);
        });
    }
};
