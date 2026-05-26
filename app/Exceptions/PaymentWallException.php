<?php

namespace App\Exceptions;

use Exception;

class PaymentWallException extends Exception
{
    public function render($request)
    {
        return response()->json([
            'status' => 'payment_required',
            'message' => $this->getMessage() ?: '5 mesaj hakkınız doldu. Sınırsız mesajlaşmak için kilidi açmalısınız.',
        ], 402);
    }
}
