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
            // Pet Name: Must start with letter, allows letters, numbers, spaces, hyphens, apostrophes, periods
            'Pet_Name' => [
                'required',
                'string',
                'min:2',
                'max:50',
                'regex:/^[A-Za-z][A-Za-z0-9\s\-\'\.]*$/'
            ],
            
            // Sex: Must be Male or Female
            'Sex' => 'required|in:Male,Female',
            
            // Age: Must be 0-360 months (max 30 years)
            'Age' => [
                'required',
                'integer',
                'min:0',
                'max:360'
            ],
            
            // Species: Required
            'Species_ID' => 'required',
            
            // Other Species: Required only if Species_ID indicates "Other"
            'other_species' => [
                'nullable',
                'required_if:Species_ID,0',
                'string',
                'max:50',
                'regex:/^[A-Za-z][A-Za-z\s\-]*$/'
            ],
            
            // Breed: Optional, must start with letter if provided
            'Breed' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[A-Za-z][A-Za-z\s\-\'\/]*$/'
            ],
            
            // Color: Required, must start with letter
            'Color' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[A-Za-z][A-Za-z\s\,\&\-\']*$/'
            ],
            
            // Reproductive Status: Must be one of allowed values
            'Reproductive_Status' => 'required|in:Intact,Neutered,Spayed,Unknown',
            
            // Medical History: Optional, max 1000 characters
            'medical_history' => 'nullable|string|max:1000',
        ], [
            // Custom error messages
            'Pet_Name.regex' => 'Pet name must start with a letter and can only contain letters, numbers, spaces, hyphens, apostrophes, and periods.',
            'Pet_Name.min' => 'Pet name must be at least 2 characters.',
            'Pet_Name.max' => 'Pet name cannot exceed 50 characters.',
            'Age.min' => 'Age cannot be negative.',
            'Age.max' => 'Age cannot exceed 360 months (30 years).',
            'other_species.regex' => 'Species name must start with a letter and contain only letters, spaces, and hyphens.',
            'Breed.regex' => 'Breed must start with a letter and can only contain letters, spaces, hyphens, apostrophes, and slashes.',
            'Color.regex' => 'Color must start with a letter and can only contain letters, spaces, commas, ampersands, and hyphens.',
            'Color.min' => 'Color must be at least 2 characters.',
        ]);

        // Sanitize inputs
        $petName = $this->sanitizeText($request->Pet_Name);
        $color = $this->sanitizeText($request->Color);
        $breed = $request->filled('Breed') ? $this->sanitizeText($request->Breed) : null;
        $otherSpecies = $request->filled('other_species') ? $this->sanitizeText($request->other_species) : null;

        $photoPath = null;
        if ($request->hasFile('pet_photo')) {
            $photoPath = $request->file('pet_photo')->store('pet_photos', 'public');
        }

        $pet = new Pet();
        $pet->Owner_ID = Auth::user()->User_ID;
        $pet->Pet_Name = $petName;
        $pet->Sex = $request->Sex;
        $pet->Age = (int) $request->Age;
        $pet->Species_ID = $request->Species_ID;
        
        // Handle breed: use the Breed field if provided, otherwise use other_species for "Other" species
        if ($breed) {
            $pet->Breed = $breed;
        } elseif ($otherSpecies) {
            $pet->Breed = $otherSpecies;
        } else {
            $pet->Breed = null;
        }
        
        $pet->Color = $color;
        $pet->Date_of_Birth = now()->subMonths((int) $request->Age);
        $pet->Reproductive_Status = $request->Reproductive_Status;
        $pet->Medical_History = $request->medical_history ? trim($request->medical_history) : null;
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

        // Check for active appointments (Pending, Confirmed, Approved)
        $hasActiveAppointments = \DB::table('appointments')
            ->where('Pet_ID', $id)
            ->whereIn('Status', ['Pending', 'Confirmed', 'Approved'])
            ->exists();

        if ($hasActiveAppointments) {
            return redirect()->route('pets.index')
                ->with('error', "Cannot remove {$pet->Pet_Name}. There are active appointments scheduled for this pet.");
        }

        // Delete non-active appointment records (Completed, Cancelled, etc.) to avoid FK constraint
        \DB::table('appointments')->where('Pet_ID', $id)->delete();

        $pet->delete();

        return redirect()->route('pets.index')
            ->with('success', 'Pet removed successfully.');
    }

    /**
     * Sanitize text input: trim, normalize spaces, capitalize properly
     */
    private function sanitizeText(string $text): string
    {
        // Trim whitespace
        $text = trim($text);
        
        // Normalize multiple spaces to single space
        $text = preg_replace('/\s+/', ' ', $text);
        
        return $text;
    }
}