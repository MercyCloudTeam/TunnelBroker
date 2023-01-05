<?php

namespace App\Console;

use App\Jobs\BGPCheck;
use App\Jobs\CalculationBandwidth;
use App\Jobs\CreateTunnel;
use App\Jobs\NodeStatusCheck;
use App\Models\Tunnel;
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
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->job(new CreateTunnel())->everyMinute();//每分钟自动开始创建Tunnel

        $schedule->job(new NodeStatusCheck())->everyFiveMinutes();//Every 5 minutes check node status
        $schedule->job(new CalculationBandwidth())->everyTenMinutes();//每十分钟获取一次流量（统计） & 获取不到的接口会归类为异常
        $schedule->job(new BGPCheck())->everyTenMinutes();//BGP Status Check

        //每6小时将创建异常的服务状态改为等待创建（重试等待机制）
        $schedule->call(function (){
            Tunnel::where('status',4)->update(['status'=>2]);
        })->everySixHours();
        //更新Cloudflare的代理IP
        $schedule->command('cloudflare:reload')->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
