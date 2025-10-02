<?php

namespace App\Http\Controllers;

use App\Models\User as Customer;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.customers.index');
        // $search = trim((string) $request->query('search', ''));
        // $sort = (string) $request->query('sort', 'id');
        // $direction = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        // $perPage = (int) $request->query('per_page', 1);
        // if ($perPage < 1 || $perPage > 100) {
        //     $perPage = 25;
        // }

        // $query = Customer::select('id', 'name', 'email', 'phone', 'address', 'status');
        // // ->where('role_id', 3)

        // if ($search !== '') {
        //     $query->where(function ($q) use ($search) {
        //         $q->where('name', 'like', "%{$search}%")
        //             ->orWhere('email', 'like', "%{$search}%")
        //             ->orWhere('phone', 'like', "%{$search}%");
        //     });
        // }

        // // Whitelist sortable columns
        // $sortable = ['id', 'name', 'email', 'created_at'];
        // $orderBy = in_array($sort, $sortable, true) ? $sort : 'id';
        // $query->orderBy($orderBy, $direction);

        // $customers = $query->paginate($perPage)->withQueryString();

        // return view('admin.customers.index', [
        //     'customers' => $customers,
        //     'sort' => $orderBy,
        //     'direction' => $direction,
        // ]);
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
            ->addColumn('status_badge', function ($customer) {
                return $customer->status == '1'
                    ? '<span class="badge bg-success text-white">Active</span>'
                    : '<span class="badge bg-secondary text-white">Inactive</span>';
            })
            // ->addColumn('actions', function ($customer) {
            //     $edit = '<a href="' . route('customers.edit', $customer->id) . '" class="btn btn-sm btn-outline-secondary"><i class="fas fa-pencil"></i> Edit</a>';
            //     $delete = '<form action="' . route('customers.destroy', $customer->id) . '" method="POST" style="display:inline;">
            //     ' . csrf_field() . method_field('DELETE') . '
            //     <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Bạn có chắc muốn xóa?\')"><i class="fas fa-trash me-2"></i> Delete</button>
            // </form>';
            ->addColumn('actions', function ($customer) {
                $editButton = '<button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#editCustomerModal">
                    <i class="fas fa-pencil"></i> Edit
                </button>';

                $deleteButton = '<button type="button" class="btn btn-sm btn-outline-danger" data-toggle="modal" data-target="#deleteCustomerModal">
                    <i class="fas fa-trash me-2"></i> Delete
                </button>';
                return $editButton . ' ' . $deleteButton;
            })
            ->rawColumns(['status_badge', 'actions']) // cho phép render HTML
            ->make(true);
    }

    public function update($id, Request $request)
    {
        $customer = Customer::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $customer->id,
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
            'status' => 'required|integer|in:0,1,3,4,5',
        ]);

        $customer->fill($validated);
        $customer->save();

        return response()->json(['message' => 'Customer updated']);
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
        return response()->json(['message' => 'Customer deleted']);
    }
}
