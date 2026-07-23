<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theme_settings', function (Blueprint $table) {
            $table->id();

            $table->string('theme_name', 40)->default('default');

            // Brand / semantic colors
            $table->string('primary_color', 20)->default('#0C6DFD');
            $table->string('secondary_color', 20)->default('#64748B');
            $table->string('success_color', 20)->default('#10B981');
            $table->string('warning_color', 20)->default('#F59E0B');
            $table->string('danger_color', 20)->default('#EF4444');
            $table->string('info_color', 20)->default('#0EA5E9');

            // Surfaces
            $table->string('body_background', 20)->default('#F8FAFC');
            $table->string('sidebar_background', 20)->default('#FFFFFF');
            $table->string('navbar_background', 20)->default('#FFFFFF');
            $table->string('card_background', 20)->default('#FFFFFF');
            $table->string('text_color', 20)->default('#1F2937');
            $table->string('border_color', 20)->default('#E5E7EB');

            // Inputs
            $table->string('input_background', 20)->default('#FFFFFF');
            $table->string('input_border', 20)->default('#E5E7EB');
            $table->string('input_focus_color', 20)->default('#0C6DFD');

            // Typography
            $table->string('font_family', 60)->default('Inter');
            $table->string('font_size', 10)->default('14px');

            // Shape
            $table->string('border_radius', 20)->default('medium');

            // Layout toggles: sidebar_fixed, navbar_fixed, box_shadow, rounded_cards,
            // rounded_inputs, rounded_buttons, compact_mode, wide_layout, fluid_layout, animations
            $table->json('layout_options')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_settings');
    }
};
