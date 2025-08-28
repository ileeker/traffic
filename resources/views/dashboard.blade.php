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
            {{-- 显示错误信息 --}}
            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- 单一域名查询表单 --}}
                    <div class="mb-8 pb-8 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">查询单个域名</h3>
                        <div class="flex items-center space-x-2">
                            <input id="domainInput" 
                                   type="text" 
                                   placeholder="请输入域名 (例如: example.com)" 
                                   class="block w-full rounded-md border-gray-300 shadow-sm 
                                        focus:border-indigo-300 focus:ring focus:ring-indigo-200 
                                        focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 
                                        dark:text-gray-300"
                                   onkeypress="if(event.key === 'Enter') { goToDomain(); return false; }">
                            <button onclick="goToDomain()" 
                                    type="button"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 
                                        border border-transparent rounded-md font-semibold text-xs text-white 
                                        uppercase tracking-widest hover:bg-blue-700 
                                        focus:bg-blue-700 active:bg-blue-900 
                                        focus:outline-none focus:ring-2 focus:ring-indigo-500 
                                        focus:ring-offset-2 dark:focus:ring-offset-gray-800 
                                        transition ease-in-out duration-150">
                                查询
                            </button>
                        </div>
                    </div>

                    {{-- 多个域名查询表单 --}}
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">批量查询域名</h3>
                        
                        {{-- 使用说明 --}}
                        <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                            <ul class="list-disc list-inside space-y-1">
                                <li>每行输入一个域名</li>
                                <li>域名格式：example.com（无需 http:// 或 www.）</li>
                                <li>一次最多查询 100 个域名</li>
                            </ul>
                        </div>

                        <form id="batchForm" action="{{ route('domains.detail') }}" method="POST" onsubmit="return validateBatchForm()">
                            @csrf
                            <textarea id="domainsTextarea"
                                      name="domains" 
                                      rows="6" 
                                      placeholder="example.com&#10;google.com&#10;facebook.com&#10;amazon.com" 
                                      class="block w-full rounded-md border-gray-300 shadow-sm 
                                           focus:border-indigo-300 focus:ring focus:ring-indigo-200 
                                           focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 
                                           dark:text-gray-300"
                                      required></textarea>
                            
                            {{-- 显示验证错误 --}}
                            @error('domains')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror

                            {{-- 域名计数和按钮组 --}}
                            <div class="mt-4 flex items-center justify-between">
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    <span id="domainCount">0</span> 个域名
                                </div>
                                
                                <div class="space-x-2">
                                    <button type="button" 
                                            onclick="clearDomains()"
                                            class="inline-flex items-center px-4 py-2 bg-gray-500 
                                                border border-transparent rounded-md font-semibold text-xs text-white 
                                                uppercase tracking-widest hover:bg-gray-600 
                                                focus:bg-gray-600 active:bg-gray-700 
                                                focus:outline-none focus:ring-2 focus:ring-gray-500 
                                                focus:ring-offset-2 dark:focus:ring-offset-gray-800 
                                                transition ease-in-out duration-150">
                                        清空
                                    </button>
                                    
                                    <button id="submitBtn"
                                            type="submit" 
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 
                                                border border-transparent rounded-md font-semibold text-xs text-white 
                                                uppercase tracking-widest hover:bg-blue-700 
                                                focus:bg-blue-700 active:bg-blue-900 
                                                focus:outline-none focus:ring-2 focus:ring-indigo-500 
                                                focus:ring-offset-2 dark:focus:ring-offset-gray-800 
                                                transition ease-in-out duration-150
                                                disabled:opacity-50 disabled:cursor-not-allowed">
                                        批量查询
                                    </button>
                                </div>
                            </div>
                        </form>

                        {{-- 示例域名快速添加 --}}
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">快速添加示例域名：</p>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" 
                                        onclick="addExampleDomain('google.com')"
                                        class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 
                                             dark:text-gray-300 rounded hover:bg-gray-200 
                                             dark:hover:bg-gray-600 text-sm transition">
                                    google.com
                                </button>
                                <button type="button" 
                                        onclick="addExampleDomain('facebook.com')"
                                        class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 
                                             dark:text-gray-300 rounded hover:bg-gray-200 
                                             dark:hover:bg-gray-600 text-sm transition">
                                    facebook.com
                                </button>
                                <button type="button" 
                                        onclick="addExampleDomain('amazon.com')"
                                        class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 
                                             dark:text-gray-300 rounded hover:bg-gray-200 
                                             dark:hover:bg-gray-600 text-sm transition">
                                    amazon.com
                                </button>
                                <button type="button" 
                                        onclick="addExampleDomain('youtube.com')"
                                        class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 
                                             dark:text-gray-300 rounded hover:bg-gray-200 
                                             dark:hover:bg-gray-600 text-sm transition">
                                    youtube.com
                                </button>
                                <button type="button" 
                                        onclick="addExampleDomain('twitter.com')"
                                        class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 
                                             dark:text-gray-300 rounded hover:bg-gray-200 
                                             dark:hover:bg-gray-600 text-sm transition">
                                    twitter.com
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript 代码 --}}
    <script>
        // 单个域名查询
        function goToDomain() {
            const domain = document.getElementById('domainInput').value.trim();
            if (domain) {
                // 清理域名（移除 http://, https://, www.）
                let cleanDomain = domain.replace(/^(https?:\/\/)?(www\.)?/, '');
                
                // 使用 Laravel route 生成 URL
                let url = "{{ route('domain.ranking', ':domain') }}".replace(':domain', encodeURIComponent(cleanDomain));
                window.location.href = url;
            } else {
                alert("请输入域名");
                document.getElementById('domainInput').focus();
            }
        }

        // 批量查询表单验证
        function validateBatchForm() {
            const textarea = document.getElementById('domainsTextarea');
            const domains = textarea.value.split('\n').map(d => d.trim()).filter(d => d.length > 0);
            
            if (domains.length === 0) {
                alert('请至少输入一个域名');
                textarea.focus();
                return false;
            }
            
            if (domains.length > 100) {
                alert('一次最多只能查询 100 个域名，当前输入了 ' + domains.length + ' 个域名');
                return false;
            }
            
            // 显示加载状态
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> 查询中...';
            
            console.log('提交表单，域名数量：', domains.length);
            return true;
        }

        // 清空域名列表
        function clearDomains() {
            if (confirm('确定要清空所有域名吗？')) {
                document.getElementById('domainsTextarea').value = '';
                updateDomainCount();
            }
        }

        // 添加示例域名
        function addExampleDomain(domain) {
            const textarea = document.getElementById('domainsTextarea');
            const currentValue = textarea.value.trim();
            
            // 检查是否已存在
            const existingDomains = currentValue.split('\n').map(d => d.trim());
            if (existingDomains.includes(domain)) {
                alert('该域名已在列表中');
                return;
            }
            
            if (currentValue) {
                textarea.value = currentValue + '\n' + domain;
            } else {
                textarea.value = domain;
            }
            
            updateDomainCount();
        }

        // 更新域名计数
        function updateDomainCount() {
            const textarea = document.getElementById('domainsTextarea');
            const domains = textarea.value.split('\n').map(d => d.trim()).filter(d => d.length > 0);
            const countSpan = document.getElementById('domainCount');
            
            countSpan.textContent = domains.length;
            
            // 根据数量改变颜色
            if (domains.length > 100) {
                countSpan.className = 'text-red-600 font-bold';
            } else if (domains.length > 50) {
                countSpan.className = 'text-yellow-600 font-bold';
            } else {
                countSpan.className = 'text-green-600 font-bold';
            }
        }

        // 页面加载时初始化
        document.addEventListener('DOMContentLoaded', function() {
            // 监听文本域变化
            const textarea = document.getElementById('domainsTextarea');
            textarea.addEventListener('input', updateDomainCount);
            textarea.addEventListener('paste', function() {
                setTimeout(updateDomainCount, 10);
            });
            
            // 初始化计数
            updateDomainCount();
            
            // 调试信息
            console.log('Dashboard 页面已加载');
            console.log('表单 action:', document.getElementById('batchForm').action);
            console.log('CSRF token:', document.querySelector('input[name="_token"]').value);
        });
    </script>
</x-app-layout>