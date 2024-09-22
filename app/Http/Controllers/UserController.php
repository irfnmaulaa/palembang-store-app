<?php

namespace App\Http\Controllers;

use App\Exports\UsersExport;
use App\Models\User;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->middleware('super_admin')->except(['close_store']);

        $this->transactionService = $transactionService;
    }

    public function index(Request $request)
    {
        // define instance
        $users = new User();

        // searching settings
        if ($request->has('keyword')) {
            $users = $users->where('name', 'LIKE', '%' . $request->get('keyword') . '%');
        }

        // order-by settings
        $order = ['name', 'asc'];
        if ($request->has('order')) {
            $order_query = explode('-', $request->get('order'));
            if (count($order_query) >= 2) $order = $order_query;
        }

        // order-by statements
        $users = $users->orderBy($order[0], $order[1]);

        // final statements
        $users = $users
            ->paginate(get_per_page_default())
            ->appends($request->query());

        // define order options for front-end
        $order_options = [
            ['label' => 'Nama A-Z', 'order' => 'name-asc'],
            ['label' => 'Nama A-Z', 'order' => 'name-desc'],
            ['label' => 'Terkahir dibuat', 'order' => 'created_at-desc'],
            ['label' => 'Pertama dibuat', 'order' => 'created_at-asc'],
        ];

        // return view
        return view('admin.users.index', compact('users', 'order_options'));
    }

    public function create()
    {
        $item = null;
        return view('admin.users.form', compact('item'));
    }

    public function store(Request $request)
    {
        // define rules
        $rules = [
            'name' => ['required', 'min:3', 'max:4'],
            'username' => ['required', 'unique:users,username'],
            'password' => ['required', 'min:4'],
            'role' => ['required', 'in:admin,staff'],
        ];

        // set pin required if admin
        if ($request->role === 'admin') {
            $rules['pin'] = ['required', 'digits:6'];
        }

        // validation
        $validated = $request->validate($rules);

        // set username to uppercase
        $validated['username'] = strtoupper($validated['username']);
        $validated['name'] = strtoupper($validated['name']);

        // hashing password
        $validated['password'] = bcrypt($validated['password']);

        // hashing pin
        if (!empty($validated['pin'])) {
            $validated['pin'] = bcrypt($validated['pin']);
        }

        // set default active
        $validated['is_active'] = 1;

        // store
        User::create($validated);

        // return
        return redirect()->back()->with('message', 'Pengguna berhasil ditambahkan');
    }

    public function edit(User $user)
    {
        $item = $user;
        return view('admin.users.form', compact('item'));
    }

    public function update(Request $request, User $user)
    {
        // validation
        $validated = $request->validate([
            'name' => ['required'],
            'username' => ['required', 'unique:users,username,' . $user->id],
            'role' => ['required', 'in:admin,staff'],
        ]);

        // set username to uppercase
        $validated['username'] = strtoupper($validated['username']);
        $validated['name'] = strtoupper($validated['name']);

        // store
        $user->update($validated);

        // return
        return redirect()->back()->with('message', 'Pengguna berhasil diperbarui');
    }

    public function destroy(Request $request, User $user)
    {
        // forbidden
        if (auth()->user()->role != 'admin') {
            return response()->json([
                'status' => 'forbidden',
                'message' => 'Tidak dapat menghapus kecuali Administrator',
            ], 403);
        }

        // validation
        $validated = $request->validate([
            'pin' => ['nullable'],
        ]);

        // check pin
        if (empty($validated['pin']) || !Hash::check($validated['pin'], $request->user()->pin)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Pin tidak valid',
            ], 400);
        }

        // delete
        try {
            $user->delete();
        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Tidak dapat menghapus karna terdapat data dari tabel lain yang berelasi dengan data ini.'
            ], 400);
        }

        // return
        return response()->json([
            'status' => 'success',
            'redirect_url' => route('admin.users.index')
        ]);
    }

    public function reset_password(Request $request, User $user)
    {
        if ($request->isMethod('POST')) {
            // validation
            $validated = $request->validate([
                'password' => ['required', 'confirmed'],
            ]);

            // update password
            $user->update([
                'password' => bcrypt($validated['password'])
            ]);

            // return
            return redirect()->route('admin.users.edit', [$user])->with('message', 'Password berhasil direset');
        }

        $item = $user;

        // return view
        return view('admin.users.reset_password', compact('item'));
    }

    public function reset_pin(Request $request, User $user)
    {
        if ($request->isMethod('POST')) {
            // validation
            $validated = $request->validate([
                'pin' => ['required', 'confirmed'],
            ]);

            // update password
            $user->update([
                'pin' => bcrypt($validated['pin'])
            ]);

            // return
            return redirect()->route('admin.users.edit', [$user])->with('message', 'Pin berhasil direset');
        }

        $item = $user;

        // return view
        return view('admin.users.reset_pin', compact('item'));
    }

    public function activate(Request $request, User $user)
    {
        // store
        $user->update([
            'is_active' => $user->is_active ? 0 : 1,
        ]);

        // return
        return redirect()->back()->with('messagePopup', 'Pengguna berhasil ' . ($user->is_active ? ' diaktifkan' : ' dinonaktifkan') . '.');
    }

    public function export($type)
    {
        $filename = 'users_' . Carbon::now()->format('YmdHis');

        switch ($type) {
            case 'excel':
                return Excel::download(new UsersExport, $filename . '.xlsx');
            case 'csv':
                return Excel::download(new UsersExport, $filename . '.csv', \Maatwebsite\Excel\Excel::CSV, [
                    'Content-Type' => 'text/csv',
                ]);
            case 'pdf':
                return Excel::download(new UsersExport, $filename . '.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
            default:
                return "url export salah";
        }
    }

    public function check_pin(Request $request)
    {
        // validation
        $validated = $request->validate([
            'pin' => ['required'],
        ]);

        // check pin
        if (Hash::check($validated['pin'], $request->user()->pin)) {
            return response()->json([
                'status' => 'success',
            ]);
        }

        // pin is invalid
        return response()->json([
            'status' => 'failed',
            'message' => 'PIN yang kamu masukan salah.'
        ], 400);
    }

    public function close_store(Request $request)
    {
        // deactivate user
        User::find($request->user()->id)->update([
            'is_active' => false,
        ]);

        // define printed by
        $printed_by = auth()->user();

        // logout
        auth()->logout();

        // define type
        $type = 'pdf';

        // download
        return $this->transactionService->export_pending($type, $printed_by);
    }
}
