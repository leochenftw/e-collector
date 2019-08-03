<?php

namespace App\Web\Task;
use SilverStripe\Dev\BuildTask;
use Leochenftw\Debugger;
use Leochenftw\eCommerce\eCollector\Model\Customer;
use Leochenftw\eCommerce\eCollector\Model\Order;
/**
 * Description
 *
 * @package silverstripe
 * @subpackage mysite
 */
class CleanupCarts extends BuildTask
{
    /**
     * @var bool $enabled If set to FALSE, keep it from showing in the list
     * and from being executable through URL or CLI.
     */
    protected $enabled = true;

    /**
     * @var string $title Shown in the overview on the TaskRunner
     * HTML or CLI interface. Should be short and concise, no HTML allowed.
     */
    protected $title = 'Delete expired carts';

    /**
     * @var string $description Describe the implications the task has,
     * and the changes it makes. Accepts HTML formatting.
     */
    protected $description = 'Delete expired carts';

    /**
     * This method called via the TaskRunner
     *
     * @param SS_HTTPRequest $request
     */
    public function run($request)
    {
        $carts  =   Order::get()->filter(['Status' => 'Pending', 'Created:LessThanOrEqual' => strtotime('-30 days')]);

        foreach ($carts as $cart) {
            // print $cart->ClassName . PHP_EOL;
            if ($cart->ClassName != Order::class) {
                $subclass   =   $cart->ClassName;
                $cart       =   call_user_func($subclass .'::get')->byID($cart->ID);
            }
            $cart->delete();
        }
        print PHP_EOL;
    }
}
