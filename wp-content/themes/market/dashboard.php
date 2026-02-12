<!DOCTYPE html>
<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Admin Approval Dashboard | CampusMarket</title>
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
                        "background-dark": "#0f172a",
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
<style type="text/tailwindcss">
        body {
            font-family: 'Lexend', sans-serif;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .sidebar-link-active {
            @apply bg-primary/10 text-primary border-r-4 border-primary;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 min-h-screen flex">
<aside class="w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex-shrink-0 hidden lg:flex flex-col">
<div class="p-6 flex items-center gap-3">
<div class="w-8 h-8 bg-primary rounded flex items-center justify-center">
<span class="material-icons text-white text-sm">school</span>
</div>
<span class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">CampusMarket</span>
</div>
<nav class="flex-1 mt-4">
<a class="sidebar-link-active flex items-center gap-3 px-6 py-4 transition-colors" href="#">
<span class="material-symbols-outlined text-[22px]">dashboard</span>
<span class="font-medium text-sm">Dashboard</span>
</a>
<a class="flex items-center gap-3 px-6 py-4 text-slate-500 hover:text-primary hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors" href="#">
<span class="material-symbols-outlined text-[22px]">format_list_bulleted</span>
<span class="font-medium text-sm">Listings Management</span>
</a>
<a class="flex items-center gap-3 px-6 py-4 text-slate-500 hover:text-primary hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors" href="#">
<span class="material-symbols-outlined text-[22px]">verified_user</span>
<span class="font-medium text-sm">User Verification</span>
</a>
<a class="flex items-center gap-3 px-6 py-4 text-slate-500 hover:text-primary hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors" href="#">
<span class="material-symbols-outlined text-[22px]">bar_chart</span>
<span class="font-medium text-sm">Reports</span>
</a>
</nav>
<div class="p-6 border-t border-slate-100 dark:border-slate-800">
<div class="flex items-center gap-3 mb-6">
<div class="w-10 h-10 rounded-full bg-slate-200 overflow-hidden">
<img alt="Admin Avatar" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAhx4MxP8d2afeWiNo9fqjCPmLhdLZnHQ3oGDI96ykGXIaMYYAVddTl6Ux_z4wVr6fpok1CmV78zug0is2kJL5F0jjAtUDsrA2bFmjlDvd9aEMjSpU0lbZnxB6KMhwLuvxFZ1HSYVeHEJ4C8CSaOkEpjzzY_QzKvXJJFB3QxD527pWXLrSIhrQp1q06XhtwRmIfmvBtfZDUSPQINkY1tds__sq_h9QcFchEPhlBKE5q-b99xzNC0ia4fOuQ98Q8IFM5M7Dk8w9N_s-8"/>
</div>
<div>
<p class="text-sm font-bold truncate">Admin User</p>
<p class="text-xs text-slate-500">Super Admin</p>
</div>
</div>
<button class="flex items-center gap-2 text-sm text-slate-500 hover:text-red-500 transition-colors">
<span class="material-symbols-outlined text-sm">logout</span>
                Logout
            </button>
</div>
</aside>
<main class="flex-1 flex flex-col min-h-screen overflow-x-hidden">
<header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-8 sticky top-0 z-10">
<h1 class="text-xl font-bold">Admin Approval Dashboard</h1>
<div class="flex items-center gap-4">
<div class="relative group">
<span class="material-symbols-outlined text-slate-400 group-hover:text-primary cursor-pointer">notifications</span>
<span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[10px] flex items-center justify-center rounded-full">12</span>
</div>
<div class="h-8 w-px bg-slate-200 dark:bg-slate-800"></div>
<div class="text-sm font-medium text-slate-500">Oct 24, 2024</div>
</div>
</header>
<div class="p-8 max-w-7xl mx-auto w-full">
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<div class="flex items-center justify-between mb-4">
<div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/20 rounded-lg flex items-center justify-center text-blue-600">
<span class="material-symbols-outlined">inventory_2</span>
</div>
<span class="text-green-500 text-xs font-bold flex items-center">
<span class="material-symbols-outlined text-xs">trending_up</span> 12%
                        </span>
</div>
<p class="text-slate-500 text-sm font-medium">Total Rentals</p>
<h2 class="text-2xl font-bold mt-1">1,482</h2>
</div>
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<div class="flex items-center justify-between mb-4">
<div class="w-12 h-12 bg-purple-50 dark:bg-purple-900/20 rounded-lg flex items-center justify-center text-purple-600">
<span class="material-symbols-outlined">work</span>
</div>
<span class="text-green-500 text-xs font-bold flex items-center">
<span class="material-symbols-outlined text-xs">trending_up</span> 8%
                        </span>
</div>
<p class="text-slate-500 text-sm font-medium">Active Services</p>
<h2 class="text-2xl font-bold mt-1">645</h2>
</div>
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm ring-2 ring-primary/20">
<div class="flex items-center justify-between mb-4">
<div class="w-12 h-12 bg-amber-50 dark:bg-amber-900/20 rounded-lg flex items-center justify-center text-amber-600">
<span class="material-symbols-outlined">pending_actions</span>
</div>
<span class="bg-amber-100 text-amber-700 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider">Priority</span>
</div>
<p class="text-slate-500 text-sm font-medium">Pending Approvals</p>
<h2 class="text-2xl font-bold mt-1">24</h2>
</div>
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<div class="flex items-center justify-between mb-4">
<div class="w-12 h-12 bg-green-50 dark:bg-green-900/20 rounded-lg flex items-center justify-center text-green-600">
<span class="material-symbols-outlined">group</span>
</div>
<span class="text-green-500 text-xs font-bold flex items-center">
<span class="material-symbols-outlined text-xs">trending_up</span> 15%
                        </span>
</div>
<p class="text-slate-500 text-sm font-medium">New Users</p>
<h2 class="text-2xl font-bold mt-1">128</h2>
</div>
</div>
<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
<div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
<div>
<h3 class="font-bold text-lg">Pending Approval Queue</h3>
<p class="text-sm text-slate-500">Review and approve new marketplace listings.</p>
</div>
<div class="flex gap-2">
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">search</span>
<input class="pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-sm focus:ring-1 ring-primary w-64" placeholder="Filter requests..." type="text"/>
</div>
</div>
</div>
<div class="overflow-x-auto">
<table class="w-full text-left">
<thead>
<tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 text-[11px] uppercase tracking-widest font-bold">
<th class="px-6 py-4">Request ID</th>
<th class="px-6 py-4">Item/Service Name</th>
<th class="px-6 py-4">Student Name</th>
<th class="px-6 py-4">Type</th>
<th class="px-6 py-4">Submitted</th>
<th class="px-6 py-4 text-right">Actions</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-100 dark:divide-slate-800">
<tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
<td class="px-6 py-4 text-sm font-mono text-slate-400">#RQ-4582</td>
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<div class="w-10 h-10 rounded bg-slate-100 dark:bg-slate-800 flex-shrink-0">
<img alt="thumbnail" class="w-full h-full object-cover rounded opacity-80" src="https://lh3.googleusercontent.com/aida-public/AB6AXuByoNXuZ2grc-_BovDikN8lYG1h8B5T9TWWwZ3PZRq4YNvRGfs1XTwNpSbArcx8m7G1TDQJoU0MxyaboWmqo27KGQ5Y158jiPuFFKD7BMSqawIEuvVYAA5Njiz4ttwY4i8o_PvmVsmxpnUvm3C93IS0mpeifUJH_W49r3OYyte6TZiE9ZGP3MLpd8l0tXMJnPkO1KhgyOH7jSJUDgsPbee36vSI-8XSfNpKzIrpqsQJurDh_UmC1bpRT9khRQMzj60nmw5rVlIPfkTw"/>
</div>
<span class="text-sm font-semibold">Organic Chemistry Textbook (2023)</span>
</div>
</td>
<td class="px-6 py-4">
<div class="text-sm font-medium">Alex Johnson</div>
<div class="text-xs text-slate-400">alex.j@university.edu</div>
</td>
<td class="px-6 py-4">
<span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">Rental</span>
</td>
<td class="px-6 py-4 text-sm text-slate-500">2 hours ago</td>
<td class="px-6 py-4 text-right">
<div class="flex items-center justify-end gap-2">
<button class="px-3 py-1.5 bg-primary text-white text-xs font-bold rounded-lg hover:bg-primary/90 transition-colors">Approve</button>
<button class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-lg hover:bg-red-50 hover:text-red-500 transition-colors">Reject</button>
<button class="p-1.5 text-slate-400 hover:text-primary transition-colors">
<span class="material-symbols-outlined text-[20px]">visibility</span>
</button>
</div>
</td>
</tr>
<tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
<td class="px-6 py-4 text-sm font-mono text-slate-400">#RQ-4581</td>
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<div class="w-10 h-10 rounded bg-slate-100 dark:bg-slate-800 flex-shrink-0">
<img alt="thumbnail" class="w-full h-full object-cover rounded opacity-80" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAMXAmWDh_1NfNYXdtKLH770Fswpifcl5NM8bOWB9i2zHpz7TlxtODjraBGjv6cvR7Y5uO9o0Bylt_CsosIZcm0YQBH9U2k52WaqgcfwTSdR-XsYRDh3aM3O9YENIGpAqaS2sOMUSyXi5qGpmgH6K6YMziYiu50HNM_MJUHyYVlnrKy6rkeK3hFtc_VZyFb1uN7Cy-zH_PFia2dhCo0iI9FJwC_xLJoXv8W-GyhAPTuGk4cbenqRw4yHUghVpoMdv7UDwswHDsig0px"/>
</div>
<span class="text-sm font-semibold">Python Data Analysis Session</span>
</div>
</td>
<td class="px-6 py-4">
<div class="text-sm font-medium">Samantha Reed</div>
<div class="text-xs text-slate-400">s.reed@university.edu</div>
</td>
<td class="px-6 py-4">
<span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">Service</span>
</td>
<td class="px-6 py-4 text-sm text-slate-500">4 hours ago</td>
<td class="px-6 py-4 text-right">
<div class="flex items-center justify-end gap-2">
<button class="px-3 py-1.5 bg-primary text-white text-xs font-bold rounded-lg hover:bg-primary/90 transition-colors">Approve</button>
<button class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-lg hover:bg-red-50 hover:text-red-500 transition-colors">Reject</button>
<button class="p-1.5 text-slate-400 hover:text-primary transition-colors">
<span class="material-symbols-outlined text-[20px]">visibility</span>
</button>
</div>
</td>
</tr>
<tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
<td class="px-6 py-4 text-sm font-mono text-slate-400">#RQ-4579</td>
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<div class="w-10 h-10 rounded bg-slate-100 dark:bg-slate-800 flex-shrink-0">
<img alt="thumbnail" class="w-full h-full object-cover rounded opacity-80" src="https://lh3.googleusercontent.com/aida-public/AB6AXuC9Z-YrEnA93MFWI6ATU_o3Dueqt9l3P6_tJR6ddJI_JZnrYfP4GWYS_bMJw-H6TLk_Xyoz4LjcHsYeqObkJ2ta2pVvZjFaMnrsXdxYhBaulmUt8G8QF8J5-8kIuI1oAtBfcn2WVTHyIIKIB_rs0e5ikuR9q1fX0beUol0RY55QGSsw8Sx2wjKC-6QA62S_LGhyhp3iiSoipbF0A5trbIdQkxf8fGR9AsOb6p_nyHe23F6ubc0vLuIX9hdvIoH9h4CR615tE4PIpXnh"/>
</div>
<span class="text-sm font-semibold">Sony A7III Camera Kit</span>
</div>
</td>
<td class="px-6 py-4">
<div class="text-sm font-medium">Liam Chen</div>
<div class="text-xs text-slate-400">l.chen@university.edu</div>
</td>
<td class="px-6 py-4">
<span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">Rental</span>
</td>
<td class="px-6 py-4 text-sm text-slate-500">Yesterday</td>
<td class="px-6 py-4 text-right">
<div class="flex items-center justify-end gap-2">
<button class="px-3 py-1.5 bg-primary text-white text-xs font-bold rounded-lg hover:bg-primary/90 transition-colors">Approve</button>
<button class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-lg hover:bg-red-50 hover:text-red-500 transition-colors">Reject</button>
<button class="p-1.5 text-slate-400 hover:text-primary transition-colors">
<span class="material-symbols-outlined text-[20px]">visibility</span>
</button>
</div>
</td>
</tr>
<tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
<td class="px-6 py-4 text-sm font-mono text-slate-400">#RQ-4575</td>
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<div class="w-10 h-10 rounded bg-slate-100 dark:bg-slate-800 flex-shrink-0">
<img alt="thumbnail" class="w-full h-full object-cover rounded opacity-80" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDfz1oXbgne13ikQ_EwW7MR2_3mWhFVO3bwGZNl71tOvNmoBh7hwUY0K7qz1V-dw3gl1RqZvJ2VxjkGy1_gv1FpquFc7IEjoXiWUuB5JFqQgd1JpdkZJHRzUVcYrZ-PwoLXSxwGhiCm3nrXU6afhxiW2wWoiTw9iIVVuuwluxEssOSPQDZFACBH0jLPYyHcrYxfauynHVbgb8yIr5Y33TNKiDwO281WpjOKLLYlgoMVy30fY5DNYQ4k-Xcghevil6uGJitLwyditnZw"/>
</div>
<span class="text-sm font-semibold">Screen Repair Service</span>
</div>
</td>
<td class="px-6 py-4">
<div class="text-sm font-medium">Jordan Smith</div>
<div class="text-xs text-slate-400">j.smith@university.edu</div>
</td>
<td class="px-6 py-4">
<span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">Service</span>
</td>
<td class="px-6 py-4 text-sm text-slate-500">Oct 22, 2024</td>
<td class="px-6 py-4 text-right">
<div class="flex items-center justify-end gap-2">
<button class="px-3 py-1.5 bg-primary text-white text-xs font-bold rounded-lg hover:bg-primary/90 transition-colors">Approve</button>
<button class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-lg hover:bg-red-50 hover:text-red-500 transition-colors">Reject</button>
<button class="p-1.5 text-slate-400 hover:text-primary transition-colors">
<span class="material-symbols-outlined text-[20px]">visibility</span>
</button>
</div>
</td>
</tr>
</tbody>
</table>
</div>
<div class="p-6 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
<p class="text-sm text-slate-500 italic">Showing 1 to 4 of 24 entries</p>
<div class="flex gap-1">
<button class="px-3 py-1.5 border border-slate-200 dark:border-slate-800 rounded text-xs font-bold hover:bg-slate-50 transition-colors">Previous</button>
<button class="px-3 py-1.5 bg-primary text-white rounded text-xs font-bold">1</button>
<button class="px-3 py-1.5 border border-slate-200 dark:border-slate-800 rounded text-xs font-bold hover:bg-slate-50 transition-colors">2</button>
<button class="px-3 py-1.5 border border-slate-200 dark:border-slate-800 rounded text-xs font-bold hover:bg-slate-50 transition-colors">Next</button>
</div>
</div>
</div>
</div>
</main>

</body></html>