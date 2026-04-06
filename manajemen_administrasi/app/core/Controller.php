<?php
require_once __DIR__ . '/../helpers/PreviewHelper.php';

class Controller
{
    public function model($model)
    {
        $path = __DIR__ . "/../models/$model.php";
        if (!file_exists($path)) {
            throw new Exception("Model $model tidak ditemukan.");
        }
        require_once $path;
        return new $model;
    }

    public function view($view, $data = [], $useLayout = true)
    {
        View::render($view, $data, $useLayout);
    }

    public function redirect($url)
    {
        header('Location: ' . BASE_URL . '/index.php?url=' . $url);
        exit;
    }

    public function authorizeSession()
    {
        if (!isset($_SESSION['user']) || !isset($_SESSION['user']['hak_akses'])) {
            header('Location: index.php?url=auth/login');
            exit;
        }
    }

    public function authorizeAdmin()
    {
        $akses = $_SESSION['user']['hak_akses'] ?? 'user';
        if (!in_array($akses, ['administrator', 'admin'])) {
            $this->redirect('error/forbidden');
        }
    }

    public function isAdmin()
    {
      return in_array($_SESSION['user']['hak_akses'] ?? 'user', ['administrator', 'admin']);
    }
}