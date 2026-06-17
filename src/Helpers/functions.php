<?php
namespace RyTM\Helpers;

if (session_status() === PHP_SESSION_NONE) session_start();

function redirect($path)
{
    header("Location: $path");
    exit;
}

function old($key)
{
    return $_SESSION['old'][$key] ?? '';
}

function addOldValue($key, $value)
{
    $_SESSION['old'][$key] = $value;
}

function setMessage($key, $msg)
{
    $_SESSION['message'][$key] = $msg;
}

function getMessage($key)
{
    $msg = $_SESSION['message'][$key] ?? '';
    unset($_SESSION['message'][$key]);
    return $msg;
}

function hasMessage($key)
{
    return isset($_SESSION['message'][$key]);
}

function addValidationError($field, $msg)
{
    $_SESSION['validation'][$field] = $msg;
}

function hasValidationError($field)
{
    return isset($_SESSION['validation'][$field]);
}

function validationErrorMessage($field)
{
    $msg = $_SESSION['validation'][$field] ?? '';
    unset($_SESSION['validation'][$field]);
    return $msg;
}

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function maskEmail($email)
{
    $atPos = strpos($email, '@');
    if ($atPos === false) return $email;
    $local = substr($email, 0, $atPos);
    $domain = substr($email, $atPos + 1);
    if (strlen($local) > 2) {
        return $local[0] . str_repeat('*', strlen($local)-2) . substr($local, -1) . '@' . $domain;
    }
    return $local[0] . str_repeat('*', strlen($local)-1) . '@' . $domain;
}