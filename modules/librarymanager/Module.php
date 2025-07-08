<?php

namespace modules\librarymanager;

use Craft;
use craft\web\View;
use yii\base\Event;
use craft\elements\Entry;
use craft\web\UrlManager;
use craft\events\ModelEvent;
use craft\web\twig\variables\Cp;
use yii\base\Module as BaseModule;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterCpNavItemsEvent;

class Module extends BaseModule
{
    public function init()
    {
        // ✅ Define @modules path alias (very important)
        Craft::setAlias('@modules', dirname(__DIR__));

        parent::init();

        // ✅ Register CP Template Root
        Event::on(
            View::class,
            View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
            function ($event) {
                $event->roots['librarymanager'] = __DIR__ . '/templates';
            }
        );

        // ✅ Register CP URL rules
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

        // ✅ Add sidebar nav item
        Event::on(
            Cp::class,
            Cp::EVENT_REGISTER_CP_NAV_ITEMS,
            function (RegisterCpNavItemsEvent $event) {
                $event->navItems[] = [
                    'label' => 'Book Manager',
                    'url' => 'librarymanager',
                    'icon' => '@app/icons/book.svg', // Or use a valid path if you want a custom icon
                ];
            }
        );

        // ✅ Validate ISBN Uniqueness for Book Entries
        Event::on(
            Entry::class,
            Entry::EVENT_BEFORE_SAVE,
            function (ModelEvent $event) {
                /** @var Entry $entry */
                $entry = $event->sender;

                // Only validate ISBN for books section and only on canonical entry
                if ($entry->section->handle === 'books' && $entry->getIsCanonical()) {
                    $isbn = $entry->isbn;

                    if ($isbn) {
                        $query = Entry::find()
                            ->section('books')
                            ->isbn($isbn);

                        // Exclude current entry if it's being edited
                        if ($entry->id) {
                            $query->id(['not', $entry->id]);
                        }

                        $duplicate = $query->one();

                        if ($duplicate) {
                            $event->isValid = false;
                            Craft::$app->getSession()->setError("❌ ISBN must be unique. Entry with this ISBN already exists.");
                        }
                    }
                }
            }
        );


        Event::on(
            Entry::class,
            Entry::EVENT_BEFORE_SAVE,
            function (ModelEvent $event) {
                $entry = $event->sender;

                if ($entry->section->handle === 'customers' && $entry->getIsCanonical()) {
                    $email = $entry->getFieldValue('email');

                    if ($email) {
                        $query = Entry::find()
                            ->section('customers')
                            ->site('*') // check all sites
                            ->email($email) // this works if field handle is 'email'
                            ->status(null); // include drafts etc.

                        // Exclude current entry if editing
                        if ($entry->id) {
                            $query->id(['not', $entry->id]);
                        }

                        $existing = $query->one();

                        if ($existing) {
                            $event->isValid = false;
                            Craft::$app->getSession()->setError("❌ Email '{$email}' already exists.");
                        }
                    }
                }
            }
        );
    }
}
