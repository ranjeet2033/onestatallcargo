@extends('layouts.public')
@section('title', 'Contact OneStall Cargo | Courier & Logistics Support India')
@section('meta_description', 'Contact OneStall Cargo for business inquiries, franchise opportunities, enterprise cargo rates, or support. Registered office in Lucknow, UP & national operational hubs.')
@section('meta_keywords', 'Logistics Company Office India, Courier Customer Care, Business Inquiries Logistics, Enterprise Cargo Contact, OneStall Lucknow Office')

@section('breadcrumbs')
    <span class="text-gray-400">/</span>
    <span class="text-gray-900 font-medium">Contact Us</span>
@endsection

@section('schema_json')
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'ContactPage',
    'name' => 'Contact OneStall Cargo',
    'url' => route('contact'),
    'description' => 'Official contact details, corporate office location, inquiry form, and support channels for OneStall Cargo and Anyurva Ayurveda Private Limited.'
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
<script type="application/ld+json">
{!! json_encode(seo()->breadcrumbSchema([
    'Contact Us' => route('contact')
]), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endsection
@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <h1 class="text-3xl font-extrabold text-brand-navy mb-8 text-center">Contact Us</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 max-w-4xl mx-auto">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8">
                <form action="{{ route('contact.post') }}" method="POST" class="space-y-6">
                    @csrf
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                            {{ session('success') }}
                        </div>
                    @endif
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Full Name</label>
                        <input type="text" name="name" required class="w-full px-4 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-brand-blue">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" required class="w-full px-4 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-brand-blue">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Phone</label>
                        <input type="text" name="phone" class="w-full px-4 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-brand-blue">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Company / Volume</label>
                        <input type="text" name="company_name" placeholder="Company Name" class="w-full px-4 py-2 mb-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-brand-blue">
                        <select name="volume" class="w-full px-4 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-brand-blue">
                            <option value="">Select Monthly Volume</option>
                            <option value="1-100">1 - 100 Shipments</option>
                            <option value="101-500">101 - 500 Shipments</option>
                            <option value="500+">500+ Shipments</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Message</label>
                        <textarea name="message" required rows="4" class="w-full px-4 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-brand-blue"></textarea>
                    </div>
                    <button type="submit" class="w-full bg-brand-navy hover:bg-black text-white font-bold py-3 rounded-xl transition">Submit Inquiry</button>
                </form>
            </div>
            
            <div class="flex flex-col justify-start pt-4">
                <!-- Company Identity -->
                <div class="mb-6">
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-brand-red mb-2 block">Registered Office</span>
                    <h2 class="text-xl font-extrabold text-gray-900 mb-1">ANYURVA AYURVEDA PRIVATE LIMITED</h2>
                    <p class="text-xs text-gray-400 font-medium mb-4">CIN: U21003UP2024PTC205462</p>
                </div>

                <!-- Contact Details -->
                <div class="space-y-3 mb-8">
                    <div class="flex items-start gap-3 text-gray-600">
                        <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fa-solid fa-location-dot text-brand-blue text-sm"></i>
                        </div>
                        <div class="text-sm leading-relaxed">
                            569/153 KHA, Barigawan, KP Road,<br>
                            32 BN PAC, Lucknow – 226023<br>
                            Uttar Pradesh, India
                        </div>
                    </div>
                    <div class="flex items-center gap-3 text-gray-600">
                        <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-envelope text-brand-blue text-sm"></i>
                        </div>
                        <a href="mailto:support@onestallcargo.com" class="text-sm hover:text-brand-blue transition">support@onestallcargo.com</a>
                    </div>
                    <div class="flex items-center gap-3 text-gray-600">
                        <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-phone text-brand-blue text-sm"></i>
                        </div>
                        <a href="tel:+917376490981" class="text-sm hover:text-brand-blue transition font-semibold">+91 73764 90981</a>
                    </div>
                </div>

                <!-- Google Map Embed – Lucknow area -->
                <div class="rounded-2xl overflow-hidden border border-gray-200 shadow-sm">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3559.6787!2d80.9462!3d26.8467!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x399bfd991f32b16b%3A0x93ccba8909978be7!2sLucknow%2C%20Uttar%20Pradesh!5e0!3m2!1sen!2sin!4v1695000000000"
                        width="100%" height="220" style="border:0;" allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        class="w-full">
                    </iframe>
                </div>

                <!-- Business Hours -->
                <div class="mt-6 bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <p class="text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-2"><i class="fa-solid fa-clock mr-1 text-brand-blue"></i> Support Hours</p>
                    <p class="text-sm text-gray-600">Monday – Saturday: <span class="font-semibold text-gray-800">9:00 AM – 7:00 PM</span></p>
                    <p class="text-sm text-gray-600">Sunday: <span class="font-semibold text-gray-800">10:00 AM – 4:00 PM</span></p>
                </div>

                <!-- Connect on Social Media -->
                <div class="mt-6 bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-brand-red mb-1.5 block">Official Channels</span>
                    <h3 class="text-sm font-bold text-gray-900 mb-2">Connect With OneStall Cargo</h3>
                    <p class="text-xs text-gray-500 mb-4">Stay tuned for shipping rate alerts, logistics guides, and franchise expansion updates.</p>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ setting('youtube_url', 'https://www.youtube.com/@onestallcargoindia') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white text-xs font-semibold transition border border-red-100">
                            <i class="fa-brands fa-youtube text-sm"></i> YouTube
                        </a>
                        <a href="{{ setting('instagram_url', 'https://www.instagram.com/onestallcargo') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-pink-50 text-pink-600 hover:bg-pink-600 hover:text-white text-xs font-semibold transition border border-pink-100">
                            <i class="fa-brands fa-instagram text-sm"></i> Instagram
                        </a>
                        <a href="{{ setting('linkedin_url', 'https://www.linkedin.com/company/onestallcargo') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white text-xs font-semibold transition border border-blue-100">
                            <i class="fa-brands fa-linkedin text-sm"></i> LinkedIn
                        </a>
                        <a href="{{ setting('twitter_url', 'https://twitter.com/onestallcargo') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-100 text-gray-800 hover:bg-black hover:text-white text-xs font-semibold transition border border-gray-200">
                            <i class="fa-brands fa-x-twitter text-sm"></i> X (Twitter)
                        </a>
                        <a href="{{ setting('facebook_url', 'https://www.facebook.com/onestallcargo') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-700 hover:text-white text-xs font-semibold transition border border-blue-200">
                            <i class="fa-brands fa-facebook-f text-sm"></i> Facebook
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
