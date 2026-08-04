<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\CreateCampaignDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCampaignRequest;
use App\Http\Resources\NewsletterCampaignResource;
use App\Services\NewsletterService;
use Illuminate\Http\JsonResponse;

class AdminNewsletterCampaignController extends Controller
{
    public function __construct(
        protected NewsletterService $newsletterService
    ) {}

    public function store(StoreCampaignRequest $request): JsonResponse
    {
        \Illuminate\Support\Facades\Gate::authorize('manage', \App\Models\NewsletterCampaign::class);

        $dto = CreateCampaignDTO::fromArray($request->validated(), $request->user()->id);
        $campaign = $this->newsletterService->createCampaign($dto);

        return response()->json([
            'message' => 'Campanha de newsletter criada com sucesso.',
            'data' => new NewsletterCampaignResource($campaign),
        ], 201);
    }

    public function send(int $id): JsonResponse
    {
        \Illuminate\Support\Facades\Gate::authorize('manage', \App\Models\NewsletterCampaign::class);

        $campaign = $this->newsletterService->dispatchCampaign($id);

        return response()->json([
            'message' => 'Envio da campanha de newsletter iniciado com sucesso.',
            'data' => new NewsletterCampaignResource($campaign),
        ], 200);
    }

    public function status(int $id): JsonResponse
    {
        \Illuminate\Support\Facades\Gate::authorize('manage', \App\Models\NewsletterCampaign::class);

        $status = $this->newsletterService->getCampaignStatus($id);

        return response()->json([
            'data' => $status,
        ], 200);
    }
}
