@extends('layouts.public')

@section('title', $post->meta_title ?: $post->title . ' | OneStall Cargo Blog')
@section('meta_description', $post->meta_description ?: $post->excerpt)
@section('meta_keywords', $post->meta_keywords ?: 'Courier Service in India, Logistics Guide, OneStall Cargo')
@section('og_type', 'article')
@section('og_image', $post->featured_image ? asset($post->featured_image) : asset('images/og-preview.webp'))

@section('breadcrumbs')
    <span class="text-gray-400">/</span>
    <a href="{{ route('blog.index') }}" class="hover:text-brand-red transition">Blog</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-900 font-medium truncate max-w-xs">{{ $post->title }}</span>
@endsection

@section('schema_json')
<script type="application/ld+json">
{!! json_encode(seo()->articleSchema([
    'title' => $post->title,
    'description' => $post->excerpt,
    'image' => $post->featured_image,
    'author' => $post->author_name,
    'published_at' => $post->published_at ? $post->published_at->toIso8601String() : $post->created_at->toIso8601String(),
    'updated_at' => $post->updated_at->toIso8601String(),
    'url' => route('blog.show', $post->slug),
]), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
<script type="application/ld+json">
{!! json_encode(seo()->breadcrumbSchema([
    'Blog' => route('blog.index'),
    $post->title => route('blog.show', $post->slug)
]), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endsection

@section('content')
<article class="bg-white py-10 md:py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Article Header -->
        <header class="mb-10 text-center md:text-left">
            <div class="inline-block px-3 py-1 rounded-full border border-blue-200 bg-blue-50 text-brand-navy text-xs font-bold mb-4 uppercase tracking-wide">
                {{ $post->category }}
            </div>
            <h1 class="text-2xl md:text-4xl lg:text-5xl font-extrabold text-brand-navy leading-tight mb-6">
                {{ $post->title }}
            </h1>

            <!-- Author & Metadata Row -->
            <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 text-xs text-gray-500 pb-6 border-b border-gray-100 font-medium">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-full bg-brand-navy text-white flex items-center justify-center font-bold text-xs">
                        {{ substr($post->author_name, 0, 1) }}
                    </div>
                    <span class="text-gray-900 font-semibold">{{ $post->author_name }}</span>
                </div>
                <span>&bull;</span>
                <span><i class="fa-regular fa-calendar mr-1"></i> Published: {{ $post->published_at ? $post->published_at->format('M d, Y') : $post->created_at->format('M d, Y') }}</span>
                <span>&bull;</span>
                <span><i class="fa-regular fa-clock mr-1"></i> {{ $post->read_time }}</span>
            </div>
        </header>

        <!-- Featured Image Banner -->
        @if($post->featured_image)
        <div class="mb-10 rounded-2xl overflow-hidden shadow-sm border border-gray-100 max-h-96">
            <picture>
                <source srcset="{{ asset($post->featured_image) }}" type="image/webp">
                <img src="{{ asset(preg_replace('/\.webp$/i', '.jpg', $post->featured_image)) }}" alt="{{ $post->title }}" width="800" height="400" class="w-full h-full object-cover">
            </picture>
        </div>
        @endif

        <!-- Article Body Content -->
        <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed space-y-6 text-base font-normal">
            {!! $post->content !!}
        </div>

        <!-- In-Article Lead Magnet CTA -->
        <div class="my-12 p-8 rounded-2xl bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <h3 class="text-xl font-extrabold text-brand-navy mb-1">Looking to Optimize Your Shipping Operations?</h3>
                <p class="text-sm text-gray-600">Connect 15+ top courier partners in India, lower shipping costs, and get 1-2 day COD remittance with OneStall Cargo.</p>
            </div>
            <div class="flex-shrink-0">
                <a href="{{ route('register') }}" class="px-6 py-3 rounded-full bg-brand-red text-white text-sm font-bold hover:bg-brand-redHover transition shadow-md whitespace-nowrap">
                    Start Shipping Free
                </a>
            </div>
        </div>

        <!-- Article Share / Back -->
        <div class="pt-6 border-t border-gray-200 flex flex-wrap justify-between items-center gap-4 text-xs font-semibold text-gray-500">
            <a href="{{ route('blog.index') }}" class="hover:text-brand-navy transition flex items-center">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to all articles
            </a>
            <div class="flex items-center space-x-3">
                <span>Share article:</span>
                <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($post->title) }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 hover:bg-brand-navy hover:text-white flex items-center justify-center transition">
                    <i class="fa-brands fa-x-twitter text-xs"></i>
                </a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 hover:bg-[#0077b5] hover:text-white flex items-center justify-center transition">
                    <i class="fa-brands fa-linkedin text-xs"></i>
                </a>
                <a href="https://api.whatsapp.com/send?text={{ urlencode($post->title . ' ' . url()->current()) }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 hover:bg-green-600 hover:text-white flex items-center justify-center transition">
                    <i class="fa-brands fa-whatsapp text-xs"></i>
                </a>
            </div>
        </div>

        <!-- Related Articles -->
        @if($relatedPosts->isNotEmpty())
        <section class="mt-16 pt-12 border-t border-gray-200">
            <h3 class="text-2xl font-extrabold text-brand-navy mb-8">Related Logistics Insights</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($relatedPosts as $related)
                <div class="bg-gray-50 rounded-xl p-5 border border-gray-200 hover:shadow-sm transition flex flex-col justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-brand-red uppercase tracking-wide">{{ $related->category }}</span>
                        <h4 class="text-sm font-bold text-gray-900 mt-1 mb-2 leading-snug hover:text-brand-red transition">
                            <a href="{{ route('blog.show', $related->slug) }}">{{ $related->title }}</a>
                        </h4>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-200 text-xs text-gray-400 flex justify-between items-center">
                        <span>{{ $related->read_time }}</span>
                        <a href="{{ route('blog.show', $related->slug) }}" class="font-bold text-brand-navy hover:underline">Read &rarr;</a>
                    </div>
                </div>
                @endforeach
            </div>
        </section>
        @endif

    </div>
</article>
@endsection
