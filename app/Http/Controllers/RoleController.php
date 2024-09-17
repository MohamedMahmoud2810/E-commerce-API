<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Role Management",
 *     description="Endpoints for managing user roles"
 * )
 */

    /**
     * @OA\Schema(
     *     schema="UserWithRoles",
     *     type="object",
     *     title="User with Roles",
     *     description="User model with roles included",
     *     properties={
     *         @OA\Property(property="id", type="integer", example=1),
     *         @OA\Property(property="name", type="string", example="John Doe"),
     *         @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *         @OA\Property(property="roles", type="array", @OA\Items(type="string", example="admin"), description="Roles assigned to the user")
     *     }
     * )
     */
class RoleController extends Controller
{


    /**
     * @OA\Post(
     *     path="/api/users/{id}/roles/assign",
     *     operationId="assignRole",
     *     tags={"Role Management"},
     *     summary="Assign a role to a user",
     *     description="Assigns a specified role to a user.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user to assign the role to",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"role"},
     *             @OA\Property(property="role", type="string", example="admin", description="The role to assign to the user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role assigned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Role assigned successfully"),
     *             @OA\Property(property="user", ref="#/components/schemas/UserWithRoles")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input or user not found"
     *     )
     * )
     */
    public function assignRole(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        // Find the user by ID
        $user = User::findOrFail($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 400);
        }

        // Assign the role to the user
        $user->assignRole($request->role);

        return response()->json([
            'message' => 'Role assigned successfully',
            'user' => $user->load('roles') // Load the roles to show in the response
        ]);
    }
/**
     * @OA\Post(
     *     path="/api/users/{id}/roles/remove",
     *     operationId="removeRole",
     *     tags={"Role Management"},
     *     summary="Remove a role from a user",
     *     description="Removes a specified role from a user.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user to remove the role from",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"role"},
     *             @OA\Property(property="role", type="string", example="admin", description="The role to remove from the user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role removed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Role removed successfully"),
     *             @OA\Property(property="user", ref="#/components/schemas/UserWithRoles")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input or user does not have the specified role"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function removeRole(Request $request, $id)
    {
        // Validate that the role exists in the roles table
        $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        // Find the user by their ID
        $user = User::findOrFail($id);

        // Check if the user has the specified role before removing it
        if ($user->hasRole($request->role)) {
            // Remove the specified role
            $user->removeRole($request->role);

            return response()->json([
                'message' => 'Role removed successfully',
                'user' => $user->load('roles')
            ]);
        } else {
            return response()->json([
                'message' => 'User does not have the specified role'
            ], 400);
        }
    }
    /**
     * @OA\Get(
     *     path="/api/users/me/roles",
     *     operationId="getUserRoles",
     *     tags={"Role Management"},
     *     summary="Get roles of the currently authenticated user",
     *     description="Retrieves the roles associated with the currently authenticated user.",
     *     @OA\Response(
     *         response=200,
     *         description="Roles retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="roles", type="array", @OA\Items(type="string", example="admin"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function getUserRoles()
    {
        // Retrieve the currently authenticated user
        $user = Auth::user();

        // Get the roles associated with the user
        $roles = $user->getRoleNames(); // or $user->roles if you have a relationship defined

        return response()->json([
            'roles' => $roles
        ]);
    }
}
