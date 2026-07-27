<?php

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = [], string $layout = 'public'): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require __DIR__ . '/../Views/' . $view . '.php';
        $content = ob_get_clean();

        ob_start();
        require __DIR__ . '/../Views/layouts/' . $layout . '.php';
        return ob_get_clean();
    }

    public static function partial(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require __DIR__ . '/../Views/' . $view . '.php';
    }
}
