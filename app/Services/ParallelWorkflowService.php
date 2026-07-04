<?php

namespace App\Services;

use App\Models\Book;
use App\Models\User;

class ParallelWorkflowService
{
    public function isParallelWorkflow(Book $book): bool
    {
        return true;
    }

    public function hasEditing(Book $book): bool
    {
        $package = $book->publishingPackage()->first();

        if (!$package) {
            return true;
        }

        return (bool) $package->includes_editing;
    }

    public function isAuthorRegistered(Book $book): bool
    {
        return !empty($book->author_user_id);
    }

    public function getWorkflowSteps(Book $book): array
    {
        if ($this->hasEditing($book)) {
            return [
                'draft',
                'editing',
                'editing_review',
                'layout',
                'layout_review',
                'cover_design',
                'cover_review',
                'acc_penulis',
                'audit_isbn',
                'ready_for_isbn',
                'isbn_submitted',
                'isbn_approved',
                'selesai',
            ];
        }

        return [
            'draft',
            'layout',
            'layout_review',
            'cover_design',
            'cover_review',
            'acc_penulis',
            'audit_isbn',
            'ready_for_isbn',
            'isbn_submitted',
            'isbn_approved',
            'selesai',
        ];
    }

    public function getAvailableNextSteps(Book $book, ?User $user = null): array
    {
        $current = (string) $book->workflow_status;
        $hasEditing = $this->hasEditing($book);
        $allSteps = $this->getWorkflowSteps($book);
        $isAdmin = $user && in_array($user->role, ['superadmin', 'admin', 'owner'], true);

        if ($isAdmin) {
            $currentIndex = array_search($current, $allSteps, true);
            $currentIndex = $currentIndex === false ? -1 : (int) $currentIndex;
            $available = [];

            foreach ($allSteps as $index => $step) {
                if ($step === 'selesai') {
                    continue;
                }

                // Allow starting tracks in parallel regardless of current workflow_status.
                if ($step === 'editing' && $hasEditing && !$book->tanggal_mulai_editing) {
                    $available[] = $step;
                    continue;
                }

                if ($step === 'layout' && !$book->tanggal_mulai_layout) {
                    $available[] = $step;
                    continue;
                }

                if ($step === 'cover_design' && !$book->tanggal_mulai_cover) {
                    $available[] = $step;
                    continue;
                }

                if ($index <= $currentIndex) {
                    continue;
                }

                // Review steps require corresponding track to be started.
                if ($step === 'editing_review' && (!$hasEditing || !$book->tanggal_mulai_editing)) {
                    continue;
                }

                if ($step === 'layout_review' && !$book->tanggal_mulai_layout) {
                    continue;
                }

                if ($step === 'cover_review' && !$book->tanggal_mulai_cover) {
                    continue;
                }

                if ($step === 'acc_penulis' && !$this->areAllReviewsDone($book)) {
                    continue;
                }

                $available[] = $step;
            }

            return array_values(array_unique($available));
        }

        $progressionRules = [
            'draft' => $hasEditing ? ['editing', 'layout', 'cover_design'] : ['layout', 'cover_design'],
            'editing' => ['editing_review', 'layout', 'cover_design'],
            'editing_review' => $hasEditing ? ['layout', 'cover_design'] : [],
            'layout' => ['layout_review'],
            'layout_review' => ['cover_design'],
            'cover_design' => ['cover_review'],
            'cover_review' => ['acc_penulis'],
            'acc_penulis' => ['audit_isbn'],
            'audit_isbn' => ['ready_for_isbn'],
            'ready_for_isbn' => ['isbn_submitted'],
            'isbn_submitted' => ['isbn_approved'],
            'isbn_approved' => ['selesai'],
            'selesai' => [],
        ];

        $available = $progressionRules[$current] ?? [];

        if ($hasEditing && $current !== 'editing') {
            $available = array_filter($available, fn($step) => $step !== 'editing_review');
        }

        if ($current !== 'layout') {
            $available = array_filter($available, fn($step) => $step !== 'layout_review');
        }

        if ($current !== 'cover_design') {
            $available = array_filter($available, fn($step) => $step !== 'cover_review');
        }

        if (!$this->areAllReviewsDone($book)) {
            $available = array_filter($available, fn($step) => $step !== 'acc_penulis');
        }

        return $this->filterByRole($user, array_values($available), $hasEditing);
    }

    private function filterByRole(?User $user, array $availableSteps, bool $hasEditing): array
    {
        if (!$user) {
            return [];
        }

        $role = $user->role;

        if (in_array($role, ['superadmin', 'admin', 'owner'], true)) {
            return $availableSteps;
        }

        $roleAllowedSteps = [
            'editor' => $hasEditing ? ['editing', 'editing_review'] : [],
            'layouter' => ['layout', 'layout_review'],
            'designer' => ['cover_design', 'cover_review'],
            'isbn' => ['audit_isbn', 'ready_for_isbn', 'isbn_submitted', 'isbn_approved'],
            'finance' => [],
            'author' => ['acc_penulis'],
        ];

        $allowed = $roleAllowedSteps[$role] ?? [];

        return array_values(array_filter($availableSteps, fn($step) => in_array($step, $allowed, true)));
    }

