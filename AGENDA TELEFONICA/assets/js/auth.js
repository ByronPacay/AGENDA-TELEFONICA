// Bloque principal: espera a que el DOM cargue para enlazar eventos de autenticacion.
document.addEventListener("DOMContentLoaded", () => {
    const formLogin = document.getElementById("form-login");
    const formRegister = document.getElementById("form-register");
    const tabLogin = document.getElementById("btn-tab-login");
    const tabRegister = document.getElementById("btn-tab-register");

    // Bloque de cambio de pestanas para mostrar el formulario seleccionado.
    tabLogin.addEventListener("click", () => mostrarPanel("login"));
    tabRegister.addEventListener("click", () => mostrarPanel("register"));

    // Bloque de submit del login: envia correo y contrasena al endpoint de inicio de sesion.
    formLogin.addEventListener("submit", async (event) => {
        event.preventDefault();

        if (!formLogin.checkValidity()) {
            mostrarAlerta("warning", "Formulario invalido", "Completa correctamente los campos requeridos.");
            return;
        }

        const payload = {
            correo: document.getElementById("login-correo").value.trim(),
            password: document.getElementById("login-password").value
        };

        const resultado = await enviarPeticion("api/login.php", "POST", payload);

        if (!resultado.ok) {
            mostrarAlerta("error", "No fue posible iniciar sesion", resultado.mensaje || "Credenciales invalidas.");
            return;
        }

        await mostrarAlerta("success", "Acceso correcto", "Bienvenido al panel de la agenda.");
        window.location.href = "agenda.php";
    });

    // Bloque de submit del registro: valida y crea una cuenta nueva.
    formRegister.addEventListener("submit", async (event) => {
        event.preventDefault();

        if (!formRegister.checkValidity()) {
            mostrarAlerta("warning", "Formulario invalido", "Completa correctamente los campos requeridos.");
            return;
        }

        const password = document.getElementById("reg-password").value;
        const confirm = document.getElementById("reg-confirm").value;
        const nombre = document.getElementById("reg-nombre").value.trim();
        const correo = document.getElementById("reg-correo").value.trim();

        if (nombre.length < 3) {
            mostrarAlerta("warning", "Nombre invalido", "El nombre debe tener al menos 3 caracteres.");
            return;
        }

        if (!validarCorreo(correo)) {
            mostrarAlerta("warning", "Correo invalido", "Ingresa un correo con formato valido.");
            return;
        }

        if (password !== confirm) {
            mostrarAlerta("warning", "Contrasenas distintas", "La confirmacion no coincide con la contrasena.");
            return;
        }

        const payload = {
            nombre,
            correo,
            password
        };

        const resultado = await enviarPeticion("api/register.php", "POST", payload);

        if (!resultado.ok) {
            mostrarAlerta("error", "No se pudo crear la cuenta", resultado.mensaje || "Intentalo nuevamente.");
            return;
        }

        await mostrarAlerta("success", "Cuenta creada", "Tu usuario fue registrado correctamente.");
        window.location.href = "agenda.php";
    });

    // Bloque de verificacion de sesion: si hay sesion activa redirige directo al panel.
    verificarSesionActiva();

    // Bloque auxiliar para intercambiar entre login y registro.
    function mostrarPanel(panel) {
        const mostrarLogin = panel === "login";
        formLogin.classList.toggle("hidden", !mostrarLogin);
        formRegister.classList.toggle("hidden", mostrarLogin);
        tabLogin.classList.toggle("btn-primary", mostrarLogin);
        tabLogin.classList.toggle("btn-outline-primary", !mostrarLogin);
        tabLogin.classList.toggle("active-tab", mostrarLogin);
        tabRegister.classList.toggle("btn-primary", !mostrarLogin);
        tabRegister.classList.toggle("btn-outline-primary", mostrarLogin);
        tabRegister.classList.toggle("active-tab", !mostrarLogin);
    }

    // Bloque auxiliar para comprobar en servidor si existe una sesion iniciada.
    async function verificarSesionActiva() {
        const resultado = await enviarPeticion("api/session.php", "GET");

        if (resultado.ok && resultado.autenticado) {
            window.location.href = "agenda.php";
        }
    }
});

// Bloque utilitario: peticiones fetch con manejo seguro de errores.
async function enviarPeticion(url, metodo, payload = null) {
    try {
        const opciones = {
            method: metodo,
            headers: {
                "Content-Type": "application/json"
            }
        };

        if (payload) {
            opciones.body = JSON.stringify(payload);
        }

        const respuesta = await fetch(url, opciones);
        const texto = await respuesta.text();

        let data;
        try {
            data = JSON.parse(texto);
        } catch (errorParse) {
            return {
                ok: false,
                mensaje: texto || `Error HTTP ${respuesta.status}`
            };
        }

        if (!respuesta.ok && !data.mensaje) {
            data.mensaje = `Error HTTP ${respuesta.status}`;
        }

        return data;
    } catch (error) {
        return {
            ok: false,
            mensaje: "Error de conexion con el servidor."
        };
    }
}

// Bloque utilitario: centraliza el uso de SweetAlert2 para toda la vista de autenticacion.
function mostrarAlerta(icon, title, text) {
    const opcionesTema = obtenerOpcionesTemaSwal();
    return Swal.fire({
        icon,
        title,
        text,
        confirmButtonColor: opcionesTema.confirmButtonColor,
        background: opcionesTema.background,
        color: opcionesTema.color
    });
}

// Bloque utilitario: validacion simple de correo con expresion regular.
function validarCorreo(correo) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(correo);
}

// Bloque utilitario: adapta colores de SweetAlert2 al tema activo.
function obtenerOpcionesTemaSwal() {
    const tema = document.documentElement.getAttribute("data-app-theme") || document.documentElement.getAttribute("data-bs-theme");
    const esOscuro = tema === "dark";
    return {
        confirmButtonColor: esOscuro ? "#6e92bb" : "#2f5e8c",
        background: esOscuro ? "#152131" : "#ffffff",
        color: esOscuro ? "#e8eef6" : "#1d2a38"
    };
}
