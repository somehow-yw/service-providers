<?php

namespace App\Console\Commands;

use App\Models\WechatAccount;
use Illuminate\Console\Command;
use Zdp\ServiceProvider\Data\Utils\UserMenu;
use Zdp\ServiceProvider\Data\Utils\UserTag;

class UpdateMenu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'menu:refresh 
                            {source? : 指定服务商}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '刷新服务商菜单';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $source = $this->argument('source');

        if (!empty($source)) {
            $this->handleOne($source);

            return;
        }

        $accounts = WechatAccount::all();

        foreach ($accounts as $account) {
            if (!empty($account->appid) && !empty($account->secret) &&
                !empty($account->token) && !empty($account->aes_key)
            ) {
                try {
                    $this->handleOne($account->source);
                } catch (\Exception $e) {
                    $this->error($e->getMessage() . PHP_EOL .
                                 $e->getTraceAsString());
                }
            }
        }
    }

    protected function handleOne($source)
    {
        $menu = new UserMenu($source);

        $menu->delMenu();

        $config = WechatAccount::getWeChatConfig($source);
        $config = array_merge(config('wechat'), $config);

        $tag = new UserTag($config);

        $tag->initWxMenu($source);
    }
}
