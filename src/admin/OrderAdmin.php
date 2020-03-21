<?php

namespace Leochenftw\eCommerce\eCollector\Admin;
use SilverStripe\Admin\ModelAdmin;
use Leochenftw\eCommerce\eCollector\Model\Order;
use SilverStripe\Security\Member;
use Symbiote\GridFieldExtensions\GridFieldConfigurablePaginator;
use SilverStripe\Forms\GridField\GridFieldPaginator;

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

        if (Member::currentUser() && Member::currentUser()->isDefaultadmin()) {
            return $list->filter(['ClassName' => Order::class]);
        }

        return $list->filter(['ClassName' => Order::class])->exclude(['Status' => 'Pending']);
    }

    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm();
        $model = $this->sanitiseClassName($this->modelClass);
        $config = $form->Fields()->fieldByName($model)->getConfig();

        $count  =   $this->getList()->count();

        if ($count > 30) {
            $config->removeComponentsByType(GridFieldPaginator::class)
                    ->addComponent($paginator = new GridFieldConfigurablePaginator(30, [30, $count]));
        }

        return $form;
    }

}
