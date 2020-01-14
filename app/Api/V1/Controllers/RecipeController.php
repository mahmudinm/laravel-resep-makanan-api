<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use File;
use App\Category;
use App\Ingredient;
use App\Recipe;
use App\RecipeIngredient;

class RecipeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $recipes = Recipe::all();

        return response()->json($recipes);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::select('id', 'name')->get();
        $ingredients = Ingredient::select('id', 'name')->get();

        return response()->json([$categories, $ingredients]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'category_id' => 'required',
            // 'image' => 'required',
            'name' => 'required',
            'step' => 'required'
        ]);

        $recipe = new Recipe;

        if ($request->file('image')) {
            $file = $request->file('image');
            $namaFile = time()."_".$file->getClientOriginalName();
            $tujuanUpload = public_path().'/image';
            $file->move($tujuanUpload, $namaFile);

            $recipe->image = $namaFile;
        }

        $recipe->category_id = $request->category_id;
        $recipe->name = $request->name;
        $recipe->step = $request->step;
        $recipe->save();

        $ingredient_id = $request->ingredient_id;
        $ingredient_quantity = $request->ingredient_quantity;

        // multiple input data untuk many to many
        foreach ($ingredient_id as $key => $id) {
           $recipe->ingredient()->attach($id, ['quantity' => $ingredient_quantity[$key]]);  
        }

        return response()->json(['message' => 'success create data']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $recipe = Recipe::find($id);
        $categories = Category::select('id', 'name');
        $ingredients = Ingredient::select('id', 'name');

        return response()->json([$recipe, $categories, $ingredients]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'category_id' => 'required',
            // 'image' => 'required',
            'name' => 'required',
            'step' => 'required'
        ]);        

        $recipe = Recipe::find($id);

        if ($request->file('image')) {
            // check jika ada image maka image akan di hapus
            if ($recipe->image) {
                $imageExist = public_path("image/{$recipe->image}");
                // image akan di hapus disini
                if (File::exists($imageExist)) {
                    unlink($imageExist);
                }
            }

            $file = $request->file('image');
            $namaFile = time()."_".$file->getClientOriginalName();
            $tujuanUpload = public_path().'/image';
            $file->move($tujuanUpload, $namaFile);

            $recipe->image = $namaFile;
        }        

        $recipe->category_id = $request->category_id;
        $recipe->name = $request->name;
        $recipe->step = $request->step;
        $recipe->save();

        $ingredient_id = $request->ingredient_id;
        $ingredient_quantity = $request->ingredient_quantity;

        // multiple input data untuk many to many
        foreach ($ingredient_id as $key => $id) {
            // false di parameter ketiga untuk tidak menghilangkan data sebelumnya
            // https://stackoverflow.com/questions/24702640/laravel-save-update-many-to-many-relationship
           $recipe->ingredient()->sync([$id => ['quantity' => $ingredient_quantity[$key]]], false);  
        }

        return response()->json(['message' => 'success update data']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $recipe = Recipe::find($id);
        // mendelete data pada table Recipe Ingredient 
        // $recipe->ingredient->detach(); untuk fungsi yang ini harus ada onDelete Cascade pada migration
        $recipe->ingredient()->sync([]);
        $recipe->delete();

        return response()->json(['message' => 'success delete data']);
    }
}
