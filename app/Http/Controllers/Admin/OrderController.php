<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\LandingPage;
use App\Models\Order;
use App\Services\Orders\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string'],
            'page_id' => ['nullable', 'integer'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $orders = Order::query()
            ->with('landingPage')
            ->when($filters['q'] ?? null, function ($query, $q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('order_number', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%")
                        ->orWhere('full_name', 'like', "%{$q}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['page_id'] ?? null, fn ($query, $pageId) => $query->where('landing_page_id', $pageId))
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, $to) => $query->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'statuses' => OrderStatus::options(),
            'pages' => LandingPage::orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['landingPage', 'product', 'offer', 'statusHistories.user', 'attribution']);

        return view('admin.orders.show', [
            'order' => $order,
            'allowedStatuses' => $order->status->allowedTransitions(),
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', new Enum(OrderStatus::class)],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->orders->changeStatus(
            $order,
            OrderStatus::from($data['status']),
            $request->user(),
            $data['note'] ?? null,
        );

        return back()->with('success', 'تم تحديث حالة الطلب.');
    }

    public function addNote(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'note' => ['required', 'string', 'max:1000'],
        ]);

        // An internal note is recorded as a same-status history entry.
        $order->statusHistories()->create([
            'from_status' => $order->status,
            'to_status' => $order->status,
            'user_id' => $request->user()->id,
            'note' => $data['note'],
        ]);

        return back()->with('success', 'تمت إضافة الملاحظة.');
    }
}
