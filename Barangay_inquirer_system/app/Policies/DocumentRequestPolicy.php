<?php

namespace App\Policies;

use App\Models\DocumentRequest;
use App\Models\User;

class DocumentRequestPolicy
{
    /**
     * Determine if the user can view the document request.
     */
    public function view(User $user, DocumentRequest $documentRequest): bool
    {
        // Users can only view their own document requests
        if ($user->role === 'resident') {
            return $documentRequest->user_id === $user->id;
        }

        // Admins and super_admins can view all document requests
        return in_array($user->role, ['admin', 'super_admin']);
    }

    /**
     * Determine if the user can download the attachment from this request.
     */
    public function download(User $user, DocumentRequest $documentRequest): bool
    {
        // Users can only download attachments from their own requests
        if ($user->role === 'resident') {
            return $documentRequest->user_id === $user->id && $documentRequest->attachment !== null;
        }

        // Admins and super_admins can download attachments from any request
        return in_array($user->role, ['admin', 'super_admin']) && $documentRequest->attachment !== null;
    }

    /**
     * Determine if the user can create a new document request.
     */
    public function create(User $user): bool
    {
        // Only residents can create document requests
        return $user->role === 'resident';
    }

    /**
     * Determine if the user can update the document request.
     */
    public function update(User $user, DocumentRequest $documentRequest): bool
    {
        // Residents cannot update their requests
        if ($user->role === 'resident') {
            return false;
        }

        // Only admins and super_admins can update requests
        return in_array($user->role, ['admin', 'super_admin']);
    }

    /**
     * Determine if the user can delete the document request.
     */
    public function delete(User $user, DocumentRequest $documentRequest): bool
    {
        // Only the request owner (resident) can delete their own pending requests
        if ($user->role === 'resident') {
            return $documentRequest->user_id === $user->id && $documentRequest->status === 'pending';
        }

        // Admins and super_admins cannot delete requests
        return false;
    }

    /**
     * Determine if the user can add notes to a request.
     */
    public function addNotes(User $user, DocumentRequest $documentRequest): bool
    {
        // Only admins and super_admins can add notes
        return in_array($user->role, ['admin', 'super_admin']);
    }

    /**
     * Determine if the user can approve a request.
     */
    public function approve(User $user, DocumentRequest $documentRequest): bool
    {
        // Only admins and super_admins can approve requests
        return in_array($user->role, ['admin', 'super_admin']);
    }

    /**
     * Determine if the user can reject a request.
     */
    public function reject(User $user, DocumentRequest $documentRequest): bool
    {
        // Only admins and super_admins can reject requests
        return in_array($user->role, ['admin', 'super_admin']);
    }
}
