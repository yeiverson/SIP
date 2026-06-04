// // multi-step-form.js

// document.addEventListener('DOMContentLoaded', function() {
//     const form = document.getElementById('multi-step-form');
//     const steps = Array.from(form.querySelectorAll('.form-step'));
//     const prevBtn = document.getElementById('prev-btn');
//     const nextBtn = document.getElementById('next-btn');
//     const submitBtn = document.getElementById('submit-btn');
//     const progressContainer = document.getElementById('progress-bar');
//     const progressSteps = Array.from(progressContainer.querySelectorAll('.progress-step'));
//     const progressLines = Array.from(progressContainer.querySelectorAll('.progress-line'));

//     let currentStepIndex = 0;

//     function showStep(index) {
//         steps.forEach((step, stepIndex) => {
//             step.classList.toggle('active', stepIndex === index);
//         });

//         // Actualizar botones
//         prevBtn.style.display = index === 0 ? 'none' : 'block';
//         if (index === steps.length - 1) {
//             nextBtn.style.display = 'none';
//             submitBtn.style.display = 'block';
//         } else {
//             nextBtn.style.display = 'block';
//             submitBtn.style.display = 'none';
//         }

//         updateProgressBar(index);
//     }

//     function updateProgressBar(index) {
//         progressSteps.forEach((step, stepIndex) => {
//             if (stepIndex <= index) {
//                 step.classList.add('active');
//             } else {
//                 step.classList.remove('active');
//             }
            
//             // Marcar como completado los pasos anteriores
//             if (stepIndex < index) {
//                 step.classList.add('completed');
//             } else {
//                 step.classList.remove('completed');
//             }
//         });

//         progressLines.forEach((line, lineIndex) => {
//             if (lineIndex < index) {
//                 line.classList.add('completed');
//             } else {
//                 line.classList.remove('completed');
//             }
//         });
//     }

//     // Funcionalidad básica de los botones
//     nextBtn.addEventListener('click', () => {
//         if (currentStepIndex < steps.length - 1) {
//             currentStepIndex++;
//             showStep(currentStepIndex);
//         }
//     });

//     prevBtn.addEventListener('click', () => {
//         if (currentStepIndex > 0) {
//             currentStepIndex--;
//             showStep(currentStepIndex);
//         }
//     });

//     // Iniciar
//     showStep(currentStepIndex);

//     // Funcionalidad para mostrar el nombre del archivo adjunto
//     const fileInputs = form.querySelectorAll('input[type="file"]');
//     fileInputs.forEach(input => {
//         input.addEventListener('change', function(e) {
//             const fileNameSpan = this.parentElement.querySelector('.file-input-name');
//             if (this.files && this.files.length > 0) {
//                 fileNameSpan.textContent = this.files[0].name;
//                 fileNameSpan.classList.remove('file-input-name'); // Opcional: cambiar estilo si hay archivo
//             } else {
//                 fileNameSpan.textContent = 'Sin archivos seleccionados';
//             }
//         });
//     });
// });

// multi-step-form.
// document.addEventListener('DOMContentLoaded', function() {
//     const form = document.getElementById('multi-step-form');
//     const steps = Array.from(document.querySelectorAll('.form-step'));
//     const prevBtn = document.getElementById('prev-btn');
//     const originalNextBtn = document.getElementById('next-btn');
//     const submitBtn = document.getElementById('submit-btn');
    
//     // --- TÉCNICA DE INGENIERÍA: CLONACIÓN PARA LIMPIAR EVENTOS ---
//     const nextBtn = originalNextBtn.cloneNode(true);
//     originalNextBtn.parentNode.replaceChild(nextBtn, originalNextBtn);
//     // -----------------------------------------------------------

//     const progressSteps = Array.from(document.querySelectorAll('.progress-step'));
//     const progressLines = Array.from(document.querySelectorAll('.progress-line'));

//     let currentStepIndex = 0;

//     function showStep(index) {
//         steps.forEach((step, stepIndex) => {
//             step.classList.toggle('active', stepIndex === index);
//             // Forzamos que los pasos no activos estén ocultos visualmente
//             step.style.display = (stepIndex === index) ? 'block' : 'none';
//         });

//         prevBtn.style.display = index === 0 ? 'none' : 'block';
        
//         if (index === steps.length - 1) {
//             nextBtn.style.display = 'none';
//             submitBtn.style.display = 'block';
//         } else {
//             nextBtn.style.display = 'block';
//             submitBtn.style.display = 'none';
//         }

