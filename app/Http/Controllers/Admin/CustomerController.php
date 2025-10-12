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
                $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($customer->name) . '&background=random&color=fff&size=40';
                if($customer->avatar) {
                    // Nếu avatar bắt đầu bằng 'avatars/' thì là file trong storage
                    if (str_starts_with($customer->avatar, 'avatars/')) {
                        $avatarUrl = asset('storage/' . $customer->avatar);
                    } 
                    // Nếu bắt đầu bằng 'storage/' thì đã có đường dẫn đầy đủ
                    elseif (str_starts_with($customer->avatar, 'storage/')) {
                        $avatarUrl = asset($customer->avatar);
                    }
                    // Nếu là URL đầy đủ thì giữ nguyên
                    elseif (str_starts_with($customer->avatar, 'http')) {
                        $avatarUrl = $customer->avatar;
                    }
                    // Trường hợp khác, thêm asset()
                    else {
                        $avatarUrl = asset('storage/avatars/' . basename($customer->avatar));
                    }
                }
                return '<div class="d-flex align-items-center gap-2">
                    <img src="' . $avatarUrl . '" 
                         class="rounded-circle border" 
                         width="40" 
                         height="40" 
                         style="object-fit: cover; min-width: 40px; min-height: 40px;"
                         alt="' . $customer->name . '"
                         onerror="this.src=\'https://ui-avatars.com/api/?name=' . urlencode($customer->name) . '&background=6c757d&color=fff&size=40\'">
                    <div>
                        <div class="fw-semibold">' . $customer->name . '</div>
                        <div class="small text-muted">' . $customer->email . '</div>
                    </div>
                </div>';
            })
            ->addColumn('status_badge', function ($customer) {
                return $customer->status == '1'
                    ? '<span class="badge bg-success text-white p-1"><i class="fas fa-check"></i> Active</span>'
                    : '<span class="badge bg-danger text-white p-1"><i class="fas fa-ban"></i> Blocked</span>';
            })
            ->addColumn('actions', function ($customer) {
                $editButton = '<button type="button" class="btn btn-sm btn-outline-warning edit-customer" data-toggle="modal" data-target="#editCustomerModal" data-id="' . $customer->id . '">
                    <i class="fas fa-pen"></i>
                </button>';

                $toggleButton = $customer->status == 1
                    ? '<button type="button" class="btn btn-sm btn-outline-secondary toggle-status" data-id="' . $customer->id . '" data-status="0" title="Khóa tài khoản">
                        <i class="fas fa-lock"></i>
                    </button>'
                    : '<button type="button" class="btn btn-sm btn-outline-success toggle-status" data-id="' . $customer->id . '" data-status="1" title="Mở khóa tài khoản">
                        <i class="fas fa-unlock"></i>
                    </button>';

                $deleteButton = '<button type="button" class="btn btn-sm btn-outline-danger delete-customer" data-toggle="modal" data-target="#deleteCustomerModal" data-id="' . $customer->id . '">
                    <i class="fas fa-trash me-2"></i>
                </button>';
                return $editButton . ' ' . $toggleButton . ' ' . $deleteButton;
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
        } catch (\Exception $e) {
            \Log::info($e->getMessage());
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi thêm khách hàng!',
                    'errors' => $e->getMessage(),
                    'type' => 'danger'
                ], 422);
            }
            return redirect()->back()->withErrors(['error' => 'Có lỗi xảy ra!'])->withInput();
        }
    }

    public function show($id)
    {
        try {
            $customer = Customer::with('ward.district.province')->findOrFail($id);

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
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi cập nhật khách hàng!',
                    'errors' => $e->getMessage(),
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
