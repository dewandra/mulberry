<?php

namespace App\Policies;

use App\Models\Feedback;
use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Super-admins and admins bypass all checks
     * EXCEPT submitFeedback (they are not allowed to submit feedback).
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($ability !== 'submitFeedback' && $user->hasRole(['super_admin', 'admin'])) {
            return true;
        }
        return null;
    }

    /**
     * Can the user view this project?
     * Applies to project detail page AND file/attachment viewing.
     */
    public function view(User $user, Project $project): bool
    {
        if ($user->hasRole('client')) {
            return $project->client_id === $user->client_id;
        }

        if ($user->hasRole('pic')) {
            return $project->pics()->where('pic_user_id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Can the user submit feedback on this project?
     * Only clients and PICs may submit feedback — never admins.
     */
    public function submitFeedback(User $user, Project $project): bool
    {
        return $user->hasRole(['client', 'pic']);
    }

    /**
     * Can the user delete this feedback entry?
     * - Admins can always delete (handled by before()).
     * - Regular users can only delete their own feedback.
     */
    public function deleteFeedback(User $user, Project $project, Feedback $feedback): bool
    {
        abort_if($feedback->project_id !== $project->id, 404);
        return $feedback->submitted_by === $user->id;
    }
}