//         updateProgressBar(index);
//     }

//     function updateProgressBar(index) {
//         progressSteps.forEach((step, stepIndex) => {
//             step.classList.toggle('active', stepIndex <= index);
//             step.classList.toggle('completed', stepIndex < index);
//         });
//         progressLines.forEach((line, lineIndex) => {
//             line.classList.toggle('completed', lineIndex < index);
//         });
//     }

//     // Nueva lógica del botón Siguiente (Limpia y única)
//     nextBtn.addEventListener('click', function(e) {
//         e.preventDefault(); // Evitamos cualquier acción por defecto
        
//         const currentStep = steps[currentStepIndex];
//         const inputs = Array.from(currentStep.querySelectorAll('input[required], select[required]'));
        
//         let allValid = true;

//         inputs.forEach(input => {
//             if (input.value.trim() === "") {
//                 allValid = false;
//                 input.style.border = "2px solid #ff4d4d";
//                 input.style.backgroundColor = "#fff5f5";
//             } else {
//                 input.style.border = "";
//                 input.style.backgroundColor = "";
//             }
//         });

//         if (!allValid) {
//             alert("Error: Hay campos obligatorios vacíos en este paso.");
//             return false;
//         }

//         // Si es válido, avanzamos manualmente
//         if (currentStepIndex < steps.length - 1) {
//             currentStepIndex++;
//             showStep(currentStepIndex);
//         }
//     });

//     prevBtn.onclick = function() {
//         if (currentStepIndex > 0) {
//             currentStepIndex--;
//             showStep(currentStepIndex);
//         }
//     };

//     // Validación de contraseñas
//     form.onsubmit = function(e) {
//         const pass = document.getElementById('password').value;
//         const confirm = document.getElementById('confirm_password').value;

//         if (pass !== confirm) {
//             e.preventDefault();
//             alert("Las contraseñas no coinciden.");
//             return false;
//         }
//     };

//     showStep(currentStepIndex);
// });





function validarEmailBasico(valor) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(valor).trim());
}

function validarCedulaBasico(valor) {
    const solo = String(valor).replace(/\D/g, '');
    return solo.length >= 7 && solo.length <= 8 && /^\d+$/.test(solo);
}

function validarPasswordComoPhp(valor) {
    if (valor.length < 8) return false;
    try {
        return /[\p{Lu}]/u.test(valor);
    } catch (err) {
        return /[A-ZÁÉÍÓÚÜÑ]/.test(valor);
    }
}

function initRegistroAjax(form) {
    const nextBtn = document.getElementById('next-btn');
    if (!nextBtn) return;

    nextBtn.addEventListener('click', function (e) {
        e.preventDefault();

        const tipoDoc = form.querySelector('select[name="tipoDocumento"]').value;
        const cedula = form.querySelector('input[name="cedula"]').value;
        const emailEl = form.querySelector('#email');
        const email = emailEl ? emailEl.value : '';
        const pass = document.getElementById('password').value;
        const confirm = document.getElementById('confirm_password').value;

        if (!tipoDoc || !cedula || !email || !pass || !confirm) {
            alert('Error: tipo de documento, cédula, correo y contraseñas son obligatorios.');
            return;
        }

        if (!validarCedulaBasico(cedula)) {
            alert('La cédula debe tener entre 7 y 8 dígitos numéricos.');
            return;
        }

        if (!validarEmailBasico(email)) {
            alert('El correo electrónico no tiene un formato válido.');
            return;
        }

        if (!validarPasswordComoPhp(pass)) {
            alert('La contraseña debe tener al menos 8 caracteres e incluir al menos una letra mayúscula.');
            return;
        }

        if (pass !== confirm) {
            alert('Las contraseñas no coinciden.');
            return;
        }

        const formData = new FormData(form);

        fetch('procesar_registro.php', {
            method: 'POST',
            body: formData
        })
            .then(function (response) {
                return response.text();
            })
            .then(function (text) {
                var data;
                try {
                    data = JSON.parse(text);
                } catch (err) {
                    throw new Error('El servidor no devolvió JSON. Revisa procesar_registro.php.');
                }
                if (data.status === 'success') {
                    alert('¡Usuario creado con éxito! Ahora ingresa con tu cédula y clave para completar tus datos.');
                    window.location.href = 'Inicio.php?registro=exitoso';
                } else {
                    alert('Error: ' + (data.message || 'No se pudo registrar.'));
                }
            })
            .catch(function (error) {
                console.error('Error:', error);
                alert('Hubo un error al conectar con el servidor de la universidad.');
            });
    });
}

