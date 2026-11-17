<?php

namespace Database\Seeders;

use App\Models\DocumentTemplate;
use Illuminate\Database\Seeder;

class DocumentTemplateSeeder extends Seeder
{
    /**
     * Template default PKS, dibuat otomatis saat order disimpan.
     */
    public function run(): void
    {
        DocumentTemplate::updateOrCreate(
            ['type' => 'agreement', 'name' => 'Perjanjian Kerja Sama Jasa'],
            [
                'body' => '<h3>PERJANJIAN KERJA SAMA JASA</h3>'
                    .'<p>Nomor: {{ nomor_pks }}<br>Tanggal: {{ tanggal }}</p>'
                    .'<p><strong>Pihak Pertama</strong><br>{{ nama_perusahaan }}<br>{{ alamat_perusahaan }}</p>'
                    .'<p><strong>Pihak Kedua</strong><br>{{ nama_klien }}<br>{{ alamat_klien }}</p>'
                    .'<h4>Pasal 1 &mdash; Ruang Lingkup</h4>'
                    .'<p>Pihak Pertama mengerjakan pekerjaan &ldquo;{{ judul_pekerjaan }}&rdquo; ({{ nomor_order }}) '
                    .'dengan nilai jasa sebesar {{ nilai_pekerjaan }}.</p>'
                    .'<h4>Pasal 2 &mdash; Jangka Waktu</h4>'
                    .'<p>Pekerjaan mulai berlaku {{ tanggal_mulai }} sampai {{ deadline }}.</p>'
                    .'<h4>Pasal 3 &mdash; Pembayaran</h4>'
                    .'<p>Pembayaran dilakukan ke {{ nama_bank }} {{ nomor_rekening }} a.n. {{ atas_nama_rekening }}.</p>'
                    .'<h4>Pasal 4 &mdash; Revisi</h4>'
                    .'<p>Masa garansi revisi {{ masa_revisi }} hari setelah pekerjaan diserahkan.</p>'
                    .'<p>Pihak Pertama,<br><br><br>{{ nama_penandatangan }}</p>',
                'is_default' => true,
            ]
        );

        DocumentTemplate::updateOrCreate(
            ['type' => 'invoice', 'name' => 'Invoice Jasa Standar'],
            [
                'body' => '<h3>INVOICE {{ nomor_invoice }}</h3>'
                    .'<p>{{ nama_perusahaan }}<br>{{ alamat_perusahaan }}</p>'
                    .'<p>Kepada: {{ nama_klien }}<br>Tanggal: {{ tanggal }}<br>Jatuh tempo: {{ jatuh_tempo }}</p>'
                    .'<p>Pekerjaan: {{ judul_pekerjaan }}</p>'
                    .'<p>Total tagihan: {{ total_tagihan }}</p>',
                'is_default' => true,
            ]
        );
    }
}
