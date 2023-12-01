<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Locale;
use App\Models\Setting;
use App\Utils\Helpers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CategoryController extends BaseController
{

    //-------------- Get All Categories ---------------\\

    public function index(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Category::class);
        // How many items do you want to display.
        $perPage = $request->limit;
        $pageStart = \Request::get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = $request->SortType;

        $categories = Category::where('deleted_at', '=', null)

            // Search With Multiple Param
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('search'), function ($query) use ($request) {
                    return $query->where('name', 'LIKE', "%{$request->search}%")
                        ->orWhere('code', 'LIKE', "%{$request->search}%");
                });
            });
        $totalRows = $categories->count();
        if ($perPage == "-1") {
            $perPage = $totalRows;
        }
        $categories = $categories->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        $defaultLocale = Setting::query()->first()->default_language;
        $locales = Locale::query()->get();

        return response()->json([
            'categories' => $categories,
            'totalRows' => $totalRows,
            'locales' => $locales,
            'defaultLocale' => $defaultLocale
        ]);
    }

    //-------------- Store New Category ---------------\\

    public function store(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'create', Category::class);

        $defaultLocale = config('translatable.defaults');
        $request->validate([
            'code' => 'required',
            "translations.$defaultLocale.name" => ['required', 'string']
        ]);

        Category::create(array_merge(
            $request->except('translations'),
            $request->translations
        ));

        return response()->json([
            'success' => true
        ], Response::HTTP_CREATED);
    }

    //------------ function show -----------\\

    public function show($id)
    {
        //

    }

    //-------------- Update Category ---------------\\

    public function update(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'update', Category::class);


        $defaultLocale = Setting::query()->first()->default_language;
        request()->validate([
            'category.code' => ['required'],
            'category.translations.*.name' => ["required_if:category.translations.*.locale,$defaultLocale"],
        ]);

        $transformedTranslations = [];
        foreach ($request->category['translations'] as $translation) {
            $locale = $translation['locale'];
            $name = $translation['name'];
            $transformedTranslations[$locale] = ['name' => $name];
        }

        $category = Category::query()->findOrFail($id);
        $category->update($transformedTranslations);

        return response()->json(['success' => true]);
    }

    //-------------- Remove Category ---------------\\

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Category::class);

        Category::whereId($id)->update([
            'deleted_at' => Carbon::now(),
        ]);
        return response()->json(['success' => true]);
    }

    //-------------- Delete by selection  ---------------\\

    public function delete_by_selection(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Category::class);
        $selectedIds = $request->selectedIds;

        foreach ($selectedIds as $category_id) {
            Category::whereId($category_id)->update([
                'deleted_at' => Carbon::now(),
            ]);
        }

        return response()->json(['success' => true]);
    }

}
