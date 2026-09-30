<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('code', 30)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('interests', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable()->after('city')->constrained()->nullOnDelete();
            $table->boolean('is_admin')->default(false)->after('password');
        });

        Schema::table('mingles', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable()->after('city')->constrained()->nullOnDelete();
        });

        Schema::create('interest_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['interest_id', 'user_id']);
        });

        $cities = [
            ['name' => 'Atlanta', 'code' => 'ATL'], ['name' => 'Charlotte', 'code' => 'CLT'],
            ['name' => 'Dallas', 'code' => 'DAL'], ['name' => 'Houston', 'code' => 'HOU'],
            ['name' => 'Miami', 'code' => 'MIA'],
        ];
        foreach ($cities as $city) DB::table('cities')->insert($city + ['created_at' => now(), 'updated_at' => now()]);

        $interests = ['Food & Drinks','Fitness & Sports','Outdoors & Adventure','Travel','Entertainment','Social & Lifestyle','Business & Networking','Arts & Culture','Music','Hobbies','Health & Wellness','Dating & Relationships','Family & Community','Faith & Spirituality'];
        foreach ($interests as $index => $name) DB::table('interests')->insert(['name' => $name, 'sort_order' => $index + 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        foreach (DB::table('cities')->get() as $city) {
            DB::table('users')->whereRaw('lower(city) = ?', [strtolower($city->name)])->update(['city_id' => $city->id]);
            DB::table('mingles')->whereRaw('lower(city) = ?', [strtolower($city->name)])->update(['city_id' => $city->id]);
        }

        DB::table('users')->where('email', 'test@example.com')->update(['is_admin' => true]);
    }

    public function down(): void
    {
        Schema::dropIfExists('interest_user');
        Schema::table('mingles', fn (Blueprint $table) => $table->dropConstrainedForeignId('city_id'));
        Schema::table('users', function (Blueprint $table) { $table->dropConstrainedForeignId('city_id'); $table->dropColumn('is_admin'); });
        Schema::dropIfExists('interests');
        Schema::dropIfExists('cities');
    }
};
