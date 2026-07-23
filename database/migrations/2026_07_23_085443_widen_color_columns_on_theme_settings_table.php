<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Color columns were originally sized for hex codes only (varchar(20)).
     * The Glass preset's translucent surfaces use rgba(...) strings up to
     * ~23 chars, which the validation layer already allows (max:40) but the
     * database columns did not — widen them to match. Raw SQL is used
     * because doctrine/dbal (required by Blueprint::change()) isn't
     * installed in this project.
     */
    protected array $colorColumns = [
        'primary_color', 'secondary_color', 'success_color', 'warning_color',
        'danger_color', 'info_color', 'body_background', 'sidebar_background',
        'navbar_background', 'card_background', 'text_color', 'border_color',
        'input_background', 'input_border', 'input_focus_color',
    ];

    public function up(): void
    {
        foreach ($this->colorColumns as $column) {
            DB::statement("ALTER TABLE `theme_settings` MODIFY `{$column}` VARCHAR(40) NOT NULL");
        }
    }

    public function down(): void
    {
        foreach ($this->colorColumns as $column) {
            DB::statement("ALTER TABLE `theme_settings` MODIFY `{$column}` VARCHAR(20) NOT NULL");
        }
    }
};
