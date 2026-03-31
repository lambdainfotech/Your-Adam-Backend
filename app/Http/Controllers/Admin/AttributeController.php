<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttributeController extends Controller
{
    /**
     * Display a listing of attributes
     */
    public function index()
    {
        $attributes = DB::table('attributes')
            ->leftJoin('attribute_values', 'attributes.id', '=', 'attribute_values.attribute_id')
            ->select(
                'attributes.id',
                'attributes.name',
                'attributes.code',
                'attributes.type',
                'attributes.is_filterable',
                'attributes.is_variation',
                'attributes.sort_order',
                DB::raw('COUNT(attribute_values.id) as values_count')
            )
            ->groupBy('attributes.id', 'attributes.name', 'attributes.code', 'attributes.type', 'attributes.is_filterable', 'attributes.is_variation', 'attributes.sort_order')
            ->orderBy('attributes.sort_order')
            ->get();

        return view('admin.attributes.index', compact('attributes'));
    }

    /**
     * Show form to create new attribute
     */
    public function create()
    {
        return view('admin.attributes.create');
    }

    /**
     * Store new attribute
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:attributes',
            'type' => 'required|in:select,color,size,text,number',
            'is_filterable' => 'boolean',
            'is_variation' => 'boolean',
            'sort_order' => 'integer|min:0',
            'values' => 'nullable|array',
            'values.*' => 'nullable|string|max:255',
        ]);

        $attributeId = DB::table('attributes')->insertGetId([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'type' => $validated['type'],
            'is_filterable' => $request->boolean('is_filterable', true),
            'is_variation' => $request->boolean('is_variation', true),
            'sort_order' => $validated['sort_order'] ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert attribute values if provided
        if (!empty($validated['values'])) {
            $values = [];
            foreach ($validated['values'] as $index => $value) {
                if (!empty($value)) {
                    $values[] = [
                        'attribute_id' => $attributeId,
                        'value' => $value,
                        'sort_order' => $index,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            if (!empty($values)) {
                DB::table('attribute_values')->insert($values);
            }
        }

        return redirect()->route('admin.attributes.index')
            ->with('success', 'Attribute created successfully.');
    }

    /**
     * Show attribute details with values
     */
    public function show($id)
    {
        $attribute = DB::table('attributes')->find($id);
        
        if (!$attribute) {
            return redirect()->route('admin.attributes.index')
                ->with('error', 'Attribute not found.');
        }

        $values = DB::table('attribute_values')
            ->where('attribute_id', $id)
            ->orderBy('sort_order')
            ->get();

        return view('admin.attributes.show', compact('attribute', 'values'));
    }

    /**
     * Show form to edit attribute
     */
    public function edit($id)
    {
        $attribute = DB::table('attributes')->find($id);
        
        if (!$attribute) {
            return redirect()->route('admin.attributes.index')
                ->with('error', 'Attribute not found.');
        }

        $values = DB::table('attribute_values')
            ->where('attribute_id', $id)
            ->orderBy('sort_order')
            ->get();

        return view('admin.attributes.edit', compact('attribute', 'values'));
    }

    /**
     * Update attribute
     */
    public function update(Request $request, $id)
    {
        $attribute = DB::table('attributes')->find($id);
        
        if (!$attribute) {
            return redirect()->route('admin.attributes.index')
                ->with('error', 'Attribute not found.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'type' => 'required|in:select,color,size,text,number',
            'is_filterable' => 'boolean',
            'is_variation' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        DB::table('attributes')
            ->where('id', $id)
            ->update([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'type' => $validated['type'],
                'is_filterable' => $request->boolean('is_filterable', true),
                'is_variation' => $request->boolean('is_variation', true),
                'sort_order' => $validated['sort_order'] ?? 0,
                'updated_at' => now(),
            ]);

        return redirect()->route('admin.attributes.index')
            ->with('success', 'Attribute updated successfully.');
    }

    /**
     * Delete attribute
     */
    public function destroy($id)
    {
        // Check if attribute is used by any products
        $productCount = DB::table('product_attributes')
            ->where('attribute_id', $id)
            ->count();

        if ($productCount > 0) {
            return redirect()->route('admin.attributes.index')
                ->with('error', 'Cannot delete attribute. It is used by ' . $productCount . ' product(s).');
        }

        // Delete attribute values first
        DB::table('attribute_values')->where('attribute_id', $id)->delete();
        
        // Delete attribute
        DB::table('attributes')->where('id', $id)->delete();

        return redirect()->route('admin.attributes.index')
            ->with('success', 'Attribute deleted successfully.');
    }

    /**
     * Add value to attribute
     */
    public function addValue(Request $request, $attributeId)
    {
        $validated = $request->validate([
            'value' => 'required|string|max:255',
            'color_code' => 'nullable|string|max:7',
            'sort_order' => 'integer|min:0',
        ]);

        DB::table('attribute_values')->insert([
            'attribute_id' => $attributeId,
            'value' => $validated['value'],
            'color_code' => $validated['color_code'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Value added successfully.');
    }

    /**
     * Update attribute value
     */
    public function updateValue(Request $request, $valueId)
    {
        $validated = $request->validate([
            'value' => 'required|string|max:255',
            'color_code' => 'nullable|string|max:7',
            'sort_order' => 'integer|min:0',
        ]);

        DB::table('attribute_values')
            ->where('id', $valueId)
            ->update([
                'value' => $validated['value'],
                'color_code' => $validated['color_code'] ?? null,
                'sort_order' => $validated['sort_order'] ?? 0,
                'updated_at' => now(),
            ]);

        return redirect()->back()
            ->with('success', 'Value updated successfully.');
    }

    /**
     * Delete attribute value
     */
    public function deleteValue($valueId)
    {
        // Check if value is used in any variant combinations
        // This would require a variant_attribute_values table
        
        DB::table('attribute_values')->where('id', $valueId)->delete();

        return redirect()->back()
            ->with('success', 'Value deleted successfully.');
    }
}
