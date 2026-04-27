// Bloque de catalogo: reglas de pais para validacion y formato telefonico.
const REGLAS_PAISES = {
    GT: { iso: "GT", nombre: "Guatemala", codigo: "+502", min: 8, max: 8, grupos: [4, 4], ejemplo: "+502-2300-5000", prefijo: "+502-" },
    SV: { iso: "SV", nombre: "El Salvador", codigo: "+503", min: 8, max: 8, grupos: [4, 4], ejemplo: "+503-2100-3000", prefijo: "+503-" },
    HN: { iso: "HN", nombre: "Honduras", codigo: "+504", min: 8, max: 8, grupos: [4, 4], ejemplo: "+504-2234-5678", prefijo: "+504-" },
    NI: { iso: "NI", nombre: "Nicaragua", codigo: "+505", min: 8, max: 8, grupos: [4, 4], ejemplo: "+505-2222-3333", prefijo: "+505-" },
    CR: { iso: "CR", nombre: "Costa Rica", codigo: "+506", min: 8, max: 8, grupos: [4, 4], ejemplo: "+506-2222-3333", prefijo: "+506-" },
    PA: { iso: "PA", nombre: "Panama", codigo: "+507", min: 8, max: 8, grupos: [4, 4], ejemplo: "+507-2233-4455", prefijo: "+507-" },
    MX: { iso: "MX", nombre: "Mexico", codigo: "+52", min: 10, max: 10, grupos: [2, 4, 4], ejemplo: "+52-55-1234-5678", prefijo: "+52-" },
    US: { iso: "US", nombre: "Estados Unidos", codigo: "+1", min: 10, max: 10, grupos: [3, 3, 4], ejemplo: "+1-415-555-7788", prefijo: "+1-" },
    CO: { iso: "CO", nombre: "Colombia", codigo: "+57", min: 10, max: 10, grupos: [3, 3, 4], ejemplo: "+57-300-555-8899", prefijo: "+57-" },
    ES: { iso: "ES", nombre: "Espana", codigo: "+34", min: 9, max: 9, grupos: [3, 3, 3], ejemplo: "+34-912-345-678", prefijo: "+34-" }
};

