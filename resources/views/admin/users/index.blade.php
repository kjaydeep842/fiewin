@extends('layouts.admin')

@section('page-title', 'User Management')
@section('page-subtitle', 'View registered players, balances, and toggle access')

@section('content')
<div class="admin-card p-3">
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email / Mobile</th>
                    <th>Main Balance</th>
                    <th>Commission</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td class="text-muted">#{{ $user->id }}</td>
                    <td class="fw-semibold">{{ $user->name }}</td>
                    <td>
                        {{ $user->email }}<br>
                        <small class="text-muted">{{ $user->mobile }}</small>
                    </td>
                    <td class="fw-bold text-success">₹{{ number_format($user->wallet?->main_balance ?? 0, 2) }}</td>
                    <td class="fw-bold text-warning">₹{{ number_format($user->wallet?->commission_balance ?? 0, 2) }}</td>
                    <td>
                        <span class="badge {{ $user->status === 'active' ? 'badge-soft-success' : 'badge-soft-danger' }} rounded-pill px-3">
                            {{ strtoupper($user->status) }}
                        </span>
                    </td>
                    <td>
                        <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="btn btn-sm {{ $user->status === 'active' ? 'btn-outline-danger' : 'btn-outline-success' }} rounded-pill py-1 px-3"
                                style="font-size: 0.78rem;">
                                {{ $user->status === 'active' ? 'Block' : 'Activate' }}
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $users->links() }}
    </div>
</div>
@endsection
