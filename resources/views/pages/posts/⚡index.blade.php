<?php

use Livewire\Component;
use App\Models\Post;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $status = 'all';

    public function with(): array
    {
        $query = Post::with('user')->latest();
        //filter by search
        if ($this->search) {
            $query->where('title', 'like', '%' . $this->search . '%')->orWhere('content', 'like', '%' . $this->search . '%');
        }

        //filter by status
        if ($this->status !== 'all') {
            $query->where('status', $this->search);
        }

        //Authorization :: author only see their own posts
        if (auth()->user()->hasRole('author')) {
            $query->where('user_id', auth()->id());
        }

        return [
            'posts' => $query->paginate(10),
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resertPage();
    }

    public function deletePost(Post $post)
    {
        //authorize
        if (auth()->user()->can('delete all posts') || (auth()->user()->can('delete own posts') && $post->user_id === auth()->user()->id)) {
            $post->delete();

            session()->flash('success', 'Post Deleted Successfully!');
        }
    }
};
?>

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Posts
            </h1>
            <p class="mt-1 text-sm text-gray-500">
                Manage and organize your blog posts
            </p>
        </div>

        @can('create posts')
            <a href="{{ route('posts.create') }}"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 
                bg-indigo-600 text-white text-sm font-medium rounded-xl
                hover:bg-indigo-700 transition shadow-sm">

                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>

                New Post
            </a>
        @endcan
    </div>


    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">

        <div class="flex flex-col sm:flex-row gap-4">

            {{-- Search --}}
            <div class="relative flex-1">

                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
                    </svg>
                </div>


                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search posts..."
                    class="w-full pl-11 pr-4 py-2.5 rounded-lg
                border border-gray-300 bg-white text-sm text-gray-900 placeholder:text-gray-400 transition focus:ring-2 focus:ring-indigo-100 focus:outline-none">
            </div>


            {{-- Status --}}
            <div class="sm:w-52">

                <select wire:model.live="status"
                    class="w-full py-2.5 rounded-lg
                border border-gray-300 bg-white text-sm text-gray-900 placeholder:text-gray-400 transition focus:ring-2 focus:ring-indigo-100 focus:outline-none
                cursor-pointer">

                    <option value="all">
                        All Posts
                    </option>

                    <option value="draft">
                        Draft
                    </option>

                    <option value="published">
                        Published
                    </option>

                    <option value="archived">
                        Archived
                    </option>

                </select>

            </div>
        </div>
    </div>

    {{-- Success --}}
    @if (session('success'))
        <div
            class="flex items-center gap-3 bg-green-50 
            text-green-700 px-5 py-3 rounded-xl border border-green-100">

            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />

            </svg>

            {{ session('success') }}

        </div>
    @endif

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        <div class="overflow-x-auto">

            <table class="min-w-full">

                <thead class="bg-gray-50">

                    <tr>

                        @foreach (['Title', 'Category', 'Author', 'Status', 'Created', ''] as $head)
                            <th
                                class="px-6 py-4 text-left text-xs 
                                font-semibold text-gray-500 uppercase">

                                {{ $head }}

                            </th>
                        @endforeach

                    </tr>

                </thead>


                <tbody class="divide-y divide-gray-100">


                    @forelse($posts as $post)
                        <tr wire:key="post-{{ $post->id }}" class="hover:bg-gray-50 transition">


                            {{-- Title --}}
                            <td class="px-6 py-5">

                                <div class="font-semibold text-gray-900">
                                    {{ $post->title }}
                                </div>

                                <p class="text-sm text-gray-500 mt-1">
                                    {{ Str::limit($post->excerpt, 60) }}
                                </p>


                                @if ($post->comments_count)
                                    <span
                                        class="inline-flex mt-2 items-center 
                                    gap-1 px-2 py-1 text-xs rounded-lg
                                    bg-blue-50 text-blue-600">

                                        💬 {{ $post->comments_count }}

                                    </span>
                                @endif

                            </td>

                            {{-- Author --}}
                            <td class="px-6 py-5 text-sm text-gray-700">

                                {{ $post->user->name }}

                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-5">

                                <span
                                    class="px-3 py-1 rounded-full text-xs font-semibold

                            @if ($post->status == 'published') bg-green-100 text-green-700

                            @elseif($post->status == 'draft')
                                bg-yellow-100 text-yellow-700

                            @else
                                bg-gray-100 text-gray-700 @endif">

                                    {{ ucfirst($post->status) }}

                                </span>

                            </td>
                            {{-- Date --}}
                            <td class="px-6 py-5 text-sm text-gray-500">

                                {{ $post->created_at->format('M d, Y') }}

                            </td>
                            {{-- Actions --}}
                            <td class="px-6 py-5 text-right">
                                <div class="flex justify-end items-center gap-2">

                                    {{-- Edit --}}
                                    @if (auth()->user()->can('edit all posts') || (auth()->user()->can('edit own posts') && $post->user_id === auth()->id()))
                                        <a href="{{ route('posts.edit', $post) }}" title="Edit Post"
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-indigo-600 hover:bg-indigo-50 hover:text-indigo-800 transition">

                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                            </svg>

                                        </a>
                                    @endif

                                    {{-- Delete --}}
                                    @if (auth()->user()->can('delete all posts') ||
                                            (auth()->user()->can('delete own posts') && $post->user_id === auth()->id()))
                                        <button wire:click="deletePost({{ $post->id }})"
                                            wire:confirm="Delete this post?" title="Delete Post"
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-800 transition">

                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16" />
                                            </svg>

                                        </button>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>

                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">

                                No posts found.

                            </td>

                        </tr>
                    @endforelse


                </tbody>

            </table>

        </div>

    </div>


    {{-- Pagination --}}
    <div>
        {{ $posts->links() }}
    </div>


</div>
