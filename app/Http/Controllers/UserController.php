<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('super_admin');
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
            ->paginate(10)
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
        // validation
        $validated = $request->validate([
            'name' => ['required'],
            'username' => ['required', 'unique:users,username'],
            'password' => ['required', 'min:6'],
            'role' => ['required', 'in:super,admin,staff'],
        ]);

        // hashing password
        $validated['password'] = bcrypt($validated['password']);

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

        // store
        $user->update($validated);

        // return
        return redirect()->back()->with('message', 'Pengguna berhasil diperbarui');
    }

    public function destroy(User $user)
    {
        // forbidden
        if ($user->role === 'super' || auth()->user()->role != 'super') {
            return response()->json([
                'message' => 'forbidden'
            ], 403);
        }

        // delete
        $user->delete();

        // return
        return response()->json([
            'status' => 'success',
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
            return redirect()->back()->with('message', 'Password berhasil direset');
        }

        $item = $user;

        // return view
        return view('admin.users.reset_password', compact('item'));
    }
}
