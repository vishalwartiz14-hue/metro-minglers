<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $this->backfillCityIds('users');
        $this->backfillCityIds('mingles');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('city');
        });

        Schema::table('mingles', function (Blueprint $table): void {
            $table->dropColumn('city');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('city', 100)->nullable()->after('city_id');
        });

        Schema::table('mingles', function (Blueprint $table): void {
            $table->string('city', 100)->nullable()->after('city_id');
        });

        $this->restoreCityNames('users');
        $this->restoreCityNames('mingles');
    }

    private function backfillCityIds(string $table): void
    {
        $legacyRows = DB::table($table)
            ->select(['id', 'city'])
            ->whereNull('city_id')
            ->whereNotNull('city')
            ->orderBy('id')
            ->get();

        foreach ($legacyRows as $row) {
            $name = trim((string) $row->city);

            if ($name === '') {
                continue;
            }

            $cityId = DB::table('cities')
                ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])
                ->orWhereRaw('LOWER(TRIM(code)) = ?', [strtolower($name)])
                ->value('id');

            if (! $cityId) {
                $cityId = $this->createLegacyCity($name);
            }

            DB::table($table)->where('id', $row->id)->update(['city_id' => $cityId]);
        }
    }

    private function createLegacyCity(string $name): int
    {
        $baseCode = 'LEGACY-'.strtoupper(substr(hash('sha256', strtolower($name)), 0, 16));
        $code = $baseCode;
        $suffix = 1;

        while (DB::table('cities')->where('code', $code)->exists()) {
            $tail = '-'.$suffix++;
            $code = substr($baseCode, 0, 30 - strlen($tail)).$tail;
        }

        return (int) DB::table('cities')->insertGetId([
            'name' => mb_substr($name, 0, 100),
            'code' => $code,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function restoreCityNames(string $table): void
    {
        $rows = DB::table($table)
            ->leftJoin('cities', 'cities.id', '=', $table.'.city_id')
            ->select([$table.'.id', 'cities.name as city_name'])
            ->get();

        foreach ($rows as $row) {
            DB::table($table)->where('id', $row->id)->update(['city' => $row->city_name]);
        }
    }
};
