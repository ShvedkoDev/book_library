<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;
use App\Models\CmsAuditLog;
use Illuminate\Auth\Access\Response;

class PagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasCmsPermission('cms.pages.view');
    }

    public function view(User $user, Page $page): bool
    {
        if (!$user->hasCmsPermission('cms.pages.view')) {
            CmsAuditLog::logPermissionDenied('cms.pages.view', $user, "Page: {$page->title}");
            return false;
        }

        CmsAuditLog::logContentAccessed($page, $user);
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasCmsPermission('cms.pages.create');
    }

    public function update(User $user, Page $page): bool
    {
        if ($user->hasCmsPermission('cms.pages.edit.any')) {
            return true;
        }

        if ($user->hasCmsPermission('cms.pages.edit.own') && $page->created_by === $user->id) {
            return true;
        }

        CmsAuditLog::logPermissionDenied('cms.pages.edit', $user, "Page: {$page->title}");
        return false;
    }

    public function delete(User $user, Page $page): bool
    {
        if ($user->hasCmsPermission('cms.pages.delete.any')) {
            return true;
        }

        if ($user->hasCmsPermission('cms.pages.delete.own') && $page->created_by === $user->id) {
            return true;
        }

        CmsAuditLog::logPermissionDenied('cms.pages.delete', $user, "Page: {$page->title}");
        return false;
    }

    public function publish(User $user, Page $page): bool
    {
        return $user->hasCmsPermission('cms.pages.publish');
    }

    public function restore(User $user, Page $page): bool
    {
        return $user->hasCmsPermission('cms.pages.delete.any');
    }

    public function forceDelete(User $user, Page $page): bool
    {
        return $user->hasCmsPermission('cms.pages.delete.any');
    }
}
