<x-app-layout>
    {{-- 页面标题 --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('仪表盘') }}
        </h2>
    </x-slot>

    {{-- 页面主要内容 --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- 单一域名查询表单 --}}
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">查询单个域名</h3>
                        <div class="flex items-center space-x-2">
                            <input id="domainInput" type="text" placeholder="请输入域名" 
                                class="block w-full rounded-md border-gray-300 shadow-sm 
                                    focus:border-indigo-300 focus:ring focus:ring-indigo-200 
                                    focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 
                                    dark:text-gray-300">
                            <button onclick="goToDomain()" 
                                class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 
                                    border border-transparent rounded-md font-semibold text-xs text-white 
                                    dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 
                                    dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white 
                                    active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none 
                                    focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 
                                    dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                查询
                            </button>
                        </div>
                    </div>

                    <script>
                    function goToDomain() {
                        const domain = document.getElementById('domainInput').value.trim();
                        if (domain) {
                            // 用 Laravel route 生成基础路径，然后替换 :domain
                            let url = "{{ route('domain.ranking', ':domain') }}".replace(':domain', encodeURIComponent(domain));
                            window.location.href = url;
                        } else {
                            alert("请输入域名");
                        }
                    }
                    </script>

                    {{-- 多个域名查询表单 --}}
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">查询多个域名 (每行一个)</h3>
                        <form action="{{ route('ranking.domains') }}" method="POST">
                            @csrf
                            <textarea name="domains" rows="4" placeholder="example.com&#10;google.com&#10;laravel.com" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300"></textarea>
                            <button type="submit" class="mt-2 inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                批量查询
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
