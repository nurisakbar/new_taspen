<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Kepesertaan PT Asuransi Jiwa Taspen</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.3;
            font-size : 10pt;
        }
        a {
            
        }
        .logo{
            width 30%;
        }
        @page {
            margin: 8mm;
        }
    </style>
</head>
<body>
        <div class="logo" align="right">
            <br>
            {{-- <img src="{{ asset('Taspen Life-Logo.png')}}" alt="" width="180"> --}}
            <img src="https://tlcare.taspenlife.com/Taspen%20Life-Logo.png" alt="" width="180">
            
        </div>
        <div class="header">
            Jakarta, {{ $pesan['created_at']->format('d/m/Y')}}<br><br>
                Kepada Yth.<br>
                Bapak/Ibu {{ucwords(strtolower($content['nama_peserta']))}}<br>
                {{ucwords(strtolower($content['alamat']??'-'))}}
            
        </div>
        <div class="content"><br>
            <p style="text-align:center"><b>Perihal : Pemberitahuan Kepesertaan</b></p>
            Peserta yang terhormat,<br><br>
            Selamat bergabung bersama kami, PT Asuransi Jiwa Taspen.<br> <br>
            Terima kasih atas kepercayaannya kepada kami dalam memberikan perlindungan asuransi jiwa bagi Anda dan keluarga.</p>
            <p>Bersama ini disampaikan data polis Bapak/Ibu yang tercatat pada sistem kami sebagai berikut:</p>
            <div style="padding: 0px 20px">
                <table width="100%">
                    <tr>
                        <td width="30%">No. Polis/No. Peserta</td>
                        <td width="5%">:</td>
                        <td width="65%">{{$content['nomor_id_claim']??'-'}}</td>
                    </tr>
                    <tr>
                        <td width="30%">Nama Pemegang Polis</td>
                        <td width="5%">:</td>
                        <td width="65%">{{ucwords($content['nama_peserta'])}}</td>
                    </tr>
                    <tr>
                        <td width="30%">  Nama Tertanggung</td>
                        <td width="5%">:</td>
                        <td width="65%">{{ucwords($content['nama_peserta'])}}</td>
                    </tr>
                    <tr>
                        <td width="30%">Produk Asuransi</td>
                        <td width="5%">:</td>
                        <td width="65%">{{$content['nama_produk']}}</td>
                    </tr>
                    <tr>
                     
                        <td width="30%">Tanggal Mulai Asuransi</td>
                        <td width="5%">:</td>
                        <td width="65%">{{date("d/m/Y",strtotime($pesan['insurance_validity_date']))}}</td>
                    </tr>
                </table>
            </div>
            <p>Untuk mengetahui informasi Polis dan Pertanggungan, silakan melakukan Pendaftaran Akun pada aplikasi <b>mytaspenlife</b> atau dapat melalui <i>website</i> 
                Taspen Life <a href="https://www.taspenlife.com/mytaspenlife#service-tab" target="new">www.taspenlife.com</a></p>
                <p>Informasi lebih lanjut, dapat menghubungi kami melalui:</p>
        </div>
        <div class="contact">
            <b>TL Care</b><br>
            <table width="50%">
                <tr>
                    {{-- <td width="2%"><img src="{{asset('phone.png')}}" width="20"></td> --}}
                    <td width="2%"><img src="https://tlcare.taspenlife.com/phone.png" width="20"></td>
                    <td width="2%">:</td>
                    <td width="15%">(021) 5080 8158</td>
                </tr>
                <tr>
                    {{-- <td width="2%"><img src="{{asset('wa.png')}}" width="20"></td> --}}
                    <td width="2%"><img src="https://tlcare.taspenlife.com/wa.png" width="20"></td>
                    <td width="5%">:</td>
                    <td width="65%">0811 8111 1808 ( Whatsapp Chat )</td>
                </tr>
                <tr>
                    {{-- <td width="2%"><img src="{{asset('email.png')}}" width="20"></td> --}}
                    <td width="2%"><img src="https://tlcare.taspenlife.com/email.png" width="20"></td>
                    <td width="5%">:</td>
                    <td width="65%"><a href="mailto:tlscenter@taspenlife.com">tlscenter@taspenlife.com</a></td>
                </tr>
            </table>
        </div>
        <div class="content"><br>
            Waktu Operasional:<br>
            Hari*)	:	Senin – Jumat<br>
            Pukul	:	08.00 – 17.00 WIB<br>
            <span style="font-size: 11px">*)Tidak beroperasi pada Sabtu, Minggu dan Hari Libur Nasional</span>
        </div>
        <div class="content">
            <br><p>Hormat kami,<br><br>
            <b>PT Asuransi Jiwa Taspen<br><br>
            Taspen Life berizin dan diawasi oleh OJK</b>
            </p>
        </div>

        <table width="100%" style="margin-top: 30px">
            <tr>
                <td width="55%"></td>
                <td width="45%">
                    <span style="color: #F19F1D">PT Asuransi Jiwa Taspen</span><br>
                    <span>Jl. Letjen Suprapto No 45 Blok B Lantai 3</span><br>
                    <span>Cempaka Putih, Jakarta Pusat 10520, Indonesia</span><br>
                    <span>T 021-420 538 8</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <span>Jl F 021-420 538 3</span><br><br>
                    <span><a href="https://www.taspenlife.com/" target="new">www.taspenlife.com</a></span>
                </td>
            </tr>
        </table>
</body>
</html>