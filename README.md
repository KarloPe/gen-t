# 📚 Sistema de Gestión de Calificaciones Escolares

Sistema web completo para la administración de calificaciones académicas, diseñado para instituciones educativas que necesitan gestionar alumnos, profesores, materias y calificaciones por bimestre.

## 🌟 Características Principales

### Para Profesores
- 📊 Dashboard personalizado con vista de todas sus materias asignadas
- 👥 Gestión de alumnos por curso y materia
- ✏️ Calificación por bimestres (4 períodos académicos)
- 📈 Resúmenes estadísticos de rendimiento por materia
- 📋 Historial detallado de calificaciones por alumno
- 🎯 Comparación de promedios individuales vs. curso

### Para Alumnos
- 🎓 Vista de todas sus materias y calificaciones
- 📊 Promedio general y por materia
- 📈 Seguimiento de progreso académico
- 👨‍🏫 Información de profesores asignados
- 📅 Calificaciones organizadas por bimestre

### Funcionalidades Generales
- 🔐 Sistema de autenticación seguro (profesores y alumnos)
- 📱 Diseño responsive (adaptable a móviles y tablets)
- 🎨 Interfaz moderna con gradientes y animaciones
- ♿ Accesibilidad web mejorada
- 📊 Estadísticas en tiempo real
- 🔄 Actualización dinámica de promedios

## 🛠️ Tecnologías Utilizadas

- **Frontend:**
  - HTML5
  - CSS3 (Diseño moderno con gradientes y animaciones)
  - JavaScript (Vanilla JS)
  
- **Backend:**
  - PHP 7+
  - MySQL/MariaDB
  
- **Base de Datos:**
  - Sistema relacional con tablas para alumnos, profesores, materias, cursos y calificaciones

## 📋 Requisitos del Sistema

- Servidor web con soporte PHP 7.0+
- MySQL 5.6+ o MariaDB 10+
- Navegador web moderno (Chrome, Firefox, Safari, Edge)

## 🚀 Instalación

### 1. Clonar el repositorio
```bash
git clone https://github.com/tu-usuario/sistema-calificaciones.git
cd sistema-calificaciones
```

### 2. Configurar la base de datos

Edita el archivo `config.php` con tus credenciales:

```php
$servidor = "localhost";
$usuario_db = "tu_usuario";
$clave_db = "tu_contraseña";
$base_datos = "nombre_base_datos";
```

### 3. Importar la estructura de la base de datos

El sistema espera las siguientes tablas principales:
- `alumnos`
- `profesores`
- `materias`
- `cursos`
- `cali` (calificaciones)

### 4. Configurar el servidor web

Apunta tu servidor web (Apache/Nginx) al directorio del proyecto.

### 5. Acceder al sistema

Abre tu navegador y navega a:
```
http://localhost/sistema-calificaciones/login.html
```

## 👤 Acceso al Sistema

### Profesores
- **Usuario:** ID del profesor (número)
- **Contraseña:** Contraseña asignada

### Alumnos
- **Usuario:** DNI del alumno
- **Contraseña:** Inicialmente el mismo DNI

### Registro de Nuevos Alumnos
Los alumnos pueden auto-registrarse desde la página de login proporcionando:
- DNI (obligatorio)
- Apellido (obligatorio)
- Nombre (opcional)
- Email (opcional)
- Teléfono (opcional)
- Fecha de nacimiento (opcional)

## 📁 Estructura del Proyecto

```
sistema-calificaciones/
├── login.html                  # Página de inicio de sesión
├── index.php                   # Redireccionamiento inicial
├── validar.php                 # Validación de credenciales
├── config.php                  # Configuración de base de datos
├── bienvenido.php             # Dashboard de profesores
├── alumno_dashboard.php       # Dashboard de alumnos
├── ver_alumnos.php            # Lista de alumnos por materia
├── calificar_alumno.php       # Formulario de calificación
├── historial_alumno.php       # Historial de calificaciones
├── resumen_calificaciones.php # Resumen estadístico
├── alumno_materia.php         # Detalle de materia para alumno
├── registrar_alumno.php       # Proceso de registro
├── registro_resultado.php     # Resultado del registro
├── cerrar_sesion.php          # Cierre de sesión
├── acceso_errado.php          # Página de error de acceso
├── verificar_sesion.php       # Funciones de verificación
├── styles.css                 # Estilos principales
├── script.js                  # Scripts JavaScript
└── README.md                  # Este archivo
```

## 🎨 Características de Diseño

- **Gradientes modernos:** Interfaz visualmente atractiva
- **Animaciones suaves:** Transiciones y efectos hover
- **Cards responsivas:** Organización de información en tarjetas
- **Sistema de notificaciones:** Alertas visuales para el usuario
- **Modales elegantes:** Ventanas emergentes para acciones
- **Indicadores de estado:** Colores para aprobado/desaprobado
- **Diseño mobile-first:** Optimizado para dispositivos móviles

## 📊 Esquema de Calificaciones

- **Escala:** 1 a 10
- **Aprobación:** 7 o superior
- **Períodos:** 4 bimestres por año
- **Promedio:** Calculado automáticamente

## 🔒 Seguridad

- Validación de datos en servidor
- Escape de caracteres SQL (mysqli_real_escape_string)
- Verificación de sesiones activas
- Control de acceso por roles
- Limpieza de inputs del usuario

## 🌐 Compatibilidad

- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+
- ✅ Dispositivos móviles (iOS/Android)

## 📱 Responsive Design

El sistema es completamente responsive con breakpoints en:
- 768px (tablets)
- 480px (móviles)

## 🚧 Funcionalidades Futuras

- [ ] Exportación de reportes en PDF
- [ ] Envío de notificaciones por email
- [ ] Gráficos interactivos de rendimiento
- [ ] Sistema de mensajería profesor-alumno
- [ ] Calendario de evaluaciones
- [ ] Asistencia integrada
- [ ] Panel administrativo completo
- [ ] API REST para integración

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Para cambios importantes:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📝 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 👨‍💻 Autor

Desarrollado para instituciones educativas que buscan digitalizar su gestión académica.

## 📞 Soporte

Para reportar bugs o solicitar features, por favor abre un issue en GitHub.

## 🙏 Agradecimientos

- Diseño inspirado en sistemas educativos modernos
- Iconos emoji para mejor UX
- Comunidad PHP y MySQL por su documentación

---

⭐ Si este proyecto te resulta útil, considera darle una estrella en GitHub!
