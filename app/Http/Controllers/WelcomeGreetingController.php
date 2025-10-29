<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesan;
use App\Http\Requests\WelcomeGreetingRequest;
use App\Models\WelcomeGreeting;
use App\Services\QontakService;
use Barryvdh\DomPDF\Facade\Pdf;

class WelcomeGreetingController extends Controller
{
    public function index(WelcomeGreetingRequest $request)
    {
        $pesan = Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
        $validated = $request->validated();

        $record = WelcomeGreeting::create([
            'nama_peserta' => $validated['nama_peserta'],
            'nomor_wa_tujuan' => $validated['nomor_wa_tujuan'],
            'nomor_polis' => $validated['nomor_polis'],
            'alamat' => $validated['alamat'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'nama_produk' => $validated['nama_produk'],
            'tanggal_mulai_asuransi' => $validated['tanggal_mulai_asuransi'],
            'nomor_polis' => $validated['nomor_polis'],
            'polis_url' => $validated['polis_url'],
            'alamat' => $validated['alamat']
        ]);
        $url = $this->generateSuratKepesertaan($pesan->id);
        $filenameFormat    = $validated['nomor_polis'] . ".pdf";
        $filename          = "polis-verify/".$pesan->id;

        // Build and send WhatsApp via Qontak (template id provided by user)
        //$messageTemplateId = '7451799a-df24-4fa1-9a94-cfb61c851223';
        $messageTemplateId = "897edb7a-86a4-44c3-99da-453caa7d7c42";
        $qontakService = app(QontakService::class);
        $waPayload = [
            'to_name' => $validated['nama_peserta'],
            'to_number' => $qontakService->normalizeIndonesianMsisdn($validated['nomor_wa_tujuan']),
            'message_template_id' => $messageTemplateId,
            'channel_integration_id' => '3702ae75-4d97-482c-969a-49f19254c418',
            'language' => ['code' => 'id'],
            'parameters' => [
                "header" => [
                     "format" => 'DOCUMENT',
                     "params" => [
                         [
                             "key" => "url",
                             "value"=>$url
                                 
                         ]
                     ],
                 ],
                //  "buttons" => [
                //      [
                //          "index" => "0",
                //          "type" => "url",
                //          "value"=>"polis-verify/4631a179-4403-4336-bb9a-80e469ea37e6"
                   
                //      ],
                //  ],
                'body' => [
                    [ 'key' => '1',  'value_text' => $validated['nama_peserta'],                      'value' => 'sapaan_nama' ],
                    [ 'key' => '2',  'value_text' => 'PT Asuransi Jiwa Taspen',                       'value' => 'nama_perusahaan' ],
                    [ 'key' => '3',  'value_text' => 'Surat Pemberitahuan Kepesertaan Asuransi',      'value' => 'judul_surat' ],
                    [ 'key' => '4',  'value_text' => 'kata sandi',                                    'value' => 'kata_sandi_label' ],
                    [ 'key' => '5',  'value_text' => 'Tanggal Lahir',                                 'value' => 'tgl_lahir_lbl' ],
                    [ 'key' => '6',  'value_text' => 'DDMMYYYY',                                      'value' => 'format_tgl' ],
                    [ 'key' => '7',  'value_text' => 'Polis dan Pertanggungan',                       'value' => 'polis_pert' ],
                    [ 'key' => '8',  'value_text' => 'Pendaftaran Akun',                              'value' => 'daftar_akun' ],
                    [ 'key' => '9',  'value_text' => 'MyTaspenLife',                                  'value' => 'nama_aplikasi' ],
                    [ 'key' => '10', 'value_text' => 'sandi',                                         'value' => 'sandi_label' ],
                    [ 'key' => '11', 'value_text' => $validated['sandi'],                             'value' => 'contoh_sandi' ],
                    [ 'key' => '12', 'value_text' => 'www.taspenlife.com/mytaspenlife',               'value' => 'tautan_mtlife' ],
                    [ 'key' => '13', 'value_text' => 'TL Care',                                       'value' => 'nama_layanan' ],
                    [ 'key' => '14', 'value_text' => '(021) 5080 8158',                               'value' => 'telepon_tlcare' ],
                    [ 'key' => '15', 'value_text' => '0811 8111 1808 (Whatsapp Chat)',                'value' => 'whatsapp_tlcare' ],
                    [ 'key' => '16', 'value_text' => 'tlscenter@taspenlife.com',                      'value' => 'email_tlcare' ],
                    [ 'key' => '17', 'value_text' => 'PT Asuransi Jiwa Taspen',                       'value' => 'penutup_pt' ],
                ]
            ],
        ];

        $whatsappResult = $qontakService->sendDirect($waPayload);

        $message = 'Data welcome greeting berhasil dibuat';
        $success = true;
        if (!$whatsappResult['success']) {
            $message .= ', namun notifikasi WhatsApp gagal dikirim';
            $success = false;
        } else {
            $message .= ' dan notifikasi WhatsApp berhasil dikirim';
            
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => [
                'id' => $record->id,
                'nama_peserta' => $record->nama_peserta,
                'nomor_wa_tujuan' => $record->nomor_wa_tujuan,
                'whatsapp_notification' => $whatsappResult,
            ],
        ], $success ? 201 : 200);
    }


    public function generateSuratKepesertaan($pesan_id)
    {
        $data['pesan'] = \App\Models\Pesan::where('id', $pesan_id)->first();
        $data['content'] = json_decode($data['pesan']->payload, true);
        //return $data['content'];
        // return view('surat_kepersertaan', $data);
        // return $data;
        $pdf = Pdf::loadView('surat_kepersertaan', $data);
        $pdf->setEncryption("viewer_password", date("dmY", strtotime($data['content']['tanggal_lahir'])));
        $pdf->save(storage_path('app/public/polis-' . $data['content']['nomor_polis'] . '.pdf'));
        return "https://taspen.klikakutansi.com/storage/polis-" . $data['content']['nomor_polis'] . ".pdf";
    }


    function formVerify($id)
    {
        $data['id'] = $id;
        return view('polis-verify', $data);
    }

    function verify($id, Request $request)
    {
        if (strlen($request->password) != 8) {
            return redirect('polis-verify/' . $id)->with('message', 'Password Yang Anda Masukan Salah');
        }
        $dateTime = \DateTime::createFromFormat('dmY', $request->password);
        if ($dateTime && $dateTime->format('dmY') != $request->password) {
            return redirect('polis-verify/' . $id)->with('message', 'Password Yang Anda Masukan Salah');
        }
        $password = \DateTime::createFromFormat('dmY', $request->password)->format('Y-m-d');
        $pesan = Pesan::where('id', $id)->first();

        if ($pesan) {
            $payload = json_decode($pesan->payload, true);
            $tanggalLahir = $payload['tanggal_lahir'] ?? null;

            if ($tanggalLahir !== $password) {
                $pesan = null;
            }
        }
        if ($pesan != null) {
            $payload = json_decode($pesan->payload, true);
            return redirect($payload['polis_url']);
        } else {
            return redirect('polis-verify/' . $id)->with('message', 'Password Yang Anda Masukan Salah');
        }
    }
}
