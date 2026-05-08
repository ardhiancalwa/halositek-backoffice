<?php

namespace App\Jobs;

use App\Exceptions\HaloSitekAIException;
use App\Models\Message;
use App\Services\HaloSitekAIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateAssistantReplyJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     */
    public function __construct(
        public string $userId,
        public string $conversationId,
        public string $message,
        public array $history = []
    ) {
    }

    public function handle(HaloSitekAIService $ai): void
    {
        try {
            $result = $ai->generate(
                userId: $this->userId,
                message: $this->message,
                history: $this->history,
            );

            $assistantType = is_string($result['type'] ?? null) && ($result['type'] ?? '') !== ''
                ? (string) $result['type']
                : Message::TYPE_TEXT;
            $assistantContent = $result['content'] ?? '';
            $assistantContent = is_string($assistantContent)
                ? $assistantContent
                : (is_scalar($assistantContent) ? (string) $assistantContent : '');

            Message::create([
                'conversation_id' => $this->conversationId,
                'user_id' => $this->userId,
                'role' => Message::ROLE_ASSISTANT,
                'type' => $assistantType,
                'content' => $assistantContent,
                'body' => $assistantContent,
                'attachment' => null,
                'read_at' => null,
            ]);
        } catch (HaloSitekAIException $exception) {
            Log::error('Queued HaloSitek AI chat request failed.', [
                'user_id' => $this->userId,
                'conversation_id' => $this->conversationId,
                'status' => $exception->statusCode(),
                'error' => $exception->getMessage(),
                'context' => $exception->context(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Unexpected queued AI chat integration error.', [
                'user_id' => $this->userId,
                'conversation_id' => $this->conversationId,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }
    }
}