/** Teléfono Venezuela: 11 dígitos, inicia con 0 (solo números en el valor). */
function telefonoVeValido(valor) {
    var d = String(valor || '').replace(/\D/g, '');
    return d.length === 11 && d.charAt(0) === '0';
}

/** Aplica solo dígitos y longitud máxima (evita letras al escribir o pegar). */
function bindSoloDigitosTelefono(input, maxLen) {
    var max = maxLen || 11;
    function filtrar() {
        var d = input.value.replace(/\D/g, '').slice(0, max);
        if (input.value !== d) {
            input.value = d;
        }
    }
    input.addEventListener('input', filtrar);
    input.addEventListener('blur', filtrar);
    input.addEventListener('paste', function (e) {
        e.preventDefault();
        var t = (e.clipboardData || window.clipboardData).getData('text') || '';
        input.value = String(t).replace(/\D/g, '').slice(0, max);
    });
}

/** Promedio: solo números y un separador decimal (coma o punto). */
function bindPromedioDecimal(input) {
    input.addEventListener('input', function () {
        var v = input.value;
        var out = '';
        var sep = false;
        for (var i = 0; i < v.length; i++) {
            var c = v.charAt(i);
            if (c >= '0' && c <= '9') {
                out += c;
            } else if ((c === ',' || c === '.') && !sep && out.length > 0) {
                out += ',';
                sep = true;
            }
        }
        if (input.value !== out) {
            input.value = out;
        }
    });
}

function promedioValido(valor) {
    var s = String(valor || '').trim().replace(',', '.');
    if (!s || !/^\d+(\.\d+)?$/.test(s)) {
        return false;
    }
    var n = parseFloat(s);
    return !isNaN(n) && n >= 0 && n <= 100;
}

/**
 * Muestra u oculta un contenedor según el valor del control origen.
 *
 * @param {HTMLFormElement} form
 * @param {string} sourceId - id del select/input que dispara la condición
 * @param {string} containerId - id del contenedor a mostrar u ocultar
 * @param {string|boolean|function(string,HTMLElement):boolean} activateValue
 *        - string: visible si el valor coincide exactamente
 *        - true o '__ANY__': visible si hay cualquier valor no vacío
 *        - función: recibe (valor, elemento origen), devuelve si debe mostrarse
 * @param {{ marcarError?: function(HTMLElement,boolean): void }} [options]
 * @returns {function(): void} función sync para invocar al cargar o encadenar con otros handlers
 */
function toggleConditionalFields(form, sourceId, containerId, activateValue, options) {
    var opts = options || {};
    var marcarErr = opts.marcarError || function () {};

    var source = form.querySelector('#' + sourceId);
    var container = form.querySelector('#' + containerId);
    if (!source || !container) {
        return function () {};
    }

    function shouldShow() {
        if (typeof activateValue === 'function') {
            return activateValue(source.value, source);
        }
        if (activateValue === true || activateValue === '__ANY__') {
            var v = source.value;
            return v !== '' && v != null && String(v).trim() !== '';
        }
        return source.value === activateValue;
    }

    function clearField(el) {
        if (el.type === 'button' || el.type === 'submit' || el.type === 'hidden') {
            return;
        }
        if (el.type === 'checkbox' || el.type === 'radio') {
            el.checked = false;
        } else {
            el.value = '';
        }
        marcarErr(el, false);
    }

    function apply() {
        var show = shouldShow();
        var fields = container.querySelectorAll('input, select, textarea');

        if (show) {
            container.style.display = '';
            fields.forEach(function (el) {
                if (el.type === 'button' || el.type === 'submit' || el.type === 'hidden') {
                    return;
                }
                if (el.hasAttribute('data-no-required-if-visible')) {
                    return;
                }
                el.setAttribute('required', 'required');
            });
        } else {
            container.style.display = 'none';
            fields.forEach(function (el) {
                el.removeAttribute('required');
                clearField(el);
            });
        }
    }

    source.addEventListener('change', apply);
    apply();
    return apply;
}

