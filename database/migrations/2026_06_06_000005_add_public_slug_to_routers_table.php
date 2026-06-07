<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AddPublicSlugToRoutersTable extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('routers')) {
            return;
        }

        if (! Schema::hasColumn('routers', 'public_slug')) {
            Schema::table('routers', function (Blueprint $table) {
                $table->string('public_slug', 120)->nullable()->after('name');
            });
        }

        $usedSlugs = [];

        DB::table('routers')
            ->select('id', 'name', 'public_slug')
            ->orderBy('id')
            ->get()
            ->each(function ($router) use (&$usedSlugs) {
                if ($router->public_slug) {
                    $usedSlugs[] = $router->public_slug;
                    return;
                }

                $base = Str::slug($router->name ?: 'routeur-' . $router->id) ?: 'routeur-' . $router->id;
                $slug = $base;
                $counter = 2;

                while (in_array($slug, $usedSlugs, true)) {
                    $slug = $base . '-' . $counter;
                    $counter++;
                }

                $usedSlugs[] = $slug;

                DB::table('routers')
                    ->where('id', $router->id)
                    ->update(['public_slug' => $slug]);
            });

        Schema::table('routers', function (Blueprint $table) {
            $table->unique('public_slug', 'routers_public_slug_unique');
        });
    }

    public function down()
    {
        if (! Schema::hasTable('routers') || ! Schema::hasColumn('routers', 'public_slug')) {
            return;
        }

        Schema::table('routers', function (Blueprint $table) {
            $table->dropUnique('routers_public_slug_unique');
            $table->dropColumn('public_slug');
        });
    }
}
