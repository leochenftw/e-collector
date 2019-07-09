<?php

namespace Leochenftw\eCommerce\eCollector\Admin;
use SilverStripe\Admin\ModelAdmin;
use Leochenftw\eCommerce\eCollector\Model\Order;

/**
 * Description
 *
 * @package silverstripe
 * @subpackage mysite
 */
class OrderAdmin extends ModelAdmin
{
    /**
     * Managed data objects for CMS
     * @var array
     */
    private static $managed_models = [
        Order::class
    ];

    /**
     * URL Path for CMS
     * @var string
     */
    private static $url_segment = 'orders';

    /**
     * Menu title for Left and Main CMS
     * @var string
     */
    private static $menu_title = 'Orders';

    private static $menu_icon = 'leochenftw/e-collector: client/img/shopping-cart.png';

    public function getList()
    {
        $list   =   parent::getList();

        return $list->filter(['ClassName' => Order::class])->exclude(['Status' => 'Pending']);
    }

}
