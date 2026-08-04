<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\SubscribeDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubscribeNewsletterRequest;
use App\Http\Resources\NewsletterSubscriberResource;
use App\Services\NewsletterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function __construct(
        protected NewsletterService $newsletterService
    ) {}

    public function subscribe(SubscribeNewsletterRequest $request): JsonResponse
    {
        $dto = SubscribeDTO::fromArray($request->validated());
        $subscriber = $this->newsletterService->subscribe($dto);

        return response()->json([
            'message' => 'Inscrição realizada com sucesso! Por favor, verifique seu e-mail para confirmar.',
            'data' => new NewsletterSubscriberResource($subscriber),
        ], 201);
    }

    public function confirm(Request $request): JsonResponse
    {
        $token = (string) $request->query('token');
        $subscriber = $this->newsletterService->confirm($token);

        return response()->json([
            'message' => 'Inscrição confirmada com sucesso!',
            'data' => new NewsletterSubscriberResource($subscriber),
        ], 200);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $token = (string) $request->input('token', $request->query('token'));
        $subscriber = $this->newsletterService->unsubscribe($token);

        return response()->json([
            'message' => 'Inscrição cancelada com sucesso.',
            'data' => new NewsletterSubscriberResource($subscriber),
        ], 200);
    }
}
