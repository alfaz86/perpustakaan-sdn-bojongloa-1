<style>
    .overflow-x-clip {
        z-index: 1;
    }
    .fi-sidebar-header,
    .fi-topbar nav {
        background-color: #6477DB !important;
    }
    .fi-logo {
        color: #FFFFFF !important;
    }
    @media (min-width: 1024px) {
        nav.fi-sidebar-nav {
            background-color: #FFFFFF !important;
            margin-top: 20px !important;
            margin-left: 20px !important;
            border-radius: 10px !important;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1) !important;
            margin-bottom: 25px !important;
        }
    }
</style>
<div class="flex flex-col items-center">
    <img src="{{ auth()->user()->getFilamentAvatarUrl() }}" alt="Avatar" class="w-16 h-16 rounded-full border">
    <p class="text-lg font-semibold text-gray-900 dark:text-white mt-2">
        {{ auth()->user()->name }}
    </p>
    <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-green-600/20 ring-inset">
        {{ __(Str::ucfirst(auth()->user()->role)) }}
    </span>
</div>
