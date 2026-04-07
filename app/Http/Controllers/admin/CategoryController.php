<?php
namespace App\Http\Controllers\admin;

// namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function category_list(Request $request)
    {
        return view('category/categorylist');
    }
    public function add_category(Request $request)
    {
        return view('category/addcategory');
    }
    public function edit_category(Request $request, $id)
    {
        $category = Category::find($id);

        if (! $category) {
            // Redirect to category list if category not found
            return redirect()->route('category.list')->with('error', 'Category not found.');
        }

        return view('category/editcategory', compact('category', 'id'));
    }
}
