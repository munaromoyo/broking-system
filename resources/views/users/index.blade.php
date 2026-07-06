@extends('layouts.app') {{-- Assuming you have a base layout --}}

@section('content')
<div class="container">
    <h2>Manage Users</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered align-middle">
        <thead>
            <tr>
                <th>Username</th>
                <th>Role</th>
                <th>Status</th>
                <th>Change Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr>
                    <td>{{ $user->user_name }}</td>
                    <td><span class="badge bg-info">{{ $user->role }}</span></td>
                    <td>
                        <span class="badge {{ $user->status == 'Active' ? 'bg-success' : 'bg-secondary' }}">
                            {{ $user->status ?? 'Active' }}
                        </span>
                    </td>
                    <td>
                        <form action="{{ route('users.updateRole', $user->id) }}" method="POST" class="d-flex">
                            @csrf
                            @method('PATCH')
                            <select name="new_role" class="form-select form-select-sm me-2">
                                @foreach(['Admin', 'Accountant', 'Director', 'Regulator', 'Auditor', 'Staff', 'Agent'] as $role)
                                    <option value="{{ $role }}" {{ $user->role == $role ? 'selected' : '' }}>{{ $role }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm">Update</button>
                        </form>
                    </td>
                    <td>
                        <div class="btn-group">
                            {{-- Toggle Status --}}
                            <form action="{{ route('users.toggleStatus', $user->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-warning btn-sm">
                                    {{ $user->status == 'Inactive' ? 'Activate' : 'Deactivate' }}
                                </button>
                            </form>

                            {{-- Delete --}}
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Delete this user?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm ms-1">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">No users found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection