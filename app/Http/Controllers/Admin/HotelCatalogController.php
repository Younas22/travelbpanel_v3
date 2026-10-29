<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AllAmenity;
use App\Models\CatalogTranslation;
use App\Models\Hotel;
use App\Models\HotelCatalogItem;
use App\Models\Language;
use App\Models\RoomType;
use App\Support\HotelTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Hotels menu lists: Stay Types, Stay Amenities, Room Types, Room Amenities, Meal Plans.
 * Every list has a name (+ optional description) per language, a status and its own
 * add / edit pages. Stay Amenities live in all_amenities; the rest in hotel_catalog_items.
 */
class HotelCatalogController extends Controller
{
    public const SECTIONS = [
        'stay-types'     => ['kind' => 'stay_type',    'title' => 'Stay Types',     'singular' => 'Stay Type',     'icon' => false, 'description' => false, 'menu' => 'bi-tags',          'hint' => 'Shown in the Stay Type dropdown when creating or editing a hotel.'],
        'stay-amenities' => ['kind' => null,           'title' => 'Stay Amenities', 'singular' => 'Stay Amenity',  'icon' => true, 'description' => false,  'menu' => 'bi-stars',         'hint' => 'Facilities of the property, selected on each hotel.'],
        'room-types'     => ['kind' => 'room_type',    'title' => 'Room Types',     'singular' => 'Room Type',     'icon' => false, 'description' => false, 'menu' => 'bi-door-closed',   'hint' => 'Names a hotel\'s room types are created from.'],
        'room-amenities' => ['kind' => 'room_amenity', 'title' => 'Room Amenities', 'singular' => 'Room Amenity',  'icon' => true, 'description' => false,  'menu' => 'bi-lamp',          'hint' => 'In-room features, selected on each hotel room type.'],
        'meal-plans'     => ['kind' => 'meal_plan',    'title' => 'Meal Plans',     'singular' => 'Meal Plan',     'icon' => false, 'description' => false, 'menu' => 'bi-cup-hot',       'hint' => 'Board basis offered with rooms (e.g. Bed & Breakfast).'],
    ];

