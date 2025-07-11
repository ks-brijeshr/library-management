<?php

namespace modules\librarymanager\controllers;

use Craft;
use craft\elements\Entry;
use craft\web\Controller;

class CustomersController extends Controller
{
    protected array|int|bool $allowAnonymous = false;

    public function actionIndex()
    {
        $request = Craft::$app->getRequest();
        $page = max(1, (int) $request->getParam('page', 1));
        $pageSize = 4;

        $query = Entry::find()
            ->section('customers')
            ->status(null)
            ->orderBy('dateCreated desc');

        $totalCount = (clone $query)->count();
        $totalPages = ceil($totalCount / $pageSize);

        $customers = $query
            ->limit($pageSize)
            ->offset(($page - 1) * $pageSize)
            ->all();

        return $this->renderTemplate('librarymanager/customers', [
            'customers' => $customers,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'successMessage' => $request->getParam('success') ? '✅ Customer saved successfully.' : null,
            'errorMessage' => $request->getParam('error') ? '❌ Failed to save customer.' : null,
        ]);
    }
}
