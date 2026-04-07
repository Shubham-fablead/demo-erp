<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\CustomInvoice;
use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /* ---------------------------------------------------------
       Helper: Resolve Branch ID
    --------------------------------------------------------- */
    private function resolveBranchId($authUser, Request $request)
    {
        if ($authUser->role === 'staff' && $authUser->branch_id) {
            return $authUser->branch_id;
        }

        if ($authUser->role === 'admin' && ! empty($request->selectedSubAdminId)) {
            return (int) $request->selectedSubAdminId;
        }

        return $authUser->id;
    }

    /* ---------------------------------------------------------
       Get All Customers
    --------------------------------------------------------- */
    // public function getAllCustomer(Request $request)
    // {
    //     $user        = Auth::guard('api')->user();
    //     $branchId    = $this->resolveBranchId($user, $request);

    //     $query = User::select('id', 'name', 'email', 'phone', 'profile_image', 'gst_number', 'pan_number')
    //         ->where('role', 'customer')
    //         ->where('isDeleted', 0)
    //         ->where('branch_id', $branchId)
    //         ->with('details:country,user_id,city');

    //     // if ($user->role === 'staff') {
    //     //     $query->where('created_by', $user->id);
    //     // } else {
    //     //     $query->where('branch_id', $branchId);
    //     // }

    //     $customers = $query->orderByDesc('id')->get()->map(function ($customer) {
    //         return [
    //             'id'                => $customer->id,
    //             'name'              => $customer->name,
    //             'email'             => $customer->email,
    //             'phone'             => $customer->phone,
    //             'gst_number'        => $customer->gst_number,
    //             'pan_number'        => $customer->pan_number,
    //             'profile_image'     => $customer->profile_image,
    //             'profile_image_url' => $customer->profile_image_url,
    //             'country'           => optional($customer->details)->country ?? '',
    //             'city'              => optional($customer->details)->city ?? '',
    //         ];
    //     });

    //     return response()->json(['status' => true, 'data' => $customers]);
    // }

    public function getAllCustomer(Request $request)
    {
        $user     = Auth::guard('api')->user();
        $branchId = $this->resolveBranchId($user, $request);

        $perPage = $request->input('per_page', 10); // default 10
        $search  = $request->input('search', '');

        $query = User::select('id', 'name', 'email', 'phone', 'profile_image', 'gst_number', 'pan_number')
            ->where('role', 'customer')
            ->where('isDeleted', 0)
            ->where('branch_id', $branchId)
            ->with('details:country,user_id,city');

        // Apply search if provided
        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('gst_number', 'LIKE', "%{$search}%")
                    ->orWhere('pan_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('details', function ($subQ) use ($search) {
                        $subQ->where('country', 'LIKE', "%{$search}%")
                            ->orWhere('city', 'LIKE', "%{$search}%");
                    });
            });
        }

        $customers = $query->orderByDesc('id')->paginate($perPage);

        // Transform data
        $transformed = $customers->map(function ($customer) {
            return [
                'id'                => $customer->id,
                'name'              => $customer->name,
                'email'             => $customer->email,
                'phone'             => $customer->phone,
                'gst_number'        => $customer->gst_number,
                'pan_number'        => $customer->pan_number,
                'profile_image'     => $customer->profile_image,
                'profile_image_url' => $customer->profile_image_url,
                'country'           => optional($customer->details)->country ?? '',
                'city'              => optional($customer->details)->city ?? '',
            ];
        });

        return response()->json([
            'status'     => true,
            'data'       => $transformed,
            'pagination' => [
                'total'        => $customers->total(),
                'per_page'     => $customers->perPage(),
                'current_page' => $customers->currentPage(),
                'last_page'    => $customers->lastPage(),
                'from'         => $customers->firstItem(),
                'to'           => $customers->lastItem(),
            ],
        ]);
    }

    /* ---------------------------------------------------------
       Get Customer By ID
    --------------------------------------------------------- */
    public function getCustomerById($id)
    {
        $customer = User::find($id);

        return $customer
            ? response()->json(['status' => true, 'customer' => $customer])
            : response()->json(['status' => false, 'error' => 'Customer not found'], 404);
    }

    /* ---------------------------------------------------------
       Create Customer
    --------------------------------------------------------- */
    public function createCustomer(Request $request)
    {
        $authUser = Auth::guard('api')->user();
        $branchId = $this->resolveBranchId($authUser, $request);

        $request->merge([
            'state_code' => explode(' - ', $request->state_code)[0]
        ]);
        
        $validator = Validator::make($request->all(), [

            'customer_name' => [
                'required',
                'string',
                'max:80',
            ],

            'email'         => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->where(function ($query) use ($branchId) {
                    return $query->where('branch_id', $branchId)
                        ->where('isDeleted', 0);
                }),
            ],

            'phone'         => [
                'required',
                'digits:10',           // strictly 10 digits
                'regex:/^[0-9]{10}$/', // numbers only
                Rule::unique('users', 'phone')->where(function ($query) use ($branchId) {
                    return $query->where('branch_id', $branchId)
                        ->where('isDeleted', 0);
                }),
            ],

            'gst_number'    => [
                'nullable',
                'string',
                'size:15',
            ],

            'pan_number'    => [
                'nullable',
                'string',
                'size:10',
            ],

            'country'       => 'nullable|string|max:100',
            'city'          => 'nullable|string|max:100',

            'state_code' => 'nullable|digits_between:1,3',

            'address'       => 'nullable|string|max:500',

            'avatar'        => [
                'nullable',
                'file',
                'image',                       // ensures image only
                'mimes:jpeg,jpg,png,webp,gif', // restrict extensions
                'max:2048',                    // 2MB
            ],

        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        DB::transaction(function () use ($request, $authUser, $branchId) {

            $customer = new User();
            $customer->fill([
                'name'       => $request->customer_name,
                'email'      => $request->email,
                'phone'      => $request->phone,
                'gst_number' => $request->gst_number,
                'pan_number' => $request->pan_number,
                'role'       => 'customer',
                'state_code' => $request->state_code,
                'branch_id'  => $branchId,
                'created_by' => $authUser->id,
                'status'     => 1,
            ]);

            if ($request->hasFile('avatar')) {

                if (! $request->file('avatar')->isValid()) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Invalid image file.',
                    ], 422);
                }

                $customer->profile_image = $request->file('avatar')
                    ->store('customer', 'public');
            }

            if ($request->hasFile('avatar')) {
                $customer->profile_image = $request->file('avatar')->store('customer', 'public');
            }

            $customer->save();

            UserDetail::create([
                'user_id' => $customer->id,
                'country' => $request->country,
                'city'    => $request->city,
                'address' => $request->address,
            ]);
            // 🔔 CREATE NOTIFICATION
            Notification::create([
                'user_id'   => $customer->id, // admin who created
                'type'      => 'customer',
                'title'     => 'New Customer Created',
                'message'   => $customer->name . ' has been added successfully.',
                'link'      => '/customer-view/' . $customer->id,
                'is_read'   => 0,
                'is_sound'  => 0,
                'branch_id' => $branchId,
            ]);
        });

        return response()->json(['status' => true, 'message' => 'Customer created successfully!']);
    }

    public function getCustomer($id)
    {
        // Find customer and include details
        $customer = User::with('details')->find($id);

        if (! $customer) {
            return response()->json(['status' => false, 'error' => 'Customer not found'], 404);
        }

        return response()->json([
            'status'   => true,
            'customer' => $customer,
        ]);
    }

    /* ---------------------------------------------------------
       Delete Customer (Soft Delete)
    --------------------------------------------------------- */
    public function deleteCustomer(Request $request, $id)
    {
        $customer = User::find($id);

        if (! $customer || $customer->role !== 'customer') {
            return response()->json(['status' => false, 'error' => 'Customer not found'], 404);
        }

        if (Order::where('user_id', $id)->exists()) {
            return response()->json(['status' => false, 'error' => 'Customer has orders'], 400);
        }

        if (CustomInvoice::where('customer_id', $id)->exists()) {
            return response()->json(['status' => false, 'error' => 'Customer linked to invoice'], 400);
        }

        DB::transaction(function () use ($customer, $id) {
            UserDetail::where('user_id', $id)->update(['isDeleted' => 1]);
            $customer->update(['isDeleted' => 1]);
        });

        return response()->json(['status' => true, 'message' => 'Customer deleted successfully!']);
    }

    /* ---------------------------------------------------------
       Update Customer
    --------------------------------------------------------- */
    public function updateCustomer(Request $request, $id)
    {
        $customer = User::findOrFail($id);
        $branchId = $customer->branch_id;

        $request->merge([
            'state_code' => explode(' - ', $request->state_code)[0]
        ]);
        
        $validator = Validator::make($request->all(), [

            'customer_name' => [
                'required',
                'string',
                'max:80',
            ],

            'email'         => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->ignore($id)->where(function ($query) use ($branchId) {
                    return $query->where('branch_id', $branchId)
                        ->where('isDeleted', 0);
                }),
            ],

            'phone'         => [
                'required',
                'digits:10',           // exactly 10 digits
                'regex:/^[0-9]{10}$/', // numbers only
                Rule::unique('users', 'phone')->ignore($id)->where(function ($query) use ($branchId) {
                    return $query->where('branch_id', $branchId)
                        ->where('isDeleted', 0);
                }),
            ],

            'gst_number'    => 'nullable|string|max:15',

            'pan_number'    => 'nullable|string|max:10',

            'country'       => 'nullable|string|max:100',

            'city'          => 'nullable|string|max:100',

            'address'       => 'nullable|string|max:500',

            'state_code'    => 'nullable|numeric|digits_between:1,3',

            'avatar'        => [
                'nullable',
                'file',
                'image',                       // ensures only images
                'mimes:jpeg,png,jpg,webp,gif', // blocks pdf, csv, xls
                'max:2048',
            ],

        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $customer->update([
            'name'       => $request->customer_name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'gst_number' => $request->gst_number,
            'pan_number' => $request->pan_number,
            'state_code' => $request->state_code,
        ]);

        // if ($request->hasFile('avatar')) {
        //     $customer->update([
        //         'profile_image' => $request->file('avatar')->store('customer', 'public'),
        //     ]);
        // }
        if ($request->hasFile('avatar')) {

            $file = $request->file('avatar');

            if (! $file->isValid()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid image file',
                ], 422);
            }

            $path = $file->store('customer', 'public');

            $customer->update([
                'profile_image' => $path,
            ]);
        }

        UserDetail::updateOrCreate(
            ['user_id' => $id],
            [
                'country' => $request->country,
                'city'    => $request->city,
                'address' => $request->address,
            ]
        );

        return response()->json(['status' => true, 'message' => 'Customer updated successfully!']);
    }

    /* ---------------------------------------------------------
       Customer Profile
    --------------------------------------------------------- */
    public function getCustomerProfile($id)
    {
        $user = User::with('userDetail')->find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json([
            'name'          => $user->name,
            'email'         => $user->email,
            'phone'         => $user->phone,
            'gst_number'    => $user->gst_number,
            'pan_number'    => $user->pan_number,
            'role'          => $user->role,
            'profile_image' => $user->profile_image,
            'state_code'    => $user->state_code,
            'address'       => optional($user->userDetail)->address ?? 'N/A',
            'city'          => optional($user->userDetail)->city ?? 'N/A',
            'country'       => optional($user->userDetail)->country ?? 'N/A',
        ]);
    }
}
