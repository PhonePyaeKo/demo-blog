<?php

use Livewire\Component;
use Livewire\Attributes\Validate;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;
    #[Validate('required|string|min:3|max:255')]
    public string $title = '';

    #[Validate('nullable|string|max:500')]
    public string $excerpt = '';

    #[Validate('required|string|min:10')]
    public string $content = '';

    #[Validate('nullable|image|max:2048')]
    public $featured_image;

    #[Validate('required|in:draft,published')]
    public string $status = 'draft';

    public function save()
    {
        $this->validate();

        $post = new Post();
        $post->user_id = auth()->id();
        $post->title = $this->title;
        $post->slug = Str::slug($this->title);
        $post->excerpt = $this->excerpt;
        $post->content = $this->content;
        $post->status = $this->status;

        if ($this->featured_image) {
            $path = $this->featured_image->store('posts', 'public');
            $post->featured_image = $path;
        }

        if ($this->status === 'published') {
            $post->published_at = now();
        }

        $post->save();

        session()->flash('success', 'Post created successfully!');

        $this->redirect(route('posts.index'), navigate: true);
    }
};
?>

<div class="max-w-5xl mx-auto px-4 py-8">

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">
            Create New Post
        </h1>
        <p class="mt-2 text-gray-500">
            Write and publish your blog post.
        </p>
    </div>

    <form wire:submit="save">

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

            <div class="p-8 space-y-8">

                <!-- Title -->
                <div class="space-y-2">
                    <label for="title" class="text-sm font-medium text-gray-700">
                        Post Title <span class="text-red-500">*</span>
                    </label>

                    <input id="title" type="text" wire:model.live.debounce="title"
                        placeholder="Enter your post title..." autofocus
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 transition focus:ring-2 focus:ring-indigo-100 focus:outline-none">

                    @error('title')
                        <p class="text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Excerpt -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label for="excerpt" class="text-sm font-semibold text-gray-800">
                            Short Description
                        </label>
                        <span class="text-xs text-gray-400">
                            Optional
                        </span>
                    </div>

                    <textarea id="excerpt" wire:model.live="excerpt" rows="4" placeholder="Write a brief summary of your post..."
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 transition focus:ring-2 focus:ring-indigo-100 focus:outline-none"></textarea>

                    <div class="flex items-center justify-between">
                        <p class="text-xs text-gray-500">
                            This description will appear in previews and search results.
                        </p>

                        <span class="text-xs text-gray-400">
                            {{ strlen($excerpt ?? '') }}/160
                        </span>
                    </div>

                    @error('excerpt')
                        <p class="flex items-center gap-1 text-sm text-red-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Content -->
                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-700">
                        Content
                    </label>

                    <div class="rounded-xl border border-gray-300 overflow-hidden">
                        <div wire:ignore>
                            <input type="hidden" id="x-content">

                            <trix-editor input="x-content" class="trix-content" x-data
                                x-on:trix-change="$wire.content = $event.target.value">
                            </trix-editor>
                        </div>
                    </div>

                    @error('content')
                        <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Image + Status -->
                <div class="grid md:grid-cols-2 gap-8">

                    <!-- Featured Image -->
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">
                            Featured Image
                        </label>

                        <input type="file" wire:model="featured_image" accept="image/*"
                            class="block w-full text-sm text-gray-600
                            file:mr-4 file:px-4 file:py-2
                            file:rounded-lg file:border-0
                            file:bg-indigo-600 file:text-white
                            hover:file:bg-indigo-700">

                        @error('featured_image')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror

                        <div wire:loading wire:target="featured_image" class="mt-3 text-sm text-indigo-600">
                            Uploading...
                        </div>

                        @if ($featured_image)
                            <div class="mt-4">
                                <img src="{{ $featured_image->temporaryUrl() }}"
                                    class="rounded-xl border w-full max-w-xs shadow-sm" alt="Preview">
                            </div>
                        @endif
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block mb-4 text-sm font-semibold text-gray-700">
                            Post Status
                        </label>

                        <div class="space-y-4">

                            <label
                                class="flex items-center p-4 rounded-xl border cursor-pointer hover:border-indigo-500">
                                <input type="radio" wire:model="status" value="draft" class="text-indigo-600">

                                <div class="ml-3">
                                    <p class="font-medium text-gray-800">
                                        Draft
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Save without publishing.
                                    </p>
                                </div>
                            </label>

                            @can('publish posts')
                                <label
                                    class="flex items-center p-4 rounded-xl border cursor-pointer hover:border-indigo-500">
                                    <input type="radio" wire:model="status" value="published" class="text-indigo-600">

                                    <div class="ml-3">
                                        <p class="font-medium text-gray-800">
                                            Published
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            Make this post visible immediately.
                                        </p>
                                    </div>
                                </label>
                            @endcan

                        </div>

                        @error('status')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

            </div>

            <!-- Footer -->
            <div class="border-t bg-gray-50 px-8 py-5 flex justify-end gap-3">

                <a href="{{ route('posts.index') }}"
                    class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition">
                    Cancel
                </a>

                <button type="submit"
                    class="px-5 py-2.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition">
                    Create Post
                </button>

            </div>

        </div>

    </form>

</div>
