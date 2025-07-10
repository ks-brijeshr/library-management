<?php

namespace modules\librarymanager\controllers;

use Craft;
use yii\web\Response;
use craft\elements\Entry;
use craft\web\Controller;

class ReturnController extends Controller
{
    protected array|int|bool $allowAnonymous = false;

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

        // Save the entry (update only)
        if (Craft::$app->elements->saveElement($entry)) {
            Craft::$app->getSession()->setNotice('Book marked as returned!');
        } else {
            Craft::$app->getSession()->setError('Could not update entry.');
        }

        return $this->redirectToPostedUrl();
    }
}
