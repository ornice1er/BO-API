<?php

namespace App\Repositories;

use App\Models\Promoter;

class PromoterRepository
{
    /**
     * Get all promoters.
     */
    public function getAll()
    {
        return Promoter::all();
    }

    /**
     * Find a promoter by ID.
     */
    public function find($id)
    {
        return Promoter::findOrFail($id);
    }

    /**
     * Create a new promoter.
     */
    public function create(array $data)
    {
        return Promoter::create($data);
    }

    /**
     * Update a promoter's data.
     */
    public function update($id, array $data)
    {
        $promoter = $this->find($id);
        $promoter->update($data);
        return $promoter;
    }

    /**
     * Delete a promoter.
     */
    public function delete($id)
    {
        $promoter = $this->find($id);
        return $promoter->delete();
    }
}
