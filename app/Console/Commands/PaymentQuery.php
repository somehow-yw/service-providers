<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Workflows\PaymentQueryWorkflow;

/**
 * 订单支付查询
 * Class PaymentQuery
 * @package App\Console\Commands
 */
class PaymentQuery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:query {order_no?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '订单支付查询';

    protected $queryWorkflow;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PaymentQueryWorkflow $queryWorkflow)
    {
        parent::__construct();

        $this->queryWorkflow = $queryWorkflow;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $orderNo = empty($this->argument('order_no')) ? '' : $this->argument('order_no');
        $this->queryWorkflow->paymentQuery($orderNo);
    }
}
