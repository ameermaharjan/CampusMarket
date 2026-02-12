<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Campus Marketplace | Peer-to-Peer Student Services</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#2b8cee",
                        "background-light": "#f6f7f8",
                        "background-dark": "#101922",
                    },
                    fontFamily: {
                        "display": ["Lexend"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
<style>
        body {
            font-family: 'Lexend', sans-serif;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200">
<!-- Sticky Navigation Bar -->
<nav class="sticky top-0 z-50 w-full bg-white/80 dark:bg-background-dark/80 backdrop-blur-md border-b border-primary/10">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="flex justify-between items-center h-16">
<div class="flex items-center gap-2">
<div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center">
<span class="material-icons text-white">school</span>
</div>
<span class="text-xl font-bold text-primary tracking-tight">CampusMarket</span>
</div>
<div class="hidden md:flex items-center space-x-8">
<a class="text-sm font-medium hover:text-primary transition-colors" href="#">Browse</a>
<a class="text-sm font-medium hover:text-primary transition-colors" href="#">How it Works</a>
<a class="text-sm font-medium hover:text-primary transition-colors" href="#">Support</a>
</div>
<div class="flex items-center gap-4">
<button class="hidden lg:flex items-center gap-2 text-primary font-medium px-4 py-2 rounded-lg hover:bg-primary/10 transition-colors">
<span class="material-icons text-sm">add_circle_outline</span>
                        List an Item
                    </button>
<button class="bg-primary text-white px-5 py-2 rounded-lg font-medium hover:bg-primary/90 transition-all shadow-md shadow-primary/20">
                        Login
                    </button>
</div>
</div>
</div>
</nav>
<!-- Hero Section -->
<section class="relative py-20 lg:py-32 overflow-hidden">
<div class="absolute inset-0 z-0">
<div class="absolute top-[-10%] right-[-10%] w-[400px] h-[400px] bg-primary/10 rounded-full blur-3xl"></div>
<div class="absolute bottom-[-10%] left-[-10%] w-[300px] h-[300px] bg-primary/5 rounded-full blur-3xl"></div>
</div>
<div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
<h1 class="text-4xl md:text-6xl font-extrabold tracking-tight mb-6">
                Your Campus, <span class="text-primary">Your Marketplace.</span>
</h1>
<p class="text-lg md:text-xl text-slate-600 dark:text-slate-400 mb-10 max-w-2xl mx-auto">
                Rent what you need, earn from what you have. The peer-to-peer hub exclusive to verified students.
            </p>
<div class="max-w-3xl mx-auto">
<div class="flex flex-col md:flex-row p-2 bg-white dark:bg-slate-800 rounded-xl md:rounded-full shadow-xl border border-slate-200 dark:border-slate-700 gap-2">
<div class="flex-1 flex items-center px-4 gap-3">
<span class="material-icons text-slate-400">search</span>
<input class="w-full bg-transparent border-none focus:ring-0 text-slate-800 dark:text-white placeholder:text-slate-400" placeholder="Search for books, laptops, or tutors..." type="text"/>
</div>
<div class="hidden md:block w-px h-10 bg-slate-200 dark:bg-slate-700 my-auto"></div>
<div class="flex-shrink-0 flex items-center px-4 gap-2 cursor-pointer group">
<span class="material-icons text-slate-400 group-hover:text-primary transition-colors">category</span>
<select class="bg-transparent border-none focus:ring-0 text-slate-600 dark:text-slate-300 font-medium cursor-pointer">
<option>All Categories</option>
<option>Rentals</option>
<option>Services</option>
</select>
</div>
<button class="bg-primary text-white px-8 py-3 rounded-xl md:rounded-full font-semibold hover:bg-primary/90 transition-all shadow-lg shadow-primary/30">
                        Search
                    </button>
</div>
<div class="mt-4 flex flex-wrap justify-center gap-3 text-sm font-medium">
<span class="text-slate-500">Popular:</span>
<a class="text-primary hover:underline" href="#">Calculus Books</a>
<a class="text-primary hover:underline" href="#">MacBook Pro</a>
<a class="text-primary hover:underline" href="#">Math Tutoring</a>
<a class="text-primary hover:underline" href="#">Graphing Calculators</a>
</div>
</div>
</div>
</section>
<!-- Categories Section -->
<section class="py-16 bg-white dark:bg-slate-900/50">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="flex items-end justify-between mb-12">
<div>
<h2 class="text-3xl font-bold mb-2">Explore Categories</h2>
<p class="text-slate-500">Everything you need to excel this semester.</p>
</div>
<a class="text-primary font-semibold flex items-center gap-1 hover:gap-2 transition-all" href="#">
                    View all <span class="material-icons text-sm">arrow_forward</span>
</a>
</div>
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
<!-- Rentals -->
<div class="group cursor-pointer">
<div class="aspect-square bg-blue-50 dark:bg-slate-800 rounded-xl flex flex-col items-center justify-center p-6 border-2 border-transparent group-hover:border-primary/40 group-hover:bg-white transition-all shadow-sm">
<div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mb-4 text-primary">
<span class="material-icons text-3xl">menu_book</span>
</div>
<span class="font-semibold text-center">Books</span>
</div>
</div>
<div class="group cursor-pointer">
<div class="aspect-square bg-blue-50 dark:bg-slate-800 rounded-xl flex flex-col items-center justify-center p-6 border-2 border-transparent group-hover:border-primary/40 group-hover:bg-white transition-all shadow-sm">
<div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mb-4 text-primary">
<span class="material-icons text-3xl">devices</span>
</div>
<span class="font-semibold text-center">Electronics</span>
</div>
</div>
<div class="group cursor-pointer">
<div class="aspect-square bg-blue-50 dark:bg-slate-800 rounded-xl flex flex-col items-center justify-center p-6 border-2 border-transparent group-hover:border-primary/40 group-hover:bg-white transition-all shadow-sm">
<div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mb-4 text-primary">
<span class="material-icons text-3xl">sports_tennis</span>
</div>
<span class="font-semibold text-center">Sports Gear</span>
</div>
</div>
<!-- Services -->
<div class="group cursor-pointer">
<div class="aspect-square bg-blue-50 dark:bg-slate-800 rounded-xl flex flex-col items-center justify-center p-6 border-2 border-transparent group-hover:border-primary/40 group-hover:bg-white transition-all shadow-sm">
<div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mb-4 text-primary">
<span class="material-icons text-3xl">history_edu</span>
</div>
<span class="font-semibold text-center">Tutoring</span>
</div>
</div>
<div class="group cursor-pointer">
<div class="aspect-square bg-blue-50 dark:bg-slate-800 rounded-xl flex flex-col items-center justify-center p-6 border-2 border-transparent group-hover:border-primary/40 group-hover:bg-white transition-all shadow-sm">
<div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mb-4 text-primary">
<span class="material-icons text-3xl">palette</span>
</div>
<span class="font-semibold text-center">Design</span>
</div>
</div>
<div class="group cursor-pointer">
<div class="aspect-square bg-blue-50 dark:bg-slate-800 rounded-xl flex flex-col items-center justify-center p-6 border-2 border-transparent group-hover:border-primary/40 group-hover:bg-white transition-all shadow-sm">
<div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mb-4 text-primary">
<span class="material-icons text-3xl">build</span>
</div>
<span class="font-semibold text-center">Repairs</span>
</div>
</div>
</div>
</div>
</section>
<!-- How it Works Section -->
<section class="py-20 bg-background-light dark:bg-background-dark">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="text-center mb-16">
<h2 class="text-3xl font-bold mb-4">How it Works</h2>
<p class="text-slate-500 max-w-xl mx-auto">Connecting the campus community through safe and secure peer-to-peer exchanges.</p>
</div>
<div class="relative">
<!-- Connector Line -->
<div class="hidden lg:block absolute top-1/2 left-0 w-full h-0.5 bg-primary/10 -translate-y-1/2"></div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 lg:gap-8 relative z-10">
<!-- Step 1 -->
<div class="flex flex-col items-center text-center group">
<div class="w-16 h-16 bg-white dark:bg-slate-800 rounded-full flex items-center justify-center shadow-lg border-4 border-primary/20 mb-6 group-hover:scale-110 transition-transform">
<span class="material-icons text-primary text-2xl">person_add_alt</span>
</div>
<h3 class="text-lg font-bold mb-2">1. Register</h3>
<p class="text-sm text-slate-500">Sign up with your university email to join the community.</p>
</div>
<!-- Step 2 -->
<div class="flex flex-col items-center text-center group">
<div class="w-16 h-16 bg-white dark:bg-slate-800 rounded-full flex items-center justify-center shadow-lg border-4 border-primary/20 mb-6 group-hover:scale-110 transition-transform">
<span class="material-icons text-primary text-2xl">publish</span>
</div>
<h3 class="text-lg font-bold mb-2">2. Upload/Search</h3>
<p class="text-sm text-slate-500">List your gear or browse available services from your peers.</p>
</div>
<!-- Step 3 -->
<div class="flex flex-col items-center text-center group">
<div class="w-16 h-16 bg-white dark:bg-slate-800 rounded-full flex items-center justify-center shadow-lg border-4 border-primary/20 mb-6 group-hover:scale-110 transition-transform">
<span class="material-icons text-primary text-2xl">forum</span>
</div>
<h3 class="text-lg font-bold mb-2">3. Request</h3>
<p class="text-sm text-slate-500">Connect with a peer directly and finalize the details via chat.</p>
</div>
<!-- Step 4 -->
<div class="flex flex-col items-center text-center group">
<div class="w-16 h-16 bg-white dark:bg-slate-800 rounded-full flex items-center justify-center shadow-lg border-4 border-primary/20 mb-6 group-hover:scale-110 transition-transform">
<span class="material-icons text-primary text-2xl">verified_user</span>
</div>
<h3 class="text-lg font-bold mb-2">4. Admin Approval</h3>
<p class="text-sm text-slate-500">Secure transactions and verification handled by the campus team.</p>
</div>
</div>
</div>
</div>
</section>
<!-- Featured Listings -->
<section class="py-20 bg-white dark:bg-slate-900/50">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="flex items-center justify-between mb-12">
<h2 class="text-3xl font-bold">Featured Listings</h2>
<div class="flex gap-2">
<button class="p-2 rounded-full bg-slate-100 dark:bg-slate-800 hover:bg-primary/10 text-slate-600 hover:text-primary transition-colors">
<span class="material-icons">chevron_left</span>
</button>
<button class="p-2 rounded-full bg-slate-100 dark:bg-slate-800 hover:bg-primary/10 text-slate-600 hover:text-primary transition-colors">
<span class="material-icons">chevron_right</span>
</button>
</div>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
<!-- Listing Card 1 -->
<div class="group bg-white dark:bg-slate-800 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300">
<div class="relative aspect-[4/3] overflow-hidden">
<img alt="Listing Image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" data-alt="Physics textbook on a wooden table" src="https://lh3.googleusercontent.com/aida-public/AB6AXuA51RvEgqdohaeCE_8uQ5IsYvU-mbNRuC_DWd29U1nAckXqGBQDCvcoq3AJaNbEeribuOWTrBg_k3czB2kFKpHZsdRgXTIZ3-mUbpwnHqcb-pKVvQcDUjpXGhxWmhPmk0tQctNeDRdJEkCXuXWQ7XgLLBeGg6U9s4DYda8UoEGTTOSmHWsQ-gRDodjb0nt2X1DsTjyif6QpMai3kpQdoh9MI6NilnylZyxuZ61GZqntZW-ltQvHwK2egjld2U3A5CdZFDUxx4cVTUDd"/>
<div class="absolute top-3 left-3 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-[10px] font-bold text-primary uppercase flex items-center gap-1">
<span class="material-icons text-[12px]">verified</span> Verified
                        </div>
</div>
<div class="p-5">
<div class="flex justify-between items-start mb-2">
<h3 class="font-bold text-lg leading-tight line-clamp-1">Physics Fundamentals Ed. 12</h3>
<span class="text-primary font-bold text-xl">$15<span class="text-xs text-slate-400 font-normal">/wk</span></span>
</div>
<div class="flex items-center gap-2 mb-4 text-xs text-slate-500">
<div class="flex text-yellow-400">
<span class="material-icons text-[14px]">star</span>
<span class="material-icons text-[14px]">star</span>
<span class="material-icons text-[14px]">star</span>
<span class="material-icons text-[14px]">star</span>
<span class="material-icons text-[14px]">star_half</span>
</div>
<span>(24 reviews)</span>
</div>
<button class="w-full py-2.5 bg-primary/10 text-primary font-semibold rounded-lg hover:bg-primary hover:text-white transition-all">
                            Rent Now
                        </button>
</div>
</div>
<!-- Listing Card 2 -->
<div class="group bg-white dark:bg-slate-800 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300">
<div class="relative aspect-[4/3] overflow-hidden">
<img alt="Listing Image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" data-alt="Professional coding tutor working on laptop" src="https://lh3.googleusercontent.com/aida-public/AB6AXuA5o4bLeYNH6GYrFVMNy_eYi5aqiXwIGME81ioiyYo59UOaz5T5b0guQ1OOujxxbiFRsjY6cNvREHeFQWTcrkjPwwOZIu6Jm1X9qI6nUFD75_HkmNI6-jlH564PPrD-CCH6mP0pDqrFL4OeMagVKLutRd1lFsHSKXnaZfGaWRhW29dp9oXIdVzJyj6gB2TSe8R3l7LmihRY8ccTPMD8UeVXqC7TyLmOrrq1X4KKg9HCPBRX6-BEUWLp-oDDJ4qfqIUlYxGuefgim1qt"/>
</div>
<div class="p-5">
<div class="flex justify-between items-start mb-2">
<h3 class="font-bold text-lg leading-tight line-clamp-1">Python &amp; React Tutoring</h3>
<span class="text-primary font-bold text-xl">$25<span class="text-xs text-slate-400 font-normal">/hr</span></span>
</div>
<div class="flex items-center gap-2 mb-4 text-xs text-slate-500">
<div class="flex text-yellow-400">
<span class="material-icons text-[14px]">star</span>
<span class="material-icons text-[14px]">star</span>
<span class="material-icons text-[14px]">star</span>
<span class="material-icons text-[14px]">star</span>
<span class="material-icons text-[14px]">star</span>
</div>
<span>(12 reviews)</span>
</div>
<button class="w-full py-2.5 bg-primary/10 text-primary font-semibold rounded-lg hover:bg-primary hover:text-white transition-all">
                            Book Session
                        </button>
</div>
</div>
<!-- Listing Card 3 -->
<div class="group bg-white dark:bg-slate-800 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300">
<div class="relative aspect-[4/3] overflow-hidden">
<img alt="Listing Image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" data-alt="Apple MacBook Pro on a clean desk" src="https://lh3.googleusercontent.com/aida-public/AB6AXuALrQskSKwsH7k1Aps6PfX060FEte2Y-9pzR4egNogXNDHRHt8f9jgZeiswpGMhY-nrQn45KaPQwJW1jQ-fUMPmvhh3xS5Y_kiDNMinMy4zqsCbYzO5pqd5KFrw68F5utP5jz1a2uPseAhgck6QTDv8WMv3JU7FRgOXnwOHgAfBfW49aPmTi9HmWoAYhWmEzWrc7tlflSiX59kcQrvlCtzDh0WDQcCFZ9KRZjVVKK7KLA_4aFd5fzj8YFgqzCkIc-Co1L1TeS4nP6qR"/>
</div>
<div class="p-5">
<div class="flex justify-between items-start mb-2">
<h3 class="font-bold text-lg leading-tight line-clamp-1">MacBook Pro 14" M1</h3>
<span class="text-primary font-bold text-xl">$50<span class="text-xs text-slate-400 font-normal">/wk</span></span>
</div>
<div class="flex items-center gap-2 mb-4 text-xs text-slate-500">
<div class="flex text-yellow-400">
<span class="material-icons text-[14px]">star</span>
<span class="material-icons text-[14px]">star</span>
<span class="material-icons text-[14px]">star</span>
<span class="material-icons text-[14px]">star</span>
<span class="material-icons text-[14px]">star_border</span>
</div>
<span>(8 reviews)</span>
</div>
<button class="w-full py-2.5 bg-primary/10 text-primary font-semibold rounded-lg hover:bg-primary hover:text-white transition-all">
                            Rent Now
                        </button>
</div>
</div>
<!-- Listing Card 4 -->
<div class="group bg-white dark:bg-slate-800 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all duration-300">
<div class="relative aspect-[4/3] overflow-hidden">
<img alt="Listing Image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" data-alt="Electronic repair tools and dismantled laptop" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBFKfX6rLEvW_SyEvoGqd2TYZXCC2kCUhwp1o2OvQndg7BCGaK2WwuloFREq-SK_b2bKn73eDY8p72hDvdb1HoUiM4QXQOe1DB83ESClJajjWOmSkWJgVmmybadN5lIZ1alZ1AsXnTwHwwMmr2hmn3ggjIntmkJrsNfYwfoKQr6FziDWl8HiSYvZNgVFTAYDMaVNfq4Y_ehi81RtrPnXDV6iCEAK56td3HHiJmO4IR8D7aL4XMq8Hu8R81La3lgCVlgJqNFypNppmhQ"/>
<div class="absolute top-3 left-3 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-[10px] font-bold text-primary uppercase flex items-center gap-1">
<span class="material-icons text-[12px]">verified</span> Verified
                        </div>
</div>
<div class="p-5">
<div class="flex justify-between items-start mb-2">
<h3 class="font-bold text-lg leading-tight line-clamp-1">Laptop Screen &amp; Battery Repair</h3>
<span class="text-primary font-bold text-xl">$30<span class="text-xs text-slate-400 font-normal">/job</span></span>
</div>
<div class="flex items-center gap-2 mb-4 text-xs text-slate-500">
<div class="flex text-yellow-400">
<span class="material-icons text-[14px]">star</span>
<span class="material-icons text-[14px]">star</span>
<span class="material-icons text-[14px]">star</span>
<span class="material-icons text-[14px]">star</span>
<span class="material-icons text-[14px]">star</span>
</div>
<span>(45 reviews)</span>
</div>
<button class="w-full py-2.5 bg-primary/10 text-primary font-semibold rounded-lg hover:bg-primary hover:text-white transition-all">
                            Request Quote
                        </button>
</div>
</div>
</div>
</div>
</section>
<!-- App CTA Section -->
<section class="py-20 bg-primary overflow-hidden relative">
<div class="absolute inset-0 bg-primary opacity-90"></div>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
<div class="grid lg:grid-cols-2 gap-12 items-center">
<div class="text-white">
<h2 class="text-3xl md:text-5xl font-extrabold mb-6 leading-tight">Ready to earn from your gear?</h2>
<p class="text-primary/10 text-lg mb-10 text-blue-100">Join thousands of students turning their unused items and skills into campus-based businesses. It's safe, fast, and student-only.</p>
<div class="flex flex-wrap gap-4">
<button class="bg-white text-primary px-8 py-4 rounded-xl font-bold shadow-xl hover:bg-blue-50 transition-all">List Your First Item</button>
<button class="bg-primary-dark border-2 border-white/30 text-white px-8 py-4 rounded-xl font-bold hover:bg-white/10 transition-all">Learn More</button>
</div>
</div>
<div class="hidden lg:block relative h-full min-h-[400px]">
<div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-white/10 rounded-full blur-[100px]"></div>
<div class="relative bg-white/5 backdrop-blur-sm border border-white/20 rounded-[2rem] p-4 shadow-2xl rotate-3">
<img alt="App Preview" class="rounded-3xl shadow-lg" data-alt="Happy college students collaborating with laptops" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBQxK-c64IQKPaFFt80tZQzlMGDB9HN9UD_3iVstAR_QkT5fbUgkpypaY5-rNkwiSUqPXU76W9n1W1kKmsJPSv94bCPmQOUPtc-Q1DiK5T33uLjuDdCBBs6SmHPxS4rwqjVD4xPUJxVBy-9sR97bquIs3K3R6uMG6O12WIMiKsWn6NR8I7urUhHikRN1lZr1rKmLJ_BEAjy40paV9NZ5YMfW8l2IIePCti4uwVCe_2YvT196w99YtIz2RocJ-QsH_fhvSmSbnPOc9og"/>
</div>
</div>
</div>
</div>
</section>
<!-- Footer -->
<footer class="bg-slate-900 text-slate-400 py-16">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-12 mb-12">
<div class="col-span-2 lg:col-span-2">
<div class="flex items-center gap-2 mb-6">
<div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
<span class="material-icons text-white text-sm">school</span>
</div>
<span class="text-xl font-bold text-white tracking-tight">CampusMarket</span>
</div>
<p class="max-w-xs mb-6 text-sm">The leading peer-to-peer marketplace designed specifically for the needs of college students worldwide.</p>
<div class="flex gap-4">
<a class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center hover:bg-primary hover:text-white transition-all" href="#"><span class="material-icons text-xl">facebook</span></a>
<a class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center hover:bg-primary hover:text-white transition-all" href="#"><span class="material-icons text-xl">alternate_email</span></a>
<a class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center hover:bg-primary hover:text-white transition-all" href="#"><span class="material-icons text-xl">language</span></a>
</div>
</div>
<div>
<h4 class="text-white font-bold mb-6 uppercase text-xs tracking-widest">Marketplace</h4>
<ul class="space-y-4 text-sm">
<li><a class="hover:text-primary transition-colors" href="#">Rent Books</a></li>
<li><a class="hover:text-primary transition-colors" href="#">Tech Gadgets</a></li>
<li><a class="hover:text-primary transition-colors" href="#">Find Tutors</a></li>
<li><a class="hover:text-primary transition-colors" href="#">Creative Pros</a></li>
</ul>
</div>
<div>
<h4 class="text-white font-bold mb-6 uppercase text-xs tracking-widest">Support</h4>
<ul class="space-y-4 text-sm">
<li><a class="hover:text-primary transition-colors" href="#">Safety Guidelines</a></li>
<li><a class="hover:text-primary transition-colors" href="#">Help Center</a></li>
<li><a class="hover:text-primary transition-colors" href="#">Terms of Service</a></li>
<li><a class="hover:text-primary transition-colors" href="#">Report Issues</a></li>
</ul>
</div>
<div>
<h4 class="text-white font-bold mb-6 uppercase text-xs tracking-widest">Trust</h4>
<ul class="space-y-4 text-sm">
<li><a class="hover:text-primary transition-colors flex items-center gap-2" href="#"><span class="material-icons text-xs">verified</span> Campus Verified</a></li>
<li><a class="hover:text-primary transition-colors" href="#">Secure Payments</a></li>
<li><a class="hover:text-primary transition-colors" href="#">Student Protection</a></li>
</ul>
</div>
</div>
<div class="pt-8 border-t border-slate-800 text-center text-xs">
<p>© 2024 CampusMarket. Built with ♥ for students, by students.</p>
</div>
</div>
</footer>
</body></html>