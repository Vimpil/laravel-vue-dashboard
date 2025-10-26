<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home'); // Изменено с welcome на home
});

Route::get('/about', function () {
    return view('about'); // Добавлен маршрут для страницы "О нас"
});

Route::get('/contact', function () {
    return view('contact'); // Добавлен маршрут для страницы "Контакты"
});
