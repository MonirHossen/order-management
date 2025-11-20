<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderInvoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    /**
     * Generate invoice PDF
     */
    public function generateInvoice(Order $order): OrderInvoice
    {
        // Generate invoice number
        $invoiceNumber = $this->generateInvoiceNumber();

        // Generate PDF
        $pdf = Pdf::loadView('invoices.order', [
            'order' => $order,
            'invoiceNumber' => $invoiceNumber,
        ]);

        // Store PDF
        $filename = "invoices/{$order->order_number}-{$invoiceNumber}.pdf";
        Storage::disk('public')->put($filename, $pdf->output());

        // Create invoice record
        return OrderInvoice::create([
            'order_id' => $order->id,
            'invoice_number' => $invoiceNumber,
            'file_path' => $filename,
            'generated_at' => now(),
        ]);
    }

    /**
     * Generate unique invoice number
     */
    protected function generateInvoiceNumber(): string
    {
        do {
            $number = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        } while (OrderInvoice::where('invoice_number', $number)->exists());

        return $number;
    }

    /**
     * Download invoice
     */
    public function downloadInvoice(OrderInvoice $invoice): string
    {
        return Storage::disk('public')->path($invoice->file_path);
    }

    /**
     * Regenerate invoice
     */
    public function regenerateInvoice(Order $order): OrderInvoice
    {
        // Delete old invoice if exists
        $oldInvoice = $order->invoices()->latest()->first();
        if ($oldInvoice) {
            Storage::disk('public')->delete($oldInvoice->file_path);
            $oldInvoice->delete();
        }

        return $this->generateInvoice($order);
    }
}