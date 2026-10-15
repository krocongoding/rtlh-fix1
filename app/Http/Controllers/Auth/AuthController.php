<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{public function create(){return view('auth.login');}public function store(Request $r){$c=$r->validate(['email'=>'required|email','password'=>'required']);if(!Auth::attempt($c,$r->boolean('remember')))return back()->withErrors(['email'=>'Email atau password salah.'])->onlyInput('email');$r->session()->regenerate();$u=$r->user();return redirect()->intended($u->hasRole('admin')?route('admin.dashboard'):($u->hasRole('surveyor')?route('surveyor.dashboard'):route('public.home')));}public function destroy(Request $r){Auth::logout();$r->session()->invalidate();$r->session()->regenerateToken();return redirect('/');}}
