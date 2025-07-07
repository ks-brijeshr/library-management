<?php

namespace modules\librarymanager;

use Craft;
use craft\web\View;
use yii\base\Event;
use craft\web\UrlManager;
use craft\web\Application;
use craft\web\twig\variables\Cp;
use yii\base\Module as BaseModule;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterCpNavItemsEvent;

class Module extends BaseModule
{
    public function init()
    {
        parent::init();

        // Register CP Template Root
        Event::on(
            View::class,
            View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
            function ($event) {
                $event->roots['librarymanager'] = __DIR__ . '/templates';
            }
        );

        // Register CP URL rules
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['librarymanager'] = 'librarymanager/default/index';
                $event->rules['librarymanager/books'] = 'librarymanager/default/books';
                $event->rules['librarymanager/customers'] = 'librarymanager/default/customers';
                $event->rules['librarymanager/borrow'] = 'librarymanager/default/borrow';
                $event->rules['librarymanager/return'] = 'librarymanager/default/return';
            }
        );

        // Add item to sidebar
        Event::on(
            Cp::class,
            Cp::EVENT_REGISTER_CP_NAV_ITEMS,
            function (RegisterCpNavItemsEvent $event) {
                $event->navItems[] = [
                    'label' => 'Book Manager',
                    'url' => 'librarymanager',
                    'icon' => '@app/icons/book.svg', // Or null if you don’t have an icon
                ];
            }
        );
    }
}
