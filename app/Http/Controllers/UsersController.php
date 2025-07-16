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

    public function getAllUsers_p(Request $request)
    {
        [$sort_field, $sort_order] = processOrderBy('users.id', 'ASC', $request->sort['table'] ?? null, $request->sort['column'] ?? null,  $request->sort['order'] ?? null);

        $data = User::when(isset($sort_field), function ($query) use ($sort_field, $sort_order) {
                    return $query
                    ->select('users.*')
                    ->orderBy($sort_field, $sort_order);
                })
                ->when(request()->filled('search'), function ($query) {
                    $query->where('users.name', 'like', "%".request('search')."%")
                    ->orWhere('users.email', 'like', "%".request('search')."%")
                    ;
                })
                ->with('roles:id,name')
                ->paginate(PaginationEnum::$DEFAULT);

        $data = [
            'paginator' => getFormattedPaginatedArray($data),
            'data' => $data->items(),
        ];
        return $this->set_response($data,  200,'success', ['Users data'], $request->merge(['log_type_id' => 5,'segment'=>'User','pagename'=>'User','pageurl'=>'/access-control/users']));
    }


    public function getAllUsers(Request $request)
    {
        $data = User::orderBy('name')->get();
        return $this->set_response($data,  200,'success', ['All Users data']);
    }

    public function getUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->set_response(null, 422, 'error', $validator->errors()->all());
        }
        $user = User::find($request->id);
        $user_roles_permissions = $this->user_roles_permissions_q();

        $user['roles']=$user_roles_permissions->where('user_id', $user->id)->unique('role_id')->values();
        $user['permissions']=$user_roles_permissions->where('user_id', $user->id)->pluck('permission_name')->unique()->toArray();

        return $this->set_response($user, 200,'success',  ['User data']);
    }

    public function createUser(UserCreateRequest $request)
    {
        $input = $request->all();

        $input['password'] = bcrypt($input['password']);
        $input['created_by'] = Auth::user()->id;

        $input = Arr::except($input,array('roles'));


        DB::beginTransaction();
        try {
            $user = User::create($input);
            foreach ($request->role_ids as $value)
            {
                DB::table('model_has_roles')->insert(
                    [
                        'role_id' => $value,
                        'model_type' => 'App\Models\User',
                        'model_id' => $user->id,
                    ]
                );
            }
            DB::commit();
            return $this->set_response($user, 200,'success', ['User created successfully']);
        } catch (\Exception $e) {
            DB::rollback();
            $logMessage = formatCommonErrorLogMessage($e);
            writeToLog($logMessage, 'error');
            return $this->set_response(null,  422,'error', ['Something went wrong. Please try again later!']);
        }
    }



    public function updateUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:users,id',
                // 'email' => 'required|email|unique:users,email,'.$request->id,
                'role_ids' => 'required|array|min:1',
                'password' => [
                        'nullable',
                        'string',
                        'min:8',             // must be at least 8 characters in length
                        'regex:/[a-z]/',      // must contain at least one lowercase letter
                        'regex:/[A-Z]/',      // must contain at least one uppercase letter
                        'regex:/[0-9]/',      // must contain at least one digit
                        'regex:/[@$!%*#?&]/', // must contain a special character
                    ],
                'joining_date' => 'nullable|date',
            ],
            [
                'password.regex' => "Password must contain at least one upper case, lower case letter and one number and one special character."
            ]
        );

        if ($validator->fails()) {
            return $this->set_response(null, 422, 'error', $validator->errors()->all());
        }

        $input = $request->all();

        if(!empty($input['password'])){

            $input['password'] = bcrypt($input['password']);

        }else{
            $input = Arr::except($input,array('password'));
        }

        $input = Arr::except($input,array('roles'));

        $user = User::find($request->id);
        $input['updated_by']=$user->id;


        DB::beginTransaction();
        try {
            $user->update($input);
            DB::table('model_has_roles')->where('model_id', $request->id)->delete();
            foreach ($request->role_ids as $value)
            {
                DB::table('model_has_roles')->insert(
                    [
                        'role_id' => $value,
                        'model_type' => 'App\Models\User',
                        'model_id' => $request->id,
                    ]
                );
            }
            if(isset($input['status']) && $input['status']==0)
            {
                OauthAccessToken::where('user_id', $request->id)->update(['expires_at' => getNow(), 'revoked' => 1]);
            }

            DB::commit();
            return $this->set_response($user, 200,'success', ['User updated successfully']);
        } catch (\Exception $e) {
            DB::rollback();
            $logMessage = formatCommonErrorLogMessage($e);
            writeToLog($logMessage, 'error');
            return $this->set_response(null,  422,'error', ['Something went wrong. Please try again later!']);
        }
    }


    // public function deleteUser($userId)
    // {
    //     if (DB::table('users')->where('id', $userId)->count()==0) {
    //         return $this->set_response(null, 422, 'failed', ["User not found!"]);
    //     }
    //     User::find($userId)->delete();
    //     return $this->set_response(null,  200,'success', ['User deleted successfully']);
    // }


    public function filterData( Request $request ) {
        $role_list = Role::select('id as value', 'name as label')->get();
        $filter = [
            'role_list' => $role_list,
        ];
        return $this->set_response( $filter,  200, 'success', [ 'filter list' ] );
    }
}
