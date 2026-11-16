@extends('layouts.public')

@section('title', 'Logistics & Shipping Blog | OneStall Cargo Insights')
@section('meta_description', 'Expert logistics guides, courier comparison tips, eCommerce shipping strategies, and cargo management advice from OneStall Cargo.')
@section('meta_keywords', 'Logistics Blog India, Courier Service Guides, E-commerce Shipping Advice, How to Reduce Shipping Costs, B2B Cargo Guides')

@section('breadcrumbs')
    <span class="text-gray-400">/</span>
    <span class="text-gray-900 font-medium">Blog</span>
@endsection

@section('schema_json')
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'Blog',
    'name' => 'OneStall Cargo Logistics Insights & Guides',
    'url' => route('blog.index'),
    'description' => 'Educational guides, industry analysis, and operational best practices for Indian eCommerce shippers, B2B manufacturers, and franchise operators.',
    'publisher' => [
        '@type' => 'Organization',
        'name' => 'OneStall Cargo',
        'url' => 'https://onestallcargo.com'
    ]
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
<script type="application/ld+json">
{!! json_encode(seo()->breadcrumbSchema([
    'Blog' => route('blog.index')
]), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endsection

@section('content')
<!-- Hero Section -->
<section class="bg-gradient-to-r from-blue-50/40 via-white to-blue-50/40 py-12 md:py-16 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <div class="inline-block px-3 py-1 rounded-full border border-blue-200 bg-blue-50 text-brand-navy text-xs font-bold mb-4 uppercase tracking-wide">
            Industry Guides &amp; Insights
        </div>
        <h1 class="text-3xl md:text-5xl font-extrabold text-brand-navy leading-tight mb-4">
            OneStall Cargo <span class="text-brand-red">Logistics Knowledge Hub</span>
        </h1>
        <p class="mt-4 text-base md:text-lg text-gray-600 max-w-2xl mx-auto font-medium">
            Proven strategies to optimize your supply chain, compare Indian courier rates, slash RTO losses, and scale eCommerce fulfillment.
        </p>

        <!-- Search Form -->
        <form action="{{ route('blog.index') }}" method="GET" class="mt-8 max-w-md mx-auto">
            <div class="relative flex items-center bg-white rounded-full border border-gray-300 shadow-sm p-1 focus-within:border-brand-navy">
                <i class="fa-solid fa-magnifying-glass text-gray-400 pl-4 pr-2 text-sm"></i>
                <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Search logistics articles..." class="w-full text-xs md:text-sm font-medium text-gray-800 bg-transparent focus:outline-none placeholder-gray-400">
                <button type="submit" class="px-5 py-2 rounded-full bg-brand-navy text-white text-xs font-bold hover:bg-black transition">Search</button>
            </div>
        </form>

        <!-- Category Pills -->
        <div class="mt-6 flex flex-wrap justify-center gap-2 text-xs font-semibold">
            <a href="{{ route('blog.index') }}" class="px-3.5 py-1.5 rounded-full transition {{ !$category ? 'bg-brand-navy text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
                All Topics
            </a>
            @foreach($categories as $cat)
            <a href="{{ route('blog.index', ['category' => $cat]) }}" class="px-3.5 py-1.5 rounded-full transition {{ $category === $cat ? 'bg-brand-navy text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
                {{ $cat }}
            </a>
            @endforeach
        </div>
    </div>
</section>

<!-- Articles Grid Section -->
<section class="py-14 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($posts->isEmpty())
        <div class="text-center py-16 text-gray-500">
            <i class="fa-solid fa-newspaper text-4xl text-gray-300 mb-3 block"></i>
            <p class="text-base font-bold">No articles found matching your criteria.</p>
            <a href="{{ route('blog.index') }}" class="mt-3 inline-block text-sm font-bold text-brand-red underline">View all articles</a>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($posts as $post)
            <article class="bg-gray-50/70 rounded-2xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition flex flex-col justify-between">
                <div>
                    <div class="h-48 overflow-hidden relative bg-brand-navy">
                        @if($post->featured_image)
                        <picture>
                            <source srcset="{{ asset($post->featured_image) }}" type="image/webp">
                            <img src="{{ asset(preg_replace('/\.webp$/i', '.jpg', $post->featured_image)) }}" alt="{{ $post->title }}" width="400" height="200" loading="lazy" class="w-full h-full object-cover transition-transform duration-300 hover:scale-105">
                        </picture>
                        @else
                        <div class="w-full h-full flex items-center justify-center text-white/40 text-4xl">
                            <i class="fa-solid fa-truck-fast"></i>
                        </div>
                        @endif
                        <span class="absolute top-3 left-3 bg-brand-navy text-white text-[10px] font-bold px-2.5 py-1 rounded-md uppercase tracking-wider">
                            {{ $post->category }}
                        </span>
                    </div>

                    <div class="p-6">
                        <div class="flex items-center text-xs text-gray-400 space-x-3 mb-2 font-medium">
                            <span><i class="fa-regular fa-calendar mr-1"></i> {{ $post->published_at ? $post->published_at->format('M d, Y') : $post->created_at->format('M d, Y') }}</span>
                            <span>&bull;</span>
                            <span><i class="fa-regular fa-clock mr-1"></i> {{ $post->read_time }}</span>
                        </div>
                        <h2 class="text-lg font-bold text-gray-900 leading-snug mb-3 hover:text-brand-red transition">
                            <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
                        </h2>
                        <p class="text-gray-600 text-xs leading-relaxed line-clamp-3">
                            {{ $post->excerpt }}
                        </p>
                    </div>
                </div>

                <div class="px-6 pb-6 pt-2 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-[11px] text-gray-500 font-medium">{{ $post->author_name }}</span>
                    <a href="{{ route('blog.show', $post->slug) }}" class="text-xs font-bold text-brand-red hover:underline flex items-center">
                        Read Article <i class="fa-solid fa-arrow-right ml-1.5 text-[10px]"></i>
                    </a>
                </div>
            </article>
            @endforeach
        </div>

        <div class="mt-12">
            {{ $posts->links() }}
        </div>
        @endif
    </div>
</section>
@endsection
