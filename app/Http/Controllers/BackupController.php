<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Backup;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    protected $sicantikUrl = 'https://sicantik.go.id/api/TemplateData/keluaran/41710.json';

    private function checkAndCreateColumns($data)
    {
        $table = 'permohonan_izin_penetapan';
        $existingColumns = Schema::getColumnListing($table);

        // Jupuk sample data gawe ngecek struktur
        $sampleData = $data[0] ?? null;
        if (!$sampleData) return;

        Schema::table($table, function (Blueprint $table) use ($sampleData, $existingColumns) {
            foreach ($sampleData as $key => $value) {
                // Skip nek kolom wes ono
                if (in_array($key, $existingColumns)) continue;

                // Tentukan tipe data berdasarkan nilai
                if (is_numeric($value) && strpos($value, '.') !== false) {
                    $table->decimal($key, 15, 2)->nullable();
                } elseif (is_numeric($value)) {
                    $table->bigInteger($key)->nullable();
                } elseif (is_bool($value)) {
                    $table->boolean($key)->nullable();
                } elseif (strtotime($value) !== false) {
                    $table->dateTime($key)->nullable();
                } else {
                    // Default dadi VARCHAR(255)
                    $table->string($key)->nullable();
                }

                Log::info("Kolom anyar ditambahke: $key");
            }
        });
    }

    public function index(Request $request)
    {
        // Query gawe njupuk kabeh tabel
        $tables = DB::select("
            SELECT table_name
            FROM information_schema.tables
            WHERE table_schema = 'public'
            AND table_type = 'BASE TABLE'
            ORDER BY table_name
        ");

        $database_tables = array_map(function($table) {
            return $table->table_name;
        }, $tables);

        $selected_table = $request->query('table');
        $data = [];
        $columns = [];

        // Nek ono table sing dipilih, langsung tampilno data ne
        if ($selected_table) {
            try {
                $data = DB::table($selected_table)->get();
                if (count($data) > 0) {
                    $columns = array_keys((array)$data[0]);
                }
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Gagal njupuk data: ' . $e->getMessage());
            }
        }

        return view('index', compact('database_tables', 'selected_table', 'data', 'columns'));

    }

    public function backup(Request $request)
    {
        try {
            // Format tanggal dadi Y-m-d (contoh: 2024-03-20)
            $startDate = Carbon::parse($request->start_date)->format('Y-m-d');
            $endDate = Carbon::parse($request->end_date)->format('Y-m-d');

            // Catat neng log gawe debugging
            Log::info("Start Date: " . $startDate);
            Log::info("End Date: " . $endDate);

            // Njupuk data seko API Sicantik
            // withoutVerifying() -> skip SSL verification
            // Parameter key1 lan key2 iku parameter tanggal gawe filter data
            $response = Http::withoutVerifying()
                ->get($this->sicantikUrl . "?key1='" . $startDate . "'&key2='" . $endDate . "'");

            // Catat response API neng log
            Log::info("API Response: " . $response->body());

            // Cek nek API-ne sukses opo ora
            if (!$response->successful()) {
                return back()->with('error', 'Waduh, gagal njupuk data seko API Sicantik! Status: ' . $response->status());
            }

            // Jupuk data seko response JSON-e, nek ora ono dikei array kosong
            $data = $response->json()['data']['data'] ?? [];

            // Cek nek datane kosong
            if (empty($data)) {
                return back()->with('error', 'Ora ono data sing ditemokke neng periode iki');
            }

            // Variabel gawe ngitung jumlah data
            $count = 0;      // Total data sing diproses
            $updated = 0;    // Jumlah data sing diupdate
            $created = 0;    // Jumlah data anyar

            // Mulai transaction database
            // Tujuane: nek ono error, kabeh perubahan dibatalke
            DB::beginTransaction();
            try {
                foreach ($data as $item) {
                    // Cek nek data wes ono opo durung berdasarkan no_permohonan
                    $existingData = Backup::where('no_permohonan', $item['no_permohonan'])->first();

                    // Siapke data sing arep disimpen
                    // except() -> ora masukke field id, created_at, lan updated_at
                    $dataToSave = collect($item)
                        ->except(['created_at', 'updated_at'])
                        ->toArray();

                    // Nek datane wes ono, update
                    if ($existingData) {
                        $existingData->update($dataToSave);
                        $updated++;
                    }
                    // Nek durung ono, gawe data anyar
                    else {
                        $dataToSave['created_at'] = Carbon::now();
                        $dataToSave['updated_at'] = Carbon::now();
                        Backup::create($dataToSave);
                        $created++;
                    }
                    $count++;
                }

                // Nek kabeh sukses, commit perubahan neng database
                DB::commit();
                return back()->with('success', "Alhamdulillah! Total $count data berhasil di-backup ($created data anyar, $updated data diupdate) 😊");

            } catch (\Exception $e) {
                // Nek ono error, batalke kabeh perubahan
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Waduh enek error: ' . $e->getMessage());
        }
    }
}

