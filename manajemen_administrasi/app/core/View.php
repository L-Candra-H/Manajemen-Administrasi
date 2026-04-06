<?php

class View
{
    public static function render($view, $data = [], $useLayout = true)
    {
        extract($data);

        $viewPath = __DIR__ . "/../views/$view.php";

        if (!file_exists($viewPath)) {
            die("View '$viewPath' tidak ditemukan.");
        }

        if ($useLayout) {
            // Tentukan layout yang dipakai
            $layoutName = $layout ?? 'main';
            $layoutPath = __DIR__ . "/../views/layouts/{$layoutName}.php";

            // Tangkap isi view sebagai string
            ob_start();
            require $viewPath;
            $renderedView = ob_get_clean();

            $content = $renderedView;

            if (file_exists($layoutPath)) {
                require $layoutPath;
            } else {
                die("Layout '$layoutPath' tidak ditemukan.");
            }
        } else {
            // ⬅️ langsung tampilkan view tanpa layout
            require $viewPath;
        }
    }
}