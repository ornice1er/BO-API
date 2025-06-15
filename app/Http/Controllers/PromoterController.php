<?php

namespace App\Http\Controllers;

use App\Repositories\PromoterRepository;
use Illuminate\Http\Request;

class PromoterController extends Controller
{
    protected $promoterRepository;

    public function __construct(PromoterRepository $promoterRepository)
    {
        $this->promoterRepository = $promoterRepository;
    }

    /**
     * Display a listing of the promoters.
     */
    public function index()
    {
        $promoters = $this->promoterRepository->getAll();
        return response()->json($promoters);
    }

    /**
     * Show the form for creating a new promoter.
     */
    public function create()
    {
        // Logic to show the create form if needed
    }

    /**
     * Store a newly created promoter in the database.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'status' => 'required|string',
        ]);

        $promoter = $this->promoterRepository->create($data);
        return response()->json($promoter, 201);
    }

    /**
     * Display the specified promoter.
     */
    public function show($id)
    {
        $promoter = $this->promoterRepository->find($id);
        return response()->json($promoter);
    }

    /**
     * Update the specified promoter in storage.
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'string',
            'email' => 'email',
            'phone' => 'string',
            'status' => 'string',
        ]);

        $promoter = $this->promoterRepository->update($id, $data);
        return response()->json($promoter);
    }

    /**
     * Remove the specified promoter from storage.
     */
    public function destroy($id)
    {
        $this->promoterRepository->delete($id);
        return response()->json(['message' => 'Promoter deleted successfully']);
    }
}