    public function index(Request $request, string $section)
    {
        $cfg = $this->section($section);
        $query = $this->query($cfg)->with('translations');

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhereHas('translations', fn ($t) => $t->where('name', 'like', $term)));
        }
        if (in_array($request->query('status'), ['0', '1'], true)) {
            $query->where('status', (int) $request->query('status'));
        }

        $items = $query->paginate(20)->withQueryString();
        $items->getCollection()->each(fn ($item) => $item->usage = $this->usage($cfg, $item));

        $base = $this->query($cfg);
        $stats = ['total' => (clone $base)->count(), 'active' => (clone $base)->where('status', 1)->count(), 'inactive' => (clone $base)->where('status', 0)->count()];
        $languages = $this->languages();

        return view('admin-nova.hotels.catalog.index', compact('section', 'cfg', 'items', 'stats', 'languages'));
    }

    public function create(string $section)
    {
        $cfg = $this->section($section);
        $item = null;
        $languages = $this->languages();

        return view('admin-nova.hotels.catalog.form', compact('section', 'cfg', 'item', 'languages'));
    }

    public function store(Request $request, string $section)
    {
        $cfg = $this->section($section);
        $data = $this->validated($request, $cfg);

        DB::transaction(function () use ($cfg, $data, &$item) {
            $item = $cfg['kind']
                ? HotelCatalogItem::create(['kind' => $cfg['kind'], 'name' => $data['name'], 'icon' => $data['icon'], 'status' => $data['status'], 'sort_order' => $data['sort_order'] ?? ((int) HotelCatalogItem::where('kind', $cfg['kind'])->max('sort_order') + 1)])
                : AllAmenity::create(['name' => $data['name'], 'icon' => $data['icon'], 'status' => $data['status']]);
            $this->saveTranslations($item, $data['translations']);
        });

        return redirect()->route('admin.hotels.catalog.index', $section)->with('success', $cfg['singular'] . ' “' . $data['name'] . '” added.');
    }

    public function edit(string $section, int $id)
    {
        $cfg = $this->section($section);
        $item = $this->query($cfg)->with('translations')->findOrFail($id);
        $languages = $this->languages();

        return view('admin-nova.hotels.catalog.form', compact('section', 'cfg', 'item', 'languages'));
    }

    public function update(Request $request, string $section, int $id)
    {
        $cfg = $this->section($section);
        $item = $this->query($cfg)->findOrFail($id);
        $data = $this->validated($request, $cfg, $item);
        $oldName = $item->name;

        DB::transaction(function () use ($cfg, $item, $data, $oldName) {
            $fields = ['name' => $data['name'], 'icon' => $data['icon'], 'status' => $data['status']];
            if ($cfg['kind'] && isset($data['sort_order'])) {
                $fields['sort_order'] = $data['sort_order'];
            }
            $item->update($fields);
            $this->saveTranslations($item, $data['translations']);

            // Records that store the name as text follow a rename.
            if ($oldName !== $data['name']) {
                if ($cfg['kind'] === 'stay_type') {
                    Hotel::where('type', HotelTypes::normalize($oldName))->update(['type' => HotelTypes::normalize($data['name'])]);
                } elseif ($cfg['kind'] === 'room_type') {
                    RoomType::where('name', $oldName)->update(['name' => $data['name']]);
                }
            }
        });

        return redirect()->route('admin.hotels.catalog.index', $section)->with('success', $cfg['singular'] . ' “' . $data['name'] . '” updated.');
    }

    public function destroy(string $section, int $id)
    {
        $cfg = $this->section($section);
        $item = $this->query($cfg)->findOrFail($id);

        if ($used = $this->usage($cfg, $item)) {
            return back()->with('error', '“' . $item->name . '” is in use (' . $used . ') and can\'t be deleted. Set it to Inactive instead.');
        }

        DB::transaction(function () use ($item) {
            $item->translations()->delete();
            $item->delete();
        });

        return back()->with('success', $cfg['singular'] . ' “' . $item->name . '” deleted.');
    }

    public function toggleStatus(string $section, int $id)
    {
        $cfg = $this->section($section);
        $item = $this->query($cfg)->findOrFail($id);
        $item->update(['status' => !$item->status]);

        return response()->json(['success' => true, 'status' => (bool) $item->status, 'message' => $cfg['singular'] . ' ' . ($item->status ? 'activated' : 'deactivated') . '.']);
    }

    // ─── helpers ─────────────────────────────────────────────────────────────

    private function section(string $section): array
    {
        abort_unless(isset(self::SECTIONS[$section]), 404);

        return self::SECTIONS[$section];
    }

    private function query(array $cfg)
    {
        return $cfg['kind']
            ? HotelCatalogItem::kind($cfg['kind'])
            : AllAmenity::query()->orderBy('name');
    }

    private function languages()
    {
        return Language::where('status', 1)->orderByDesc('is_default')->orderBy('sort_order')->get();
    }

    /** How many records use this item (deletion is blocked while > 0). */
    private function usage(array $cfg, Model $item): int
    {
        return match ($cfg['kind']) {
            'stay_type'    => Hotel::where('type', HotelTypes::normalize($item->name))->count(),
            'room_type'    => RoomType::where('name', $item->name)->count(),
            'room_amenity' => DB::table('room_type_room_amenity')->where('room_amenity_id', $item->id)->count(),
            'meal_plan'    => 0,
            default        => $item->hotels()->count() + $item->roomTypes()->count(),
        };
    }

    private function validated(Request $request, array $cfg, ?Model $item = null): array
    {
        $default = $this->languages()->firstWhere('is_default', true)?->code ?? 'en';

        $request->validate([
            "translations.$default.name"   => 'required|string|max:255',
            'translations'                 => 'required|array',
            'translations.*.name'          => 'nullable|string|max:255',
            'translations.*.description'   => 'nullable|string|max:2000',
            'icon'                         => 'nullable|string|max:100',
            'status'                       => 'required|in:0,1',
            'sort_order'                   => 'nullable|integer|min:0',
        ], [
            "translations.$default.name.required" => 'The name (' . strtoupper($default) . ') is required.',
        ]);

        $name = trim($request->input("translations.$default.name"));

        // no duplicate names inside one list
        $dupe = $this->query($cfg)->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->when($item, fn ($q) => $q->where('id', '!=', $item->id))->exists();
        if ($dupe) {
            throw \Illuminate\Validation\ValidationException::withMessages(["translations.$default.name" => "“{$name}” already exists in {$cfg['title']}."]);
        }

        return [
            'name'         => $name,
            'icon'         => $cfg['icon'] ? $request->input('icon') : null,
            'status'       => (int) $request->input('status'),
            'sort_order'   => $request->input('sort_order'),
            'translations' => (array) $request->input('translations'),
        ];
    }

    private function saveTranslations(Model $item, array $translations): void
    {
        $allowed = Language::pluck('code')->all();
        foreach ($translations as $locale => $fields) {
            if (!in_array($locale, $allowed, true)) {
                continue;
            }
            $name = trim((string) ($fields['name'] ?? ''));
            $description = trim((string) ($fields['description'] ?? ''));
            if ($name === '' && $description === '') {
                $item->translations()->where('locale', $locale)->delete();
                continue;
            }
            CatalogTranslation::updateOrCreate(
                ['translatable_type' => $item->getMorphClass(), 'translatable_id' => $item->id, 'locale' => $locale],
                ['name' => $name ?: null, 'description' => $description ?: null]
            );
        }
    }
}
