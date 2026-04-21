<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\ExpenseReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function __construct(
        protected ExpenseReportService $reportService,
    ) {}

    public function index(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $categoryId = $request->get('category_id');

        $query = Expense::with('category')
            ->byDateRange($startDate, $endDate)
            ->orderByDesc('date');

        if ($categoryId) {
            $query->byCategory((int) $categoryId);
        }

        $expenses = $query->paginate(20);
        $summary = $this->reportService->getExpenseSummary($startDate, $endDate, $categoryId ? (int) $categoryId : null);
        $categories = ExpenseCategory::ordered()->get();

        return view('admin.expenses.index', compact('expenses', 'summary', 'categories', 'startDate', 'endDate', 'categoryId'));
    }

    public function create()
    {
        $categories = ExpenseCategory::active()->ordered()->get();
        return view('admin.expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:expense_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'receipt_image' => ['nullable', 'image', 'max:2048'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('receipt_image')) {
            $validated['receipt_image'] = $request->file('receipt_image')->store('expenses/receipts', 'public');
        }

        $validated['created_by'] = auth()->id();

        Expense::create($validated);

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Expense added successfully.');
    }

    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::active()->ordered()->get();
        return view('admin.expenses.edit', compact('expense', 'categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:expense_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'receipt_image' => ['nullable', 'image', 'max:2048'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('receipt_image')) {
            if ($expense->receipt_image) {
                Storage::disk('public')->delete($expense->receipt_image);
            }
            $validated['receipt_image'] = $request->file('receipt_image')->store('expenses/receipts', 'public');
        }

        $expense->update($validated);

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->receipt_image) {
            Storage::disk('public')->delete($expense->receipt_image);
        }

        $expense->delete();

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }

    public function categories()
    {
        $categories = ExpenseCategory::ordered()->get();
        return view('admin.expenses.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:expense_categories,name'],
            'description' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:20'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        ExpenseCategory::create($validated);

        return redirect()->route('admin.expenses.categories')
            ->with('success', 'Category added successfully.');
    }

    public function updateCategory(Request $request, ExpenseCategory $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:expense_categories,name,' . $category->id],
            'description' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:20'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $category->update($validated);

        return redirect()->route('admin.expenses.categories')
            ->with('success', 'Category updated successfully.');
    }

    public function destroyCategory(ExpenseCategory $category)
    {
        if ($category->expenses()->count() > 0) {
            return redirect()->route('admin.expenses.categories')
                ->with('error', 'Cannot delete category with existing expenses.');
        }

        $category->delete();

        return redirect()->route('admin.expenses.categories')
            ->with('success', 'Category deleted successfully.');
    }
}
