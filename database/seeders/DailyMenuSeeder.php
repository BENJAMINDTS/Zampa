<?php

namespace Database\Seeders;

use App\Models\DailyMenu;
use App\Models\DailyMenuSection;
use App\Models\DailyMenuTimingRule;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Siembra el menú del día demo con secciones, productos y reglas de tiempos.
 *
 * @author BenjaminDTS
 */
class DailyMenuSeeder extends Seeder
{
    /**
     * @return void
     */
    public function run(): void
    {
        $user = User::where('email', 'admin@zampa.app')->firstOrFail();

        // Evita duplicados en re-seed
        DailyMenu::where('user_id', $user->id)->delete();

        $menu = DailyMenu::create([
            'user_id'     => $user->id,
            'title'       => 'Menú del Día',
            'description' => 'Primer plato, segundo plato, postre o café y pan incluido.',
            'price'       => 12.50,
            'max_per_day' => null,
            'is_active'   => true,
            'day_of_week' => null,
            'specific_date' => null,
        ]);

        // ── Secciones ──────────────────────────────────────────────────────────
        $sections = [
            [
                'type'          => 'first_course',
                'is_required'   => true,
                'is_free'       => false,
                'max_quantity'  => 1,
                'display_order' => 0,
                'products'      => ['Ensalada mixta', 'Ensalada de pimientos con ventresca', 'Tomate con anchoas'],
            ],
            [
                'type'          => 'second_course',
                'is_required'   => true,
                'is_free'       => false,
                'max_quantity'  => 1,
                'display_order' => 1,
                'products'      => ['Lomo a la plancha', 'Carne en salsa', 'Calamares fritos', 'Patatas bravas', 'Secreto ibérico'],
            ],
            [
                'type'          => 'dessert',
                'is_required'   => false,
                'is_free'       => true,
                'max_quantity'  => 1,
                'display_order' => 2,
                'products'      => ['Colacao', 'Batido de chocolate'],
            ],
            [
                'type'          => 'drink',
                'is_required'   => false,
                'is_free'       => true,
                'max_quantity'  => 1,
                'display_order' => 3,
                'products'      => [],
            ],
            [
                'type'          => 'bread',
                'is_required'   => false,
                'is_free'       => true,
                'max_quantity'  => 1,
                'display_order' => 4,
                'products'      => [],
            ],
        ];

        foreach ($sections as $sectionData) {
            $section = DailyMenuSection::create([
                'daily_menu_id' => $menu->id,
                'type'          => $sectionData['type'],
                'is_required'   => $sectionData['is_required'],
                'is_free'       => $sectionData['is_free'],
                'max_quantity'  => $sectionData['max_quantity'],
                'display_order' => $sectionData['display_order'],
            ]);

            if (!empty($sectionData['products'])) {
                $productIds = Product::where('user_id', $user->id)
                    ->whereIn('name', $sectionData['products'])
                    ->pluck('id');

                $section->products()->attach($productIds);
            }
        }

        // ── Reglas de tiempos (3 rondas) ──────────────────────────────────────
        $timingRules = [
            [
                'round_number'            => 1,
                'default_delay_minutes'   => 0,
                'estimated_prep_minutes'  => 5,
                'section_types'           => ['bread', 'drink'],
            ],
            [
                'round_number'            => 2,
                'default_delay_minutes'   => 5,
                'estimated_prep_minutes'  => 10,
                'section_types'           => ['first_course'],
            ],
            [
                'round_number'            => 3,
                'default_delay_minutes'   => 20,
                'estimated_prep_minutes'  => 15,
                'section_types'           => ['second_course'],
            ],
            [
                'round_number'            => 4,
                'default_delay_minutes'   => 40,
                'estimated_prep_minutes'  => 5,
                'section_types'           => ['dessert', 'coffee'],
            ],
        ];

        foreach ($timingRules as $rule) {
            DailyMenuTimingRule::create([
                'daily_menu_id'           => $menu->id,
                'round_number'            => $rule['round_number'],
                'default_delay_minutes'   => $rule['default_delay_minutes'],
                'estimated_prep_minutes'  => $rule['estimated_prep_minutes'],
                'section_types'           => $rule['section_types'],
            ]);
        }
    }
}
