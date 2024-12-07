<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceService
{
    public function generateInvoice(Order $order)
    {
        $pdf = PDF::loadView('pdf.invoice', [
            'order' => $order
        ]);

        return $pdf->output();
    }

    public function downloadInvoice(Order $order)
    {
        $pdf = PDF::loadView('pdf.invoice', [
            'order' => $order
        ]);

        return $pdf->download('invoice-' . $order->id . '.pdf');
    }

    public function streamInvoice(Order $order)
    {
        $pdf = PDF::loadView('pdf.invoice', [
            'order' => $order
        ]);

        return $pdf->stream('invoice-' . $order->id . '.pdf');
    }
}
