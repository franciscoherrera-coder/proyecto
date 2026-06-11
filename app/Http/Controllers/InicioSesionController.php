<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Usuario;
use Flasher\Laravel\Facade\Flasher;


// <!-- Gestiona el inicio y cierre de sesión de usuarios, valida credenciales, muestra mensajes de error o éxito con toasts, y redirige según si el perfil está completo. -->

class InicioSesionController extends Controller
{
    public function index()
    {
        return view('autenticacion.iniciar_sesion');
    }


    public function procesar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Es obligatorio ingresar email',
            'email.email' => 'Debes ingresar un email válido',
            'password.required' => 'Es obligatorio ingresar contraseña',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                Flasher::addError($error);
            }
            return back()->withInput();
        }

        $credenciales = $request->only('email', 'password');

        if (Auth::guard('usuarios')->attempt($credenciales)) {
            $request->session()->regenerate();
            $usuario = Auth::guard('usuarios')->user();

            if (!$usuario->perfil_completado) {
                Flasher::addSuccess('Completa tu perfil', 'Exito');
                return redirect()->route('bolsadetrabajo.perfil.editar');
            }
            

            Flasher::addSuccess('¡Sesión iniciada correctamente!', 'Exito');
            return redirect('/bolsadetrabajo/home');
        }

        Flasher::addError('Credenciales incorrectas.');
        return back()->withInput();
    }


    public function logout(Request $request)
    {
        Auth::guard('usuarios')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        
        Flasher::addSuccess('Sesión cerrada.', 'Exito');
        return redirect('/bolsadetrabajo/inicio');
    }
}
