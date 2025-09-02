<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\SimilarwebDomain;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DomainController extends Controller
{
    /**
     * 获取指定域名的排名信息和 Similarweb 数据并显示页面
     *
     * @param string $domain
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function getDomainDetail(string $domain)
    {
        try {
            // 根据域名查找排名记录
            $domainRecord = Domain::with('websiteIntroduction')->where('domain', $domain)->first();
            
            // 根据域名查找 SimilarwebDomain 记录
            $similarwebRecord = SimilarwebDomain::where('domain', $domain)->first();
            
            // 如果两个记录都未找到
            if (!$domainRecord && !$similarwebRecord) {
                return redirect()->back()->with('error', '域名 ' . $domain . ' 未找到任何记录');
            }
            
            // 获取 websiteIntroduction（从任一个存在的记录中获取）
            $websiteIntroduction = optional($domainRecord)->websiteIntroduction;

            // 返回视图并传递数据
            return view('domain.ranking', compact('domainRecord', 'similarwebRecord', 'websiteIntroduction'));
            
        } catch (\Exception $e) {
            // 处理异常情况
            return redirect()->back()->with('error', '获取域名信息失败：' . $e->getMessage());
        }
    }

    /**
     * 批量获取域名的 Similarweb 数据
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function getDomainsDetail(Request $request)
    {
        try {
            // 验证请求数据
            $request->validate([
                'domains' => 'required|string|max:10000'
            ]);

            // 获取域名列表，按行分割并清理
            $domainLines = explode("\n", $request->input('domains'));
            $domains = array_filter(array_map('trim', $domainLines));
            
            if (empty($domains)) {
                return redirect()->back()->with('error', '请输入有效的域名列表');
            }

            // 限制查询数量
            if (count($domains) > 100) {
                return redirect()->back()->with('error', '一次最多只能查询 100 个域名');
            }

            // 批量查询 SimilarwebDomain 数据，并加载 websiteIntroduction 关联
            $similarwebRecords = SimilarwebDomain::with('websiteIntroduction')
                ->whereIn('domain', $domains)
                ->select([
                    'domain',
                    'current_month', 
                    'current_emv',
                    'ts_social',
                    'ts_paid_referrals', 
                    'ts_mail',
                    'ts_referrals',
                    'ts_search',
                    'ts_direct',
                    'global_rank',
                    'last_updated'
                ])
                ->orderByRaw('FIELD(domain, "' . implode('","', $domains) . '")')
                ->get()
                ->keyBy('domain');

            // 准备结果数据，保持输入顺序
            $results = [];
            $foundDomains = [];
            $notFoundDomains = [];

            foreach ($domains as $domain) {
                if ($similarwebRecords->has($domain)) {
                    $record = $similarwebRecords[$domain];
                    $results[] = [
                        'domain' => $record->domain,
                        'current_month' => $record->current_month,
                        'current_emv' => $record->current_emv,
                        'global_rank' => $record->global_rank,
                        'traffic_sources' => [
                            'direct' => $record->ts_direct,
                            'search' => $record->ts_search,
                            'referrals' => $record->ts_referrals,
                            'social' => $record->ts_social,
                            'paid' => $record->ts_paid_referrals,
                            'mail' => $record->ts_mail
                        ],
                        'last_updated' => $record->last_updated,
                        // 添加 websiteIntroduction 数据，处理可能为 null 的情况
                        'registered_at' => optional($record->websiteIntroduction)->registered_at
                    ];
                    $foundDomains[] = $domain;
                } else {
                    $notFoundDomains[] = $domain;
                }
            }

            return view('domains.detail', compact(
                'results',
                'foundDomains', 
                'notFoundDomains',
                'domains'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', '查询失败：' . $e->getMessage());
        }
    }

    /**
     * 浏览域名数据 - 分页展示上个月数据
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function browseDomains(Request $request)
    {
        try {

            $currentMonth = SimilarwebDomain::find(1)->current_month;

            // 获取上个月的月份字符串 (如: 2025-07)
            $lastMonth = $currentMonth;
            // $lastMonth = now()->subMonth()->format('Y-m');
            
            // 获取排序参数
            $sortBy = $request->get('sort', 'current_emv'); // 默认按访问量排序
            $sortOrder = $request->get('order', 'desc'); // 默认降序
            
            // 获取过滤参数
            $filterField = $request->get('filter_field');
            $filterValue = $request->get('filter_value');
            
            // 验证排序字段
            $allowedSorts = [
                'current_emv',
                'ts_direct',
                'ts_search',
                'ts_referrals', 
                'ts_social',
                'ts_paid_referrals',
                'ts_mail'
            ];
            
            if (!in_array($sortBy, $allowedSorts)) {
                $sortBy = 'current_emv';
            }
            
            // 验证排序顺序
            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'desc';
            }

            // 构建查询
            $query = SimilarwebDomain::where('current_month', $lastMonth)
                ->select([
                    'domain',
                    'current_emv',
                    'ts_direct',
                    'ts_search', 
                    'ts_referrals',
                    'ts_social',
                    'ts_paid_referrals',
                    'ts_mail'
                ]);

            // 应用过滤条件
            if ($filterField && $filterValue !== null && $filterValue !== '') {
                if (in_array($filterField, $allowedSorts)) {
                    if (in_array($filterField, ['ts_direct', 'ts_search', 'ts_referrals', 'ts_social', 'ts_paid_referrals', 'ts_mail'])) {
                        // 流量来源字段：输入的是百分比，需要转换为小数
                        $filterValue = floatval($filterValue) / 100;
                    } else {
                        // 访问量字段：直接使用数值
                        $filterValue = floatval($filterValue);
                    }
                    $query->where($filterField, '>=', $filterValue);
                }
            }

            // 应用排序
            $query->orderBy($sortBy, $sortOrder);

            // 分页查询 - 每页100条
            $domains = $query->paginate(100);
            
            // 获取统计信息
            $totalCount = SimilarwebDomain::where('current_month', $lastMonth)->count();
            
            // 获取过滤后的统计信息
            $filteredCount = $query->count();

            return view('domains.browse', compact(
                'domains',
                'lastMonth', 
                'sortBy',
                'sortOrder',
                'totalCount',
                'filteredCount',
                'filterField',
                'filterValue'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', '数据加载失败：' . $e->getMessage());
        }
    }

    /**
     * 获取分类翻译映射
     *
     * @return array
     */
    private function getCategoryTranslations()
    {
        return [
            'adult' => '成人内容',
            'arts_and_entertainment' => '艺术与娱乐',
            'arts_and_entertainment-animation_and_comics' => '艺术与娱乐/动画与漫画',
            'arts_and_entertainment-arts_and_entertainment' => '艺术与娱乐/艺术与娱乐',
            'arts_and_entertainment-books_and_literature' => '艺术与娱乐/书籍与文学',
            'arts_and_entertainment-humor' => '艺术与娱乐/幽默',
            'arts_and_entertainment-music' => '艺术与娱乐/音乐',
            'arts_and_entertainment-performing_arts' => '艺术与娱乐/表演艺术',
            'arts_and_entertainment-tv_movies_and_streaming' => '艺术与娱乐/电视、电影与流媒体',
            'arts_and_entertainment-visual_arts_and_design' => '艺术与娱乐/视觉艺术与设计',
            'business_and_consumer_services' => '商业与消费者服务',
            'business_and_consumer_services-business_and_consumer_services' => '商业与消费者服务/商业与消费者服务',
            'business_and_consumer_services-business_services' => '商业与消费者服务/商业服务',
            'business_and_consumer_services-marketing_and_advertising' => '商业与消费者服务/市场营销与广告',
            'business_and_consumer_services-online_marketing' => '商业与消费者服务/在线营销',
            'business_and_consumer_services-publishing_and_printing' => '商业与消费者服务/出版与印刷',
            'business_and_consumer_services-real_estate' => '商业与消费者服务/房地产',
            'business_and_consumer_services-relocation_and_household_moving' => '商业与消费者服务/搬迁与家庭搬家',
            'business_and_consumer_services-shipping_and_logistics' => '商业与消费者服务/运输与物流',
            'business_and_consumer_services-textiles' => '商业与消费者服务/纺织品',
            'community_and_society' => '社区与社会',
            'community_and_society-community_and_society' => '社区与社会/社区与社会',
            'community_and_society-decease' => '社区与社会/丧葬',
            'community_and_society-faith_and_beliefs' => '社区与社会/信仰与信念',
            'community_and_society-holidays_and_seasonal_events' => '社区与社会/节假日与季节性活动',
            'community_and_society-lgbtq' => '社区与社会/LGBTQ+',
            'community_and_society-philanthropy' => '社区与社会/慈善事业',
            'community_and_society-romance_and_relationships' => '社区与社会/爱情与人际关系',
            'computers_electronics_and_technology' => '计算机、电子与技术',
            'computers_electronics_and_technology-advertising_networks' => '计算机、电子与技术/广告网络',
            'computers_electronics_and_technology-computers_electronics_and_technology' => '计算机、电子与技术/计算机、电子与技术',
            'computers_electronics_and_technology-computer_hardware' => '计算机、电子与技术/计算机硬件',
            'computers_electronics_and_technology-computer_security' => '计算机、电子与技术/计算机安全',
            'computers_electronics_and_technology-consumer_electronics' => '计算机、电子与技术/消费电子产品',
            'computers_electronics_and_technology-email' => '计算机、电子与技术/电子邮件',
            'computers_electronics_and_technology-file_sharing_and_hosting' => '计算机、电子与技术/文件共享与托管',
            'computers_electronics_and_technology-graphics_multimedia_and_web_design' => '计算机、电子与技术/图形、多媒体与网页设计',
            'computers_electronics_and_technology-programming_and_developer_software' => '计算机、电子与技术/编程与开发者软件',
            'computers_electronics_and_technology-search_engines' => '计算机、电子与技术/搜索引擎',
            'computers_electronics_and_technology-social_networks_and_online_communities' => '计算机、电子与技术/社交网络与在线社区',
            'computers_electronics_and_technology-telecommunications' => '计算机、电子与技术/电信',
            'computers_electronics_and_technology-web_hosting_and_domain_names' => '计算机、电子与技术/网站托管与域名',
            'e-commerce_and_shopping' => '电子商务与购物',
            'e-commerce_and_shopping-auctions' => '电子商务与购物/拍卖',
            'e-commerce_and_shopping-classifieds' => '电子商务与购物/分类广告',
            'e-commerce_and_shopping-coupons_and_rebates' => '电子商务与购物/优惠券与返利',
            'e-commerce_and_shopping-e-commerce_and_shopping' => '电子商务与购物/电子商务与购物',
            'e-commerce_and_shopping-marketplace' => '电子商务与购物/线上市场',
            'e-commerce_and_shopping-price_comparison' => '电子商务与购物/价格比较',
            'e-commerce_and_shopping-tickets' => '电子商务与购物/票务',
            'finance' => '金融',
            'finance-accounting_and_auditing' => '金融/会计与审计',
            'finance-banking_credit_and_lending' => '金融/银行、信贷与借贷',
            'finance-finance' => '金融/金融',
            'finance-financial_planning_and_management' => '金融/财务规划与管理',
            'finance-insurance' => '金融/保险',
            'finance-investing' => '金融/投资',
            'food_and_drink' => '食品与饮料',
            'food_and_drink-beverages' => '食品与饮料/饮料',
            'food_and_drink-cooking_and_recipes' => '食品与饮料/烹饪与食谱',
            'food_and_drink-food_and_drink' => '食品与饮料/食品与饮料',
            'food_and_drink-groceries' => '食品与饮料/食品杂货',
            'food_and_drink-restaurants_and_delivery' => '食品与饮料/餐厅与外卖',
            'food_and_drink-vegetarian_and_vegan' => '食品与饮料/素食与纯素',
            'gambling' => '赌博',
            'gambling-casinos' => '赌博/赌场',
            'gambling-gambling' => '赌博/赌博',
            'gambling-lottery' => '赌博/彩票',
            'gambling-poker' => '赌博/扑克',
            'gambling-sports_betting' => '赌博/体育博彩',
            'games' => '游戏',
            'games-board_and_card_games' => '游戏/桌游与卡牌游戏',
            'games-games' => '游戏/游戏',
            'games-puzzles_and_brainteasers' => '游戏/益智与解谜',
            'games-roleplaying_games' => '游戏/角色扮演游戏',
            'games-video_games_consoles_and_accessories' => '游戏/视频游戏、主机与配件',
            'health' => '健康',
            'health-addictions' => '健康/成瘾',
            'health-alternative_and_natural_medicine' => '健康/替代与自然医学',
            'health-biotechnology_and_pharmaceuticals' => '健康/生物技术与制药',
            'health-childrens_health' => '健康/儿童健康',
            'health-dentist_and_dental_services' => '健康/牙医与牙科服务',
            'health-developmental_and_physical_disabilities' => '健康/发育与身体残疾',
            'health-geriatric_and_aging_care' => '健康/老年与养老护理',
            'health-health' => '健康/健康',
            'health-health_conditions_and_concerns' => '健康/健康状况与问题',
            'health-medicine' => '健康/医学',
            'health-mens_health' => '健康/男性健康',
            'health-mental_health' => '健康/心理健康',
            'health-nutrition_diets_and_fitness' => '健康/营养、饮食与健身',
            'health-pharmacy' => '健康/药店与药剂学',
            'health-public_health_and_safety' => '健康/公共卫生与安全',
            'health-womens_health' => '健康/女性健康',
            'heavy_industry_and_engineering' => '重工业与工程',
            'heavy_industry_and_engineering-aerospace_and_defense' => '重工业与工程/航空航天与国防',
            'heavy_industry_and_engineering-agriculture' => '重工业与工程/农业',
            'heavy_industry_and_engineering-architecture' => '重工业与工程/建筑学',
            'heavy_industry_and_engineering-chemical_industry' => '重工业与工程/化学工业',
            'heavy_industry_and_engineering-construction_and_maintenance' => '重工业与工程/建筑与维护',
            'heavy_industry_and_engineering-energy_industry' => '重工业与工程/能源产业',
            'heavy_industry_and_engineering-heavy_industry_and_engineering' => '重工业与工程/重工业与工程',
            'heavy_industry_and_engineering-metals_and_mining' => '重工业与工程/金属与采矿',
            'heavy_industry_and_engineering-waste_water_and_environmental' => '重工业与工程/废水与环境',
            'hobbies_and_leisure' => '爱好与休闲',
            'hobbies_and_leisure-ancestry_and_genealogy' => '爱好与休闲/血统与家谱',
            'hobbies_and_leisure-antiques_and_collectibles' => '爱好与休闲/古董与收藏品',
            'hobbies_and_leisure-camping_scouting_and_outdoors' => '爱好与休闲/露营、童子军与户外',
            'hobbies_and_leisure-crafts' => '爱好与休闲/手工艺',
            'hobbies_and_leisure-hobbies_and_leisure' => '爱好与休闲/爱好与休闲',
            'hobbies_and_leisure-models' => '爱好与休闲/模型',
            'hobbies_and_leisure-photography' => '爱好与休闲/摄影',
            'home_and_garden' => '家居与园艺',
            'home_and_garden-furniture' => '家居与园艺/家具',
            'home_and_garden-gardening' => '家居与园艺/园艺',
            'home_and_garden-home_and_garden' => '家居与园艺/家居与园艺',
            'home_and_garden-home_improvement_and_maintenance' => '家居与园艺/家居改善与维护',
            'home_and_garden-interior_design' => '家居与园艺/室内设计',
            'jobs_and_career' => '工作与职业',
            'jobs_and_career-human_resources' => '工作与职业/人力资源',
            'jobs_and_career-jobs_and_career' => '工作与职业/工作与职业',
            'jobs_and_career-jobs_and_employment' => '工作与职业/工作与就业',
            'law_and_government' => '法律与政府',
            'law_and_government-government' => '法律与政府/政府',
            'law_and_government-immigration_and_visas' => '法律与政府/移民与签证',
            'law_and_government-law_and_government' => '法律与政府/法律与政府',
            'law_and_government-law_enforcement_and_protective_services' => '法律与政府/执法与保护服务',
            'law_and_government-legal' => '法律与政府/法律',
            'law_and_government-national_security' => '法律与政府/国家安全',
            'lifestyle' => '生活方式',
            'lifestyle-beauty_and_cosmetics' => '生活方式/美容与化妆品',
            'lifestyle-childcare' => '生活方式/育儿与托儿',
            'lifestyle-fashion_and_apparel' => '生活方式/时尚与服装',
            'lifestyle-gifts_and_flowers' => '生活方式/礼品与鲜花',
            'lifestyle-jewelry_and_luxury_products' => '生活方式/珠宝与奢侈品',
            'lifestyle-lifestyle' => '生活方式/生活方式',
            'lifestyle-tobacco' => '生活方式/烟草',
            'lifestyle-weddings' => '生活方式/婚礼',
            'news_and_media' => '新闻与媒体',
            'pets_and_animals' => '宠物与动物',
            'pets_and_animals-animals' => '宠物与动物/动物',
            'pets_and_animals-birds' => '宠物与动物/鸟类',
            'pets_and_animals-fish_and_aquaria' => '宠物与动物/鱼类与水族',
            'pets_and_animals-horses' => '宠物与动物/马',
            'pets_and_animals-pets' => '宠物与动物/宠物',
            'pets_and_animals-pets_and_animals' => '宠物与动物/宠物与动物',
            'pets_and_animals-pet_food_and_supplies' => '宠物与动物/宠物食品与用品',
            'reference_materials-dictionaries_and_encyclopedias' => '参考资料/词典与百科全书',
            'reference_materials-maps' => '参考资料/地图',
            'reference_materials-public_records_and_directories' => '参考资料/公共记录与名录',
            'reference_materials-reference_materials' => '参考资料/参考资料',
            'science_and_education' => '科学与教育',
            'science_and_education-astronomy' => '科学与教育/天文学',
            'science_and_education-biology' => '科学与教育/生物学',
            'science_and_education-business_training' => '科学与教育/商业培训',
            'science_and_education-chemistry' => '科学与教育/化学',
            'science_and_education-earth_sciences' => '科学与教育/地球科学',
            'science_and_education-education' => '科学与教育/教育',
            'science_and_education-environmental_science' => '科学与教育/环境科学',
            'science_and_education-grants_scholarships_and_financial_aid' => '科学与教育/助学金、奖学金与经济援助',
            'science_and_education-history' => '科学与教育/历史',
            'science_and_education-libraries_and_museums' => '科学与教育/图书馆与博物馆',
            'science_and_education-literature' => '科学与教育/文学',
            'science_and_education-math' => '科学与教育/数学',
            'science_and_education-philosophy' => '科学与教育/哲学',
            'science_and_education-physics' => '科学与教育/物理学',
            'science_and_education-public_records_and_directories' => '科学与教育/公共记录与名录',
            'science_and_education-science_and_education' => '科学与教育/科学与教育',
            'science_and_education-social_sciences' => '科学与教育/社会科学',
            'science_and_education-universities_and_colleges' => '科学与教育/大学与学院',
            'science_and_education-weather' => '科学与教育/天气',
            'sports' => '体育运动',
            'sports-american_football' => '体育运动/美式足球',
            'sports-baseball' => '体育运动/棒球',
            'sports-basketball' => '体育运动/篮球',
            'sports-boxing' => '体育运动/拳击',
            'sports-climbing' => '体育运动/攀岩',
            'sports-cycling_and_biking' => '体育运动/自行车运动',
            'sports-fantasy_sports' => '体育运动/梦幻体育',
            'sports-fishing' => '体育运动/钓鱼',
            'sports-golf' => '体育运动/高尔夫',
            'sports-hunting_and_shooting' => '体育运动/狩猎与射击',
            'sports-martial_arts' => '体育运动/武术',
            'sports-rugby' => '体育运动/橄榄球',
            'sports-running' => '体育运动/跑步',
            'sports-soccer' => '体育运动/足球',
            'sports-sports' => '体育运动/体育运动',
            'sports-tennis' => '体育运动/网球',
            'sports-volleyball' => '体育运动/排球',
            'sports-water_sports' => '体育运动/水上运动',
            'sports-winter_sports' => '体育运动/冬季运动',
            'travel_and_tourism' => '旅行与旅游',
            'travel_and_tourism-accommodation_and_hotels' => '旅行与旅游/住宿与酒店',
            'travel_and_tourism-air_travel' => '旅行与旅游/航空旅行',
            'travel_and_tourism-car_rentals' => '旅行与旅游/汽车租赁',
            'travel_and_tourism-ground_transportation' => '旅行与旅游/地面交通',
            'travel_and_tourism-tourist_attractions' => '旅行与旅游/旅游景点',
            'travel_and_tourism-transportation_and_excursions' => '旅行与旅游/交通与短途旅行',
            'travel_and_tourism-travel_and_tourism' => '旅行与旅游/旅行与旅游',
            'vehicles' => '交通工具',
            'vehicles-automotive_industry' => '交通工具/汽车工业',
            'vehicles-aviation' => '交通工具/航空',
            'vehicles-boats' => '交通工具/船只',
            'vehicles-makes_and_models' => '交通工具/品牌与型号',
            'vehicles-motorcycles' => '交通工具/摩托车',
            'vehicles-motorsports' => '交通工具/赛车运动',
            'vehicles-vehicles' => '交通工具/交通工具'
        ];
    }

    /**
     * 将分类名中的 "/" 转换为 "-"，用于URL
     *
     * @param string $category
     * @return string
     */
    private function categoryToUrl($category)
    {
        return str_replace('/', '--', $category);
    }

    /**
     * 将URL中的 "-" 转换回 "/"，还原分类名
     *
     * @param string $urlCategory
     * @return string
     */
    private function urlToCategory($urlCategory)
    {
        return str_replace('-', '/', $urlCategory);
    }

    /**
     * 显示所有分类及其数量
     *
     * @return \Illuminate\View\View
     */
    public function showCategories()
    {
        try {
            // 获取所有分类及其数量
            $categoriesWithCount = SimilarwebDomain::selectRaw('category, COUNT(*) as count')
                ->whereNotNull('category')
                ->where('category', '!=', '')
                ->groupBy('category')
                ->orderBy('count', 'desc')
                ->get();

            // 获取分类翻译映射
            $categoryTranslations = $this->getCategoryTranslations();

            // 为每个分类添加中文翻译和URL友好的分类名
            $categoriesWithTranslation = $categoriesWithCount->map(function ($item) use ($categoryTranslations) {
                $urlCategory = $this->categoryToUrl($item->category);
                $item->chinese_name = $categoryTranslations[$urlCategory] ?? $item->category;
                $item->url_category = $urlCategory;
                return $item;
            });

            // 获取总域名数量
            $totalDomains = SimilarwebDomain::count();

            return view('domains.categories', compact(
                'categoriesWithTranslation',
                'totalDomains'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', '获取分类数据失败：' . $e->getMessage());
        }
    }

    /**
     * 显示指定分类下的所有域名
     *
     * @param Request $request
     * @param string $category
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showCategoryDomains(Request $request, string $category)
    {
        try {
            // URL解码并将 "--" 转换回 "/"
            $decodedCategory = urldecode($category);
            $originalCategory = $this->urlToCategory($decodedCategory);
            
            // 检查分类是否存在
            $categoryExists = SimilarwebDomain::where('category', $originalCategory)->exists();
            if (!$categoryExists) {
                return redirect()->route('domains.categories')
                    ->with('error', '分类 "' . $originalCategory . '" 不存在');
            }

            // 获取排序参数
            $sortBy = $request->get('sort', 'current_emv'); // 默认按EMV排序
            $sortOrder = $request->get('order', 'desc'); // 默认降序

            // 验证排序字段
            $allowedSorts = ['domain', 'current_emv', 'registered_at'];
            if (!in_array($sortBy, $allowedSorts)) {
                $sortBy = 'current_emv';
            }

            // 验证排序顺序
            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'desc';
            }

            // 查询该分类下的所有域名，加载 websiteIntroduction 关联
            $domainsQuery = SimilarwebDomain::with('websiteIntroduction')
                ->where('category', $originalCategory)
                ->select([
                    'domain',
                    'current_emv',
                    'global_rank'
                ]);

            // 根据排序字段应用不同的排序逻辑
            if ($sortBy === 'registered_at') {
                // 对于注册时间排序，需要 join websiteIntroduction 表
                $domainsQuery = $domainsQuery->leftJoin('website_introductions', 'similarweb_domains.domain', '=', 'website_introductions.domain')
                    ->select([
                        'similarweb_domains.domain',
                        'similarweb_domains.current_emv',
                        'similarweb_domains.global_rank',
                        'website_introductions.registered_at'
                    ])
                    ->orderBy('website_introductions.registered_at', $sortOrder);
            } else {
                // 对于其他字段的排序
                $domainsQuery = $domainsQuery->orderBy($sortBy, $sortOrder);
            }

            // 分页查询 - 每页50条
            $domains = $domainsQuery->paginate(50)->appends($request->query());

            // 获取该分类的统计信息
            $categoryStats = SimilarwebDomain::where('category', $originalCategory)
                ->selectRaw('COUNT(*) as total_count, AVG(current_emv) as avg_emv, MAX(current_emv) as max_emv, MIN(current_emv) as min_emv')
                ->first();

            // 获取分类的中文名称
            $categoryTranslations = $this->getCategoryTranslations();
            $chineseName = $categoryTranslations[$decodedCategory] ?? $originalCategory;

            return view('domains.category-domains', compact(
                'domains',
                'originalCategory',
                'decodedCategory',
                'chineseName',
                'categoryStats',
                'sortBy',
                'sortOrder'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', '获取分类域名失败：' . $e->getMessage());
        }
    }
}