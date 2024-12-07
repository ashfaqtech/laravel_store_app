<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;

class OrdersExport
{
    protected $orders;

    public function __construct(Collection $orders)
    {
        $this->orders = $orders;
    }

    public function download($filename)
    {
        Excel::create($filename, function($excel) {
            $excel->sheet('Orders', function($sheet) {
                $data = [];
                
                // Headers
                $data[] = [
                    'Order Number',
                    'Date',
                    'Customer Name',
                    'Email',
                    'Total Amount',
                    'Status',
                    'Payment Status',
                    'Items'
                ];

                // Data rows
                foreach ($this->orders as $order) {
                    $items = $order->items->map(function($item) {
                        return $item->product_name . ' (x' . $item->quantity . ')';
                    })->implode(', ');

                    $data[] = [
                        $order->order_number,
                        $order->created_at->format('Y-m-d H:i:s'),
                        $order->shipping_name,
                        $order->shipping_email,
                        $order->total_amount,
                        $order->status,
                        $order->payment_status,
                        $items
                    ];
                }

                $sheet->fromArray($data);

                // Styling
                $sheet->row(1, function($row) {
                    $row->setFontWeight('bold');
                });

                $sheet->setAutoSize(true);
            });
        })->export('xlsx');
    }
}
