<?php

namespace App\Http\Controllers;

use App\Models\DailyMenu;
use App\Models\DailyMenuSection;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Class DailyMenuController
 *
 * Gestiona el CRUD del Menú del Día desde el panel del gerente.
 * Permite crear, editar, eliminar y configurar menús del día (plantillas
 * semanales y excepciones por fecha), gestionar sus secciones con productos
 * y configurar las rondas de timing de envío a cocina/barra.
 *
 * @package App\Http\Controllers
 * @author Ayrtonalania
 */
class DailyMenuController extends Controller
{
    /**
     * Muestra el listado de menús del día del usuario autenticado.
     * Incluye un resumen semanal con el estado de cada día de la semana.
     *
     * @return View
     */
    public function index(): View
    {
        $ownerId = Auth::user()->ownerUserId();

        $menus = DailyMenu::where('user_id', $ownerId)
            ->orderByRaw("CASE WHEN specific_date IS NOT NULL THEN 0 ELSE 1 END")
            ->orderBy('specific_date')
            ->orderByRaw("FIELD(day_of_week, 'monday','tuesday','wednesday','thursday','friday','saturday','sunday')")
            ->paginate(15);

        $weekDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $today    = now()->startOfDay();
        $nextWeek = now()->addDays(7)->endOfDay();

        $weekSummary = [];
        foreach ($weekDays as $day) {
            $menu = DailyMenu::where('user_id', $ownerId)
                ->where('day_of_week', $day)
                ->where('is_active', true)
                ->first();

            $exception = DailyMenu::where('user_id', $ownerId)
                ->whereNotNull('specific_date')
                ->whereBetween('specific_date', [$today, $nextWeek])
                ->where('is_active', true)
                ->first();

            $weekSummary[$day] = [
                'menu'      => $menu,
                'exception' => $exception,
            ];
        }

        return view('daily-menus.index', compact('menus', 'weekSummary'));
    }

    /**
     * Muestra el formulario para crear un nuevo menú del día.
     *
     * @return View
     */
    public function create(): View
    {
        return view('daily-menus.create');
    }

    /**
     * Almacena un nuevo menú del día en la base de datos.
     *
     * @param  Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string|max:1000',
            'price'         => 'required|numeric|min:0.01',
            'max_per_day'   => 'nullable|integer|min:1',
            'is_active'     => 'boolean',
            'day_of_week'   => 'nullable|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday|required_without:specific_date',
            'specific_date' => 'nullable|date|after_or_equal:today|required_without:day_of_week',
        ]);

        $ownerId = Auth::user()->ownerUserId();

        if (!empty($validated['day_of_week'])) {
            $exists = DailyMenu::where('user_id', $ownerId)
                ->where('day_of_week', $validated['day_of_week'])
                ->where('is_active', true)
                ->exists();

            if ($exists) {
                return back()->withInput()->withErrors([
                    'day_of_week' => 'Ya existe un menú activo para ese día de la semana.',
                ]);
            }
        }

        if (!empty($validated['specific_date'])) {
            $exists = DailyMenu::where('user_id', $ownerId)
                ->where('specific_date', $validated['specific_date'])
                ->where('is_active', true)
                ->exists();

            if ($exists) {
                return back()->withInput()->withErrors([
                    'specific_date' => 'Ya existe un menú activo para esa fecha específica.',
                ]);
            }
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $menu = DailyMenu::create(array_merge($validated, ['user_id' => $ownerId]));

        return redirect()
            ->route('daily-menus.sections', $menu)
            ->with('success', 'Menú del Día creado. Configura ahora sus secciones.');
    }

    /**
     * Muestra el formulario para editar un menú del día existente.
     *
     * @param  DailyMenu $dailyMenu
     * @return View
     */
    public function edit(DailyMenu $dailyMenu): View
    {
        abort_if($dailyMenu->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');

        return view('daily-menus.edit', compact('dailyMenu'));
    }

    /**
     * Actualiza un menú del día en la base de datos.
     *
     * @param  Request   $request
     * @param  DailyMenu $dailyMenu
     * @return RedirectResponse
     */
    public function update(Request $request, DailyMenu $dailyMenu): RedirectResponse
    {
        abort_if($dailyMenu->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');

        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string|max:1000',
            'price'         => 'required|numeric|min:0.01',
            'max_per_day'   => 'nullable|integer|min:1',
            'is_active'     => 'boolean',
            'day_of_week'   => 'nullable|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday|required_without:specific_date',
            'specific_date' => 'nullable|date|required_without:day_of_week',
        ]);

        $ownerId = Auth::user()->ownerUserId();

        if (!empty($validated['day_of_week'])) {
            $exists = DailyMenu::where('user_id', $ownerId)
                ->where('day_of_week', $validated['day_of_week'])
                ->where('is_active', true)
                ->where('id', '!=', $dailyMenu->id)
                ->exists();

            if ($exists) {
                return back()->withInput()->withErrors([
                    'day_of_week' => 'Ya existe un menú activo para ese día de la semana.',
                ]);
            }
        }

        if (!empty($validated['specific_date'])) {
            $exists = DailyMenu::where('user_id', $ownerId)
                ->where('specific_date', $validated['specific_date'])
                ->where('is_active', true)
                ->where('id', '!=', $dailyMenu->id)
                ->exists();

            if ($exists) {
                return back()->withInput()->withErrors([
                    'specific_date' => 'Ya existe un menú activo para esa fecha específica.',
                ]);
            }
        }

        $validated['is_active'] = $request->boolean('is_active');

        $dailyMenu->update($validated);

        return redirect()
            ->route('daily-menus.index')
            ->with('success', 'Menú del Día actualizado correctamente.');
    }

    /**
     * Elimina un menú del día (las secciones se eliminan en cascada por FK).
     *
     * @param  DailyMenu $dailyMenu
     * @return RedirectResponse
     */
    public function destroy(DailyMenu $dailyMenu): RedirectResponse
    {
        abort_if($dailyMenu->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');

        $dailyMenu->delete();

        return redirect()
            ->route('daily-menus.index')
            ->with('success', 'Menú del Día eliminado correctamente.');
    }

    /**
     * Muestra la vista de configuración de secciones y timing del menú.
     *
     * @param  DailyMenu $dailyMenu
     * @return View
     */
    public function sections(DailyMenu $dailyMenu): View
    {
        abort_if($dailyMenu->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');

        $dailyMenu->load('sections.products', 'timingRules');

        $products = Product::where('user_id', Auth::user()->ownerUserId())
            ->where('is_active', true)
            ->with('category')
            ->orderBy('name')
            ->get();

        $sectionTypes = ['first_course', 'second_course', 'dessert', 'coffee', 'drink', 'bread'];

        return view('daily-menus.sections', compact('dailyMenu', 'products', 'sectionTypes'));
    }

    /**
     * Sincroniza los productos asignados a una sección del menú.
     *
     * @param  Request          $request
     * @param  DailyMenu        $dailyMenu
     * @param  DailyMenuSection $section
     * @return RedirectResponse
     */
    public function syncProducts(Request $request, DailyMenu $dailyMenu, DailyMenuSection $section): RedirectResponse
    {
        abort_if($dailyMenu->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');
        abort_if($section->daily_menu_id !== $dailyMenu->id, 403, 'Acceso denegado.');

        $validated = $request->validate([
            'product_ids'   => 'nullable|array',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        if (!empty($validated['product_ids'])) {
            $invalid = Product::whereIn('id', $validated['product_ids'])
                ->where('user_id', '!=', Auth::user()->ownerUserId())
                ->exists();

            if ($invalid) {
                abort(403, 'Acceso denegado.');
            }
        }

        DB::transaction(function () use ($section, $validated) {
            $section->products()->sync($validated['product_ids'] ?? []);
        });

        return redirect()
            ->route('daily-menus.sections', $dailyMenu)
            ->with('success', 'Productos de la sección actualizados.');
    }

    /**
     * Guarda la configuración de rondas de timing del menú del día.
     * Elimina las reglas anteriores y las reemplaza en una sola transacción.
     *
     * @param  Request   $request
     * @param  DailyMenu $dailyMenu
     * @return RedirectResponse
     */
    public function storeTiming(Request $request, DailyMenu $dailyMenu): RedirectResponse
    {
        abort_if($dailyMenu->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');

        $request->validate([
            'rounds'                          => 'required|array|min:1|max:4',
            'rounds.*.round_number'           => 'required|integer|min:1|max:4',
            'rounds.*.default_delay_minutes'  => 'required|integer|min:0',
            'rounds.*.estimated_prep_minutes' => 'required|integer|min:1',
            'rounds.*.section_types'          => 'required|array|min:1',
            'rounds.*.section_types.*'        => 'in:first_course,second_course,dessert,coffee,drink,bread',
        ]);

        DB::transaction(function () use ($request, $dailyMenu) {
            $dailyMenu->timingRules()->delete();
            foreach ($request->rounds as $round) {
                $dailyMenu->timingRules()->create($round);
            }
        });

        return redirect()
            ->route('daily-menus.sections', $dailyMenu)
            ->with('success', 'Configuración de tiempos guardada correctamente.');
    }
}