function initPerfilMultipaso(form) {
    const steps = Array.from(form.querySelectorAll('.form-step'));
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const submitBtn = document.getElementById('submit-btn');
    const progressBar = document.getElementById('progress-bar');
    const progressSteps = progressBar ? Array.from(progressBar.querySelectorAll('.progress-step')) : [];
    const progressLines = progressBar ? Array.from(progressBar.querySelectorAll('.progress-line')) : [];

    if (steps.length === 0 || !nextBtn) return;

    var currentStepIndex = 0;

    form.querySelectorAll('.js-input-phone-ve').forEach(function (el) {
        bindSoloDigitosTelefono(el, 11);
    });
    form.querySelectorAll('.js-input-phone-ve-optional').forEach(function (el) {
        bindSoloDigitosTelefono(el, 11);
    });
    form.querySelectorAll('.js-input-promedio').forEach(function (el) {
        bindPromedioDecimal(el);
    });

    form.querySelectorAll('input[type="file"]').forEach(function (input) {
        input.addEventListener('change', function () {
            var wrap = input.closest('.file-input-wrapper');
            var nameSpan = wrap ? wrap.querySelector('.file-input-name') : null;
            if (nameSpan) {
                nameSpan.textContent =
                    input.files && input.files.length > 0 ? input.files[0].name : 'Sin archivos seleccionados';
            }
        });
    });

    function marcarError(el, on) {
        if (!el) return;
        el.style.border = on ? '2px solid #ff4d4d' : '';
        el.style.backgroundColor = on ? '#fff5f5' : '';
    }

    var optsToggle = { marcarError: marcarError };

    toggleConditionalFields(form, 'tipoResidencia', 'grupo-piso-apartamento', 'Apartamento', optsToggle);

    toggleConditionalFields(form, 'condicionUsuario', 'grupo-campos-militares', 'Militar', optsToggle);

    toggleConditionalFields(form, 'trabajaUnefa', 'grupo-laboral-sin-unefa', 'No', optsToggle);

    toggleConditionalFields(form, 'trabajaUnefa', 'grupo-area-unefa', 'Sí', optsToggle);

    toggleConditionalFields(form, 'municipio', 'grupo-parroquia', true, optsToggle);

    /**
     * Muestra #grupo-dedicacion si no trabaja en UNEFA, o si trabaja en UNEFA como Docente.
     */
    function syncDedicacionCombinada() {
        var tu = form.querySelector('#trabajaUnefa');
        var gd = form.querySelector('#grupo-dedicacion');
        var ded = form.querySelector('#dedicacion');
        if (!tu || !gd || !ded) {
            return;
        }

        var au = form.querySelector('#areaUnefa');
        var mostrar =
            tu.value === 'No' || (tu.value === 'Sí' && au && au.value === 'Docente');

        if (mostrar) {
            gd.style.display = '';
            ded.setAttribute('required', 'required');
        } else {
            gd.style.display = 'none';
            ded.removeAttribute('required');
            ded.value = '';
            marcarError(ded, false);
        }
    }

    var trabajaUnefaEl = form.querySelector('#trabajaUnefa');
    var areaUnefaEl = form.querySelector('#areaUnefa');
    if (trabajaUnefaEl) {
        trabajaUnefaEl.addEventListener('change', syncDedicacionCombinada);
    }
    if (areaUnefaEl) {
        areaUnefaEl.addEventListener('change', syncDedicacionCombinada);
    }

    syncDedicacionCombinada();

    function showStep(index) {
        steps.forEach(function (step, i) {
            step.classList.toggle('active', i === index);
            step.style.display = i === index ? 'block' : 'none';
        });

        if (prevBtn) prevBtn.style.display = index === 0 ? 'none' : 'block';
        nextBtn.style.display = index === steps.length - 1 ? 'none' : 'block';
        if (submitBtn) submitBtn.style.display = index === steps.length - 1 ? 'block' : 'none';

        progressSteps.forEach(function (p, i) {
            p.classList.toggle('active', i === index);
            p.classList.toggle('completed', i < index);
        });
        progressLines.forEach(function (line, i) {
            line.classList.toggle('completed', i < index);
        });

        if (steps[index] && steps[index].id === 'step-4') {
            syncDedicacionCombinada();
        }
    }

    function validarCamposRequeridosVisibles(stepEl) {
        var ok = true;
        var primero = null;

        var textLike = stepEl.querySelectorAll(
            'input[required]:not([type="radio"]):not([type="file"]):not([type="hidden"]), select[required], textarea[required]'
        );
        textLike.forEach(function (input) {
            if (input.offsetParent === null) return;
            var v = input.type === 'file' ? (input.files && input.files.length ? '1' : '') : (input.value || '').trim();
            if (!v) {
                ok = false;
                marcarError(input, true);
                if (!primero) primero = input;
            } else {
                marcarError(input, false);
            }
        });

        var radiosConRequired = stepEl.querySelectorAll('input[type="radio"][required]');
        var nombresRadio = {};
        radiosConRequired.forEach(function (r) {
            nombresRadio[r.name] = true;
        });
        Object.keys(nombresRadio).forEach(function (name) {
            var group = form.elements[name];
            var marcado = false;
            if (group) {
                if (typeof group.length === 'number') {
                    for (var ri = 0; ri < group.length; ri++) {
                        if (group[ri].checked) {
                            marcado = true;
                            break;
                        }
                    }
                } else if (group.checked) {
                    marcado = true;
                }
            }
            if (!marcado) {
                ok = false;
                if (!primero && group) {
                    primero = typeof group.length === 'number' ? group[0] : group;
                }
            }
        });

        if (!ok) {
            alert('Completa los campos obligatorios de este paso.');
            if (primero && primero.scrollIntoView) {
                primero.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return false;
        }
        return true;
    }

    function validarPaso(stepEl) {
        if (!validarCamposRequeridosVisibles(stepEl)) {
            return false;
        }

        if (stepEl.id === 'step-2') {
            var tipoRes = form.querySelector('#tipoResidencia');
            var inputApto = form.querySelector('#inputApartamento');
            var inputPiso = form.querySelector('#inputPiso');
            if (tipoRes && tipoRes.value === 'Apartamento') {
                if (inputPiso) {
                    var pi = (inputPiso.value || '').trim();
                    if (!pi) {
                        marcarError(inputPiso, true);
                        alert('Indica el piso.');
                        return false;
                    }
                    marcarError(inputPiso, false);
                }
                if (inputApto) {
                    var ap = (inputApto.value || '').trim();
                    if (!ap) {
                        marcarError(inputApto, true);
                        alert('Indica el número de apartamento.');
                        return false;
                    }
                    marcarError(inputApto, false);
                }
            }
        }

        if (stepEl.id === 'step-3') {
            var tel = stepEl.querySelector('input[name="telefono"]');
            var cel = stepEl.querySelector('input[name="celular"]');
            if (tel) {
                if (!telefonoVeValido(tel.value)) {
                    marcarError(tel, true);
                    alert('El teléfono fijo solo admite números: 11 dígitos e iniciar con 0.');
                    return false;
                }
                marcarError(tel, false);
            }
            if (cel) {
                if (!telefonoVeValido(cel.value)) {
                    marcarError(cel, true);
                    alert('El celular solo admite números: 11 dígitos e iniciar con 0.');
                    return false;
                }
                marcarError(cel, false);
            }
        }

        if (stepEl.id === 'step-4') {
            var tt = form.querySelector('input[name="telefonoTrabajo"]');
            if (tt) {
                var ttv = (tt.value || '').trim();
                if (ttv && !telefonoVeValido(ttv)) {
                    marcarError(tt, true);
                    alert('El teléfono de trabajo solo admite números: 11 dígitos e iniciar con 0, o déjelo vacío.');
                    return false;
                }
                marcarError(tt, false);
            }
            var prom = stepEl.querySelector('input[name="promedio"]');
            if (prom) {
                var pv = (prom.value || '').trim();
                if (!promedioValido(pv)) {
                    marcarError(prom, true);
                    alert('El promedio debe ser un número entre 0 y 100 (puede usar coma o punto decimal).');
                    return false;
                }
                marcarError(prom, false);
            }
        }

        return true;
    }

    nextBtn.addEventListener('click', function (e) {
        e.preventDefault();
        if (!validarPaso(steps[currentStepIndex])) {
            return;
        }
        if (currentStepIndex < steps.length - 1) {
            currentStepIndex += 1;
            showStep(currentStepIndex);
        }
    });

    if (prevBtn) {
        prevBtn.addEventListener('click', function () {
            if (currentStepIndex > 0) {
                currentStepIndex -= 1;
                showStep(currentStepIndex);
            }
        });
    }

    form.addEventListener('submit', function (e) {
        if (!validarPaso(steps[steps.length - 1])) {
            e.preventDefault();
        }
    });

    showStep(0);
}

document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('multi-step-form');
    if (!form) return;

    if (form.getAttribute('data-registro-ajax') === '1') {
        initRegistroAjax(form);
        return;
    }

    initPerfilMultipaso(form);
});