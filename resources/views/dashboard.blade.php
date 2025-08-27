<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    {{-- 使用 Flex 布局来并排显示侧边栏和主内容 --}}
    <div class="flex">
        
        {{-- 1. 使用 @include 引入侧边栏 --}}
        @include('layouts.sidebar')

        {{-- 2. 主内容区域 --}}
        <div class="flex-grow">
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            {{ __("You're logged in!") }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>