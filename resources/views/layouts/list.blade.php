<x-app-layout>
    {{-- The header section will be provided by the child templates --}}
    <x-slot name="header">
        @yield('list_header')
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Domain accessibility test card (common to both pages) --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">域名访问性测试</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">点击按钮测试当前页面所有域名的HTTP/HTTPS访问性。测试失败的域名行将被隐藏。</p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <button id="testAllDomains" 
                                    class="px-4 py-2 bg-green-600 text-black rounded-md hover:bg-green-700 transition-colors duration-200 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                测试所有域名
                            </button>
                            <button id="stopTest" 
                                    class="px-4 py-2 bg-red-600 text-black rounded-md hover:bg-red-700 transition-colors duration-200 hidden">
                                停止测试
                            </button>
                            <button id="clearResults" 
                                    class="px-4 py-2 bg-gray-600 text-black rounded-md hover:bg-gray-700 transition-colors duration-200 hidden">
                                清除结果
                            </button>
                        </div>
                    </div>
                    <div id="testProgress" class="mt-4 hidden">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-600 dark:text-gray-400">测试进度：<span id="progressText">0/0</span></span>
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                成功: <span id="successCount" class="text-green-600 font-bold">0</span> | 
                                失败: <span id="failCount" class="text-red-600 font-bold">0</span> | 
                                超时: <span id="timeoutCount" class="text-yellow-600 font-bold">0</span>
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                            <div id="progressBar" class="bg-green-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- The controls and stats card (content provided by child templates) --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @yield('list_controls')
                </div>
            </div>

            {{-- The main data table card (content provided by child templates) --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                @yield('table_head')
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @yield('table_body')
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- The pagination links card (uses the $paginator variable from child) --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    {{ $paginator->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Common Javascript for all list pages --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Page jump functionality
            const pageJumpInput = document.getElementById('pageJumpInput');
            const pageJumpBtn = document.getElementById('pageJumpBtn');
            
            function jumpToPage() {
                const page = parseInt(pageJumpInput.value);
                const maxPage = parseInt(pageJumpInput.getAttribute('max'));
                
                if (page && page >= 1 && page <= maxPage) {
                    const url = new URL(window.location);
                    url.searchParams.set('page', page);
                    window.location.href = url.toString();
                } else {
                    // Revert to current page if input is invalid
                    alert('请输入有效的页码（1 - ' + maxPage + '）');
                    pageJumpInput.value = {{ $paginator->currentPage() }};
                }
            }
            
            if (pageJumpBtn) pageJumpBtn.addEventListener('click', jumpToPage);
            if (pageJumpInput) pageJumpInput.addEventListener('keypress', function(e) { if (e.key === 'Enter') jumpToPage(); });
            
            // Sort functionality
            const sortSelect = document.getElementById('sortSelect');
            if (sortSelect) {
                sortSelect.addEventListener('change', function() {
                    const [sort, order] = this.value.split('-');
                    const url = new URL(window.location);
                    url.searchParams.set('sort', sort);
                    url.searchParams.set('order', order);
                    url.searchParams.delete('page');
                    window.location.href = url.toString();
                });
            }

            // Filter functionality
            const applyFilterBtn = document.getElementById('applyFilter');
            if(applyFilterBtn) {
                applyFilterBtn.addEventListener('click', function() {
                    const filterField = document.getElementById('filterField').value;
                    const filterValue = document.getElementById('filterValue').value;
                    const url = new URL(window.location);
                    if (filterField && filterValue !== '') {
                        url.searchParams.set('filter_field', filterField);
                        url.searchParams.set('filter_value', filterValue);
                    } else {
                        url.searchParams.delete('filter_field');
                        url.searchParams.delete('filter_value');
                    }
                    url.searchParams.delete('page');
                    window.location.href = url.toString();
                });
            }

            // Clear filter functionality
            const clearFilterBtn = document.getElementById('clearFilter');
            if (clearFilterBtn) {
                clearFilterBtn.addEventListener('click', function() {
                    const url = new URL(window.location);
                    url.searchParams.delete('filter_field');
                    url.searchParams.delete('filter_value');
                    url.searchParams.delete('page');
                    window.location.href = url.toString();
                });
            }
            
            const filterValueInput = document.getElementById('filterValue');
            if(filterValueInput) {
                 filterValueInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter' && document.getElementById('applyFilter')) {
                        document.getElementById('applyFilter').click();
                    }
                });
            }

            // --- Domain Accessibility Test Functionality (Common) ---
            let isTestRunning = false;
            let shouldStopTest = false;
            
            const testBtn = document.getElementById('testAllDomains');
            const stopBtn = document.getElementById('stopTest');
            const clearBtn = document.getElementById('clearResults');
            const progressDiv = document.getElementById('testProgress');
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');
            const successCount = document.getElementById('successCount');
            const failCount = document.getElementById('failCount');
            const timeoutCount = document.getElementById('timeoutCount');
            
            function getAllDomains() {
                const domains = [];
                document.querySelectorAll('.domain-test-status').forEach(el => {
                    const domain = el.getAttribute('data-domain');
                    if (domain) {
                        domains.push({ domain: domain, element: el });
                    }
                });
                return domains;
            }
            
            async function testDomain(domain, timeout = 5000) {
                const protocols = ['https://', 'http://'];
                for (const protocol of protocols) {
                    try {
                        const result = await new Promise((resolve) => {
                            const img = new Image();
                            const timer = setTimeout(() => {
                                img.src = '';
                                resolve({ success: false, protocol: protocol, method: 'timeout' });
                            }, timeout);
                            
                            img.onload = () => {
                                clearTimeout(timer);
                                resolve({ success: true, protocol: protocol, method: 'favicon' });
                            };
                            
                            img.onerror = () => {
                                clearTimeout(timer);
                                fetch(protocol + domain, { mode: 'no-cors', method: 'HEAD' })
                                    .then(() => resolve({ success: true, protocol: protocol, method: 'fetch' }))
                                    .catch(() => resolve({ success: false, protocol: protocol, method: 'error' }));
                            };
                            
                            img.src = protocol + domain + '/favicon.ico';
                        });
                        if (result.success) return result;
                    } catch (error) {
                        console.error(`Error testing ${domain}:`, error);
                    }
                }
                return { success: false, protocol: null, method: 'failed' };
            }
            
            function updateDomainStatus(element, status) {
                element.innerHTML = '';
                if (status.success) {
                    element.innerHTML = `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">✓ ${status.protocol.replace('://', '')}</span>`;
                } else if (status.method === 'timeout') {
                    element.innerHTML = `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">⏱ 超时</span>`;
                } else {
                    element.innerHTML = `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">✗ 失败</span>`;
                }
            }
            
            async function testAllDomains() {
                if (isTestRunning) return;
                isTestRunning = true;
                shouldStopTest = false;
                
                const domains = getAllDomains();
                const total = domains.length;
                let completed = 0, success = 0, fail = 0, timeout = 0;
                
                testBtn.classList.add('hidden');
                stopBtn.classList.remove('hidden');
                progressDiv.classList.remove('hidden');
                
                function updateProgress() {
                    const percent = (completed / total * 100).toFixed(1);
                    progressBar.style.width = percent + '%';
                    progressText.textContent = `${completed}/${total}`;
                    successCount.textContent = success;
                    failCount.textContent = fail;
                    timeoutCount.textContent = timeout;
                }
                
                domains.forEach(item => {
                    item.element.innerHTML = `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100"><svg class="animate-spin h-3 w-3 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>测试中</span>`;
                });
                
                const concurrency = 5;
                for (let i = 0; i < domains.length; i += concurrency) {
                    if (shouldStopTest) break;
                    const batch = domains.slice(i, i + concurrency);
                    const promises = batch.map(async (item) => {
                        if (shouldStopTest) return;
                        const result = await testDomain(item.domain);
                        
                        if (result.success) {
                            updateDomainStatus(item.element, result);
                            success++;
                        } else {
                            const row = item.element.closest('tr');
                            if (row) {
                                row.style.display = 'none';
                            }
                            if (result.method === 'timeout') {
                                timeout++;
                            } else {
                                fail++;
                            }
                        }

                        completed++;
                        updateProgress();
                    });
                    await Promise.all(promises);
                }
                
                isTestRunning = false;
                stopBtn.classList.add('hidden');
                clearBtn.classList.remove('hidden');
                testBtn.textContent = '重新测试';
                testBtn.classList.remove('hidden');
            }
            
            stopBtn.addEventListener('click', function() {
                shouldStopTest = true;
                stopBtn.classList.add('hidden');
                testBtn.classList.remove('hidden');
                clearBtn.classList.remove('hidden');
            });
            
            clearBtn.addEventListener('click', function() {
                document.querySelectorAll('tbody tr').forEach(row => {
                    row.style.display = '';
                });

                document.querySelectorAll('.domain-test-status').forEach(el => el.innerHTML = '');
                progressDiv.classList.add('hidden');
                clearBtn.classList.add('hidden');
                testBtn.textContent = '测试所有域名';
                
                progressBar.style.width = '0%';
                progressText.textContent = '0/0';
                successCount.textContent = '0';
                failCount.textContent = '0';
                timeoutCount.textContent = '0';
            });
            
            testBtn.addEventListener('click', testAllDomains);
        });
    </script>
    
    {{-- This stack allows child templates to push their own specific scripts --}}
    @stack('scripts')
</x-app-layout>
