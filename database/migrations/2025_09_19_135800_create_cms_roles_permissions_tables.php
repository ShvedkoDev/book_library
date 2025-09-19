<?php

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
        // Create roles table
        Schema::create('cms_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->json('permissions')->nullable();
            $table->boolean('is_system_role')->default(false);
            $table->integer('level')->default(1); // Role hierarchy level
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['name', 'is_active']);
        });

        // Create permissions table
        Schema::create('cms_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->string('group'); // Group permissions logically
            $table->boolean('is_system_permission')->default(false);
            $table->timestamps();

            $table->index(['group', 'name']);
        });

        // Create role_permissions pivot table
        Schema::create('cms_role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('cms_roles')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('cms_permissions')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['role_id', 'permission_id']);
        });

        // Create user_roles table
        Schema::create('cms_user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('cms_roles')->onDelete('cascade');
            $table->timestamp('assigned_at')->useCurrent();
            $table->foreignId('assigned_by')->nullable()->constrained('users');
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'role_id']);
            $table->index(['user_id', 'is_active']);
        });

        // Create content workflow table
        Schema::create('cms_content_workflow', function (Blueprint $table) {
            $table->id();
            $table->string('workflowable_type'); // Model type (Page, etc.)
            $table->unsignedBigInteger('workflowable_id'); // Model ID
            $table->enum('status', ['draft', 'pending_review', 'approved', 'rejected', 'published', 'archived'])
                ->default('draft');
            $table->enum('previous_status', ['draft', 'pending_review', 'approved', 'rejected', 'published', 'archived'])
                ->nullable();
            $table->foreignId('author_id')->constrained('users'); // Content author
            $table->foreignId('reviewer_id')->nullable()->constrained('users'); // Assigned reviewer
            $table->foreignId('approver_id')->nullable()->constrained('users'); // Who approved/rejected
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->json('revision_history')->nullable();
            $table->timestamps();

            $table->index(['workflowable_type', 'workflowable_id']);
            $table->index(['status', 'reviewer_id']);
            $table->index(['author_id', 'status']);
        });

        // Create audit log table for permission changes
        Schema::create('cms_audit_log', function (Blueprint $table) {
            $table->id();
            $table->string('event'); // role_assigned, permission_changed, content_accessed, etc.
            $table->string('auditable_type')->nullable(); // Model type
            $table->unsignedBigInteger('auditable_id')->nullable(); // Model ID
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['event', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['auditable_type', 'auditable_id']);
        });

        // Add CMS fields to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->text('bio')->nullable();
            $table->string('avatar')->nullable();
            $table->string('phone')->nullable();
            $table->string('department')->nullable();
            $table->boolean('is_cms_user')->default(false);
            $table->timestamp('last_cms_access')->nullable();
            $table->json('cms_preferences')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name', 'last_name', 'bio', 'avatar', 'phone',
                'department', 'is_cms_user', 'last_cms_access',
                'cms_preferences', 'is_active'
            ]);
        });

        Schema::dropIfExists('cms_audit_log');
        Schema::dropIfExists('cms_content_workflow');
        Schema::dropIfExists('cms_user_roles');
        Schema::dropIfExists('cms_role_permissions');
        Schema::dropIfExists('cms_permissions');
        Schema::dropIfExists('cms_roles');
    }
};