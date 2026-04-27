// Bloque de constantes globales para controlar el almacenamiento del tema.
const CLAVE_TEMA = "app-theme";

// Bloque principal: inicializa el tema y registra eventos del boton de cambio.
document.addEventListener("DOMContentLoaded", () => {
    const botonesTema = document.querySelectorAll(".theme-toggle");
    const temaInicial = obtenerTemaActual();

    aplicarTema(temaInicial, false);
    actualizarControlesTema(temaInicial, botonesTema);

    botonesTema.forEach((boton) => {
        boton.addEventListener("click", () => {
            const temaSiguiente = obtenerTemaActual() === "dark" ? "light" : "dark";
            aplicarTema(temaSiguiente, true);
            actualizarControlesTema(temaSiguiente, botonesTema);
        });
    });
});

// Bloque utilitario: determina el tema efectivo (guardado o preferencia del sistema).
function obtenerTemaActual() {
    const guardado = leerTemaGuardado();
    if (guardado === "dark" || guardado === "light") {
        return guardado;
    }

    const sistemaOscuro = window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches;
    return sistemaOscuro ? "dark" : "light";
}

// Bloque utilitario: aplica el tema en html y body, y opcionalmente lo guarda.
function aplicarTema(tema, persistir) {
    document.documentElement.setAttribute("data-bs-theme", tema);
    document.documentElement.setAttribute("data-app-theme", tema);

    if (document.body) {
        document.body.classList.remove("theme-light", "theme-dark");
        document.body.classList.add(tema === "dark" ? "theme-dark" : "theme-light");
    }

    if (persistir) {
        guardarTema(tema);
    }
}

// Bloque utilitario: actualiza icono y texto del boton segun el tema.
function actualizarControlesTema(tema, botonesTema) {
    botonesTema.forEach((boton) => {
        const icono = boton.querySelector(".theme-icon");
        const texto = boton.querySelector(".theme-text");

        if (tema === "dark") {
            if (icono) {
                icono.classList.remove("bi-moon-stars-fill");
                icono.classList.add("bi-brightness-high-fill");
            }
            if (texto) texto.textContent = "Modo claro";
            boton.classList.add("is-dark");
        } else {
            if (icono) {
                icono.classList.remove("bi-brightness-high-fill");
                icono.classList.add("bi-moon-stars-fill");
            }
            if (texto) texto.textContent = "Modo oscuro";
            boton.classList.remove("is-dark");
        }
    });
}

// Bloque utilitario: lee tema guardado con control de errores.
function leerTemaGuardado() {
    try {
        return localStorage.getItem(CLAVE_TEMA);
    } catch (error) {
        return null;
    }
}

// Bloque utilitario: guarda tema en localStorage con control de errores.
function guardarTema(tema) {
    try {
        localStorage.setItem(CLAVE_TEMA, tema);
    } catch (error) {
        // Si el navegador bloquea almacenamiento, el tema aun se aplica en memoria.
    }
}
