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
                    
                    {{-- 表格容器 --}}
                    <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="py-3 px-6">
                                        域名
                                    </th>
                                    <th scope="col" class="py-3 px-6">
                                        当前排名
                                    </th>
                                    <th scope="col" class="py-3 px-6">
                                        上升趋势
                                    </th>
                                    <th scope="col" class="py-3 px-6">
                                        网站介绍
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- 表格内容行 (示例) --}}
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <th scope="row" class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        example.com
                                    </th>
                                    <td class="py-4 px-6">
                                        1
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="text-green-500">↑ 5</span>
                                    </td>
                                    <td class="py-4 px-6">
                                        这是一个示例网站的介绍。
                                    </td>
                                </tr>
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <th scope="row" class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        google.com
                                    </th>
                                    <td class="py-4 px-6">
                                        2
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="text-red-500">↓ 2</span>
                                    </td>
                                    <td class="py-4 px-6">
                                        全球最大的搜索引擎。
                                    </td>
                                </tr>
                                <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <th scope="row" class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        laravel.com
                                    </th>
                                    <td class="py-4 px-6">
                                        10
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="text-gray-500">-</span>
                                    </td>
                                    <td class="py-4 px-6">
                                        PHP框架的官方网站。
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
