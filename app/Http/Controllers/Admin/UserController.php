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
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
                'message' => 'Cập nhật quyền cho nhân viên ' . $user->name . ' thành công!',
                'type' => 'success'
            ]);
        }

        return redirect()
            ->route('admin.users.permissions.edit', ['id' => $user->id])
            ->with('success', 'Cập nhật quyền cho nhân viên ' . $user->name . ' thành công!');
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
                    1 => ['Admin', 'primary', 'fas fa-user-shield'],
                    2 => ['Nhân viên', 'info', 'fas fa-user-tie'],
                    3 => ['Khách hàng', 'secondary', 'fas fa-user']
                ];
                [$label, $color, $icon] = $roleMap[$user->role_id] ?? ['Unknown', 'dark', 'fas fa-user'];
                return "<span class=\"badge bg-{$color}\"><i class=\"{$icon}\"></i> {$label}</span>";
            })
            ->addColumn('actions', function ($user) {
                $buttons = '
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

    // ==================== IMPORT EXCEL METHODS ====================

    /**
     * Hiển thị trang import
     */
    public function showImport()
    {
        return view('admin.users.import');
    }

    /**
     * Tải file Excel mẫu
     */
    public function downloadTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Nhân viên');

            // Headers
            $headers = ['Họ và tên', 'Email', 'Số điện thoại', 'Địa chỉ', 'Mật khẩu', 'Trạng thái'];
            $sheet->fromArray($headers, null, 'A1');

            // Style header
            $headerStyle = [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ];
            $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

            // Sample data
            $sampleData = [
                ['Nguyễn Văn A', 'nguyenvana@example.com', '0901234567', '123 Đường ABC, Quận 1, TP.HCM', 'Password@123', 'Hoạt động'],
                ['Trần Thị B', 'tranthib@example.com', '0912345678', '456 Đường XYZ, Quận 2, TP.HCM', 'Password@456', 'Hoạt động'],
                ['Lê Văn C', 'levanc@example.com', '0923456789', '789 Đường DEF, Quận 3, TP.HCM', 'Password@789', 'Khóa']
            ];
            $sheet->fromArray($sampleData, null, 'A2');

            // Tạo dropdown cho cột Trạng thái (F2:F1000)
            $validation = $sheet->getCell('F2')->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Lỗi trạng thái');
            $validation->setError('Vui lòng chọn trạng thái từ danh sách.');
            $validation->setPromptTitle('Chọn trạng thái');
            $validation->setPrompt('Chọn "Hoạt động" hoặc "Khóa".');
            $validation->setFormula1('"Hoạt động,Khóa"');

            // Apply validation cho nhiều dòng
            for ($row = 2; $row <= 1000; $row++) {
                $sheet->getCell('F' . $row)->setDataValidation(clone $validation);
            }

            // Thêm ghi chú cho các cột
            $sheet->getComment('E1')->getText()->createTextRun(
                "Mật khẩu phải:\n- Tối thiểu 8 ký tự\n- Có ít nhất 1 chữ hoa\n- Có ít nhất 1 chữ thường\n- Có ít nhất 1 số\n- Có ít nhất 1 ký tự đặc biệt"
            );
            $sheet->getComment('E1')->setWidth('300pt');
            $sheet->getComment('E1')->setHeight('100pt');

            $sheet->getComment('F1')->getText()->createTextRun(
                "Trạng thái tài khoản:\n- Hoạt động: Tài khoản có thể đăng nhập\n- Khóa: Tài khoản bị khóa, không thể đăng nhập"
            );
            $sheet->getComment('F1')->setWidth('300pt');
            $sheet->getComment('F1')->setHeight('80pt');

            // Auto width
            foreach (range('A', 'F') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Tạo sheet hướng dẫn
            $instructionSheet = $spreadsheet->createSheet();
            $instructionSheet->setTitle('Hướng dẫn');

            $instructions = [
                ['HƯỚNG DẪN IMPORT NHÂN VIÊN'],
                [''],
                ['1. Họ và tên:', 'Bắt buộc. Tối đa 120 ký tự. Chỉ chứa chữ cái và khoảng trắng.'],
                ['2. Email:', 'Bắt buộc. Phải là email hợp lệ và chưa tồn tại trong hệ thống.'],
                ['3. Số điện thoại:', 'Bắt buộc. Tối đa 15 ký tự.'],
                ['4. Địa chỉ:', 'Bắt buộc. Tối đa 255 ký tự.'],
                ['5. Mật khẩu:', 'Bắt buộc. Tối thiểu 8 ký tự, có chữ hoa, chữ thường, số và ký tự đặc biệt.'],
                ['6. Trạng thái:', 'Bắt buộc. Chọn "Hoạt động" hoặc "Khóa" từ dropdown.'],
                [''],
                ['LƯU Ý:'],
                ['- Không xóa dòng tiêu đề (dòng 1)'],
                ['- Email không được trùng với nhân viên đã có'],
                ['- Email không được trùng trong file'],
                ['- Mật khẩu phải đủ mạnh theo yêu cầu'],
                ['- Trạng thái phải chọn từ dropdown'],
                ['- Xóa các dòng mẫu trước khi nhập dữ liệu thực'],
                ['- Tất cả nhân viên được import sẽ có vai trò "Nhân viên"'],
                ['- Có thể phân quyền chi tiết sau khi import'],
                [''],
                ['VÍ DỤ MẬT KHẨU HỢP LỆ:'],
                ['- Password@123'],
                ['- Admin@2024'],
                ['- Staff#456'],
                [''],
                ['VÍ DỤ MẬT KHẨU KHÔNG HỢP LỆ:'],
                ['- password (thiếu chữ hoa, số, ký tự đặc biệt)'],
                ['- PASSWORD123 (thiếu chữ thường, ký tự đặc biệt)'],
                ['- Pass@1 (quá ngắn, dưới 8 ký tự)']
            ];

            $instructionSheet->fromArray($instructions, null, 'A1');

            // Style cho sheet hướng dẫn
            $instructionSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $instructionSheet->getStyle('A3:A8')->getFont()->setBold(true);
            $instructionSheet->getStyle('A10')->getFont()->setBold(true);
            $instructionSheet->getStyle('A19')->getFont()->setBold(true);
            $instructionSheet->getStyle('A24')->getFont()->setBold(true);
            $instructionSheet->getColumnDimension('A')->setWidth(30);
            $instructionSheet->getColumnDimension('B')->setWidth(70);

            // Set active sheet về Nhân viên
            $spreadsheet->setActiveSheetIndex(0);

            $writer = new Xlsx($spreadsheet);
            $fileName = 'file_mau_import_nhan_vien' .  date('Y-m-d') . '.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), $fileName);
            $writer->save($tempFile);

            return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Download template error: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi tải file mẫu!');
        }
    }

    /**
     * Preview dữ liệu từ file Excel
     */
    public function previewImport(Request $request)
    {
        try {
            $request->validate([
                'excel_file' => 'required|file|mimes:xlsx,xls|max:2048'
            ]);

            $file = $request->file('excel_file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            if (empty($rows) || count($rows) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'File Excel trống hoặc không có dữ liệu!'
                ], 400);
            }

            // Lấy header
            $header = array_map('trim', $rows[0]);

            // Kiểm tra các cột bắt buộc
            $requiredColumns = ['Họ và tên', 'Email', 'Số điện thoại', 'Địa chỉ', 'Mật khẩu', 'Trạng thái'];
            $missingColumns = array_diff($requiredColumns, $header);

            if (!empty($missingColumns)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File Excel thiếu các cột: ' . implode(', ', $missingColumns)
                ], 400);
            }

            // Lấy dữ liệu (bỏ dòng header)
            $data = array_slice($rows, 1);

            // Validate và chuẩn bị dữ liệu preview
            $previewData = [];
            $errors = [];
            $existingEmails = User::pluck('email')->map(function ($email) {
                return strtolower(trim($email));
            })->toArray();
            $emailsInFile = [];

            foreach ($data as $index => $row) {
                $rowNumber = $index + 2; // +2 vì bắt đầu từ dòng 2

                // Bỏ qua dòng trống
                if (empty(array_filter($row))) {
                    continue;
                }

                $name = trim($row[0] ?? '');
                $email = trim($row[1] ?? '');
                $phone = trim($row[2] ?? '');
                $address = trim($row[3] ?? '');
                $password = trim($row[4] ?? '');
                $statusText = trim($row[5] ?? '');

                $rowErrors = [];
                $emailLower = strtolower($email);

                // Validate họ tên
                if (empty($name)) {
                    $rowErrors[] = 'Họ và tên không được để trống';
                } elseif (strlen($name) > 120) {
                    $rowErrors[] = 'Họ và tên không được vượt quá 120 ký tự';
                } elseif (!preg_match('/^[\pL\s]+$/u', $name)) {
                    $rowErrors[] = 'Họ và tên chỉ được chứa chữ cái và khoảng trắng';
                }

                // Validate email
                if (empty($email)) {
                    $rowErrors[] = 'Email không được để trống';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $rowErrors[] = 'Email không hợp lệ';
                } elseif (in_array($emailLower, $existingEmails)) {
                    $rowErrors[] = 'Email đã tồn tại trong hệ thống';
                } elseif (in_array($emailLower, $emailsInFile)) {
                    $rowErrors[] = 'Email bị trùng trong file';
                } else {
                    $emailsInFile[] = $emailLower;
                }

                // Validate số điện thoại
                if (empty($phone)) {
                    $rowErrors[] = 'Số điện thoại không được để trống';
                } elseif (strlen($phone) > 15) {
                    $rowErrors[] = 'Số điện thoại không được vượt quá 15 ký tự';
                }

                // Validate địa chỉ
                if (empty($address)) {
                    $rowErrors[] = 'Địa chỉ không được để trống';
                } elseif (strlen($address) > 255) {
                    $rowErrors[] = 'Địa chỉ không được vượt quá 255 ký tự';
                }

                // Validate mật khẩu
                if (empty($password)) {
                    $rowErrors[] = 'Mật khẩu không được để trống';
                } elseif (strlen($password) < 8) {
                    $rowErrors[] = 'Mật khẩu phải có tối thiểu 8 ký tự';
                } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/', $password)) {
                    $rowErrors[] = 'Mật khẩu phải có ít nhất 1 chữ hoa, 1 chữ thường, 1 số và 1 ký tự đặc biệt';
                }

                // Validate trạng thái
                $status = null;
                if (empty($statusText)) {
                    $rowErrors[] = 'Trạng thái không được để trống';
                } elseif ($statusText === 'Hoạt động') {
                    $status = 1;
                } elseif ($statusText === 'Khóa') {
                    $status = 0;
                } else {
                    $rowErrors[] = 'Trạng thái phải là "Hoạt động" hoặc "Khóa"';
                }

                $previewData[] = [
                    'row_number' => $rowNumber - 1,
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'address' => $address,
                    'password' => $password,
                    'status' => $status,
                    'status_text' => $statusText,
                    'username' => Str::slug($name),
                    'errors' => $rowErrors,
                    'has_error' => !empty($rowErrors)
                ];

                if (!empty($rowErrors)) {
                    $errors[] = "Dòng {$rowNumber}: " . implode(', ', $rowErrors);
                }
            }

            // Kiểm tra có dữ liệu hợp lệ không
            if (empty($previewData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File Excel không có dữ liệu hợp lệ!'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $previewData,
                'total_rows' => count($previewData),
                'valid_rows' => count(array_filter($previewData, fn($item) => !$item['has_error'])),
                'error_rows' => count(array_filter($previewData, fn($item) => $item['has_error'])),
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            Log::error('Preview import error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xử lý file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xử lý import dữ liệu vào database
     */
    public function processImport(Request $request)
    {
        try {
            $validated = $request->validate([
                'data' => 'required|array',
                'data.*.name' => 'required|string|max:120',
                'data.*.email' => 'required|email|max:255',
                'data.*.phone' => 'required|string|max:15',
                'data.*.address' => 'required|string|max:255',
                'data.*.password' => 'required|string|min:8',
                'data.*.status' => 'required|integer|in:0,1',
                'data.*.username' => 'required|string|max:255'
            ]);

            $importedCount = 0;
            $errors = [];

            foreach ($validated['data'] as $item) {
                try {
                    // Kiểm tra trùng email một lần nữa
                    if (User::where('email', $item['email'])->exists()) {
                        $errors[] = "Email '{$item['email']}' đã tồn tại";
                        continue;
                    }

                    $user = User::create([
                        'username' => $item['username'],
                        'name' => $item['name'],
                        'email' => $item['email'],
                        'password' => Hash::make($item['password']),
                        'phone' => $item['phone'],
                        'address' => $item['address'],
                        'role_id' => 2, // Nhân viên
                        'status' => $item['status'],
                        'email_verified_at' => now(),
                        'avatar' => '',
                    ]);

                    // Gán role "Nhân viên"
                    $user->assignRole('Nhân viên');

                    $importedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Lỗi khi thêm '{$item['name']}': " . $e->getMessage();
                }
            }

            if ($importedCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có nhân viên nào được import!',
                    'errors' => $errors
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => "Đã import thành công {$importedCount} nhân viên!" .
                    (!empty($errors) ? " Có " . count($errors) . " lỗi." : ""),
                'imported_count' => $importedCount,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            Log::error('Process import error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi import: ' . $e->getMessage()
            ], 500);
        }
    }
}
