<?php

namespace App\Console;

use App\Console\Commands\MigrateOldMarkUp;
use App\Console\Commands\PaymentQuery;
use App\Console\Commands\UpdateMenu;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // 更新行政区域
        \Zdp\Map\Commands\UpdateArea::class,
        // 支付订单查询
        PaymentQuery::class,
        // 刷新菜单
        UpdateMenu::class,
        // 服务商第二版加价规则升级 - 原有数据迁移
        MigrateOldMarkUp::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        $schedule->command('payment:query')
                 ->everyTenMinutes();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
