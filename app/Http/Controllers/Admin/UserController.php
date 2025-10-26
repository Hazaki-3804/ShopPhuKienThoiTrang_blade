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
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Support\Facades\Auth;

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

    public function editPermissions($id)
    {
        $user = User::where('role_id', 2)->findOrFail($id);

        // Optional guard: only users with proper permission can manage permissions
        // if (Auth::user() && !Auth::user()->can('manage permissions')) {
        //     abort(403);
        // }

        $permissions = Permission::orderBy('name')->get();
        // All effective permissions (direct + via roles)
        $userPermissionNames = $user->getPermissionNames();
        // Split for UI behavior
        $directPermissionNames = $user->getDirectPermissions()->pluck('name');
        $inheritedPermissionNames = $user->getPermissionsViaRoles()->pluck('name');

        return view('admin.users.permissions', [
            'user' => $user,
            'permissions' => $permissions,
            'userPermissionNames' => $userPermissionNames,
            'directPermissionNames' => $directPermissionNames,
            'inheritedPermissionNames' => $inheritedPermissionNames,
        ]);
    }

    public function updatePermissions(Request $request, $id)
    {
        $user = User::where('role_id', 2)->findOrFail($id);

        // if (Auth::user() && !Auth::user()->can('manage permissions')) {
        //     abort(403);
        // }

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $permissionNames = $validated['permissions'] ?? [];

        // Sync user permissions (detach all then attach provided)
        $user->syncPermissions($permissionNames);

        // Clear permission cache to apply immediately
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Kiểm tra nếu là AJAX request
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật quyền cho nhân viên '.$user->name.' thành công!',
                'type' => 'success'
            ]);
        }

        return redirect()
            ->route('admin.users.permissions.edit', ['id' => $user->id])
            ->with('success', 'Cập nhật quyền cho nhân viên '.$user->name.' thành công!');
    }

    // Base permissions applied to all staff via Role "Nhân viên"
    public function editBasePermissions()
    {
        // if (Auth::user() && !Auth::user()->can('manage permissions')) abort(403);
        $permissions = Permission::orderBy('name')->get();
        $role = SpatieRole::where('name', 'Nhân viên')->firstOrFail();
        $rolePermissionNames = $role->permissions->pluck('name');

        return view('admin.users.base-permissions', [
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissionNames' => $rolePermissionNames,
        ]);
    }

    public function updateBasePermissions(Request $request)
    {
        // if (Auth::user() && !Auth::user()->can('manage permissions')) abort(403);
        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $permissionNames = $validated['permissions'] ?? [];
        $role = SpatieRole::where('name', 'Nhân viên')->firstOrFail();
        $role->syncPermissions($permissionNames);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Kiểm tra nếu là AJAX request
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật quyền cơ bản cho toàn bộ nhân viên thành công!',
                'type' => 'success'
            ]);
        }

        return redirect()
            ->route('admin.users.base-permissions.edit')
            ->with('success', 'Cập nhật quyền cơ bản cho toàn bộ nhân viên thành công!');
    }

    public function data(Request $request)
    {
        $query = User::query();

        // Lọc chỉ nhân viên (role_id = 2)
        // $query->whereIn('role_id', [1, 2]);
        $query->where('role_id', 2);


        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('user_info', function ($user) {
                
                return '
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <img src="https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=random&color=fff&size=40" 
                         class="rounded-circle border" 
                         width="40" 
                         height="40" 
                         style="object-fit: cover; min-width: 40px; min-height: 40px;" 
                         alt="' . $user->name . '">
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
                    ? '<span class="badge bg-success text-white"><i class="fas fa-check"></i> Mở tài khoản</span>'
                    : '<span class="badge bg-danger text-white"><i class="fas fa-ban"></i> Khóa tài khoản</span>';
            })
            ->addColumn('role_badge', function ($user) {
                $roleMap = [
                    1 => ['Admin', 'primary','fas fa-user-shield'],
                    2 => ['Nhân viên', 'info','fas fa-user-tie'],
                    3 => ['Khách hàng', 'secondary','fas fa-user']
                ];
                [$label, $color,$icon] = $roleMap[$user->role_id] ?? ['Unknown', 'dark','fas fa-user'];
                return "<span class=\"badge bg-{$color}\"><i class=\"{$icon}\"></i> {$label}</span>";
            })
            ->addColumn('actions', function ($user) {
                $buttons = '
                <style>
/* 🌟 Nút ba chấm gọn gàng, hover có nền nhạt */
.btn-light.border-0 {
    padding: 12px;
    background: transparent !important;
    transition: background-color 0.2s ease, transform 0.2s ease;
}
.btn-light.border-0:hover {
    background-color: #f1f3f5 !important;
    transform: scale(1.05);
}
.btn-light.border-0:active {
    background-color: #e9ecef !important;
}

/* 🌈 Dropdown menu hiện đại */
.dropdown-menu {
    min-width: 180px;
    font-size: 0.9rem;
    border: none;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.12);
    background-color: #ffffff;
    padding: 6px 0;
    opacity: 0;
    transform: translateY(8px);
    visibility: hidden;
    transition: all 0.25s ease;
}

/* 🎬 Hiệu ứng xuất hiện mượt */
.show > .dropdown-menu {
    opacity: 1;
    transform: translateY(0);
    visibility: visible;
}

/* ✨ Mục trong menu */
.dropdown-item {
    padding: 8px 16px;
    color: #495057;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
    border-radius: 6px;
    margin: 2px 6px;
}
.dropdown-item i {
    width: 18px;
    text-align: center;
}
.dropdown-item:hover {
    background-color: #f1f3f5;
    color: #212529 !important;
    transform: translateX(2px);
}

/* 🧩 Fix DataTables bị che dropdown */
table.dataTable td {
    overflow: visible !important;
}
</style>

                <div class="dropdown text-center">
                    <button class="btn btn-sm btn-light border-0" type="button" id="actionsMenu' . $user->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="box-shadow:none;">
                        <i class="fas fa-ellipsis-v text-secondary"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right shadow-sm border-0 rounded" aria-labelledby="actionsMenu' . $user->id . '">
                ';

                // Chỉnh sửa
                if (Auth::user() && Auth::user()->can('edit staffs')) {
                    $buttons .= '
                        <a class="dropdown-item edit-user" href="#" data-toggle="modal" data-target="#editUserModal" data-id="' . $user->id . '">
                            <i class="fas fa-edit text-warning mr-2"></i>Chỉnh sửa
                        </a>
                    ';

                    if ($user->status == 1) {
                        $buttons .= '
                            <a class="dropdown-item toggle-status" href="#" data-id="' . $user->id . '" data-status="0">
                                <i class="fas fa-lock text-muted mr-2"></i>Khóa tài khoản
                            </a>
                        ';
                    } else {
                        $buttons .= '
                            <a class="dropdown-item toggle-status" href="#" data-id="' . $user->id . '" data-status="1">
                                <i class="fas fa-unlock text-success mr-2"></i>Mở khóa
                            </a>
                        ';
                    }
                }

                // Phân quyền
                if (Auth::user() && Auth::user()->can('manage permissions')) {
                    $permUrl = route('admin.users.permissions.edit', ['id' => $user->id]);
                    $buttons .= '
                        <a class="dropdown-item" href="' . $permUrl . '">
                            <i class="fas fa-key text-primary mr-2"></i>Phân quyền
                        </a>
                    ';
                }

                // Xóa
                if (Auth::user() && Auth::user()->can('delete staffs')) {
                    $buttons .= '
                        <a class="dropdown-item delete-user text-danger" href="#" data-toggle="modal" data-target="#deleteUserModal" data-id="' . $user->id . '">
                            <i class="fas fa-trash mr-2"></i>Xóa
                        </a>
                    ';
                }

                $buttons .= '</div></div>';
                return $buttons;
            })


            // ->addColumn('actions', function ($user) {
            //     $buttons = '';

            //     if (Auth::user() && Auth::user()->can('edit staffs')) {
            //         $buttons .= '<button type="button" class="btn btn-sm btn-outline-warning edit-user" style="margin-right:6px" data-toggle="modal" data-target="#editUserModal" data-id="' . $user->id . '"><i class="fas fa-edit"></i></button>';

            //         $buttons .= $user->status == 1
            //             ? '<button type="button" class="btn btn-sm btn-outline-secondary toggle-status" style="margin-right:6px" data-id="' . $user->id . '" data-status="0" title="Khóa tài khoản"><i class="fas fa-lock"></i></button>'
            //             : '<button type="button" class="btn btn-sm btn-outline-success toggle-status" style="margin-right:6px" data-id="' . $user->id . '" data-status="1" title="Mở khóa tài khoản"><i class="fas fa-unlock"></i></button>';
            //     }

            //     if (Auth::user() && Auth::user()->can('manage permissions')) {
            //         $permUrl = route('admin.users.permissions.edit', ['id' => $user->id]);
            //         $buttons .= '<a href="' . $permUrl . '" class="btn btn-sm btn-outline-primary" style="margin-right:6px" title="Phân quyền"><i class="fas fa-key"></i></a>';
            //     }

            //     if (Auth::user() && Auth::user()->can('delete staffs')) {
            //         $buttons .= '<button type="button" class="btn btn-sm btn-outline-danger delete-user" data-toggle="modal" data-target="#deleteUserModal" data-id="' . $user->id . '"><i class="fas fa-trash me-2"></i></button>';
            //     }

            //     return trim($buttons) !== '' ? $buttons : '<span class="text-muted">Không có quyền</span>';
            // })
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
                'avatar' => '',
            ]);
            $user->assignRole('Nhân viên');

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
            \Log::info("Lỗi: " . $e->getMessage());
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
