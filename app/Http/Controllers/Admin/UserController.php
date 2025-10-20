<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Thống kê cho dashboard
        $stats = [
            'total_users' => User::where('role_id', 2)->count(),
            'active_users' => User::where('status', 1)->where('role_id', 2)->count(),
            'blocked_users' => User::where('status', 0)->where('role_id', 2)->count(),
            'new_staff_users' => User::where('role_id', 2)
                ->where('created_at', '>=', Carbon::now()->subMonth())
                ->count(),
        ];

        return view('admin.users.index', compact('stats'));
    }

    public function data(Request $request)
    {
        $query = User::query();

        // Lọc chỉ nhân viên (role_id = 2)
        $query->where('role_id', 2);

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('user_info', function ($user) {
                $image = $user->avatar
                    ? '<img src="' . asset($user->avatar) . '" width="50" height="50" style="object-fit: cover; margin-right:10px; border-radius:10px">'
                    : '<div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 72px; height: 72px;  margin-right:10px;"><i class="fas fa-image text-muted"></i></div>';

                return '
                <div class="d-flex align-items-center">
                    ' . $image . '
                    <div class="mr-2" style="text-align:left;">
                        <div class="fw-bold">
                            <span style="display:inline-block; word-wrap:break-word; white-space:normal;">' . htmlspecialchars($user->name) . '</span>
                        </div>
                        <small class="text-muted">ID: ' . $user->id . '</small>
                    </div>
                </div>';
            })
            ->addColumn('status_badge', function ($user) {
                return $user->status == '1'
                    ? '<span class="badge bg-success text-white p-1"><i class="fas fa-check"></i> Active</span>'
                    : '<span class="badge bg-danger text-white p-1"><i class="fas fa-ban"></i> Blocked</span>';
            })
            ->addColumn('role_badge', function ($user) {
                $roleMap = [
                    1 => ['Admin', 'primary'],
                    2 => ['Staff', 'info'],
                    3 => ['Customer', 'secondary']
                ];
                [$label, $color] = $roleMap[$user->role_id] ?? ['Unknown', 'dark'];
                return "<span class=\"badge bg-{$color}\">{$label}</span>";
            })
            ->addColumn('actions', function ($user) {
                $editButton = '<button type="button" class="btn btn-sm btn-outline-warning edit-user" style="margin-right:6px" data-toggle="modal" data-target="#editUserModal" data-id="' . $user->id . '">
                    <i class="fas fa-edit"></i>
                </button>';

                $toggleButton = $user->status == 1
                    ? '<button type="button" class="btn btn-sm btn-outline-secondary toggle-status" style="margin-right:6px" data-id="' . $user->id . '" data-status="0" title="Khóa tài khoản">
                        <i class="fas fa-lock"></i>
                    </button>'
                    : '<button type="button" class="btn btn-sm btn-outline-success toggle-status" style="margin-right:6px" data-id="' . $user->id . '" data-status="1" title="Mở khóa tài khoản">
                        <i class="fas fa-unlock"></i>
                    </button>';

                $deleteButton = '<button type="button" class="btn btn-sm btn-outline-danger delete-user" data-toggle="modal" data-target="#deleteUserModal" data-id="' . $user->id . '">
                    <i class="fas fa-trash me-2"></i>
                </button>';

                return $editButton . ' ' . $toggleButton . ' ' . $deleteButton;
            })
            ->rawColumns(['user_info', 'status_badge', 'role_badge', 'actions'])
            ->make(true);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
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
                'status' => ['required', 'integer', 'in:0,1'],
            ], [
                'password.min' => 'Mật khẩu phải có tối thiểu 8 ký tự.',
                'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
                'password.regex' => 'Mật khẩu phải có ít nhất 1 chữ hoa, 1 chữ thường, 1 số và 1 ký tự đặc biệt.',
                'email.unique' => 'Email đã được sử dụng.',
            ]);

            $user = User::create([
                'username' => Str::slug($validated['name']),
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'role_id' => 2, // Staff role
                'status' => $validated['status'],
                'email_verified_at' => now(),
                'avatar' => 'storage/default-avatar.png',
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Thêm nhân viên thành công!',
                    'type' => 'success'
                ]);
            }

            return redirect()->route('admin.users.index')->with('success', 'Thêm nhân viên thành công!');
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
            Log::error('Error adding user: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi thêm nhân viên!',
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
            $user = User::where('role_id', 2)->findOrFail($id);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'user' => $user
                ]);
            }

            return response()->json(['error' => 'Invalid request'], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy nhân viên!'
            ], 404);
        }
    }

    public function update(Request $request)
    {
        try {
            $user = User::where('role_id', 2)->findOrFail($request->id);
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:120', 'regex:/^[\pL\s]+$/u'],
                'email' => ['required', 'email', 'unique:users,email,' . $user->id],
                'phone' => ['required', 'string', 'max:15'],
                'address' => ['required', 'string', 'max:255'],
                'status' => ['required', 'integer', 'in:0,1'],
            ], [
                'email.unique' => 'Email đã được sử dụng.',
            ]);

            $user->fill($validated);
            $user->save();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật nhân viên thành công!',
                    'type' => 'success'
                ]);
            }

            return redirect()->route('admin.users.index')->with('success', 'Cập nhật nhân viên thành công!');
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
                    'message' => 'Có lỗi xảy ra khi cập nhật nhân viên!',
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
            $user = User::where('role_id', 2)->findOrFail($request->id);
            $newStatus = $request->status;

            $user->status = $newStatus;
            $user->save();

            $message = $newStatus == 1 ? 'Mở khóa tài khoản thành công!' : 'Khóa tài khoản thành công!';

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'type' => 'success'
                ]);
            }

            return redirect()->route('admin.users.index')->with('success', $message);
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

    public function updateRole(Request $request)
    {
        try {
            $user = User::findOrFail($request->id);
            $validated = $request->validate([
                'role_id' => ['required', 'integer', 'in:1,2,3'],
            ]);

            $user->role_id = $validated['role_id'];
            $user->save();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật quyền thành công!',
                    'type' => 'success'
                ]);
            }

            return redirect()->route('admin.users.index')->with('success', 'Cập nhật quyền thành công!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi cập nhật quyền!',
                    'type' => 'danger'
                ], 500);
            }
            return redirect()->back()->with('error', 'Có lỗi xảy ra!');
        }
    }

    public function destroy(Request $request)
    {
        try {
            $user = User::where('role_id', 2)->findOrFail($request->id);
            $user->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Xóa nhân viên thành công!',
                    'type' => 'success'
                ]);
            }

            return redirect()->route('admin.users.index')->with('success', 'Xóa nhân viên thành công!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi xóa nhân viên!',
                    'type' => 'danger'
                ], 500);
            }
            return redirect()->back()->with('error', 'Có lỗi xảy ra!');
        }
    }

    // API endpoint để lấy thống kê mới
    public function getStats()
    {
        try {
            $stats = [
                'total_users' => User::where('role_id', 2)->count(),
                'active_users' => User::where('status', 1)->where('role_id', 2)->count(),
                'blocked_users' => User::where('status', 0)->where('role_id', 2)->count(),
                'new_staff_users' => User::where('role_id', 2)
                    ->where('created_at', '>=', Carbon::now()->subMonth())
                    ->count(),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy thống kê!'
            ], 500);
        }
    }
}
