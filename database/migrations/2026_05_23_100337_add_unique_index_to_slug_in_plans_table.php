<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AddUniqueIndexToSlugInPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('plans', 'slug')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('name');
            });
        }

        $plans = DB::table('plans')->select('id', 'name', 'slug')->orderBy('id')->get();
        $usedSlugs = [];

        foreach ($plans as $plan) {
            $baseSlug = Str::slug($plan->slug ?: $plan->name);
            $baseSlug = $baseSlug ?: 'forfait-' . $plan->id;
            $slug = $baseSlug;
            $suffix = 2;

            while (in_array($slug, $usedSlugs, true)) {
                $slug = $baseSlug . '-' . $suffix;
                $suffix++;
            }

            $usedSlugs[] = $slug;

            DB::table('plans')->where('id', $plan->id)->update(['slug' => $slug]);
        }

        if (! $this->indexExists('plans', 'plans_slug_unique')) {
            Schema::table('plans', function (Blueprint $table) {
                $table->unique('slug');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('plans', 'slug')) {
            $indexExists = $this->indexExists('plans', 'plans_slug_unique');

            Schema::table('plans', function (Blueprint $table) {
                if ($indexExists) {
                    $table->dropUnique(['slug']);
                }

                $table->dropColumn('slug');
            });
        }
    }

    private function indexExists($table, $indexName)
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);

        return count($indexes) > 0;
    }
}
