<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function test(Request $request): \Illuminate\Http\JsonResponse
    {
// Установите URL-адрес API и токен доступа
        $url = "https://api.openai.com/v1/chat/completions";
        $token = "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";

// Установите параметры запроса
        $data = array(
            "model" => "gpt-3.5-turbo",
            "messages" => array(
                array(
                    "role" => "user",
                    "content" => $request->ask
                )
            ),
            "max_tokens" => 1500,
            "temperature" => 0
        );

// Преобразуйте параметры в формат JSON
        $data_string = json_encode($data);

// Создайте новый cURL-запрос
        $ch = curl_init();

// Установите параметры запроса
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

// Выполните запрос и получите ответ
        $result = curl_exec($ch);

// Закройте cURL-соединение
        curl_close($ch);

// Выведите ответ
        return response()->json(json_decode($result));
    }
}
