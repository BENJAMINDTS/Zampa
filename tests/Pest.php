<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature');

beforeEach(function () {
    Http::preventStrayRequests();

    Http::fake([
        'api.groq.com/*' => Http::response([
            'choices' => [
                ['message' => ['content' => 'Respuesta mock del chatbot']],
            ],
            'usage' => ['total_tokens' => 50],
        ], 200),
    ]);
});
