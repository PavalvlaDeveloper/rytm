<?php
declare(strict_types=1);

namespace RyTM\Helpers;

class Validator
{
    public static function validateRegistration(string $username, string $email, string $password, string $passwordConfirm): array
    {
        $errors = [];

        if (empty($username)) {
            $errors['username'] = 'Введите логин';
        } elseif (strlen($username) < 3 || strlen($username) > 50) {
            $errors['username'] = 'Логин должен быть от 3 до 50 символов';
        } elseif (!preg_match('/^[a-zA-Z0-9_\-]+$/', $username)) {
            $errors['username'] = 'Логин может содержать только латинские буквы, цифры, _ и -';
        }

        if (empty($email)) {
            $errors['email'] = 'Введите email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Некорректный email';
        } elseif (!Functions::validateEmailDomain($email)) {
            $errors['email'] = 'Домен почты не существует или не принимает письма';
        }

        if (empty($password)) {
            $errors['password'] = 'Введите пароль';
        } else {
            if (strlen($password) < 8) {
                $errors['password'] = 'Пароль должен быть не менее 8 символов';
            }
            if (!preg_match('/[A-ZА-ЯЁ]/u', $password)) {
                $errors['password'] = 'Пароль должен содержать хотя бы одну заглавную букву';
            }
            if (!preg_match('/[a-zа-яё]/u', $password)) {
                $errors['password'] = 'Пароль должен содержать хотя бы одну строчную букву';
            }
            if (!preg_match('/[\-_!?@&]/', $password)) {
                $errors['password'] = 'Пароль должен содержать хотя бы один специальный символ: _ - ! ? @ &';
            }
        }

        if ($password !== $passwordConfirm) {
            $errors['password_confirmation'] = 'Пароли не совпадают';
        }

        return $errors;
    }
}