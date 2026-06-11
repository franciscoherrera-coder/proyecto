<?php
namespace App\Http\Controllers;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Flasher\Laravel\Facade\Flasher;


//Controlador que gestiona el registro de usuarios: valida los datos con mensajes personalizados, muestra errores mediante toasts, crea el usuario con contraseña hasheada, muestra un toast de éxito y redirige al login. 
class RegistroController extends Controller
{
    public function index()
    {
        return view('autenticacion.registro');
    }

    public function store(Request $request)
    {
        // 1. Validar con mensajes personalizados
        $validator = Validator::make($request->all(), [
            'nombre'   => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email'    => 'required|email|unique:usuarios,email',
            'password' => 'required|min:8',
        ], [
            'nombre.required'   => 'Es obligatorio ingresar nombre',
            'apellido.required' => 'Es obligatorio ingresar apellido',
            'email.required'    => 'Es obligatorio ingresar email',
            'email.email'       => 'Debes ingresar un email válido',
            'email.unique'      => 'Este email ya está registrado',
            'password.required' => 'Es obligatorio ingresar contraseña',
            'password.min'      => 'La contraseña debe tener al menos 8 caracteres',
        ]);

        if ($validator->fails()) {
            // 2. Mostrar cada error como toast
            foreach ($validator->errors()->all() as $error) {
                Flasher::addError($error);
            }
            return back()->withInput();
        }

        // 3. Crear usuario
        $usuario = Usuario::create([
            'nombre'   => $request->nombre,
            'apellido' => $request->apellido,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'rol'      => 'usuario',
        ]);

        // 4. Mostrar toast de éxito
        Flasher::addSuccess('Cuenta creada correctamente.', 'Éxito', [
            'position' => 'top-right',
            'timer'    => 4000,
        ]);

        // 5. Redirigir al login
        return redirect()->route('bolsadetrabajo.iniciar-sesion');

    }
}
