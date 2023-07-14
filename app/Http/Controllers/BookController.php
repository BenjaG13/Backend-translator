<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Normalizer;
use Vanderlee\Sentence\Sentence;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Illuminate\Support\Facades\Http;
use Ramsey\Uuid\Uuid;


class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    $params = $request->query();
    $id = $params['id'];

    try {
        $book = Book::select('book_name', 'book_text')->where('id', $id)->first();

        $texto = $book->book_text;


        $texto = str_replace("\r\n", " ", $texto);

        $Sentence = new Sentence();

        $sentences = $Sentence->split($texto);

        return response()->json($sentences, 200);
    } catch (\Exception $e) {
        $error = [
            "message" => $e->getMessage(),
            "code" => $e->getCode(),
            "line" => $e->getLine(),
            "file" => $e->getFile()
        ];
        return response()->json($error, 500);
    }
}

public function translator(Request $request)
    {

    $subscriptionKey = env('TRANSLATION_API_KEY');
    $endpoint = "https://api.cognitive.microsofttranslator.com/";
    $path = "translate?api-version=3.0";
    $params = "&to=es";

    $text = $request->input('text');

    if (!function_exists('com_create_guid')) {
        function com_create_guid()
        {
            return sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff)
            );
        }
    }

    function Translate($host, $path, $key, $params, $content)
    {
        $headers = "Content-type: application/json\r\n" .
            "Content-length: " . strlen($content) . "\r\n" .
            "Ocp-Apim-Subscription-Key: $key\r\n" .
            "X-ClientTraceId: " . com_create_guid() . "\r\n";

        $options = array(
            'http' => array(
                'header' => $headers,
                'method' => 'POST',
                'content' => $content
            )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($host . $path . $params, false, $context);
        return $result;
    }

    $requestBody = array(
        array(
            'Text' => $text,
        ),
    );
    $content = json_encode($requestBody);

    $result = Translate($endpoint, $path, $subscriptionKey, $params, $content);

    $translatedData = json_decode($result);
    $responseBody = json_encode($translatedData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    return response($responseBody, 200)->header('Content-Type', 'application/json');

}


public function getIndice(Request $request)
{
    $params = $request->query();
    $id = $params['id'];

    try {
        $indice = Book::select('book_Y', 'book_indice')->where('id', $id)->first();

        return response()->json($indice, 200);
    } catch (\Exception $e) {
        $error = [
            "message" => $e->getMessage()
        ];
        return response()->json($error, 500);
    }
}

public function postIndice(Request $request)
{

    $data = $request->json()->all();
    $id = $data['data']['id'];
    $y = $data['data']['y'];
    $indice = $data['data']['indice'];


    try {
        $book = Book::find($id);
        $book->book_Y = $y;
        $book->book_indice = $indice;
        $book->save();

        return response()->json(['message' => 'Índice guardado correctamente'], 200)->header('Content-Type', 'application/json');
    } catch (\Exception $e) {
        $error = [
            "message" => $e->getMessage()
        ];
        return response()->json($error, 500);
    }
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        //
    }
}
