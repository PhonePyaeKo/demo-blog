<?php

use Livewire\Component;
use Livewire\Attributes\Validate;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

new class extends Component {
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|email|max:255|unique:users')]
    public string $email = '';

    #[Validate('required|string|min:8')]
    public string $password = '';

    #[Validate('required|array|min:1')]
    public array $selectedRoles = [];

    public function with(): array
    {
        return [
            'roles' => Role::all(),
        ];
    }

    public function save(): void
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        $user->assignRole($this->selectedRoles);

        session()->flash('success', 'User created successfully!');

        $this->redirect(route('users.index'), navigate: true);
    }
};
?>

<div class="space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">
            Create New User
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            Add a new user account and assign roles
        </p>
    </div>

    {{-- Form Card --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

        <form wire:submit="save" class="space-y-6">
            {{-- Name --}}
            <div>

                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Name
                </label>

                <input type="text" wire:model="name" placeholder="Enter user's full name" autofocus
                    class="w-full rounded-xl
                    px-4 py-2.5 text-sm
                    border border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 transition focus:ring-2 focus:ring-indigo-100 focus:outline-none">
                @error('name')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>

            {{-- Email --}}
            <div>

                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Email Address
                </label>

                <input type="email" wire:model="email" placeholder="user@example.com"
                    class="w-full rounded-xl
                    px-4 py-2.5 text-sm
                    border border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 transition focus:ring-2 focus:ring-indigo-100 focus:outline-none">
                @error('email')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Password --}}
            <div>

                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Password
                </label>

                <input type="password" wire:model="password" placeholder="Minimum 8 characters"
                    class="w-full rounded-xl
                    px-4 py-2.5 text-sm
                    border border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 transition focus:ring-2 focus:ring-indigo-100 focus:outline-none">
                @error('password')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Roles --}}
            <div>

                <label class="block text-sm font-semibold text-gray-700 mb-3">
                    Assign Roles
                </label>

                <div class="grid sm:grid-cols-2 gap-3">

                    @foreach ($roles as $role)
                        <label
                            class="flex items-start gap-3 p-4
                    rounded-xl border border-gray-200
                    hover:border-indigo-300
                    hover:bg-indigo-50/30
                    transition cursor-pointer">
                            <input type="checkbox" wire:model="selectedRoles" value="{{ $role->name }}"
                                class="mt-1 rounded border-gray-300
                            text-indigo-600
                            focus:ring-indigo-500">

                            <div>

                                <p class="text-sm font-medium text-gray-800">
                                    {{ ucfirst($role->name) }}
                                </p>

                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $role->description }}
                                </p>

                            </div>
                        </label>
                    @endforeach
                </div>

                @error('selectedRoles')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-4 border-t border-gray-100">

                <button type="submit"
                    class="inline-flex items-center gap-2
                    px-5 py-2.5
                    bg-indigo-600 text-white
                    text-sm font-medium
                    rounded-xl
                    hover:bg-indigo-700
                    transition shadow-sm">

                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />

                    </svg>
                    Create User
                </button>

                <a href="{{ route('users.index') }}"
                    class="px-5 py-2.5
                    bg-white border border-gray-200
                    text-gray-700 text-sm font-medium
                    rounded-xl
                    hover:bg-gray-50 transition">

                    Cancel

                </a>
            </div>
        </form>
    </div>
</div>
