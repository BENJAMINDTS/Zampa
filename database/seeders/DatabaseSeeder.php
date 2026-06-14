<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Plan;
use App\Models\Table;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Zone;

/**
 * @author BenjaminDTS
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Escenario completo basado en el estado real del negocio demo:
     * 1. Tres superadmins del equipo Zampa
     * 2. Tres planes: Básico · Profesional · Premium
     * 3. Usuario Admin de demo (admin@zampa.app / password) con plan Básico
     * 4. 12 Categorías reales del negocio
     * 5. Mapa real: 16 mesas + 66 elementos de mobiliario + 2 zonas
     * 6. 50 Ingredientes reales con alérgenos UE 1169/2011
     *
     * @return void
     */
    public function run(): void
    {
        // ── 1. Superadmins del equipo Zampa ───────────────────────────────────
        User::firstOrCreate(
            ['email' => 'benjamin@zampa.app'],
            [
                'name'     => 'BenjaminDTS',
                'password' => Hash::make('password'),
                'role'     => 'superadmin',
                'admin_id' => null,
            ]
        );

        User::firstOrCreate(
            ['email' => 'sebastian@zampa.app'],
            [
                'name'     => 'SebastianBCF',
                'password' => Hash::make('password'),
                'role'     => 'superadmin',
                'admin_id' => null,
            ]
        );

        User::firstOrCreate(
            ['email' => 'ayrton@zampa.app'],
            [
                'name'     => 'Ayrton',
                'password' => Hash::make('password'),
                'role'     => 'superadmin',
                'admin_id' => null,
            ]
        );

        // ── 2. Planes de Zampa ────────────────────────────────────────────────
        $planBasico = Plan::updateOrCreate(
            ['name' => 'Básico'],
            [
                'price'         => 29.99,
                'price_monthly' => 29.99,
                'price_yearly'  => 299.90,
                'max_tables'    => 20,
                'max_staff'     => 10,
                'max_floors'    => 1,
            ]
        );

        Plan::updateOrCreate(
            ['name' => 'Profesional'],
            [
                'price'         => 74.99,
                'price_monthly' => 74.99,
                'price_yearly'  => 749.90,
                'max_tables'    => 50,
                'max_staff'     => 25,
                'max_floors'    => 3,
            ]
        );

        Plan::updateOrCreate(
            ['name' => 'Premium'],
            [
                'price'         => 119.99,
                'price_monthly' => 119.99,
                'price_yearly'  => 1199.90,
                'max_tables'    => null,
                'max_staff'     => null,
                'max_floors'    => null,
            ]
        );

        // ── 3. Admin demo ─────────────────────────────────────────────────────
        $user = User::firstOrCreate(
            ['email' => 'admin@zampa.app'],
            [
                'name'                   => 'Admin Demo',
                'password'               => Hash::make('password'),
                'role'                   => 'admin',
                'plan_id'                => $planBasico->id,
                'admin_id'               => null,
                'business_name'          => 'Bar Zampa Demo',
                'address'                => 'Calle Mayor 1, Madrid',
                'lat'                    => 40.4168,
                'lng'                    => -3.7038,
                'is_waiter'              => true,
                'is_kitchen'             => true,
                'split_payment_enabled'  => false,
                'floors_enabled'         => false,
                'floor_count'            => 1,
                'floor_canvas_sizes'     => ['1' => ['width' => 1600, 'height' => 1000]],
            ]
        );

        // ── 4. Staff del gerente demo ─────────────────────────────────────────
        User::firstOrCreate(
            ['email' => 'camarero@zampa.app'],
            [
                'name'     => 'Camarero Demo',
                'password' => Hash::make('password'),
                'role'     => 'waiter',
                'admin_id' => $user->id,
            ]
        );

        User::firstOrCreate(
            ['email' => 'cocinero@zampa.app'],
            [
                'name'     => 'Cocinero Demo',
                'password' => Hash::make('password'),
                'role'     => 'kitchen',
                'admin_id' => $user->id,
            ]
        );

        // ── 5. Categorías reales del negocio ──────────────────────────────────
        $categoryData = [
            ['name' => 'Desayunos',  'destination' => 'bar'],
            ['name' => 'Refrescos',  'destination' => 'bar'],
            ['name' => 'Tostadas',   'destination' => 'kitchen'],
            ['name' => 'Ensaladas',  'destination' => 'kitchen'],
            ['name' => 'Bocadillos', 'destination' => 'kitchen'],
            ['name' => 'Roscas',     'destination' => 'kitchen'],
            ['name' => 'Pizzas',     'destination' => 'kitchen'],
            ['name' => 'Raciones',   'destination' => 'kitchen'],
            ['name' => 'Cerveza',    'destination' => 'bar'],
            ['name' => 'Vino',       'destination' => 'bar'],
            ['name' => 'Licores',    'destination' => 'bar'],
            ['name' => 'Copas',      'destination' => 'bar'],
        ];

        foreach ($categoryData as $cat) {
            Category::firstOrCreate(
                ['name' => $cat['name'], 'user_id' => $user->id],
                ['destination' => $cat['destination']]
            );
        }

        // ── 6. Mapa real: mesas de servicio ───────────────────────────────────
        // Limpiamos mesas existentes del usuario para evitar duplicados al re-seed
        Table::where('user_id', $user->id)->delete();
        Zone::where('user_id', $user->id)->delete();

        $tableData = [
            // Mesas numeradas (zona terraza - rotación 45°)
            ['name' => '101', 'shape' => 'square',    'x' =>   88, 'y' =>  43, 'w' => 75, 'h' => 79, 'rot' => 45,  'svc' => true],
            ['name' => '102', 'shape' => 'square',    'x' =>   84, 'y' => 179, 'w' => 75, 'h' => 79, 'rot' => 45,  'svc' => true],
            ['name' => '103', 'shape' => 'square',    'x' =>   81, 'y' => 319, 'w' => 75, 'h' => 79, 'rot' => 45,  'svc' => true],
            ['name' => '104', 'shape' => 'square',    'x' =>   77, 'y' => 458, 'w' => 75, 'h' => 79, 'rot' => 45,  'svc' => true],
            // Mesas zona barra (rotación 45°)
            ['name' => '2',   'shape' => 'square',    'x' =>  898, 'y' => 382, 'w' => 75, 'h' => 79, 'rot' => 45,  'svc' => true],
            ['name' => '3',   'shape' => 'square',    'x' =>  721, 'y' => 382, 'w' => 75, 'h' => 79, 'rot' => 45,  'svc' => true],
            ['name' => '4',   'shape' => 'square',    'x' =>  555, 'y' => 428, 'w' => 75, 'h' => 79, 'rot' => 45,  'svc' => true],
            ['name' => '5',   'shape' => 'square',    'x' =>  412, 'y' => 430, 'w' => 75, 'h' => 79, 'rot' => 45,  'svc' => true],
            ['name' => '6',   'shape' => 'square',    'x' =>  265, 'y' => 435, 'w' => 75, 'h' => 79, 'rot' => 45,  'svc' => true],
            ['name' => '7',   'shape' => 'square',    'x' =>  100, 'y' => 100, 'w' => 75, 'h' => 79, 'rot' =>  0,  'svc' => true],
            ['name' => '8',   'shape' => 'square',    'x' =>  100, 'y' => 100, 'w' => 75, 'h' => 79, 'rot' =>  0,  'svc' => true],
            // Mesa VIP (esquina derecha)
            ['name' => '1',   'shape' => 'square',    'x' => 1400, 'y' => 438, 'w' => 75, 'h' => 79, 'rot' =>  1,  'svc' => true],
            // Isla central y tableros
            ['name' => 'Isla',    'shape' => 'rectangle', 'x' => 1138, 'y' => 116, 'w' => 213, 'h' => 107, 'rot' => 90, 'svc' => true],
            ['name' => 'Tabla 1', 'shape' => 'rectangle', 'x' => 1189, 'y' => 467, 'w' => 149, 'h' =>  42, 'rot' => 90, 'svc' => true],
            ['name' => 'Tabla 2', 'shape' => 'rectangle', 'x' => 1091, 'y' => 467, 'w' => 149, 'h' =>  42, 'rot' => 90, 'svc' => true],
            ['name' => 'Tabla 3', 'shape' => 'rectangle', 'x' =>  995, 'y' => 467, 'w' => 149, 'h' =>  42, 'rot' => 90, 'svc' => true],
        ];

        foreach ($tableData as $t) {
            Table::create([
                'user_id'         => $user->id,
                'name'            => $t['name'],
                'shape'           => $t['shape'],
                'position_x'      => $t['x'],
                'position_y'      => $t['y'],
                'width'           => $t['w'],
                'height'          => $t['h'],
                'rotation'        => $t['rot'],
                'floor'           => 1,
                'is_service_point'=> $t['svc'],
                'unique_hash'     => Str::uuid()->toString(),
                'status'          => 'free',
            ]);
        }

        // ── 7. Mapa real: mobiliario ─────────────────────────────────────────
        $elementData = [
            // Barra principal (polígono)
            ['name' => 'Barra',     'shape' => 'bar',       'x' =>  532, 'y' =>  71, 'w' => 477, 'h' => 40,  'rot' =>  0,
             'vertices' => [['x'=>-174,'y'=>1],['x'=>236.5,'y'=>0],['x'=>354.75,'y'=>0],['x'=>426.8125,'y'=>-1],['x'=>428.875,'y'=>-70],['x'=>472.9375,'y'=>-70],['x'=>473,'y'=>0],['x'=>473,'y'=>40],['x'=>-175,'y'=>42]]],
            // Chimenea
            ['name' => 'Chimenea',  'shape' => 'fireplace', 'x' =>  794, 'y' => 520, 'w' => 102, 'h' => 40,  'rot' =>  0],
            // Columnas
            ['name' => 'Columna',   'shape' => 'column',    'x' =>  887, 'y' => 240, 'w' =>  60, 'h' => 57,  'rot' =>  0],
            ['name' => 'Columna',   'shape' => 'column',    'x' =>  648, 'y' => 238, 'w' =>  60, 'h' => 57,  'rot' =>  0],
            // Taburetes barra
            ['name' => 'Taburete',  'shape' => 'stool',     'x' => 1011, 'y' =>  34, 'w' =>  41, 'h' => 40,  'rot' =>  0],
            ['name' => 'Taburete',  'shape' => 'stool',     'x' =>  749, 'y' => 118, 'w' =>  41, 'h' => 41,  'rot' =>  0],
            ['name' => 'Taburete',  'shape' => 'stool',     'x' =>  872, 'y' => 117, 'w' =>  41, 'h' => 41,  'rot' =>  0],
            ['name' => 'Taburete',  'shape' => 'stool',     'x' =>  633, 'y' => 118, 'w' =>  41, 'h' => 41,  'rot' =>  0],
            ['name' => 'Taburete',  'shape' => 'stool',     'x' =>  510, 'y' => 118, 'w' =>  41, 'h' => 41,  'rot' =>  0],
            ['name' => 'Taburete',  'shape' => 'stool',     'x' =>  383, 'y' => 119, 'w' =>  41, 'h' => 41,  'rot' =>  0],
            // Taburetes isla
            ['name' => 'Taburete',  'shape' => 'stool',     'x' => 1225, 'y' =>  19, 'w' =>  41, 'h' => 41,  'rot' =>  0],
            ['name' => 'Taburete',  'shape' => 'stool',     'x' => 1140, 'y' =>  92, 'w' =>  41, 'h' => 41,  'rot' =>  0],
            ['name' => 'Taburete',  'shape' => 'stool',     'x' => 1306, 'y' =>  94, 'w' =>  41, 'h' => 41,  'rot' =>  0],
            ['name' => 'Taburete',  'shape' => 'stool',     'x' => 1136, 'y' => 195, 'w' =>  41, 'h' => 41,  'rot' =>  0],
            ['name' => 'Taburete',  'shape' => 'stool',     'x' => 1307, 'y' => 195, 'w' =>  41, 'h' => 41,  'rot' =>  0],
            ['name' => 'Taburete',  'shape' => 'stool',     'x' => 1223, 'y' => 281, 'w' =>  41, 'h' => 41,  'rot' =>  0],
            // Taburetes tableros
            ['name' => 'Taburete',  'shape' => 'stool',     'x' => 1093, 'y' => 442, 'w' =>  41, 'h' => 41,  'rot' =>  0],
            ['name' => 'Taburete',  'shape' => 'stool',     'x' => 1093, 'y' => 501, 'w' =>  41, 'h' => 41,  'rot' =>  0],
            ['name' => 'Taburete',  'shape' => 'stool',     'x' => 1188, 'y' => 442, 'w' =>  41, 'h' => 41,  'rot' =>  0],
            ['name' => 'Taburete',  'shape' => 'stool',     'x' => 1187, 'y' => 499, 'w' =>  41, 'h' => 41,  'rot' =>  0],
            ['name' => 'Taburete',  'shape' => 'stool',     'x' => 1288, 'y' => 499, 'w' =>  41, 'h' => 41,  'rot' =>  0],
            ['name' => 'Taburete',  'shape' => 'stool',     'x' => 1286, 'y' => 441, 'w' =>  41, 'h' => 41,  'rot' =>  0],
            // Sillas terraza (izquierda)
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>   62, 'y' =>  24, 'w' =>  40, 'h' => 40,  'rot' => 315],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  151, 'y' =>  22, 'w' =>  40, 'h' => 40,  'rot' =>  45],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>   59, 'y' => 159, 'w' =>  40, 'h' => 40,  'rot' => 315],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  148, 'y' => 160, 'w' =>  40, 'h' => 40,  'rot' =>  45],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>   58, 'y' => 100, 'w' =>  40, 'h' => 40,  'rot' => 225],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  149, 'y' => 102, 'w' =>  40, 'h' => 40,  'rot' => 135],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>   57, 'y' => 239, 'w' =>  40, 'h' => 40,  'rot' => 225],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  139, 'y' => 243, 'w' =>  40, 'h' => 40,  'rot' => 135],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>   58, 'y' => 296, 'w' =>  40, 'h' => 40,  'rot' => 315],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  142, 'y' => 297, 'w' =>  40, 'h' => 40,  'rot' =>  45],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>   54, 'y' => 380, 'w' =>  40, 'h' => 40,  'rot' => 225],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  139, 'y' => 380, 'w' =>  40, 'h' => 40,  'rot' => 135],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>   53, 'y' => 436, 'w' =>  40, 'h' => 40,  'rot' => 315],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  139, 'y' => 437, 'w' =>  40, 'h' => 40,  'rot' =>  45],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>   49, 'y' => 517, 'w' =>  40, 'h' => 40,  'rot' => 225],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  137, 'y' => 517, 'w' =>  40, 'h' => 40,  'rot' => 135],
            // Sillas mesas bar
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  240, 'y' => 415, 'w' =>  40, 'h' => 40,  'rot' => 315],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  329, 'y' => 415, 'w' =>  40, 'h' => 40,  'rot' =>  45],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  240, 'y' => 497, 'w' =>  40, 'h' => 40,  'rot' => 225],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  326, 'y' => 497, 'w' =>  40, 'h' => 40,  'rot' => 135],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  388, 'y' => 409, 'w' =>  40, 'h' => 40,  'rot' => 315],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  474, 'y' => 407, 'w' =>  40, 'h' => 40,  'rot' =>  45],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  385, 'y' => 490, 'w' =>  40, 'h' => 40,  'rot' => 225],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  470, 'y' => 492, 'w' =>  40, 'h' => 40,  'rot' => 135],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  530, 'y' => 491, 'w' =>  40, 'h' => 40,  'rot' => 225],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  532, 'y' => 406, 'w' =>  40, 'h' => 40,  'rot' => 315],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  613, 'y' => 489, 'w' =>  40, 'h' => 40,  'rot' => 135],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  617, 'y' => 407, 'w' =>  40, 'h' => 40,  'rot' =>  45],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  694, 'y' => 442, 'w' =>  40, 'h' => 40,  'rot' => 225],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  697, 'y' => 361, 'w' =>  40, 'h' => 40,  'rot' => 315],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  782, 'y' => 443, 'w' =>  40, 'h' => 40,  'rot' => 135],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  784, 'y' => 362, 'w' =>  40, 'h' => 40,  'rot' =>  45],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  871, 'y' => 444, 'w' =>  40, 'h' => 40,  'rot' => 225],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  872, 'y' => 361, 'w' =>  40, 'h' => 40,  'rot' => 315],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  955, 'y' => 445, 'w' =>  40, 'h' => 40,  'rot' => 135],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  963, 'y' => 359, 'w' =>  40, 'h' => 40,  'rot' =>  45],
            // Sillas mesa VIP (1)
            ['name' => 'Silla',     'shape' => 'chair',     'x' => 1360, 'y' => 456, 'w' =>  40, 'h' => 40,  'rot' =>   1],
            ['name' => 'Silla',     'shape' => 'chair',     'x' => 1422, 'y' => 396, 'w' =>  40, 'h' => 40,  'rot' =>   1],
            ['name' => 'Silla',     'shape' => 'chair',     'x' => 1420, 'y' => 519, 'w' =>  40, 'h' => 40,  'rot' =>   1],
            ['name' => 'Silla',     'shape' => 'chair',     'x' => 1476, 'y' => 457, 'w' =>  40, 'h' => 40,  'rot' =>   1],
            // Sillas zona central
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  299, 'y' => 203, 'w' =>  40, 'h' => 40,  'rot' =>   0],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  299, 'y' => 326, 'w' =>  40, 'h' => 40,  'rot' => 180],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  529, 'y' => 240, 'w' =>  40, 'h' => 40,  'rot' => 270],
            ['name' => 'Silla',     'shape' => 'chair',     'x' =>  586, 'y' => 305, 'w' =>  40, 'h' => 40,  'rot' => 180],
        ];

        foreach ($elementData as $e) {
            Table::create([
                'user_id'          => $user->id,
                'name'             => $e['name'],
                'shape'            => $e['shape'],
                'position_x'       => $e['x'],
                'position_y'       => $e['y'],
                'width'            => $e['w'],
                'height'           => $e['h'],
                'rotation'         => $e['rot'],
                'floor'            => 1,
                'is_service_point' => false,
                'unique_hash'      => Str::uuid()->toString(),
                'status'           => 'free',
                'vertices'         => $e['vertices'] ?? null,
            ]);
        }

        // ── 8. Zonas del mapa ─────────────────────────────────────────────────
        Zone::create([
            'user_id'    => $user->id,
            'name'       => 'Bar',
            'color'      => '#6366f1',
            'position_x' => 489,
            'position_y' => 1,
            'width'      => 876,
            'height'     => 595,
            'rotation'   => 0,
            'floor'      => 1,
            'vertices'   => [
                ['x' => -251,   'y' => 0],
                ['x' =>  876,   'y' => 0],
                ['x' =>  876,   'y' => 297.5],
                ['x' =>  875,   'y' => 350.875],
                ['x' => 1039,   'y' => 352.25],
                ['x' => 1039,   'y' => 562.625],
                ['x' =>  893,   'y' => 563],
                ['x' => -252,   'y' => 563],
            ],
        ]);

        Zone::create([
            'user_id'    => $user->id,
            'name'       => 'Terraza',
            'color'      => '#9ac997',
            'position_x' => 0,
            'position_y' => 0,
            'width'      => 236,
            'height'     => 565,
            'rotation'   => 0,
            'floor'      => 1,
            'vertices'   => null,
        ]);

        // ── 9. Ingredientes reales con alérgenos UE 1169/2011 ─────────────────
        $ingredientData = [
            ['name' => 'Aceite',            'allergen_types' => []],
            ['name' => 'Aceitunas',         'allergen_types' => []],
            ['name' => 'Ajo',               'allergen_types' => []],
            ['name' => 'Ali-oli',           'allergen_types' => ['huevos']],
            ['name' => 'Anchoas',           'allergen_types' => ['pescado']],
            ['name' => 'Atún',              'allergen_types' => ['pescado']],
            ['name' => 'Bacalao',           'allergen_types' => ['pescado']],
            ['name' => 'Bacon',             'allergen_types' => ['soja']],
            ['name' => 'Berenjena',         'allergen_types' => []],
            ['name' => 'Calamares',         'allergen_types' => ['pescado']],
            ['name' => 'Carne de ternera',  'allergen_types' => []],
            ['name' => 'Cebolla',           'allergen_types' => []],
            ['name' => 'Champiñones',       'allergen_types' => []],
            ['name' => 'Chorizo',           'allergen_types' => ['soja']],
            ['name' => 'Cola Cao en polvo', 'allergen_types' => ['gluten', 'frutos-cascara']],
            ['name' => 'Harina',            'allergen_types' => ['gluten']],
            ['name' => 'Huevo',             'allergen_types' => ['huevos']],
            ['name' => 'Jamón',             'allergen_types' => []],
            ['name' => 'Jamón york',        'allergen_types' => ['soja']],
            ['name' => 'Leche',             'allergen_types' => ['lacteos']],
            ['name' => 'Lechuga',           'allergen_types' => []],
            ['name' => 'Lomo de cerdo',     'allergen_types' => []],
            ['name' => 'Lomo embuchado',    'allergen_types' => ['soja']],
            ['name' => 'Maiz',              'allergen_types' => []],
            ['name' => 'Mantequilla',       'allergen_types' => ['lacteos']],
            ['name' => 'Masa de pizza',     'allergen_types' => ['gluten', 'soja', 'apio', 'sesamo', 'sulfitos']],
            ['name' => 'Mayonesa',          'allergen_types' => ['huevos']],
            ['name' => 'Miel de caña',      'allergen_types' => []],
            ['name' => 'Morcilla',          'allergen_types' => ['soja']],
            ['name' => 'Naranja',           'allergen_types' => []],
            ['name' => 'Pan',               'allergen_types' => ['gluten']],
            ['name' => 'Pan rallado',       'allergen_types' => ['gluten']],
            ['name' => 'Patata',            'allergen_types' => []],
            ['name' => 'Paté de cerdo',     'allergen_types' => ['lacteos', 'soja']],
            ['name' => 'Pimentón',          'allergen_types' => []],
            ['name' => 'Pimientos asados',  'allergen_types' => []],
            ['name' => 'Pollo',             'allergen_types' => []],
            ['name' => 'Queso',             'allergen_types' => ['lacteos']],
            ['name' => 'Queso gouda',       'allergen_types' => ['lacteos']],
            ['name' => 'Queso mozzarella',  'allergen_types' => ['lacteos']],
            ['name' => 'Queso parmesano',   'allergen_types' => ['lacteos']],
            ['name' => 'Queso roquefort',   'allergen_types' => ['lacteos']],
            ['name' => 'Rejos',             'allergen_types' => ['pescado']],
            ['name' => 'Sal',               'allergen_types' => []],
            ['name' => 'Salchichón',        'allergen_types' => ['soja']],
            ['name' => 'Sésamo',            'allergen_types' => ['sesamo']],
            ['name' => 'Sobrasada',         'allergen_types' => []],
            ['name' => 'Tomate',            'allergen_types' => []],
            ['name' => 'Ventresca',         'allergen_types' => ['pescado']],
            ['name' => 'Vino',              'allergen_types' => ['sulfitos']],
        ];

        foreach ($ingredientData as $data) {
            $hasAllergens = !empty($data['allergen_types']);
            Ingredient::firstOrCreate(
                ['name' => $data['name'], 'user_id' => $user->id],
                [
                    'is_allergen'    => $hasAllergens,
                    'allergen_types' => $hasAllergens ? $data['allergen_types'] : null,
                ]
            );
        }

        // ── 10. Productos reales del bar demo ─────────────────────────────
        $this->call(ProductSeeder::class);
    }
}
