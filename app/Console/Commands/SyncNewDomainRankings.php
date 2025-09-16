<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Domain;
use App\Models\RankingChange;
use App\Models\WebsiteIntroduction;
use App\Models\NewDomainRanking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncNewDomainRankings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:new-domain-rankings {--dry-run : 只显示将要执行的操作，不实际执行}';

    /**
     * The console description of the console command.
     *
     * @var string
     */
    protected $description = '同步新域名排名数据：从Domain、RankingChange、WebsiteIntroduction表中筛选符合条件的数据同步到NewDomainRanking表';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $today = Carbon::today();
        $sixtyDaysAgo = Carbon::today()->subDays(60);

        $this->info("开始同步新域名排名数据...");
        $this->info("筛选条件: record_date = {$today->toDateString()}, ranking ≤ 200000, registered_at 在 {$sixtyDaysAgo->toDateString()} 到 {$today->toDateString()} 之间");

        if ($isDryRun) {
            $this->warn("【模拟运行模式】- 不会实际修改数据库");
        }

        try {
            DB::beginTransaction();

            // 步骤1：获取符合条件的数据A
            $this->line("步骤1: 查询符合条件的数据...");
            $dataA = $this->getQualifiedDomains($today, $sixtyDaysAgo);
            $this->info("找到符合条件的域名数量: " . $dataA->count());

            if ($dataA->isEmpty()) {
                $this->warn("没有找到符合条件的数据，程序结束");
                DB::rollBack();
                return 0;
            }

            // 获取数据A中的所有域名列表
            $domainListA = $dataA->pluck('domain')->toArray();

            // 步骤2：处理插入和更新
            $this->line("\n步骤2: 处理数据插入和更新...");
            $insertCount = 0;
            $updateCount = 0;

            foreach ($dataA as $domainData) {
                $existingRecord = NewDomainRanking::where('domain', $domainData->domain)->first();

                if (!$existingRecord) {
                    // 插入新记录
                    if (!$isDryRun) {
                        NewDomainRanking::create([
                            'domain' => $domainData->domain,
                            'current_ranking' => $domainData->current_ranking,
                            'daily_change' => $domainData->daily_change,
                            'daily_trend' => $domainData->daily_trend,
                            'week_change' => $domainData->week_change,
                            'week_trend' => $domainData->week_trend,
                            'biweek_change' => $domainData->biweek_change,
                            'biweek_trend' => $domainData->biweek_trend,
                            'triweek_change' => $domainData->triweek_change,
                            'triweek_trend' => $domainData->triweek_trend,
                            'registered_at' => $domainData->registered_at,
                            'is_visible' => null,
                            'metadata' => [
                                'introduction' => $domainData->intro,
                            ],
                        ]);
                    }
                    $insertCount++;
                    $this->line("  插入: {$domainData->domain}");
                } else {
                    // 更新现有记录（只更新排名相关字段）
                    if (!$isDryRun) {
                        $existingRecord->update([
                            'current_ranking' => $domainData->current_ranking,
                            'daily_change' => $domainData->daily_change,
                            'daily_trend' => $domainData->daily_trend,
                            'week_change' => $domainData->week_change,
                            'week_trend' => $domainData->week_trend,
                            'biweek_change' => $domainData->biweek_change,
                            'biweek_trend' => $domainData->biweek_trend,
                            'triweek_change' => $domainData->triweek_change,
                            'triweek_trend' => $domainData->triweek_trend
                        ]);
                    }
                    $updateCount++;
                    $this->line("  更新: {$domainData->domain}");
                }
            }

            // 步骤3：删除不在数据A中的记录
            $this->line("\n步骤3: 删除不再符合条件的域名...");
            $toDeleteDomains = NewDomainRanking::whereNotIn('domain', $domainListA)->get();
            $deleteCount = $toDeleteDomains->count();

            if ($deleteCount > 0) {
                foreach ($toDeleteDomains as $domain) {
                    $this->line("  删除: {$domain->domain}");
                }
                
                if (!$isDryRun) {
                    NewDomainRanking::whereNotIn('domain', $domainListA)->delete();
                }
            } else {
                $this->info("没有需要删除的域名");
            }

            // 输出统计信息
            $this->line("\n=== 操作统计 ===");
            $this->info("插入新域名: {$insertCount} 个");
            $this->info("更新域名: {$updateCount} 个");
            $this->info("删除域名: {$deleteCount} 个");

            if (!$isDryRun) {
                DB::commit();
                $this->info("\n数据同步完成！");
                Log::info("NewDomainRanking同步完成", [
                    'inserted' => $insertCount,
                    'updated' => $updateCount,
                    'deleted' => $deleteCount
                ]);
            } else {
                DB::rollBack();
                $this->warn("\n【模拟运行完成】- 实际数据库未被修改");
            }

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("同步过程中发生错误: " . $e->getMessage());
            Log::error("NewDomainRanking同步失败", ['error' => $e->getMessage()]);
            return 1;
        }
    }

    /**
     * 获取符合条件的域名数据
     *
     * @param Carbon $today
     * @param Carbon $sixtyDaysAgo
     * @return \Illuminate\Support\Collection
     */
    private function getQualifiedDomains(Carbon $today, Carbon $sixtyDaysAgo)
    {
        return DB::table('domains as d')
            ->leftJoin('ranking_changes as rc', 'd.domain', '=', 'rc.domain')
            ->leftJoin('website_introductions as wi', 'd.domain', '=', 'wi.domain')
            ->select([
                'd.domain',
                'd.current_ranking',
                'rc.daily_change',
                'rc.daily_trend',
                'rc.week_change',
                'rc.week_trend',
                'rc.biweek_change',
                'rc.biweek_trend',
                'rc.triweek_change',
                'rc.triweek_trend',
                'wi.registered_at',
                'wi.intro'
            ])
            ->where('d.record_date', $today->toDateString())
            ->where('d.current_ranking', '<=', 600000)
            ->where('wi.registered_at', '>=', $sixtyDaysAgo->toDateString())
            ->whereNotNull('wi.registered_at')
            ->get();
    }
}