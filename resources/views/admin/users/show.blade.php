@extends('admin.layouts.app')

@section('title', 'User Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>User Details</h2>
        <div>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit User
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
        </div>
    </div>

    <div class="row">
        <!-- User Information -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">User Information</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Name:</strong>
                        <p class="mb-0">{{ $user->name }}</p>
                    </div>
                    <div class="mb-3">
                        <strong>Email:</strong>
                        <p class="mb-0">{{ $user->email }}</p>
                    </div>
                    <div class="mb-3">
                        <strong>Phone:</strong>
                        <p class="mb-0">{{ $user->phone ?? 'Not provided' }}</p>
                    </div>
                    <div class="mb-3">
                        <strong>Address:</strong>
                        <p class="mb-0">{{ $user->address ?? 'Not provided' }}</p>
                    </div>
                    <div class="mb-3">
                        <strong>Joined:</strong>
                        <p class="mb-0">{{ $user->created_at->format('F d, Y') }}</p>
                    </div>
                    <div class="mb-3">
                        <strong>Total Orders:</strong>
                        <p class="mb-0">{{ $user->orders->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order History -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Order History</h4>
                </div>
                <div class="card-body">
                    @if($user->orders->count() > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->orders as $order)
                                        <tr>
                                            <td>{{ $order->order_number }}</td>
                                            <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <span class="badge bg-{{ 
                                                    $order->status === 'completed' ? 'success' : 
                                                    ($order->status === 'cancelled' ? 'danger' : 
                                                    ($order->status === 'processing' ? 'info' : 'warning'))
                                                }}">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                            <td>${{ number_format($order->total_amount, 2) }}</td>
                                            <td>
                                                <a href="{{ route('admin.orders.show', $order) }}" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <p class="mb-0">No orders found for this user.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
