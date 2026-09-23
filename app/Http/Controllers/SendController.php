<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendRequest;
use App\Jobs\SendMessage;
use App\Models\Pesan;
use App\Models\PolisSend;

class SendController extends Controller
{
    public function send(SendRequest $request)
    {
        $header = $request->header('x-clientkey');
        if ($header != 'HMMKLCQSqUFjkhm0E85qg3P0bxvqii4i') {
            return response()->json(['message' => 'Unauthorized', 'statusCode' => 401], 401);
        }

        $pesan = Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        $record = PolisSend::create([
            'pesan_id' => $pesan->id,
            'phone_number' => $request->input('phone_number'),
            'customer_name' => $request->input('customer_name'),
            'polis_number' => $request->input('polis_number'),
            'product_name' => $request->input('product_name'),
            'polis_url' => $request->input('polis_url'),
            'address' => $request->input('address'),
            'address2' => $request->input('address2'),
            'address3' => $request->input('address3'),
            'name_of_the_insured' => $request->input('name_of_the_insured'),
            'insurance_validity_date' => $request->input('insurance_validity_date'),
            'birth_date' => $request->input('birth_date'),
        ]);

        SendMessage::dispatch($record->id);

        return ['status' => 'sucess', 'message' => 'pesan sedang dikirim', 'id' => $pesan->id];
    }
}
