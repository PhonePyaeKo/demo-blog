<?php

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Post;
use App\Models\Tag;
use App\Models\Category;
use Livewire\Attributes\Validate;
new class extends Component {
    use WithFileUploads;

    public Post $post;

    #[Validate('required|string|min:3|max:255')]
    public string $title = '';

    #[Validate('nullable|string|max:500')]
    public string $excerpt = '';

    #[Validate('required|string|min:10')]
    public string $content = '';

    #[Validate('nullable|image|max:2048')]
    public $featured_image;

    #[Validate('required|in:draft,published,archived')]
    public string $status = '';

    public string $existing_image = '';

    public function mount(Post $post): void
    {
        // Authorization check
        if (!auth()->user()->can('edit all posts') && !(auth()->user()->can('edit own posts') && $post->user_id === auth()->id())) {
            abort(403);
        }

        $this->post = $post;
        $this->title = $post->title;
        $this->excerpt = $post->excerpt ?? '';
        $this->content = $post->content;
        $this->status = $post->status;
        $this->existing_image = $post->featured_image ?? '';
    }

    public function update(): void
    {
        $this->validate();

        $this->post->title = $this->title;
        $this->post->slug = Str::slug($this->title);
        $this->post->excerpt = $this->excerpt;
        $this->post->content = $this->content;
        $this->post->status = $this->status;

        if ($this->featured_image) {
            // Delete old image if exists
            if ($this->existing_image) {
                \Storage::disk('public')->delete($this->existing_image);
            }

            $path = $this->featured_image->store('posts', 'public');
            $this->post->featured_image = $path;
            $this->existing_image = $path;
        }

        if ($this->status === 'published' && !$this->post->published_at) {
            $this->post->published_at = now();
        }

        $this->post->save();

        session()->flash('success', 'Post updated successfully!');

        $this->redirect(route('posts.index'), navigate: true);
    }
};
?>

<div>
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Edit Post</h1>
        <p class="mt-1 text-sm text-gray-600">Update your blog post information</p>
    </div>

    <!-- Form Card -->
    <div class="overflow-hidden bg-white border border-gray-200 shadow-sm rounded-2xl">
        <form wire:submit="update">

            <!-- Form Body -->
            <div class="p-6 space-y-6">

                <!-- Title -->
                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-700">
                        Post Title
                    </label>

                    <input type="text" wire:model.live.debounce="title" placeholder="Enter post title..." autofocus
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 transition focus:ring-2 focus:ring-indigo-100 focus:outline-none">

                    @error('title')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Excerpt -->
                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-700">
                        Short Description
                    </label>

                    <textarea wire:model="excerpt" rows="3" placeholder="Write a short summary..."
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 transition focus:ring-2 focus:ring-indigo-100 focus:outline-none"></textarea>

                    @error('excerpt')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Content -->
                <div wire:ignore x-data="{ content: @entangle('content').live }" x-init="const input = $refs.input;
                const editor = $refs.editor;
                
                input.value = content;
                
                editor.addEventListener('trix-change', e => {
                    content = e.target.value;
                });
                
                $watch('content', value => {
                    if (input.value !== value) {
                        input.value = value ?? '';
                        editor.editor.loadHTML(value ?? '');
                    }
                });">
                    <label class="block mb-2 text-sm font-semibold text-gray-700">
                        Content
                    </label>

                    <input x-ref="input" id="content" type="hidden">

                    <trix-editor x-ref="editor" input="content"
                        class="trix-content rounded-xl border border-gray-300 bg-white">
                    </trix-editor>

                    @error('content')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Featured Image -->
                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-700">
                        Featured Image
                    </label>

                    @if ($existing_image && !$featured_image)
                        <div class="mb-4">
                            <p class="mb-2 text-sm text-gray-500">
                                Current Image
                            </p>

                            <img src="{{ Storage::url($existing_image) }}" alt="Current Image"
                                class="h-40 rounded-xl border border-gray-200 object-cover">
                        </div>
                    @endif

                    <input type="file" wire:model="featured_image" accept="image/*"
                        class="block w-full text-sm text-gray-500
                        file:mr-4 file:rounded-lg file:border-0
                        file:bg-indigo-50 file:px-4 file:py-2
                        file:font-medium file:text-indigo-600
                        hover:file:bg-indigo-100">

                    @error('featured_image')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    @if ($featured_image)
                        <div class="mt-4" wire:transition>
                            <p class="mb-2 text-sm text-gray-500">
                                New Preview
                            </p>

                            <img src="{{ $featured_image->temporaryUrl() }}" alt="Preview"
                                class="h-40 rounded-xl border border-gray-200 object-cover">
                        </div>
                    @endif

                    <div wire:loading wire:target="featured_image" class="mt-2 text-sm text-indigo-600">
                        Uploading image...
                    </div>
                </div>

                <!-- Status -->
                <div>
                    <label class="block mb-3 text-sm font-semibold text-gray-700">
                        Status
                    </label>

                    <div class="grid gap-3 sm:grid-cols-3">

                        <label
                            class="flex items-center gap-3 rounded-xl border border-gray-200 p-3 cursor-pointer hover:border-indigo-500">

                            <input type="radio" wire:model="status" value="draft"
                                class="text-indigo-600 focus:ring-indigo-500">

                            <span class="text-sm font-medium text-gray-700">
                                Draft
                            </span>
                        </label>

                        @can('publish posts')
                            <label
                                class="flex items-center gap-3 rounded-xl border border-gray-200 p-3 cursor-pointer hover:border-indigo-500">

                                <input type="radio" wire:model="status" value="published"
                                    class="text-indigo-600 focus:ring-indigo-500">

                                <span class="text-sm font-medium text-gray-700">
                                    Published
                                </span>
                            </label>

                            <label
                                class="flex items-center gap-3 rounded-xl border border-gray-200 p-3 cursor-pointer hover:border-indigo-500">

                                <input type="radio" wire:model="status" value="archived"
                                    class="text-indigo-600 focus:ring-indigo-500">

                                <span class="text-sm font-medium text-gray-700">
                                    Archived
                                </span>
                            </label>
                        @endcan
                    </div>

                    @error('status')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-3 border-t border-gray-200 bg-gray-50 px-6 py-4">

                <a href="{{ route('posts.index') }}"
                    class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100">
                    Cancel
                </a>

                <button type="submit" wire:loading.attr="disabled"
                    class="inline-flex items-center rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-700 disabled:opacity-50">

                    <svg wire:loading wire:target="update" class="mr-2 h-4 w-4 animate-spin"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">

                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>

                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>

                    <span wire:loading.remove wire:target="update">
                        Update Post
                    </span>

                    <span wire:loading wire:target="update">
                        Updating...
                    </span>

                </button>

            </div>

        </form>
    </div>
</div>
