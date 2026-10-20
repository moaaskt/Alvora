<?php

use App\Http\Controllers\Api\MercadoPagoWebhookController;
use Illuminate\Support\Facades\Route;

// Webhook seguro do Mercado Pago
Route::post('/webhooks/mercadopago', [MercadoPagoWebhookController::class, 'handle'])->name('webhooks.mercadopago');
