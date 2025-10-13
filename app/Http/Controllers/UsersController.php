<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Traits\Queries;
use Illuminate\Support\Arr;
use App\Enum\PaginationEnum;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Models\OauthAccessToken;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Modules\AccessControl\User\UserCreateRequest;

class UsersController extends Controller
{
    use ApiResponser;
    use Queries;
    public function index($id)
    {
        $user = DB::table('users')->where('id', $id)->first();

        if (!$user) {
            return $this->set_response(null, 404, 'error', ['User not found']);
        }

        // Roles
        $roles = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_type', 'App\Models\User')
            ->where('model_has_roles.model_id', $id)
            ->pluck('roles.name')
            ->toArray();

        // App and portal role
        $app = DB::table('application')->where('id', $user->app_id)->first();
        $portal_role = DB::table('portal_role')->where('id', $user->portal_role_id)->first();

        $data = [
            'user' => $user,
            'roles' => $roles,
            'app_name' => $app->name ?? null,
            'portal_role' => $portal_role->role_name ?? null,
        ];

        return $this->set_response($data, 200, 'success', ['User and role data']);
    }

    // Get all users
    public function getAllUsers(Request $request)
    {
        $users = DB::table('users')->orderBy('name')->get();

        return $this->set_response($users, 200, 'success', ['All Users data']);
    }

    // Create new user
    public function createUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'app_id' => 'nullable|exists:application,id',
            'portal_role_id' => 'nullable|exists:portal_role,id',
            'role_ids' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return $this->set_response(null, 422, 'error', $validator->errors()->all());
        }

        DB::beginTransaction();
        try {
            $userId = DB::table('users')->insertGetId([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'app_id' => $request->app_id,
                'portal_role_id' => $request->portal_role_id,
                'role_info' => json_encode($request->role_ids),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($request->role_ids as $roleId) {
                DB::table('model_has_roles')->insert([
                    'role_id' => $roleId,
                    'model_type' => 'App\Models\User',
                    'model_id' => $userId,
                ]);
            }

            DB::commit();
            return $this->set_response(['id' => $userId], 200, 'success', ['User created successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->set_response(null, 422, 'error', ['Something went wrong: ' . $e->getMessage()]);
        }
    }

    // Update user
    public function updateUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:users,id',
            'name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,' . $request->id,
            'password' => 'nullable|string|min:8',
            'app_id' => 'nullable|exists:application,id',
            'portal_role_id' => 'nullable|exists:portal_role,id',
            'role_ids' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return $this->set_response(null, 422, 'error', $validator->errors()->all());
        }

        DB::beginTransaction();
        try {
            $data = $request->only(['name', 'email', 'app_id', 'portal_role_id']);
            if ($request->filled('password')) {
                $data['password'] = bcrypt($request->password);
            }
            $data['role_info'] = json_encode($request->role_ids);
            $data['updated_at'] = now();

            DB::table('users')->where('id', $request->id)->update($data);

            // Update roles
            DB::table('model_has_roles')->where('model_id', $request->id)->delete();
            foreach ($request->role_ids as $roleId) {
                DB::table('model_has_roles')->insert([
                    'role_id' => $roleId,
                    'model_type' => 'App\Models\User',
                    'model_id' => $request->id,
                ]);
            }

            DB::commit();
            return $this->set_response(null, 200, 'success', ['User updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->set_response(null, 422, 'error', ['Something went wrong: ' . $e->getMessage()]);
        }
    }

    // Delete user
    public function deleteUser($id)
    {
        if (!DB::table('users')->where('id', $id)->exists()) {
            return $this->set_response(null, 404, 'error', ['User not found']);
        }

        DB::table('users')->where('id', $id)->delete();
        DB::table('model_has_roles')->where('model_id', $id)->delete();

        return $this->set_response(null, 200, 'success', ['User deleted successfully']);
    }
}

