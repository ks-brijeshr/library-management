<?php

namespace modules\librarymanager\controllers;

use Craft;
use craft\elements\Entry;
use craft\web\Controller;

class BooksController extends Controller
{
    protected array|int|bool $allowAnonymous = false;

    public function actionIndex()
    {
        $request = Craft::$app->request;
        $search = $request->getParam('search');
        $author = $request->getParam('author');
        $page = max(1, (int) $request->getParam('page', 1));
        $pageSize = 5;

        $query = Entry::find()
            ->section('books')
            ->status(null)
            ->orderBy('dateCreated desc');


        //Partial + full search on bookTitle and isbn
        if ($search) {
            // Search in all fields (including custom fields)
            $query->search($search);
        }

        if ($author) {
            // Use custom field filter for author instead of search()
            $query->bookAuthor($author); // only works if field handle is bookAuthor
        }

        // Clone query before pagination to get total count
        $totalBooks = clone $query;
        $totalCount = $totalBooks->count(); // this is an integer
        $totalPages = ceil($totalCount / $pageSize); // no error

        // Apply pagination
        $query->limit($pageSize)->offset(($page - 1) * $pageSize);
        $books = $query->all();

        // Dump search, author, and results to debug
        // Craft::info("📚 Search Triggered | Search: $search | Author: $author | Count: " . count($books), __METHOD__);
        // foreach ($books as $b) {
        //     Craft::info("→ {$b->bookTitle} | {$b->bookAuthor} | {$b->isbn}", __METHOD__);
        // }


        return $this->renderTemplate('librarymanager/books', [
            'books' => $books,
            'search' => $search,
            'author' => $author,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'successMessage' => $request->getParam('success') ? '✅ Book saved successfully.' : null,
            'errorMessage' => $request->getParam('error') ? '❌ Error saving book. Please try again.' : null,
        ]);
    }
}
