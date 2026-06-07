<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImportBatchToTicketsTable extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('tickets') || Schema::hasColumn('tickets', 'import_batch')) {
            return;
        }

        Schema::table('tickets', function (Blueprint $table) {
            $table->string('import_batch', 80)->nullable()->after('profile');
            $table->index(['import_batch', 'plan_id'], 'tickets_import_batch_plan_index');
        });
    }

    public function down()
    {
        if (! Schema::hasTable('tickets') || ! Schema::hasColumn('tickets', 'import_batch')) {
            return;
        }

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex('tickets_import_batch_plan_index');
            $table->dropColumn('import_batch');
        });
    }
}
