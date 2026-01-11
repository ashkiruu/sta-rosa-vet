<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\Species;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PetController extends Controller
{
    public function index()
    {
        $pets = Pet::where('Owner_ID', Auth::user()->User_ID)->get();
        return view('pets.index', compact('pets'));
    }

    public function create()
    {
        $species = Species::all();
        return view('pets.create', compact('species'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'Pet_Name' => 'required|string|max:255',
            'Sex' => 'required|in:Male,Female',
            'Age' => 'required|integer|min:0',
            'Species_ID' => 'required|exists:species,Species_ID',
            'other_species' => 'nullable|string|max:255',
            'medical_history' => 'nullable|string|max:1000',
            'pet_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $photoPath = null;
        if ($request->hasFile('pet_photo')) {
            $photoPath = $request->file('pet_photo')->store('pet_photos', 'public');
        }

        $pet = new Pet();
        $pet->Owner_ID = Auth::user()->User_ID;
        $pet->Pet_Name = $request->Pet_Name;
        $pet->Sex = $request->Sex;
        $pet->Age = $request->Age;
        $pet->Species_ID = $request->Species_ID;
        $pet->Breed = $request->other_species;
        $pet->Color = '';
        $pet->Date_of_Birth = now()->subMonths($request->Age);
        $pet->Reproductive_Status = 'Unknown';
        $pet->Medical_History = $request->medical_history;
        $pet->Registration_Date = now();
        $pet->save();

        return redirect()->route('pets.index')
            ->with('success', 'Pet registered successfully!');
    }

    public function show($id)
    {
        $pet = Pet::where('Pet_ID', $id)
            ->where('Owner_ID', Auth::user()->User_ID)
            ->firstOrFail();

        return view('pets.show', compact('pet'));
    }

    public function destroy($id)
    {
        $pet = Pet::where('Pet_ID', $id)
            ->where('Owner_ID', Auth::user()->User_ID)
            ->firstOrFail();

        $pet->delete();

        return redirect()->route('pets.index')
            ->with('success', 'Pet removed successfully.');
    }
}