    public function getPipelineStepStatus(Book $book): array
    {
        $statuses = [];
        $steps = $this->getWorkflowSteps($book);
        $current = $book->workflow_status;
        $approvedTypes = $book->relationLoaded('approvals') ? $book->approvals : collect();
        $hasEditing = $this->hasEditing($book);
        $stepOrder = array_flip($steps);

        foreach ($steps as $step) {
            $stepOrderValue = $stepOrder[$step] ?? 0;
            $currentOrder = $stepOrder[$current] ?? 0;
            $isInProgress = $step === $current;
            $isCompleted = $stepOrderValue < $currentOrder;

            $needsAuthorApproval = false;
            $approvalType = null;

            if ($hasEditing && $step === 'editing_review') {
                $needsAuthorApproval = true;
                $approvalType = 'editor';
            }

            if ($step === 'layout_review') {
                $needsAuthorApproval = true;
                $approvalType = 'layout';
            }

            if ($step === 'cover_review') {
                $needsAuthorApproval = true;
                $approvalType = 'cover';
            }

            if ($isCompleted && $needsAuthorApproval) {
                $statuses[$step] = $approvedTypes->where('approval_type', $approvalType)->isNotEmpty()
                    ? 'completed_with_approval'
                    : 'completed';
            } elseif ($isCompleted) {
                $statuses[$step] = 'completed';
            } elseif ($isInProgress) {
                $statuses[$step] = 'in_progress';
            } else {
                $statuses[$step] = 'pending';
            }
        }

        return $statuses;
    }

    public function getProgressPercent(Book $book): int
    {
        $current = $book->workflow_status;

        $map = [
            'draft' => 0,
            'editing' => 10,
            'editing_review' => 20,
            'layout' => 30,
            'layout_review' => 40,
            'cover_design' => 50,
            'cover_review' => 60,
            'acc_penulis' => 70,
            'audit_isbn' => 80,
            'ready_for_isbn' => 85,
            'isbn_submitted' => 90,
            'isbn_approved' => 100,
            'selesai' => 100,
        ];

        if (!$this->hasEditing($book)) {
            $map = [
                'draft' => 0,
                'layout' => 15,
                'layout_review' => 25,
                'cover_design' => 35,
                'cover_review' => 45,
                'acc_penulis' => 60,
                'audit_isbn' => 70,
                'ready_for_isbn' => 78,
                'isbn_submitted' => 88,
                'isbn_approved' => 100,
                'selesai' => 100,
            ];
        }

        return $map[$current] ?? 0;
    }

    public function getTrackStatus(Book $book): array
    {
        $status = $book->workflow_status;
        $result = [];

        if ($this->hasEditing($book)) {
            $editingDone = in_array($status, [
                'editing_review',
                'layout',
                'layout_review',
                'cover_design',
                'cover_review',
                'acc_penulis',
                'audit_isbn',
                'ready_for_isbn',
                'isbn_submitted',
                'isbn_approved',
                'selesai',
            ], true);

            $result['editing'] = [
                'completed' => $editingDone,
                'in_progress' => in_array($status, ['editing', 'editing_review'], true),
                'status' => $editingDone
                    ? 'completed'
                    : (in_array($status, ['editing', 'editing_review'], true) ? 'in_progress' : 'pending'),
            ];
        }

        $layoutDone = in_array($status, [
            'layout_review',
            'cover_design',
            'cover_review',
            'acc_penulis',
            'audit_isbn',
            'ready_for_isbn',
            'isbn_submitted',
            'isbn_approved',
            'selesai',
        ], true);

        $result['layout'] = [
            'completed' => $layoutDone,
            'in_progress' => in_array($status, ['layout', 'layout_review'], true),
            'status' => $layoutDone
                ? 'completed'
                : (in_array($status, ['layout', 'layout_review'], true) ? 'in_progress' : 'pending'),
        ];

        $coverDone = in_array($status, [
            'cover_review',
            'acc_penulis',
            'audit_isbn',
            'ready_for_isbn',
            'isbn_submitted',
            'isbn_approved',
            'selesai',
        ], true);

        $result['cover'] = [
            'completed' => $coverDone,
            'in_progress' => in_array($status, ['cover_design', 'cover_review'], true),
            'status' => $coverDone
                ? 'completed'
                : (in_array($status, ['cover_design', 'cover_review'], true) ? 'in_progress' : 'pending'),
        ];

        return $result;
    }

    public function canAccPenulisByAdmin(Book $book, User $user): bool
    {
        return in_array($user->role, ['superadmin', 'admin', 'owner'], true)
            && !$this->isAuthorRegistered($book)
            && $this->areAllReviewsDone($book);
    }

    private function areAllReviewsDone(Book $book): bool
    {
        $status = $book->workflow_status;

        $layoutDone = in_array($status, [
            'layout_review',
            'cover_design',
            'cover_review',
            'acc_penulis',
            'audit_isbn',
            'ready_for_isbn',
            'isbn_submitted',
            'isbn_approved',
            'selesai',
        ], true);

        $coverDone = in_array($status, [
            'cover_review',
            'acc_penulis',
            'audit_isbn',
            'ready_for_isbn',
            'isbn_submitted',
            'isbn_approved',
            'selesai',
        ], true);

        if ($this->hasEditing($book)) {
            $editingDone = in_array($status, [
                'editing_review',
                'layout',
                'layout_review',
                'cover_design',
                'cover_review',
                'acc_penulis',
                'audit_isbn',
                'ready_for_isbn',
                'isbn_submitted',
                'isbn_approved',
                'selesai',
            ], true);

            return $editingDone && $layoutDone && $coverDone;
        }

        return $layoutDone && $coverDone;
    }
}
