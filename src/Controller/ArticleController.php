<?php

namespace App\Controller;
use PDO;
use Twig\Environment;

class ArticleController
{
    private Environment $twig;
    private PDO $pdo;

    public function __construct(PDO $pdo, Environment $twig)
    {
        $this->pdo = $pdo;
        $this->twig = $twig;
    }

    public function list(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM articles');
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
              'template' => 'article_list.twig',
              'data' => ['articles' => $articles]
        ];
    }

    public function show($id): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM articles WHERE id = ?');
        $stmt->execute([$id]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$article) {
            return [
                  'template' => '404.twig',
                  'data' => []
            ];
        }

        $stmt = $this->pdo->prepare('SELECT * FROM comments WHERE article_id = ?');
        $stmt->execute([$id]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
              'template' => 'article.twig',
              'data' => ['article' => $article, 'comments' => $comments]
        ];
    }
    public function like($id): void
    {
        $stmt = $this->pdo->prepare('UPDATE articles SET likes = likes + 1 WHERE id = ?');
        $stmt->execute([$id]);

        header('Location: /articles/' . $id);
    }
    public function addComment($id): void
    {
        $comment = $_POST['comment'];
        $name = $_POST['name'];

        $stmt = $this->pdo->prepare('INSERT INTO comments (article_id, name, comment, created_at) VALUES (?, ?, ?, ?)');
        $stmt->execute([$id, $name, $comment, date('Y-m-d H:i:s')]);

        header('Location: /articles/' . $id);
    }
    public function likeComment($id): void
    {
        $stmt = $this->pdo->prepare('UPDATE comments SET likes = likes + 1 WHERE id = ?');
        $stmt->execute([$id]);


        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
    public function createForm(): array
    {
        return [
              'template' => 'create_article.twig',
              'data' => []
        ];
    }

    public function create(): void
    {
        $title = $_POST['title'];
        $content = $_POST['content'];
        $created_at = date('Y-m-d H:i:s');

        $stmt = $this->pdo->prepare('INSERT INTO articles (title, content, created_at) VALUES (?, ?, ?)');
        $stmt->execute([$title, $content, $created_at]);

        header('Location: /articles');
    }
    public function edit($id): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM articles WHERE id = ?');
        $stmt->execute([$id]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$article) {
            return [
                  'template' => '404.twig',
                  'data' => []
            ];
        }

        return [
              'template' => 'edit_article.twig',
              'data' => ['article' => $article]
        ];
    }

    public function update($id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'];
            $content = $_POST['content'];

            $stmt = $this->pdo->prepare('UPDATE articles SET title = ?, content = ? WHERE id = ?');
            $stmt->execute([$title, $content, $id]);

            header('Location: /articles/' . $id);
            exit;
        }
    }

    public function delete($id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $this->pdo->prepare('DELETE FROM articles WHERE id = ?');
            $stmt->execute([$id]);

            header('Location: /articles');
            exit;
        }
    }
}
