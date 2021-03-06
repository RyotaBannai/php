<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use DB;
use Str;

class UserController extends Controller
{
    public function index(Request $req)
    {
        // $users = User::all();
        // $users = DB::table('users')->get(); // query builder
        $users = DB::table('users')->pluck('name'); // 単一カラムの値をコレクションで取得したい場合はpluck. (key, value) で二つ取得することもできる

        // SQL文を直接渡したい場合は、rawメソッドを使用
        // $user_count = DB::table('users')->select(DB::raw('count(*) as user_count, status'))
            // ->selectRaw('')
            // ->whereRaw(), ->orWhereRaw('')
            // ->whereColumn('first', 'second') // where first and second columns are the same.
            // ->whereColumn('first', '>', 'second')

            // ->where('status', '<>', 1)
            // ->where(function($q){ // more than two
            //          $q->where('a', 1)
            //            ->where('b', '<>', 1)
            // })

            // json column query by using -> notation. ->記法
            // ->where('preferences->dining->meal', 'salad')
            // ->get()

            // ->groupBy('status')
            // ->get();

        // when() 条件節を使う.$roleがtrueの場合のみクロージャを実行
        // 第３引数にもう一つのクロージャを渡すことで、false用の処理も実行できる
        $role = '1';
//        $users_bool = DB::table('users')
//            ->when($role, function($q) use($role){
//                return $q->where('role_id', $role);
//            } //, function($q) { // this is for the case of $role is false }
//            )
//            ->get();
        // 悲観的ロック
        // ->sharedLock()->get()
        // lockForUpdate()->get()
        return view(
            'user.index',
            compact('users')
        );
    }
    public function userForm(Request $req){
        $data = 'data';
        return view('user.sidebar', compact('data'));
    }

    public function userOut(Request $req){
        $post_data= $req->all();
        return view("user.out", compact("post_data"));
    }

    public function userList(Request $req){
        $users = User::all();
        $id = $req->input('id') ?? null;

        if (isset($id)){
            if ($users->contains($id)){
                return "you are registered";
            }
            else{
                return "Not registered yet";
            }
        }
        else {
            $names = $users->reject(function ($user) {
                return $user->active === 0; // reject
            })->map(function ($user) {
                return $user->name;
            });
            return view(
                'user.index',
                ['users' => $names]
            );
        }
    }

    public function name()
    {
        $user = User::find(1);
        return $user->name;
    }

    public function dupe(User $user){
        return view('user.dupe', [
            'user' => User::find($user->id)
        ]);
    }

    public function executeDupe(User $user){
        $new_user = $user->replicate();
        $new_user->email = Str::random(10).'@random.com';
        $new_user->save();
        return redirect('users/'.$new_user->id.'/show');
    }

    public function show(User $user){
        return view('user.profile',[
            'user' => $user
        ]);
    }

    public function mdw(User $user, Request $request){
        if($request->get('is_model_instance_set')){
            dump('model is set aready.');
            return view('user.profile',[
                'user' => \Session::get("user_data_$user->id")
            ]);
        }

        // after many procedures...
        $new_data = User::find($user->id);
        return view('user.profile', [
               'user' => $new_data
           ]);
    }
}
