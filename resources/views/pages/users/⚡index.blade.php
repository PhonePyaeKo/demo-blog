<?php

use Livewire\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $roleFilter = 'all';

    public function with(): array
    {
        $query = User::with('roles')->latest();

        // Filter by search
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')->orWhere('email', 'like', '%' . $this->search . '%');
        }

        // Filter by role
        if ($this->roleFilter !== 'all') {
            $query->whereHas('roles', function ($q) {
                $q->where('name', $this->roleFilter);
            });
        }

        return [
            'users' => $query->paginate(10),
            'roles' => Role::all(),
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    public function deleteUser(User $user): void
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account!');
            return;
        }

        $user->delete();
        session()->flash('success', 'User deleted successfully!');
    }
};
?>

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Users
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Manage user accounts and roles
            </p>
        </div>


        <a href="{{ route('users.create') }}"
            class="inline-flex items-center justify-center gap-2
            px-4 py-2.5 bg-indigo-600 text-white text-sm font-medium
            rounded-xl hover:bg-indigo-700 transition shadow-sm">

            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />

            </svg>

            New User

        </a>

    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">

        <div class="flex flex-col sm:flex-row gap-4">


            {{-- Search --}}
            <div class="relative flex-1">

                <div class="absolute inset-y-0 left-0 flex items-center pl-4">

                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />

                    </svg>

                </div>


                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search users..."
                    class="w-full pl-11 py-2.5 rounded-xl
                    border border-gray-300 bg-white text-sm text-gray-900 placeholder:text-gray-400 transition focus:ring-2 focus:ring-indigo-100 focus:outline-none">

            </div>



            {{-- Role Filter --}}
            <select wire:model.live="roleFilter"
                class="sm:w-52 py-2.5 rounded-xl
                border border-gray-300 bg-white text-sm text-gray-900 placeholder:text-gray-400 transition focus:ring-2 focus:ring-indigo-100 focus:outline-none">


                <option value="all">
                    All Roles
                </option>


                @foreach ($roles as $role)
                    <option value="{{ $role->name }}">
                        {{ ucfirst($role->name) }}
                    </option>
                @endforeach


            </select>


        </div>

    </div>

    {{-- Messages --}}
    @if (session('success'))
        <div
            class="flex items-center gap-3
            bg-green-50 text-green-700
            border border-green-100
            px-5 py-3 rounded-xl">

            ✅ {{ session('success') }}

        </div>
    @endif

    @if (session('error'))
        <div
            class="flex items-center gap-3
            bg-red-50 text-red-700
            border border-red-100
            px-5 py-3 rounded-xl">

            ⚠️ {{ session('error') }}
        </div>
    @endif

    {{-- Users Table --}}
    <div class="bg-white rounded-2xl border border-gray-100
        shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        @foreach (['User', 'Email', 'Roles', 'Joined', ''] as $title)
                            <th
                                class="px-6 py-4 text-left text-xs
                                font-semibold text-gray-500 uppercase">

                                {{ $title }}

                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                        <tr wire:key="user-{{ $user->id }}" class="hover:bg-gray-50 transition">
                            {{-- User --}}
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <img class="w-11 h-11 rounded-full"
                                        src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=6366f1&color=fff"
                                        alt="">
                                    <div>
                                        <p class="font-semibold text-gray-900">
                                            {{ $user->name }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            User ID #{{ $user->id }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            {{-- Email --}}
                            <td class="px-6 py-5 text-sm text-gray-700">
                                {{ $user->email }}
                            </td>
                            {{-- Roles --}}
                            <td class="px-6 py-5">
                                <div class="flex flex-wrap gap-2">
                                    @forelse($user->roles as $role)
                                        <span
                                            class="px-3 py-1 rounded-full
                                text-xs font-medium
                                bg-indigo-100 text-indigo-700">
                                            {{ ucfirst($role->name) }}
                                        </span>
                                    @empty
                                        <span
                                            class="px-3 py-1 rounded-full
                                text-xs bg-gray-100 text-gray-600">
                                            No role
                                        </span>
                                    @endforelse
                                </div>
                            </td>
                            {{-- Joined --}}
                            <td class="px-6 py-5 text-sm text-gray-500">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            {{-- Actions --}}
                            <td class="px-6 py-5 text-right">
                                <div class="flex justify-end items-center gap-2">

                                    {{-- Edit --}}
                                    <a href="{{ route('users.edit', $user) }}" title="Edit User"
                                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-indigo-600 hover:bg-indigo-50 hover:text-indigo-800 transition">

                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5 M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                        </svg>

                                    </a>

                                    {{-- Delete --}}
                                    @if ($user->id !== auth()->id())
                                        <button wire:click="deleteUser({{ $user->id }})"
                                            wire:confirm="Delete this user?" title="Delete User"
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg  text-red-600  hover:bg-red-50  hover:text-red-800 transition">

                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6  M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3 M4 7h16" />
                                            </svg>

                                        </button>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div>
        {{ $users->links() }}
    </div>
</div>
