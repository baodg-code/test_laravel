<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'is_admin' => ['nullable', 'in:0,1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $perPage = $filters['per_page'] ?? 10;

        $users = User::query()
            ->when(
                !empty($filters['q']),
                fn ($query) => $query->where(function ($subQuery) use ($filters) {
                    $subQuery
                        ->where('name', 'like', '%'.$filters['q'].'%')
                        ->orWhere('email', 'like', '%'.$filters['q'].'%');
                })
            )
            ->when(
                isset($filters['is_admin']),
                fn ($query) => $query->where('is_admin', (int) $filters['is_admin'])
            )
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json($users);
    }
}
