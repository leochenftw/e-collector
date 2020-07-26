<?php

namespace Leochenftw\eCommerce\eCollector\Admin;
use SilverStripe\Admin\ModelAdmin;
use Leochenftw\eCommerce\eCollector\Model\Customer;

/**
 * Description
 *
 * @package silverstripe
 * @subpackage mysite
 */
class CustomerAdmin extends ModelAdmin
{
    /**
     * Managed data objects for CMS
     * @var array
     */
    private static $managed_models = [
        Customer::class
    ];

    /**
     * URL Path for CMS
     * @var string
     */
    private static $url_segment = 'customers';

    /**
     * Menu title for Left and Main CMS
     * @var string
     */
    private static $menu_title = 'Customers';

    /**
     * Menu icon for Left and Main CMS
     * @var string
     */
    private static $menu_icon = 'leochenftw/e-collector: client/img/customer.png';

}
