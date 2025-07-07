<?php

namespace modules\librarymanager\controllers;

use craft\web\Controller;

class DefaultController extends Controller
{
    protected array|int|bool $allowAnonymous = false;

    public function actionIndex(): \yii\web\Response|string
    {
        return $this->renderTemplate('librarymanager/index', [
            'title' => '📘 Book Manager',
        ]);
    }
    public function actionBooks(): \yii\web\Response
    {
        return $this->renderTemplate('librarymanager/books');
    }

    public function actionCustomers(): \yii\web\Response
    {
        return $this->renderTemplate('librarymanager/customers');
    }

    public function actionBorrow(): \yii\web\Response
    {
        return $this->renderTemplate('librarymanager/borrow');
    }

    public function actionReturn(): \yii\web\Response
    {
        return $this->renderTemplate('librarymanager/return');
    }
}
