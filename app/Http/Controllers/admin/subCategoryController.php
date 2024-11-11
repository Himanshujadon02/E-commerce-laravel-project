<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class subCategoryController extends Controller
{

    public function index(Request $request){
        // Use a left join to fetch the category name along with subcategories
        $subcategories = SubCategory::select('sub_categories.*', 'categories.name as category_name')
                            ->leftJoin('categories', 'sub_categories.category_id', '=', 'categories.id')
                            ->latest('sub_categories.id');

        // If a keyword is provided, filter the results based on the subcategory name
        if (!empty($request->get('keyword'))){
            $subcategories = $subcategories->where('sub_categories.name', 'like', '%' . $request->get('keyword') . '%');
        }

        // Paginate the results
        $subcategories = $subcategories->paginate(10);

        // Return the view with the subcategories data
        return view('admin.SubCategory.list', compact('subcategories'));
    }

    public function create(){
        $categories = Category::orderBy('name','ASC')->get();
        $data['categories'] = $categories;
        return view('admin.SubCategory.create',$data);
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'slug' => 'required|unique:sub_categories',
            'category' => 'required',
            'status' => 'required'
        ]);
        if($validator->passes()){
            $subCategory = new SubCategory();
            $subCategory->name = $request->name;
            $subCategory->slug = $request->slug;
            $subCategory->status = $request->status;
            $subCategory->showHome = $request->showHome;
            $subCategory->category_id = $request->category;
            $subCategory->save();

            $request->session()->flash('success','subCategory added successfully');
            return response()->json([
                'status' => true,
                'message' => ('subcategory added successfully')
            ]);

        }else{
            return response([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

    }

    public function edit($id,Request $request){
        $subCategory = SubCategory::find($id);
        if(empty($subCategory)){
            $request->session()->flash('error','Subcategory are not found');
            return redirect()->route('sub-categories.list');
        }

        $categories = Category::orderBy('name','ASC')->get();
        $data['categories'] = $categories;
        $data['subCategory'] = $subCategory;
        return view('admin.SubCategory.edit',$data);
    }

    public function update($id,Request $request){
        $subCategory = SubCategory::find($id);
        if(empty($subCategory)){
            $request->session()->flash('error','Subcategory are not found');
            return response([
                'status' => false,
                'notFound'=> true,
                'message' => 'Subcategory are not found'
            ]);
            // return redirect()->route('sub-categories.list');
        }
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            // 'slug' => 'required|unique:sub_categories',
            'slug' => 'required|unique:sub_categories,slug,'.$subCategory->id.',id',
            'category' => 'required',
            'status' => 'required'
        ]);
        if($validator->passes()){
            $subCategory->name = $request->name;
            $subCategory->slug = $request->slug;
            $subCategory->status = $request->status;
            $subCategory->showHome = $request->showHome;
            $subCategory->category_id = $request->category;
            $subCategory->save();

            $request->session()->flash('success','subCategory updated successfully');
            return response()->json([
                'status' => true,
                'message' => ('subcategory updated successfully')
            ]);

        }else{
            return response([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function destroy($id,Request $request){
        $subCategory = SubCategory::find($id);

        if (empty($subCategory)){
            // return redirect()->route('categories.list');
            $request->session()->flash('error','sub category not found');
            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Sub Category not found'
            ]);
        }
        $subCategory->delete();

        $request->session()->flash('success','sub category deleted successfully');

        return response()->json([
            'status' => true,
            'message' => 'Sub Category deleted successfully'
        ]);
    }
}
