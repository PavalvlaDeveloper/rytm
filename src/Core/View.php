<?php
namespace RyTM\Core;

class View
{
    public static function render($view, $data = [])
    {
        extract($data);
        $file = __DIR__ . '/../../templates/' . $view . '.php';
        if (file_exists($file)) {
            require $file;
        } else {
            throw new \Exception("View {$view} not found");
        }
    }
}