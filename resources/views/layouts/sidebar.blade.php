{{-- resources/views/layouts/sidebar.blade.php --}}
<aside class="w-64 flex-shrink-0 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700">
    <div class="p-4">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">导航菜单</h2>
    </div>
    <nav class="mt-4 px-2">
        {{-- 使用 Alpine.js 来控制下拉菜单 --}}
        <div x-data="{ open: false }">
            {{-- 一级菜单：Tranco --}}
            <button @click="open = !open" class="w-full flex justify-between items-center px-2 py-2 text-left text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md">
                <span>Tranco</span>
                {{-- 箭头图标，根据开合状态旋转 --}}
                <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-90': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </button>
            {{-- 二级菜单 --}}
            <div x-show="open" x-transition class="mt-2 ml-4 space-y-2">
                <a href="{{ route('ranking-changes.index') }}" class="block px-2 py-1 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md">Trending</a>
                <!-- <a href="#" class="block px-2 py-1 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md">week 2 trend</a> -->
            </div>
        </div>

        <div x-data="{ open: false }" class="mt-2">
            {{-- 一级菜单：Similarweb --}}
            <button @click="open = !open" class="w-full flex justify-between items-center px-2 py-2 text-left text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md">
                <span>Similarweb</span>
                <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-90': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </button>
            {{-- 二级菜单 --}}
            <div x-show="open" x-transition class="mt-2 ml-4 space-y-2">
                <a href="{{ route('domains.browse') }}" class="block px-2 py-1 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md">All sites</a>
                <a href="{{ route('similarweb-changes.index') }}" class="block px-2 py-1 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md">Trending</a>
                <a href="{{ route('domains.categories') }}" class="block px-2 py-1 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md">Categories</a>
            </div>
        </div>
    </nav>
</aside>
