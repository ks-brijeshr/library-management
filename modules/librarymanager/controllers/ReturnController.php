<?php

namespace modules\librarymanager\controllers;

use Craft;
use yii\web\Response;
use craft\elements\Entry;
use craft\web\Controller;

class ReturnController extends Controller
{
    protected array|int|bool $allowAnonymous = false;

    public function actionIndex(): Response
    {
        $request = Craft::$app->getRequest();
        $currentPage = (int)$request->getParam('page', 1);
        $historyPage = (int)$request->getParam('historyPage', 1);

        $perPage = 4;

        // Pending Returns
        $pendingQuery = Entry::find()
            ->section('borrowRecords')
            ->returnDate(':empty:')
            ->orderBy('dateCreated DESC');

        $totalPending = $pendingQuery->count();
        $pendingEntries = $pendingQuery
            ->limit($perPage)
            ->offset(($currentPage - 1) * $perPage)
            ->all();

        // Borrow History
        $historyQuery = Entry::find()
            ->section('borrowRecords')
            ->orderBy('dateCreated DESC');

        $totalHistory = $historyQuery->count();
        $historyEntries = $historyQuery
            ->limit($perPage)
            ->offset(($historyPage - 1) * $perPage)
            ->all();

        return $this->renderTemplate('librarymanager/return', [
            'borrowEntries' => $pendingEntries,
            'totalPages' => ceil($totalPending / $perPage),
            'currentPage' => $currentPage,

            'borrowHistory' => $historyEntries,
            'historyPages' => ceil($totalHistory / $perPage),
            'currentHistoryPage' => $historyPage,
        ]);
    }

    public function actionMarkReturned(): Response
    {
        $this->requirePostRequest();

        $elementId = Craft::$app->request->getBodyParam('elementId');
        $returnDate = Craft::$app->request->getBodyParam('returnDate');

        if (!$elementId || !$returnDate) {
            Craft::$app->getSession()->setError('Missing required data.');
            return $this->redirectToPostedUrl();
        }

        $entry = Entry::find()->id($elementId)->one();

        if (!$entry) {
            Craft::$app->getSession()->setError('Entry not found.');
            return $this->redirectToPostedUrl();
        }

        // Set the return date
        $entry->setFieldValue('returnDate', $returnDate);

        if (Craft::$app->elements->saveElement($entry)) {
            Craft::$app->getSession()->setNotice('Book marked as returned!');
        } else {
            Craft::$app->getSession()->setError('Could not update entry.');
        }

        // Redirect to return list first page
        return $this->redirect('librarymanager/return?success=1');
    }
}
