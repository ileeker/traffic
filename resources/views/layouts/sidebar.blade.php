{{-- resources/views/layouts/sidebar.blade.php --}}
<div class="w-64 h-screen bg-white dark:bg-gray-800 shadow-md flex-shrink-0">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">导航菜单</h3>
        <ul class="mt-4 space-y-2">
            <li>
                <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 rounded">
                    Dashboard
                </a>
            </li>
            <li>
                <a href="#" class="block px-4 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 rounded">
                    Analytics
                </a>
            </li>
            <li>
                <a href="#" class="block px-4 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700 rounded">
                    Settings
                </a>
            </li>
            </ul>
    </div>
</div>