<?php

namespace App\Policies;

use App\Models\CmsCategory;
use App\Models\User;
use App\Models\CmsAuditLog;

class CmsCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasCmsPermission('cms.categories.view');
    }

    public function view(User $user, CmsCategory $category): bool
    {
        return $user->hasCmsPermission('cms.categories.view');
    }

    public function create(User $user): bool
    {
        return $user->hasCmsPermission('cms.categories.create');
    }

    public function update(User $user, CmsCategory $category): bool
    {
        return $user->hasCmsPermission('cms.categories.edit');
    }

    public function delete(User $user, CmsCategory $category): bool
    {
        if (!$user->hasCmsPermission('cms.categories.delete')) {
            CmsAuditLog::logPermissionDenied('cms.categories.delete', $user, "Category: {$category->name}");
            return false;
        }

        return true;
    }

    public function restore(User $user, CmsCategory $category): bool
    {
        return $user->hasCmsPermission('cms.categories.delete');
    }

    public function forceDelete(User $user, CmsCategory $category): bool
    {
        return $user->hasCmsPermission('cms.categories.delete');
    }
}
