<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\User as Customer;
use App\Models\Ward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.customers.index');
    }

    public function data(Request $request)
    {
        $query = Customer::query();
        // Lọc role_id nếu muốn
        $query->where('role_id', 3);
        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addColumn('customer_info', function ($customer) {
                // Nếu có avatar và là Cloudinary URL thì dùng, không thì dùng UI Avatars
                if ($customer->avatar) {
                    $avatarUrl = $customer->avatar;
                } else {
                    $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($customer->name) . '&background=random&color=fff&size=40';
                }
                
                return '<div class="d-flex align-items-center"style="gap: 10px;">
                    <img src="' . $avatarUrl . '" 
                         class="rounded-circle border" 
                         width="40" 
                         height="40" 
                         style="object-fit: cover; min-width: 40px; min-height: 40px;"
                         alt="' . $customer->name . '"
                         onerror="this.src=\'https://ui-avatars.com/api/?name=' . urlencode($customer->name) . '&background=6c757d&color=fff&size=40\'">
                    <div class="text-left">
                        <div class="fw-semibold">' . $customer->name . '</div>
                        <div class="small text-muted">' . $customer->email . '</div>
                    </div>
                </div>';
            })
            ->addColumn('status_badge', function ($customer) {
                return $customer->status == '1'
                    ? '<span class="badge bg-success text-white p-1"><i class="fas fa-check mr-1"></i>Mở tài khoản</span>'
                    : '<span class="badge bg-danger text-white p-1"><i class="fas fa-ban mr-1"></i>Khóa tài khoản</span>';
            })
            ->addColumn('actions', function ($customer) {
                $editButton = '';
                if(auth()->user()->can('edit customers')) {
                   $editButton = '<button type="button" class="btn btn-sm btn-outline-warning edit-customer" data-toggle="modal" data-target="#editCustomerModal" data-id="' . $customer->id . '">
                    <i class="fas fa-edit"></i>
                </button>'; 
                }
                $toggleButton = '';
                if(auth()->user()->can('lock/unlock customers')) {
                $toggleButton = $customer->status == 1
                    ? '<button type="button" class="btn btn-sm btn-outline-secondary toggle-status" data-id="' . $customer->id . '" data-status="0" title="Khóa tài khoản">
                        <i class="fas fa-lock"></i>
                    </button>'
                    : '<button type="button" class="btn btn-sm btn-outline-success toggle-status" data-id="' . $customer->id . '" data-status="1" title="Mở khóa tài khoản">
                        <i class="fas fa-unlock"></i>
                    </button>';
                }
                $deleteButton = '';
                if(auth()->user()->can('delete customers')) {
                    $deleteButton = '<button type="button" class="btn btn-sm btn-outline-danger delete-customer" data-toggle="modal" data-target="#deleteCustomerModal" data-id="' . $customer->id . '">
                        <i class="fas fa-trash"></i>
                    </button>';
                }
                return '<div class="btn-action">' . $editButton . ' ' . $toggleButton . ' ' . $deleteButton . '</div>';
            })
            ->rawColumns(['customer_info', 'status_badge', 'actions']) // cho phép render HTML
            ->make(true);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'username' => ['nullable', 'string', 'max:50', 'unique:users,username'],
                'name' => ['required', 'string', 'max:120', 'regex:/^[\pL\s]+$/u'],
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/'
                ],
                'phone' => ['required', 'string', 'max:15'],
                'address' => ['required', 'string', 'max:255'],
                // 'ward_id' => ['required', 'integer', 'exists:wards,id'],
                'status' => ['required', 'integer', 'in:0,1'],
            ], [
                'password.min' => 'Mật khẩu phải có tối thiểu 8 ký tự.',
                'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
                'password.regex' => 'Mật khẩu phải có ít nhất 1 chữ hoa, 1 chữ thường, 1 số và 1 ký tự đặc biệt.',
                'email.unique' => 'Email đã được sử dụng.',
                'username.unique' => 'Tên người dùng đã được sử dụng.',
                // 'ward_id.exists' => 'Xã/phường không hợp lệ.'
                'name.regex' => "Tên người dùng chỉ được chứa chữ cái và khoảng trắng.",
            ]);

            if (empty($validated['username'])) {
                $validated['username'] = Str::slug($validated['name'], '');
            }
            $customer = Customer::create([
                'username' => $validated['username'],
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'email_verified_at' => now(),
                // 'ward_id' => $validated['ward_id'],
                'role_id' => 3, // Customer role
                'status' => $validated['status'],
                'avatar' => 'storage/default-avatar.png',
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Thêm khách hàng thành công!',
                    'type' => 'success'
                ]);
            }

            return redirect()->route('admin.customers.index')->with('success', 'Thêm khách hàng thành công!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ!',
                    'errors' => $e->errors(),
                    'type' => 'danger'
                ], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::info('Lỗi: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi thêm khách hàng!',
                    'errors' => ['general' => [$e->getMessage()]],
                    'type' => 'danger'
                ], 422);
            }
            return redirect()->back()->withErrors(['error' => 'Có lỗi xảy ra!'])->withInput();
        }
    }

    public function show($id)
    {
        try {
            $customer = Customer::findOrFail($id);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'customer' => $customer
                ]);
            }

            return response()->json(['error' => 'Invalid request'], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy khách hàng!'
            ], 404);
        }
    }

    public function update(Request $request)
    {
        try {
            $customer = Customer::findOrFail($request->id);
            $validated = $request->validate([
                'username' => ['nullable', 'string', 'max:50', 'unique:users,username,' . $customer->id],
                'name' => ['required', 'string', 'max:120', 'regex:/^[\pL\s]+$/u'],
                'email' => ['required', 'email', 'unique:users,email,' . $customer->id],
                'phone' => ['required', 'string', 'max:15'],
                'address' => ['required', 'string', 'max:255'],
                // 'ward_id' => ['required', 'integer', 'exists:wards,id'],
                'status' => ['required', 'integer', 'in:0,1'],
            ], [
                'email.unique' => 'Email đã được sử dụng.',
                'username.unique' => 'Tên người dùng đã được sử dụng.',
                // 'ward_id.exists' => 'Xã/phường không hợp lệ.'
            ]);

            if (empty($validated['username'])) {
                $validated['username'] = Str::slug($validated['name'], '');
            }

            $customer->fill($validated);
            $customer->save();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật khách hàng thành công!',
                    'type' => 'success'
                ]);
            }

            return redirect()->route('admin.customers.index')->with('success', 'Cập nhật khách hàng thành công!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ!',
                    'errors' => $e->errors(),
                    'type' => 'danger'
                ], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi cập nhật khách hàng!',
                    'errors' => ['general' => [$e->getMessage()]],
                    'type' => 'danger'
                ], 422);
            }
            return redirect()->back()->withErrors(['error' => 'Có lỗi xảy ra!'])->withInput();
        }
    }

    public function toggleStatus(Request $request)
    {
        try {
            $customer = Customer::findOrFail($request->id);
            $newStatus = $request->status;

            $customer->status = $newStatus;
            $customer->save();

            $message = $newStatus == 1 ? 'Mở khóa tài khoản thành công!' : 'Khóa tài khoản thành công!';

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'type' => 'success'
                ]);
            }

            return redirect()->route('admin.customers.index')->with('success', $message);
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi thay đổi trạng thái tài khoản!',
                    'type' => 'danger'
                ], 500);
            }
            return redirect()->back()->with('error', 'Có lỗi xảy ra!');
        }
    }

    public function destroy(Request $request)
    {
        try {
            $customer = Customer::findOrFail($request->id);
            if ($customer->orders()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Khách hàng có đơn hàng nên không thể xóa!',
                    'type' => 'danger'
                ], 422);
            }
            $customer->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Xóa khách hàng thành công!',
                    'type' => 'success'
                ]);
            }

            return redirect()->route('admin.customers.index')->with('success', 'Customer deleted successfully');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi xóa khách hàng!',
                    'type' => 'danger'
                ], 500);
            }
            return redirect()->back()->with('error', 'Có lỗi xảy ra!');
        }
    }
}
