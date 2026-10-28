# Manuales finales

Este directorio contiene las versiones completadas del manual de usuario y del manual técnico del Sistema de Asistencia del ISFT N.º 38. Conservan la primera página de las portadas entregadas.

- `Manual_de_Usuario_ISFT_38_final.pdf`
- `Manual_Tecnico_ISFT_38_final.pdf`

Los HTML contienen el texto editable y el generador PHP crea los PDF con las capturas y las portadas originales:

```powershell
php docs\manuales\generar_desde_html.php
```

También pueden pasarse las rutas de ambos PDF de portada como argumentos. El script Python queda disponible como alternativa para ensamblar las versiones PDF preexistentes.