// Bloque principal: inicia CRUD, validaciones y eventos de interfaz.
document.addEventListener("DOMContentLoaded", () => {
    const formContacto = document.getElementById("form-contacto");
    const tablaContactos = document.getElementById("tabla-contactos");
    const btnCancelarEdicion = document.getElementById("btn-cancelar-edicion");
    const btnGuardar = document.getElementById("btn-guardar");
    const btnLogout = document.getElementById("btn-logout");
    const btnExportarCsv = document.getElementById("btn-exportar-csv");
    const filtroContactos = document.getElementById("filtro-contactos");

    const inputNombre = document.getElementById("contacto-nombre");
    const selectPais = document.getElementById("contacto-pais");
    const inputCodigo = document.getElementById("contacto-codigo");
    const inputTelefono = document.getElementById("contacto-telefono");
    const inputCorreo = document.getElementById("contacto-correo");
    const inputDireccion = document.getElementById("contacto-direccion");
    const inputNotas = document.getElementById("contacto-notas");
    const inputFavorito = document.getElementById("contacto-favorito");

    let contactoEnEdicion = null;
    let cacheContactos = [];
    let temporizadorBusqueda = null;

    // Bloque de inicializacion de codigo y placeholder segun pais seleccionado.
    sincronizarPaisConTelefono(true);

    // Bloque de carga inicial de contactos.
    cargarContactos();

    // Bloque de submit: valida, normaliza y crea/actualiza contacto.
    formContacto.addEventListener("submit", async (event) => {
        event.preventDefault();

        const validacion = validarFormulario();
        if (!validacion.ok) {
            mostrarAlerta("warning", "Datos invalidos", validacion.mensaje);
            return;
        }

        const payload = {
            id: contactoEnEdicion,
            nombre: validacion.data.nombre,
            pais_iso: validacion.data.pais_iso,
            telefono: validacion.data.telefono,
            correo: validacion.data.correo,
            direccion: validacion.data.direccion,
            favorito: validacion.data.favorito,
            notas: validacion.data.notas
        };

        const esEdicion = Boolean(contactoEnEdicion);
        const endpoint = esEdicion ? "api/contacts/update.php" : "api/contacts/create.php";
        const metodo = esEdicion ? "PUT" : "POST";
        const resultado = await enviarPeticion(endpoint, metodo, payload);

        if (!resultado.ok) {
            mostrarAlerta("error", "No fue posible guardar", resultado.mensaje || "Revisa los datos e intenta nuevamente.");
            return;
        }

        await mostrarAlerta("success", esEdicion ? "Contacto actualizado" : "Contacto creado", resultado.mensaje || "Operacion completada.");
        limpiarFormulario();
        await cargarContactos(filtroContactos.value.trim());
    });

    // Bloque para cancelar edicion actual.
    btnCancelarEdicion.addEventListener("click", () => {
        limpiarFormulario();
    });

    // Bloque de cambio de pais para adaptar codigo y sugerencia de telefono.
    selectPais.addEventListener("change", () => {
        sincronizarPaisConTelefono(true);
    });

    // Bloque de control de teclado para bloquear letras en telefono.
    inputTelefono.addEventListener("keydown", (event) => {
        if (!permitirTeclaTelefono(event, inputTelefono)) {
            event.preventDefault();
        }
    });

    // Bloque de saneamiento en tiempo real para quitar caracteres no permitidos.
    inputTelefono.addEventListener("input", () => {
        inputTelefono.value = sanitizarTelefono(inputTelefono.value);
    });

    // Bloque de control de pegado para mantener formato limpio de telefono.
    inputTelefono.addEventListener("paste", () => {
        setTimeout(() => {
            inputTelefono.value = sanitizarTelefono(inputTelefono.value);
        }, 0);
    });

    // Bloque de busqueda por nombre o telefono.
    filtroContactos.addEventListener("input", () => {
        clearTimeout(temporizadorBusqueda);
        temporizadorBusqueda = setTimeout(() => {
            cargarContactos(filtroContactos.value.trim());
        }, 260);
    });

    // Bloque de exportacion CSV.
    btnExportarCsv.addEventListener("click", () => {
        window.location.href = "api/contacts/export.php";
    });

    // Bloque para cerrar sesion con confirmacion.
    btnLogout.addEventListener("click", async () => {
        const opcionesTema = obtenerOpcionesTemaSwal();
        const confirmacion = await Swal.fire({
            icon: "question",
            title: "Cerrar sesion",
            text: "Deseas salir de tu cuenta?",
            showCancelButton: true,
            confirmButtonText: "Si, salir",
            cancelButtonText: "Cancelar",
            confirmButtonColor: opcionesTema.confirmButtonColor,
            background: opcionesTema.background,
            color: opcionesTema.color
        });

        if (!confirmacion.isConfirmed) {
            return;
        }

        const resultado = await enviarPeticion("api/logout.php", "POST");
        if (resultado.ok) {
            window.location.href = "index.php";
            return;
        }

        mostrarAlerta("error", "No se pudo cerrar sesion", resultado.mensaje || "Intenta nuevamente.");
    });

    // Bloque de acciones en tabla para editar o eliminar.
    tablaContactos.addEventListener("click", async (event) => {
        const boton = event.target.closest("button[data-action]");
        if (!boton) {
            return;
        }

        const id = Number(boton.dataset.id);
        const accion = boton.dataset.action;

        if (accion === "edit") {
            cargarContactoEnFormulario(id);
            return;
        }

        if (accion === "delete") {
            await eliminarContacto(id);
        }
    });

    // Bloque de carga de contactos con filtro opcional.
    async function cargarContactos(termino = "") {
        const query = termino ? `?q=${encodeURIComponent(termino)}` : "";
        const resultado = await enviarPeticion(`api/contacts/list.php${query}`, "GET");

        if (!resultado.ok) {
            tablaContactos.innerHTML = '<tr><td colspan="6" class="text-center text-secondary py-4">No se pudieron cargar los contactos.</td></tr>';
            cacheContactos = [];
            return;
        }

        cacheContactos = Array.isArray(resultado.contactos) ? resultado.contactos : [];
        pintarTabla(cacheContactos);
    }

    // Bloque de pintado de tabla con columnas solicitadas.
    function pintarTabla(contactos) {
        if (!Array.isArray(contactos) || contactos.length === 0) {
            tablaContactos.innerHTML = '<tr><td colspan="6" class="text-center text-secondary py-4">No hay contactos que coincidan con la busqueda.</td></tr>';
            return;
        }

        tablaContactos.innerHTML = contactos.map((contacto) => {
            const nombreSeguro = escaparHTML(contacto.nombre || "");
            const paisSeguro = escaparHTML(contacto.pais_nombre || "");
            const telefonoSeguro = escaparHTML(contacto.telefono || "");
            const correoSeguro = escaparHTML(contacto.correo || "-");
            const direccionSegura = escaparHTML(contacto.direccion || "-");
            const notasSeguras = escaparHTML(contacto.notas || "");
            const estrella = contacto.favorito ? '<i class="bi bi-star-fill text-warning ms-1" title="Favorito"></i>' : "";
            const notasHtml = notasSeguras ? `<small class="d-block text-secondary mt-1">${notasSeguras}</small>` : "";

            return `
                <tr>
                    <td class="fw-semibold">${nombreSeguro}${estrella}${notasHtml}</td>
                    <td>${paisSeguro}</td>
                    <td>${telefonoSeguro}</td>
                    <td>${correoSeguro}</td>
                    <td>${direccionSegura}</td>
                    <td>
                        <div class="row-actions">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-action="edit" data-id="${contacto.id}">Editar</button>
                            <button type="button" class="btn btn-sm btn-danger" data-action="delete" data-id="${contacto.id}">Eliminar</button>
                        </div>
                    </td>
                </tr>
            `;
        }).join("");
    }

    // Bloque de carga de contacto al formulario para edicion.
    function cargarContactoEnFormulario(id) {
        const contacto = cacheContactos.find((item) => Number(item.id) === Number(id));
        if (!contacto) {
            mostrarAlerta("warning", "Contacto no encontrado", "Recarga el listado e intenta otra vez.");
            return;
        }

        contactoEnEdicion = Number(contacto.id);
        document.getElementById("contacto-id").value = String(contacto.id);
        inputNombre.value = contacto.nombre || "";
        selectPais.value = contacto.pais_iso || "GT";
        sincronizarPaisConTelefono(false);
        inputTelefono.value = contacto.telefono || "";
        inputCorreo.value = contacto.correo || "";
        inputDireccion.value = contacto.direccion || "";
        inputNotas.value = contacto.notas || "";
        inputFavorito.checked = Boolean(contacto.favorito);

        btnGuardar.textContent = "Actualizar contacto";
        btnCancelarEdicion.classList.remove("hidden");
        inputNombre.focus();
    }

    // Bloque de eliminacion con confirmacion.
    async function eliminarContacto(id) {
        const opcionesTema = obtenerOpcionesTemaSwal();
        const confirmacion = await Swal.fire({
            icon: "warning",
            title: "Eliminar contacto",
            text: "Esta accion no se puede deshacer.",
            showCancelButton: true,
            confirmButtonText: "Si, eliminar",
            cancelButtonText: "Cancelar",
            confirmButtonColor: opcionesTema.dangerButtonColor,
            background: opcionesTema.background,
            color: opcionesTema.color
        });

        if (!confirmacion.isConfirmed) {
            return;
        }

        const resultado = await enviarPeticion("api/contacts/delete.php", "DELETE", { id });
        if (!resultado.ok) {
            mostrarAlerta("error", "No se pudo eliminar", resultado.mensaje || "Intenta nuevamente.");
            return;
        }

        await mostrarAlerta("success", "Contacto eliminado", resultado.mensaje || "El contacto fue eliminado.");
        limpiarFormulario();
        await cargarContactos(filtroContactos.value.trim());
    }

    // Bloque de limpieza de formulario y estado de edicion.
    function limpiarFormulario() {
        contactoEnEdicion = null;
        formContacto.reset();
        document.getElementById("contacto-id").value = "";
        selectPais.value = "GT";
        sincronizarPaisConTelefono(true);
        btnGuardar.textContent = "Guardar contacto";
        btnCancelarEdicion.classList.add("hidden");
        inputFavorito.checked = false;
    }

    // Bloque para sincronizar codigo/placeholder al cambiar de pais.
    function sincronizarPaisConTelefono(prefijarTelefono) {
        const regla = REGLAS_PAISES[selectPais.value] || REGLAS_PAISES.GT;
        inputCodigo.value = regla.codigo;
        inputTelefono.placeholder = `Ejemplo: ${regla.ejemplo}`;

        if (!prefijarTelefono) {
            return;
        }

        const valorActual = inputTelefono.value.trim();
        if (valorActual === "" || valorActual === "+" || valorActual === regla.codigo || valorActual === `${regla.codigo}-`) {
            inputTelefono.value = regla.prefijo;
        }
    }

    // Bloque de validacion integral del formulario con mensajes especificos.
    function validarFormulario() {
        const nombre = normalizarTexto(inputNombre.value);
        const paisIso = selectPais.value;
        const telefonoEntrada = normalizarTexto(inputTelefono.value);
        const correo = normalizarTexto(inputCorreo.value).toLowerCase();
        const direccion = normalizarTexto(inputDireccion.value);
        const notas = normalizarTexto(inputNotas.value);
        const favorito = inputFavorito.checked;

        if (!nombre) {
            return { ok: false, mensaje: "El nombre es obligatorio." };
        }

        if (nombre.length > 50) {
            return { ok: false, mensaje: "El nombre no puede superar 50 caracteres." };
        }

        if (!/[A-Za-zÀ-ÿ]/.test(nombre)) {
            return { ok: false, mensaje: "El nombre debe contener letras reales, no solo numeros." };
        }

        if (!/^[A-Za-zÀ-ÿ\s.'-]+$/.test(nombre)) {
            return { ok: false, mensaje: "El nombre contiene caracteres no permitidos." };
        }

        const telefonoNormalizado = normalizarTelefonoPorPais(paisIso, telefonoEntrada);
        if (!telefonoNormalizado.ok) {
            return { ok: false, mensaje: telefonoNormalizado.mensaje };
        }

        if (correo) {
            if (correo === "opcional@correo.com") {
                return { ok: false, mensaje: 'El correo "opcional@correo.com" no es valido para guardar.' };
            }

            if (!validarCorreo(correo)) {
                return { ok: false, mensaje: "El correo debe tener formato usuario@dominio.extension." };
            }
        }

        if (direccion.length > 100) {
            return { ok: false, mensaje: "La direccion no puede superar 100 caracteres." };
        }

        if (notas.length > 255) {
            return { ok: false, mensaje: "Las observaciones no pueden superar 255 caracteres." };
        }

        return {
            ok: true,
            data: {
                nombre,
                pais_iso: telefonoNormalizado.paisIso,
                telefono: telefonoNormalizado.formato,
                telefono_e164: telefonoNormalizado.e164,
                correo: correo || "",
                direccion: direccion || "",
                favorito,
                notas: notas || ""
            }
        };
    }
});

// Bloque utilitario: normaliza telefono segun reglas de pais (frontend).
function normalizarTelefonoPorPais(paisIso, telefonoEntrada) {
    const regla = REGLAS_PAISES[paisIso];
    if (!regla) {
        return { ok: false, mensaje: "Debes seleccionar un pais valido." };
    }

    if (!telefonoEntrada) {
        return { ok: false, mensaje: "El telefono es obligatorio." };
    }

    if (!/^[0-9+\-\s()]+$/.test(telefonoEntrada)) {
        return { ok: false, mensaje: "El telefono solo permite numeros, guiones, espacios, parentesis y +." };
    }

    const codigoNumerico = regla.codigo.replace("+", "");
    const limpio = telefonoEntrada.trim();
    const todosLosDigitos = obtenerDigitos(limpio);
    let nacional = "";

    if (paisIso === "GT") {
        if (/^\d{8}$/.test(limpio)) {
            return { ok: false, mensaje: "Para Guatemala usa 2300-5000 o +502-2300-5000." };
        }

        if (/^\d{4}-\d{4}$/.test(limpio)) {
            nacional = limpio.replace("-", "");
        } else {
            if (!limpio.startsWith("+")) {
                return { ok: false, mensaje: "Para Guatemala usa +502-XXXX-XXXX o local XXXX-XXXX." };
            }

            if (!limpio.startsWith(regla.codigo)) {
                return { ok: false, mensaje: "El codigo de pais no coincide con Guatemala (+502)." };
            }

            if (!todosLosDigitos.startsWith(codigoNumerico)) {
                return { ok: false, mensaje: "El numero no contiene un codigo de pais valido." };
            }

            nacional = todosLosDigitos.slice(codigoNumerico.length);
        }
    } else {
        if (limpio.startsWith("+")) {
            if (!limpio.startsWith(regla.codigo)) {
                return { ok: false, mensaje: "El codigo de pais no coincide con el pais seleccionado." };
            }

            if (!todosLosDigitos.startsWith(codigoNumerico)) {
                return { ok: false, mensaje: "El numero no contiene un codigo de pais valido." };
            }

            nacional = todosLosDigitos.slice(codigoNumerico.length);
        } else {
            nacional = todosLosDigitos;
        }
    }

    if (nacional.length < regla.min || nacional.length > regla.max) {
        return { ok: false, mensaje: "La cantidad de digitos no coincide con el pais seleccionado." };
    }

    const longitudE164 = (codigoNumerico + nacional).length;
    if (longitudE164 < 9 || longitudE164 > 15) {
        return { ok: false, mensaje: "El telefono debe cumplir E.164 (9 a 15 digitos)." };
    }

    const formato = `${regla.codigo}-${formatearPorGrupos(nacional, regla.grupos)}`;
    const e164 = `+${codigoNumerico}${nacional}`;
    return { ok: true, paisIso, formato, e164 };
}

// Bloque utilitario: permite solo teclas validas para el telefono.
function permitirTeclaTelefono(evento, input) {
    if (evento.ctrlKey || evento.metaKey) {
        return true;
    }

    const teclasControl = ["Backspace", "Delete", "ArrowLeft", "ArrowRight", "Home", "End", "Tab", "Enter"];
    if (teclasControl.includes(evento.key)) {
        return true;
    }

    if (evento.key.length > 1) {
        return true;
    }

    if (!/[0-9+\-\s()]/.test(evento.key)) {
        return false;
    }

    if (evento.key === "+") {
        const yaTieneMas = input.value.includes("+");
        const cursor = input.selectionStart ?? 0;
        return !yaTieneMas && cursor === 0;
    }

    return true;
}

// Bloque utilitario: elimina caracteres no permitidos del telefono.
function sanitizarTelefono(valor) {
    let limpio = valor.replace(/[^0-9+\-\s()]/g, "");

    if (limpio.indexOf("+") > 0) {
        limpio = limpio.replace(/\+/g, "");
    }

    if (limpio.startsWith("+")) {
        limpio = `+${limpio.slice(1).replace(/\+/g, "")}`;
    } else {
        limpio = limpio.replace(/\+/g, "");
    }

    return limpio;
}

// Bloque utilitario: compacta espacios extra.
function normalizarTexto(valor) {
    return String(valor || "").trim().replace(/\s+/g, " ");
}

// Bloque utilitario: obtiene solo digitos de un texto.
function obtenerDigitos(valor) {
    return String(valor || "").replace(/\D/g, "");
}

// Bloque utilitario: formatea digitos con grupos fijos por pais.
function formatearPorGrupos(digitos, grupos) {
    const partes = [];
    let cursor = 0;

    grupos.forEach((tamano) => {
        if (cursor >= digitos.length) {
            return;
        }

        partes.push(digitos.substring(cursor, cursor + tamano));
        cursor += tamano;
    });

    if (cursor < digitos.length) {
        partes.push(digitos.substring(cursor));
    }

    return partes.join("-");
}

// Bloque utilitario: peticiones al backend con manejo robusto de respuestas.
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

        const contentType = respuesta.headers.get("content-type") || "";
        if (contentType.includes("text/csv")) {
            return { ok: true };
        }

        const texto = await respuesta.text();
        let data;

        try {
            data = JSON.parse(texto);
        } catch (errorParse) {
            return { ok: false, mensaje: texto || `Error HTTP ${respuesta.status}` };
        }

        if (!respuesta.ok && !data.mensaje) {
            data.mensaje = `Error HTTP ${respuesta.status}`;
        }

        return data;
    } catch (error) {
        return { ok: false, mensaje: "No fue posible conectar con el servidor." };
    }
}

// Bloque utilitario: alertas SweetAlert2.
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

// Bloque utilitario: escape de HTML para evitar inyeccion en tabla.
function escaparHTML(texto) {
    const valor = String(texto ?? "");
    return valor
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#39;");
}

// Bloque utilitario: valida correo con extension de dominio.
function validarCorreo(correo) {
    const regex = /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i;
    return regex.test(correo);
}

// Bloque utilitario: adapta SweetAlert2 al tema activo.
function obtenerOpcionesTemaSwal() {
    const tema = document.documentElement.getAttribute("data-app-theme") || document.documentElement.getAttribute("data-bs-theme");
    const esOscuro = tema === "dark";
    return {
        confirmButtonColor: esOscuro ? "#6e92bb" : "#2f5e8c",
        dangerButtonColor: esOscuro ? "#d46b6b" : "#b54848",
        background: esOscuro ? "#152131" : "#ffffff",
        color: esOscuro ? "#e8eef6" : "#1d2a38"
    };
}
