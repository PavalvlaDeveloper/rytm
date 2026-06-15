rytm/
│
├── public/                                 # Публичная зона (DocumentRoot)
│   ├── index.php                           # Фронт-контроллер
│   ├── .htaccess                           # ЧПУ: все запросы → index.php
│   ├── assets/
│   │   ├── css/
│   │   │   ├── style.css
│   │   │   └── auth.css
│   │   ├── js/
│   │   │   ├── main.js
│   │   │   └── registration.js
│   │   ├── img/                            # Логотипы, иконки, фоны
│   │   └── vendor/                         # Сторонние библиотеки
│   └── uploads/
│       └── avatars/                        # Аватарки пользователей (публичные)
│
├── src/                                    # Приватный PHP-код
│   ├── Controllers/
│   │   ├── AuthController.php              # Регистрация, логин, подтверждение почты
│   │   └── UserController.php              # Личный кабинет, профиль
│   ├── Models/
│   │   ├── User.php                        # Работа с пользователями
│   ├── Services/
│   │   ├── MailService.php                 # Отправка email
│   │   ├── AuthService.php                 # Логика 3 этапов регистрации
│   │   └── SessionService.php              # Сессии, корзина
│   ├── Helpers/
│   │   ├── functions.php                   # Глобальные функции (redirect, old, csrf)
│   │   └── validator.php                   # Валидация полей
│   └── Core/                               # Микро-фреймворк
│       ├── Router.php                      # Маршрутизация
│       ├── Database.php                    # Подключение PDO
│       ├── View.php                        # Рендер шаблонов
│       └── App.php                         # Инициализация приложения
│
├── templates/                              # HTML-шаблоны
│   ├── layouts/
│   │   └── main.php                        # Основной шаблон (header/footer)
│   ├── auth/
│   │   ├── register_step1.php              # Форма регистрации
│   │   ├── register_step2.php              # Ожидание подтверждения email
│   │   └── register_step3.php              # Поздравление
│   ├── home.php                            # Главная страница (каталог товаров)
│   └── error/
│       ├── 404.php
│       └── 500.php
│
├── storage/                                # Хранилище файлов (вне публичного доступа)
│   ├── beats/                              # Демо-файлы битов
│   ├── snippets/                           # Архивы сниппетов
│   └── temp/                               # Временные файлы
│
├── config/
│   ├── db.php                              # Параметры БД (читает .env)
│   ├── app.php                             # Настройки приложения (название, debug)
│   └── mail.php                            # SMTP-настройки (читает .env)
│
├── logs/
│   └── error.log                           # Лог ошибок
│
├── vendor/                                 # Composer (автозагрузка)
│   └── autoload.php
│
├── .htaccess                               # Редирект всех запросов на public/
├── .env                                    # Переменные окружения (НЕ в Git)
├── .env.example                            # Пример переменных
├── .gitignore                              # Исключения для Git
├── composer.json                           # Зависимости и PSR-4 автозагрузка
├── README.md                               # Этот файл
└── LICENSE                                 # Лицензия (MIT)