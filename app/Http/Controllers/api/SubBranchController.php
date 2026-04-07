<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class SubBranchController extends Controller
{
    public function createSubbranch(Request $request)
    {
        $rules = [
            'customer_name' => 'required|string|max:80',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|numeric|digits:10|unique:users,phone',
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/'
            ],
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'avatar' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp,gif',
                'mimetypes:image/jpeg,image/png,image/webp,image/gif',
                'max:2048' // 2MB limit
            ],
        ];
        $messages = [
            'password.regex' =>
            'Password must contain at least one uppercase letter, one lowercase letter, one number and one special character (@$!%*?&).',
            'password.min' => 'Password must be at least 8 characters.',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // ✅ Create user
        $customer           = new User();
        $customer->name     = $request->customer_name;
        $customer->email    = $request->email;
        $customer->phone    = $request->phone;
        $customer->password = Hash::make($request->password);

        if ($request->hasFile('avatar')) {
            $path                    = $request->file('avatar')->store('staff', 'public');
            $customer->profile_image = $path;
        }

        $customer->role   = 'sub-admin';
        $customer->status = 1;
        $customer->save();

        // ✅ Create user details
        $customerDetail          = new UserDetail();
        $customerDetail->user_id = $customer->id;
        $customerDetail->country = $request->country;
        $customerDetail->city    = $request->city;
        $customerDetail->address = $request->address;
        $customerDetail->save();

        // ✅ Store dynamic permissions
        if ($request->has('modules') && is_array($request->modules)) {
            foreach ($request->modules as $moduleData) {
                UserPermission::create([
                    'user_id'   => $customer->id,
                    'module_id' => $moduleData['module_id'],
                    'view'      => isset($moduleData['view']),
                    'add'       => isset($moduleData['insert']),
                    'edit'      => isset($moduleData['edit']),
                    'delete'    => isset($moduleData['delete']),
                ]);
            }
        }

        return response()->json([
            'status'  => true,
            'message' => 'Sub Branch created successfully',
        ]);
    }

    public function updateSubbranch(Request $request)
    {
        $rules =  [
            'id'            => 'required|exists:users,id',
            'customer_name' => 'required|string|max:80',
            'email'         => 'required|email|unique:users,email,' . $request->id,
            'phone'         => 'required|digits:10|numeric|unique:users,phone,' . $request->id,
            'password' => [
                'nullable',
                'string',
                'min:8',
                'max:255',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/'
            ],
            'country'       => 'nullable|string|max:100',
            'city'          => 'nullable|string|max:100',
            'address'       => 'nullable|string|max:500',

            // ✅ STRICT IMAGE VALIDATION
            'avatar' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp,gif',
                'mimetypes:image/jpeg,image/png,image/webp,image/gif',
                'max:2048' // 2MB limit
            ],
        ];
        $messages = [
            'password.regex' =>
            'Password must contain at least one uppercase letter, one lowercase letter, one number and one special character (@$!%*?&).',
            'password.min' => 'Password must be at least 8 characters.',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // ✅ Fetch existing user
        $customer        = User::find($request->id);
        $customer->name  = $request->customer_name;
        $customer->email = $request->email;
        $customer->phone = $request->phone;

        // ✅ Only update password if provided
        if (! empty($request->password)) {
            $customer->password = Hash::make($request->password);
        }

        if ($request->hasFile('avatar')) {

            $file = $request->file('avatar');

            $allowedMime = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];

            if (!in_array($file->getMimeType(), $allowedMime)) {
                return response()->json([
                    'status' => false,
                    'errors' => ['avatar' => ['Only JPG, JPEG, PNG or WEBP images allowed']]
                ], 422);
            }

            $path = $file->store('staff', 'public');
            $customer->profile_image = $path;
        }

        // ✅ Do not change role if user is admin
        if ($customer->role !== 'admin') {
            $customer->role = 'sub-admin';
        }

        $customer->status = 1;
        $customer->save();

        // ✅ Update or create user details
        UserDetail::updateOrCreate(
            ['user_id' => $customer->id],
            [
                'country' => $request->country,
                'city'    => $request->city,
                'address' => $request->address,
            ]
        );

        // ✅ Remove old permissions if needed (optional)
        UserPermission::where('user_id', $customer->id)->delete();

        // ✅ Reinsert new permissions
        if ($request->has('modules') && is_array($request->modules)) {
            foreach ($request->modules as $moduleData) {
                UserPermission::create([
                    'user_id'   => $customer->id,
                    'module_id' => $moduleData['module_id'],
                    'view'      => isset($moduleData['view']),
                    'add'       => isset($moduleData['insert']),
                    'edit'      => isset($moduleData['edit']),
                    'delete'    => isset($moduleData['delete']),

                ]);
            }
        }

        return response()->json(['status' => true, 'message' => 'Sub Branch updated successfully'], 200);
    }

    public function deleteSubbranch($id)
    {
        $branch = User::find($id);

        if (! $branch) {
            return response()->json([
                'status'  => false,
                'message' => 'Branch not found.',
            ], 404);
        }

        // 🚫 Prevent deleting admin account
        if ($branch->role === 'admin') {
            return response()->json([
                'status'  => false,
                'message' => 'Admin accounts cannot be deleted.',
            ], 403);
        }

        // ✅ If sub-admin branch, dynamically check all tables
        if ($branch->role === 'sub-admin') {
            $tables       = DB::select('SHOW TABLES');
            $databaseName = DB::getDatabaseName();
            $keyName      = 'Tables_in_' . $databaseName;

            foreach ($tables as $table) {
                $tableName = $table->$keyName;

                // Skip system tables if any (optional)
                if (in_array($tableName, ['migrations', 'password_resets', 'personal_access_tokens'])) {
                    continue;
                }

                // Check if table has a branch_id column
                if (Schema::hasColumn($tableName, 'branch_id')) {
                    $exists = DB::table($tableName)
                        ->where('branch_id', $id)
                        ->where(function ($query) use ($tableName) {
                            if (Schema::hasColumn($tableName, 'isDeleted')) {
                                $query->where('isDeleted', 0);
                            }
                        })
                        ->exists();

                    if ($exists) {
                        return response()->json([
                            'status'  => false,
                            'message' => "This branch cannot be deleted. Records exist in the '{$tableName}' table.",
                        ], 403);
                    }
                }
            }
        }

        // ✅ Safe to delete
        $branch->isDeleted = 1;
        $branch->save();

        $userDetail = UserDetail::where('user_id', $id)->first();
        if ($userDetail) {
            $userDetail->isDeleted = 1;
            $userDetail->save();
        }

        return response()->json([
            'status'  => true,
            'message' => 'Branch deleted successfully.',
        ], 200);
    }

    public function getSubbranch($id)
    {
        $subbranch = User::with('userDetail')
            ->where('isDeleted', 0)
            ->where('id', $id)
            ->first(); // ✅ changed from get() to first()

        return response()->json(['status' => true, 'data' => $subbranch], 200);
    }

    // public function getAllSubbranch()
    // {
    //     // dd('here');
    //     $subbranch = User::with('userDetail')->where('isDeleted', 0)->whereIn('role', ['sub-admin', 'admin'])
    //     ->orderBy('created_at', 'desc')->get();
    //     // dd($subbranch);
    //     return response()->json(['status' => true, 'data' => $subbranch], 200);
    // }

    public function getAllSubbranch(Request $request)
{
    $page    = $request->input('page', 1);
    $perPage = $request->input('per_page', 10);
    $search  = $request->input('search', '');

    $query = User::with('userDetail')
        ->where('isDeleted', 0)
        ->whereIn('role', ['sub-admin', 'admin']);

    // ✅ SEARCH FILTER
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
              ->orWhere('email', 'LIKE', "%{$search}%")
              ->orWhere('phone', 'LIKE', "%{$search}%")
              ->orWhere('role', 'LIKE', "%{$search}%")
              ->orWhereHas('userDetail', function ($uq) use ($search) {
                    $uq->where('country', 'LIKE', "%{$search}%")
                       ->orWhere('city', 'LIKE', "%{$search}%");
              });
        });
    }

    $total = $query->count();

    $subbranch = $query
        ->orderBy('created_at', 'desc')
        ->skip(($page - 1) * $perPage)
        ->take($perPage)
        ->get();

    return response()->json([
        'status' => true,
        'data' => $subbranch,
        'pagination' => [
            'current_page' => (int)$page,
            'per_page' => (int)$perPage,
            'total' => $total,
            'last_page' => ceil($total / $perPage),
            'from' => $total ? (($page - 1) * $perPage) + 1 : 0,
            'to' => (($page - 1) * $perPage) + $subbranch->count(),
        ]
    ]);
}
}
