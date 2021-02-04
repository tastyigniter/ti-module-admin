<?php

namespace Admin\Actions;

use Admin\Facades\AdminLocation;
use Illuminate\Support\Facades\Event;
use System\Classes\BaseController;
use System\Classes\ControllerAction;

class LocationAwareController extends ControllerAction
{
    /**
     * Define controller location configuration array.
     *  $locationConfig = [
     *      'applyScopeOnListQuery'  => true',
     *      'applyScopeOnFormQuery'  => true',
     *      'addAbsenceConstraint'  => false',
     *  ];
     * @var array
     */
    public $locationConfig;

    public $requiredProperties = [];

    /**
     * @var array Required controller configuration array keys
     */
    protected $requiredConfig = [];

    /**
     * List_Controller constructor.
     *
     * @param BaseController $controller
     *
     * @throws \Exception
     */
    public function __construct($controller)
    {
        parent::__construct($controller);

        $this->locationConfig = $controller->locationConfig;

        // Build configuration
        $this->setConfig($controller->locationConfig, $this->requiredConfig);

        $this->hideAction([
            'locationApplyScope',
        ]);

        $this->controller->bindEvent('controller.afterConstructor', function () {
            $this->locationBindEvents();
        });
    }

    public function locationApplyScope($query)
    {
        if (!in_array(\Admin\Traits\Locationable::class, class_uses($query->getModel())))
            return;

        if (is_null($ids = AdminLocation::getIdOrAll()))
            return;

        (bool)$this->getConfig('addAbsenceConstraint', FALSE)
            ? $query->whereHasOrDoesntHaveLocation($ids)
            : $query->whereHasLocation($ids);
    }

    protected function locationBindEvents()
    {
        if ($this->controller->isClassExtendedWith('Admin\Actions\ListController')) {
            Event::listen('admin.list.extendQuery', function ($listWidget, $query) {
                if ((bool)$this->getConfig('applyScopeOnListQuery', TRUE))
                    $this->locationApplyScope($query);
            });

            Event::listen('admin.filter.extendQuery', function ($filterWidget, $query, $scope) {
                if (array_key_exists('locationAware', $scope->config)
                    AND (bool)$this->getConfig('applyScopeOnListQuery', TRUE)
                ) $this->locationApplyScope($query);
            });
        }

        if ($this->controller->isClassExtendedWith('Admin\Actions\FormController')) {
            $this->controller->bindEvent('controller.form.extendQuery', function ($query) {
                if ((bool)$this->getConfig('applyScopeOnFormQuery', TRUE))
                    $this->locationApplyScope($query);
            });
        }
    }
}
