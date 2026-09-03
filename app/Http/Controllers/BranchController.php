<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreBranchRequest;
use App\Http\Requests\Api\V1\UpdateBranchRequest;
use App\Http\Resources\Api\V1\BranchResource;
use App\Models\Branch;
use App\Services\BranchService;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function __construct(
        protected BranchService $branchService
    ) {}

    public function index(Request $request)
    {
        $perPage = min(
            $request->integer('per_page', 20),
            100
        );

        $branches = $this->branchService->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Branches retrieved successfully.',
            'data' => BranchResource::collection(
                $branches->items()
            ),
            'meta' => [
                'current_page' => $branches->currentPage(),
                'last_page' => $branches->lastPage(),
                'per_page' => $branches->perPage(),
                'total' => $branches->total(),
            ],
        ]);
    }

    public function store(StoreBranchRequest $request)
    {
        $branch = $this->branchService->create(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Branch created successfully.',
            'data' => new BranchResource($branch),
        ], 201);
    }

    public function show(Branch $branch)
    {
        $branch = $this->branchService->find($branch);

        return response()->json([
            'success' => true,
            'message' => 'Branch retrieved successfully.',
            'data' => new BranchResource($branch),
        ]);
    }

    public function update(
        UpdateBranchRequest $request,
        Branch $branch
    ) {
        $branch = $this->branchService->update(
            $branch,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Branch updated successfully.',
            'data' => new BranchResource($branch),
        ]);
    }

    public function destroy(Branch $branch)
    {
        if ($branch->stores()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This branch cannot be deleted because it has stores assigned to it.',
            ], 422);
        }

        $this->branchService->delete($branch);

        return response()->json([
            'success' => true,
            'message' => 'Branch deleted successfully.',
        ]);
    }
}
