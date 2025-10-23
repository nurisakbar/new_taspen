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
        Pesan::create([
            'url_endpoint' => $request->getPathInfo(),
            'payload' => json_encode($request->all(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        ]);
        $validated = $request->validated();

        $record = WelcomeGreeting::create([
            'nama_peserta' => $validated['nama_peserta'],
            'nomor_wa_tujuan' => $validated['nomor_wa_tujuan'],
        ]);

        // Build and send WhatsApp via Qontak (template id provided by user)
        $messageTemplateId = '7451799a-df24-4fa1-9a94-cfb61c851223';
        $qontakService = app(QontakService::class);
        $waPayload = [
            'to_name' => $validated['nama_peserta'],
            'to_number' => $qontakService->normalizeIndonesianMsisdn($validated['nomor_wa_tujuan']),
            'message_template_id' => $messageTemplateId,
            'channel_integration_id' => '3702ae75-4d97-482c-969a-49f19254c418',
            'language' => ['code' => 'id'],
            'parameters' => [
                'body' => [
                    [ 'key' => '1',  'value_text' => $validated['nama_peserta'],               'value' => 'sapaan_nama' ],
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


    public function generateSuratKepesertaan(){
        $data['pesan'] = \App\Models\Pesan::first();
        $data['content'] = json_decode($data['pesan']->payload, true);
        //return $data['content'];
        return view('surat_kepersertaan', $data);
        return $data;
        // $pdf = Pdf::loadView('surat_kepersertaan', $data);
        // $pdf->save(storage_path('app/public/polis-' . $data['content']['polis_number'] . '.pdf'));
        
        // return url('') . "/storage/polis-" . $data['content']['polis_number'] . ".pdf";
    }
}


