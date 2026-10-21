<?php

use App\Http\Controllers\Api\HealthCheckController;
use App\Http\Controllers\Api\MercadoPagoWebhookController;
use Illuminate\Support\Facades\Route;

// Endpoint público de Health Check do sistema
Route::get('/health', [HealthCheckController::class, 'check'])->name('api.health');

// Webhook seguro do Mercado Pago
Route::post('/webhooks/mercadopago', [MercadoPagoWebhookController::class, 'handle'])->name('webhooks.mercadopago');
