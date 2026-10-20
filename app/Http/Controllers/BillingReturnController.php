<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingReturnController extends Controller
{
    /**
     * Tela de retorno com sucesso do Checkout Pro.
     */
    public function success(Request $request): View
    {
        $paymentId = $request->query('payment_id');
        $status = $request->query('status', 'approved');
        $merchantOrderId = $request->query('merchant_order_id');

        return view('billing.success', [
            'paymentId' => $paymentId,
            'status' => $status,
            'merchantOrderId' => $merchantOrderId,
        ]);
    }

    /**
     * Tela de retorno com pagamento em análise/pendente.
     */
    public function pending(Request $request): View
    {
        $paymentId = $request->query('payment_id');

        return view('billing.pending', [
            'paymentId' => $paymentId,
        ]);
    }

    /**
     * Tela de retorno com falha ou rejeição de pagamento.
     */
    public function failure(Request $request): View
    {
        return view('billing.failure');
    }
}
