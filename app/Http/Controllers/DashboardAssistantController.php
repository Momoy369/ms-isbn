<?php

namespace App\Http\Controllers;

use App\Models\AssistantChatLog;
use App\Models\User;
use App\Services\DashboardAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardAssistantController extends Controller
{
    public function index()
    {
        return view('assistant.index');
    }

    public function ask(Request $request, DashboardAssistantService $assistant): JsonResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:3000'],
            'current_path' => ['nullable', 'string', 'max:255'],
            'page_title' => ['nullable', 'string', 'max:255'],
        ]);

        $result = $assistant->ask(
            $request->user(),
            (string) $data['question'],
            [
                'current_path' => (string) ($data['current_path'] ?? ''),
                'page_title' => (string) ($data['page_title'] ?? ''),
            ]
        );

        return response()->json([
            'ok' => true,
            'answer' => (string) ($result['answer'] ?? ''),
            'source' => (string) ($result['source'] ?? 'local'),
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $data = $request->validate([
            'before_id' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $limit = (int) ($data['limit'] ?? 30);
        $beforeId = (int) ($data['before_id'] ?? 0);

        $query = AssistantChatLog::query()
            ->where('user_id', $request->user()->id);

        if ($beforeId > 0) {
            $query->where('id', '<', $beforeId);
        }

        $rows = $query
            ->latest('id')
            ->limit($limit)
            ->get(['id', 'question', 'answer', 'source', 'created_at']);

        $items = $rows
            ->reverse()
            ->values();

        $oldestId = optional($rows->last())->id;
        $hasMore = false;

        if ($oldestId !== null) {
            $hasMore = AssistantChatLog::query()
                ->where('user_id', $request->user()->id)
                ->where('id', '<', (int) $oldestId)
                ->exists();
        }

        return response()->json([
            'ok' => true,
            'items' => $items,
            'next_before_id' => $oldestId,
            'has_more' => $hasMore,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $actor = $request->user();
        $targetUserId = (int) $request->query('user_id', $actor->id);

        $supportRoles = ['admin', 'owner', 'finance', 'superadmin'];
        $canExportOtherUser = in_array((string) $actor->role, $supportRoles, true);

        if (!$canExportOtherUser) {
            $targetUserId = (int) $actor->id;
        }

        $targetUser = User::query()->find($targetUserId);

        if ($targetUser === null) {
            abort(404, 'User target export tidak ditemukan.');
        }

        $logs = AssistantChatLog::query()
            ->where('user_id', $targetUser->id)
            ->orderBy('id')
            ->get(['created_at', 'source', 'question', 'answer', 'context']);

        $filename = 'assistant-chat-history-user-' . $targetUser->id . '-' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($logs) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['created_at', 'source', 'question', 'answer', 'current_path', 'page_title']);

            foreach ($logs as $log) {
                $ctx = is_array($log->context) ? $log->context : [];

                fputcsv($out, [
                    optional($log->created_at)->toDateTimeString(),
                    (string) $log->source,
                    (string) $log->question,
                    (string) ($log->answer ?? ''),
                    (string) ($ctx['current_path'] ?? ''),
                    (string) ($ctx['page_title'] ?? ''),
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
