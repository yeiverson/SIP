<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro SIP-Postgrado - UNEFA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style-registro.css">
    <link rel="icon" href="imagenes/sip.ico">
    <style>
        .doc-helper { font-size: 0.75rem; color: #999; margin-top: 4px; }
        .error-msg { background: #fee; color: #c00; padding: 8px 12px; border-radius: 6px; margin-bottom: 12px; font-size: 0.82rem; text-align: center; border: 1px solid #fcc; }
        .success-msg { background: #efe; color: #080; padding: 8px 12px; border-radius: 6px; margin-bottom: 12px; font-size: 0.82rem; text-align: center; border: 1px solid #afa; }
    </style>
</head>
<body>

    <header class="navbar-header">
        <div class="header-content">
            <div class="logo-container">
                <img src="imagenes/LOGO-1-1.png" alt="Logo UNEFA" class="logo-img">
            </div>
            <div class="nav-buttons">
                <a href="Inicio.php" class="btn-primary">Inicio de Sesión</a>
                <a href="registro.php" class="btn-outline active">Registro</a>
            </div>
        </div>
    </header>

    <main class="main-container">
        
        <div class="header-titles">
            <h2>Registro SIP-Postgrado</h2>
            <p>Bienvenido al Sistema de Registro de Postgrado. Complete los campos a continuación para crear su perfil de usuario y dar el primer paso hacia su especialización profesional en nuestra casa de estudios.</p>
        </div>

        <div class="progress-container">
            <div class="progress-bar" id="progress-bar">
                <div class="progress-step active" data-step="1">1</div>
                <div class="progress-line" id="line-1"></div>
                <div class="progress-step" data-step="2">2</div>
                <div class="progress-line" id="line-2"></div>
                <div class="progress-step" data-step="3">3</div>
                <div class="progress-line" id="line-3"></div>
                <div class="progress-step" data-step="4">4</div>
                <div class="progress-line" id="line-4"></div>
                <div class="progress-step" data-step="5">5</div>
            </div>
        </div>

        <section class="registration-card">
            
            <form id="multi-step-form">
                
                <div class="form-step active" id="step-1">
                    <h3>Crea tu usuario: Datos Personales</h3>
                    
                    <div class="form-grid-2">
                        <div class="input-group">
                            <label>Primer Nombre:</label>
                            <input type="text" name="primerNombre" placeholder="Escribe tu Primer Nombre" required>
                        </div>
                        <div class="input-group">
                            <label>Segundo Nombre:</label>
                            <input type="text" name="segundoNombre" placeholder="Escribe tu Segundo Nombre">
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="input-group">
                            <label>Primer Apellido:</label>
                            <input type="text" name="primerApellido" placeholder="Escribe tu Primer Apellido" required>
                        </div>
                        <div class="input-group">
                            <label>Segundo Apellido:</label>
                            <input type="text" name="segundoApellido" placeholder="Escribe tu segundo Apellido">
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="input-group">
                            <label>Tipo de documento:</label>
                            <select name="tipoDocumento" required>
                                <option value="" disabled selected>Seleccione el tipo de Documento</option>
                                <option value="V">V</option>
                                <option value="E">E</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Cédula de Identidad:</label>
                            <input type="text" name="cedula" placeholder="Escribe tu número de cédula" required>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="input-group">
                            <label>Fecha de Nacimiento:</label>
                            <input type="date" name="fechaNacimiento" required>
                        </div>
                        <div class="input-group">
                            <label>Sexo:</label>
                            <select name="sexo" required>
                                <option value="" disabled selected>Selecciona tu sexo:</option>
                                <option value="Femenino">Femenino</option>
                                <option value="Masculino">Masculino</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="input-group">
                            <label>País de Nacimiento:</label>
                            <select name="paisNacimiento">
                                <option value="" disabled selected>Seleccione</option>
                                <option value="Venezuela">Venezuela</option>
                                </select>
                        </div>
                        <div class="input-group">
                            <label>Estado Civil:</label>
                            <select name="estadoCivil">
                                <option value="" disabled selected>Seleccione</option>
                                <option value="Soltero/a">Soltero/a</option>
                                <option value="Casado/a">Casado/a</option>
                                <option value="Divorciado/a">Divorciado/a</option>
                                <option value="Viudo/a">Viudo/a</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-step" id="step-2">
                    <h3>Dirección de Habitación</h3>
                    
                    <div class="form-grid-2">
                        <div class="input-group">
                            <label>Estado:</label>
                            <select name="estadoHabitacion">
                                <option value="" disabled selected>Seleccione</option>
                                </select>
                        </div>
                        <div class="input-group">
                            <label>Municipio:</label>
                            <select name="municipioHabitacion">
                                <option value="" disabled selected>Seleccione</option>
                                </select>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="input-group">
                            <label>Parroquia:</label>
                            <select name="parroquiaHabitacion">
                                <option value="" disabled selected>Seleccione</option>
                                </select>
                        </div>
                        <div class="input-group">
                            <label>Ciudad / Pueblo:</label>
                            <input type="text" name="ciudadHabitacion" placeholder="Escribe tu Ciudad o Pueblo">
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="input-group">
                            <label>Avenida / Calle / Vereda:</label>
                            <input type="text" name="avenidaCalle" placeholder="Escribe tu Avenida o Calle">
                        </div>
                        <div class="input-group">
                            <label>Urbanización / Barrio / Sector:</label>
                            <input type="text" name="urbanizacionBarrio" placeholder="Escribe tu Urbanización o Barrio">
                        </div>
                    </div>

                    <div class="form-grid-3">
                        <div class="input-group">
                            <label>Residencia:</label>
                            <input type="text" name="residencia" placeholder="Nombre o Número de Casa/Edificio">
                        </div>
                        <div class="input-group">
                            <label>Piso:</label>
                            <input type="text" name="piso" placeholder="Escribe tu Piso">
                        </div>
                        <div class="input-group">
                            <label>Apartamento:</label>
                            <input type="text" name="apartamento" placeholder="Escribe tu Apartamento">
                        </div>
                    </div>
                </div>

                <div class="form-step" id="step-3">
                    <h3>Datos de Contacto</h3>
                    
                    <div class="form-grid-2">
                        <div class="input-group icon-input">
                            <label>Teléfono:</label>
                            <div class="input-with-icon">
                                <span class="icon">&#128222;</span> <input type="tel" name="telefono" placeholder="Número fijo">
                            </div>
                        </div>
                        <div class="input-group icon-input">
                            <label>Celular:</label>
                            <div class="input-with-icon">
                                <span class="icon">&#128241;</span> <input type="tel" name="celular" placeholder="Número celular">
                            </div>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="input-group icon-input">
                            <label>Email:</label>
                            <div class="input-with-icon">
                                <span class="icon">&#9993;</span> <input type="email" name="email" placeholder="Escribe tu Correo" required>
                            </div>
                        </div>
                        <div class="input-group">
                            <label>Condición:</label>
                            <select name="condicion">
                                <option value="" disabled selected>Seleccione</option>
                                <option value="Nuevo Ingreso">Nuevo Ingreso</option>
                                <option value="Reingreso">Reingreso</option>
                            </select>
                        </div>
                    </div>

                    <h3>Datos Académicos</h3>
                    
                    <div class="form-grid-2">
                        <div class="input-group">
                            <label>Área de conocimiento:</label>
                            <select name="areaConocimiento">
                                <option value="" disabled selected>Seleccione</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Nivel Académico:</label>
                            <select name="nivelAcademico">
                                <option value="" disabled selected>Seleccione</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="input-group">
                            <label>Universidad:</label>
                            <select name="universidad">
                                <option value="" disabled selected>Seleccione</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Título:</label>
                            <input type="text" name="tituloAcademico" placeholder="Escribe tu Título obtenido">
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="input-group">
                            <label>Año de Graduación:</label>
                            <select name="anoGraduacion">
                                <option value="" disabled selected>Seleccione</option>
                                </select>
                        </div>
                        <div class="input-group">
                            <label>Promedio de Calificaciones:</label>
                            <input type="text" name="promedio" placeholder="Escribe tu Promedio">
                        </div>
                    </div>
                </div>

                <div class="form-step" id="step-4">
                    <h3>Datos Laborales</h3>
                    
                    <div class="form-grid-2">
                        <div class="input-group">
                            <label>Tipo de Institución:</label>
                            <select name="tipoInstitucion">
                                <option value="" disabled selected>Seleccione</option>
                                <option value="Pública">Pública</option>
                                <option value="Privada">Privada</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Nombre de la Institución u Organismo:</label>
                            <input type="text" name="nombreInstitucion" placeholder="Escribe el nombre">
                        </div>
                    </div>

                    <div class="form-grid-3">
                        <div class="input-group">
                            <label>Antigüedad:</label>
                            <input type="text" name="antiguedad" placeholder="Años/Meses">
                        </div>
                        <div class="input-group">
                            <label>Teléfono (Trabajo):</label>
                            <input type="tel" name="telefonoTrabajo" placeholder="Número fijo">
                        </div>
                        <div class="input-group">
                            <label>Cargo:</label>
                            <input type="text" name="cargo" placeholder="Escribe tu Cargo">
                        </div>
                    </div>

                    <div class="form-grid-3">
                        <div class="input-group">
                            <label>Trabajas en la UNEFA:</label>
                            <select name="trabajaUnefa">
                                <option value="" disabled selected>Seleccione</option>
                                <option value="Sí">Sí</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Área:</label>
                            <select name="areaTrabajo">
                                <option value="" disabled selected>Seleccione</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Dedicación:</label>
                            <select name="dedicacion">
                                <option value="" disabled selected>Seleccione</option>
                                <option value="Tiempo Completo">Tiempo Completo</option>
                                <option value="Medio Tiempo">Medio Tiempo</option>
                                <option value="Por Horas">Por Horas</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-step" id="step-5">
                    <h3>Aspectos para la Entrevista</h3>
                    <h4>Aspectos a Evaluar </h4>
                    
                    <div class="interview-table-container">
                        <table class="interview-table">
                            <thead>
                                <tr>
                                    <th>Academico</th>
                                    <th>Sí</th>
                                    <th>No</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1. Participación en eventos científicos nacionales e internacionales.</td>
                                    <td><input type="radio" name="aspecto1" value="si"></td>
                                    <td><input type="radio" name="aspecto1" value="no"></td>
                                </tr>
                                <tr>
                                    <td>2. Participación como jurado o tutor en trabajos de investigación.</td>
                                    <td><input type="radio" name="aspecto2" value="si"></td>
                                    <td><input type="radio" name="aspecto2" value="no"></td>
                                </tr>
                                <tr>
                                    <td>3. Disposición a participar en actividad académicas, investigación e institucionales (Escribe para algún boletín, revista o periódico regularmente. Indica experiencia en la escritura de articulo arbitrados)</td>
                                    <td><input type="radio" name="aspecto3" value="si"></td>
                                    <td><input type="radio" name="aspecto3" value="no"></td>
                                </tr>

                            </tbody>
                            <thead>
                                <tr>
                                    <th>Investigación</th>
                                    <th>Sí</th>
                                    <th>No</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>4. Tema de interés específica para su investigación vinculadas a las áreas prioritarias de desarrollo de la nación. Explique por favor</td>
                                    <td><input type="radio" name="aspecto4" value="si"></td>
                                    <td><input type="radio" name="aspecto4" value="no"></td>
                                </tr>
                                <tr>
                                    <td>5.Vinculación entre el área profesional, laboral con los estudios de postgrado a cursar.</td>
                                    <td><input type="radio" name="aspecto5" value="si"></td>
                                    <td><input type="radio" name="aspecto5" value="no"></td>
                                </tr>
                                <tr>
                                    <td>6.Afiliación a grupo o red de investigadores nacionales o internacionales.</td>
                                    <td><input type="radio" name="aspecto6" value="si"></td>
                                    <td><input type="radio" name="aspecto6" value="no"></td>
                                </tr>
                                <tr>
                                    <td>7. Participación como evaluador en artículos científicos.</td>
                                    <td><input type="radio" name="aspecto7" value="si"></td>
                                    <td><input type="radio" name="aspecto7" value="no"></td>
                                </tr>
                                                                <tr>
                                    <td>8. Ha escrito o publicado artículos científicos.</td>
                                    <td><input type="radio" name="aspecto8" value="si"></td>
                                    <td><input type="radio" name="aspecto8" value="no"></td>
                                </tr>
                                                                <tr>
                                    <td>9. Ha escrito o publicado artículos científicos.</td>
                                    <td><input type="radio" name="aspecto9" value="si"></td>
                                    <td><input type="radio" name="aspecto9" value="no"></td>
                                </tr>
                                                                <tr>
                                    <td>10. Familiarización con las líneas de investigación de la Universidad.</td>
                                    <td><input type="radio" name="aspecto10" value="si"></td>
                                    <td><input type="radio" name="aspecto10" value="no"></td>
                                </tr>
                                                                <tr>
                                    <td>11. La investigación a desarrollar satisface fines personales, personales o institucionales.</td>
                                    <td><input type="radio" name="aspecto11" value="si"></td>
                                    <td><input type="radio" name="aspecto11" value="no"></td>
                                </tr>
                            </tbody>
                            <thead>
                                <tr>
                                    <th>Otros</th>
                                    <th>Sí</th>
                                    <th>No</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>12. Acceso y disponibilidad al manejo de equipos de tecnología y estrategias multimodales.</td>
                                    <td><input type="radio" name="aspecto12" value="si"></td>
                                    <td><input type="radio" name="aspecto12" value="no"></td>
                                </tr>
                                <tr>
                                    <td>13. Disponibilidad personal para financiar los estudios de postgrado.</td>
                                    <td><input type="radio" name="aspecto13" value="si"></td>
                                    <td><input type="radio" name="aspecto13" value="no"></td>
                                </tr>
                                <tr>
                                    <td>14. Disponibilidad personal para financiar los estudios de postgrado.</td>
                                    <td><input type="radio" name="aspecto14" value="si"></td>
                                    <td><input type="radio" name="aspecto14" value="no"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h3>Otros Datos</h3>
                    
                    <div class="form-grid-2">
                        <div class="input-group">
                            <label>Tipo de Beca:</label>
                            <select name="tipoBeca">
                                <option value="" disabled selected>Seleccione</option>
                                <option value="Sí">Sí</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Fecha de ingreso a la UNEFA:</label>
                            <input type="date" name="fechaIngresoUnefa">
                        </div>
                    </div>

                    <h3>Adjuntar Documentos</h3>
                    
                    <div class="upload-instructions">
                        <p><strong>Instrucciones:</strong></p>
                        <ul>
                            <li>Solo se aceptan archivos de imagen en formato JPG o PNG.</li>
                            <li>Las imágenes deben ser en <strong>Color, Claras y Legibles</strong></li>
                            <li>La resolución recomendada es de 1400 x 1400</li>
                        </ul>
                    </div>

                    <div class="form-grid-2">
                        <div class="input-group file-input-group">
                            <label>Documento de Identidad:</label>
                            <div class="file-input-wrapper">
                                <input type="file" name="docIdentidad" accept=".jpg, .jpeg, .png">
                                <div class="file-input-overlay">
                                    <span class="file-input-button">Seleccionar archivo</span>
                                    <span class="file-input-name">Sin archivos seleccionados</span>
                                </div>
                            </div>
                            <span class="file-help-text">Solo se aceptan formatos .JPG y .PNG</span>
                        </div>
                        <div class="input-group file-input-group">
                            <label>Título:</label>
                            <div class="file-input-wrapper">
                                <input type="file" name="tituloAdjunto" accept=".jpg, .jpeg, .png">
                                <div class="file-input-overlay">
                                    <span class="file-input-button">Seleccionar archivo</span>
                                    <span class="file-input-name">Sin archivos seleccionados</span>
                                </div>
                            </div>
                            <span class="file-help-text">Solo se aceptan formatos .JPG y .PNG</span>
                        </div>
                    </div>
                </div>

                <div class="form-navigation">
                    <button type="button" class="btn-step-prev" id="prev-btn" style="display: none;">Atrás</button>
                    <button type="button" class="btn-step-next" id="next-btn">Siguiente</button>
                    <button type="submit" class="btn-step-submit" id="submit-btn" style="display: none;">Finalizar Registro</button>
                </div>

            </form>
        </section>
    </main>

    <footer class="main-footer">
        <div class="footer-content">
            <div class="footer-divider"></div>
            <div class="footer-text">
                <p>UNEFA | Excelencia Educativa Abierta al Pueblo</p>
                <p>Vicerrectorado de Investigación, Postgrado y Recreación</p>
            </div>
        </div>
    </footer>

    <script src="multi-step-form.js"></script>

</body>
</html> -->


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro SIP-Postgrado - UNEFA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style-registro.css">
    <link rel="icon" href="imagenes/sip.ico">
</head>
<body>

    <header class="navbar-header">
        <div class="header-content">
            <div class="logo-container">
                <img src="imagenes/LOGO-1-1.png" alt="Logo UNEFA" class="logo-img">
            </div>
            <div class="nav-buttons">
                <a href="Inicio.php" class="btn-primary">Inicio de Sesión</a>
                <a href="registro.php" class="btn-outline active">Registro</a>
            </div>
        </div>
    </header>

    <main class="main-container">
        
        <div class="header-titles">
            <h2>Registro SIP-Postgrado</h2>
            <p>Complete los campos a continuación para crear su perfil de usuario y dar el primer paso hacia su especialización profesional.</p>
        </div>

        <div class="progress-container progress-registro-centrado">
            <div class="progress-bar progress-bar-registro" id="progress-bar">
                <div class="progress-step-column">
                    <div class="progress-step active" data-step="1">1</div>
                    <span class="progress-step-caption">Registre su usuario</span>
                </div>
            </div>
        </div>

        <section class="registration-card">
            <form id="multi-step-form" action="procesar_registro.php" method="POST" data-registro-ajax="1">
                
                <div class="form-step active" id="step-1">
                    <div class="form-grid-2">
                        <div class="input-group">
                            <label>Tipo de documento:</label>
                            <select name="tipoDocumento" required onchange="toggleRegDocType()">
                                <option value="" disabled selected>Seleccione</option>
                                <option value="V">V — Venezolano</option>
                                <option value="E">E — Extranjero</option>
                                <option value="P">P — Pasaporte</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label id="doc-label">Cédula / Pasaporte:</label>
                            <input type="text" name="cedula" id="reg-cedula" placeholder="Número de cédula o pasaporte" required>
                        </div>
                    </div>
                    <div class="form-grid-2" style="margin-top: 16px;">
                        <div class="input-group">
                            <label>Correo electrónico:</label>
                            <input type="email" name="email" id="email" placeholder="tu.correo@ejemplo.com" required>
                        </div>
                    </div>
                    <div class="password-section" style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin-top: 20px; border: 1px solid #eee;">
                        <h4 style="margin-bottom: 15px; color: #333;">Seguridad de la Cuenta</h4>
                        <div class="form-grid-2">
                            <div class="input-group">
                                <label>Crea tu Contraseña:</label>
                                <input type="password" name="password" id="password" required placeholder="Mínimo 8 caracteres">
                            </div>
                            <div class="input-group">
                                <label>Confirma tu Contraseña:</label>
                                <input type="password" name="confirm_password" id="confirm_password" required placeholder="Repite tu clave">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-navigation" style="margin-top: 30px; display: flex; justify-content: space-between;">
                   
                    <button   type="button" id="next-btn" class="btn-primary">Crear usuario</button>
                    </div>

            </form>
        </section>
    </main>

    <script src="multi-step-form.js"></script>
</body>
</html>  



                    <!-- <h3>Datos Académicos</h3>
                    
                    <div class="form-grid-2">
                        <div class="input-group">
                            <label>Área de conocimiento:</label>
                            <select name="areaConocimiento">
                                <option value="" disabled selected>Seleccione</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Nivel Académico:</label>
                            <select name="nivelAcademico">
                                <option value="" disabled selected>Seleccione</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="input-group">
                            <label>Universidad:</label>
                            <select name="universidad">
                                <option value="" disabled selected>Seleccione</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Título:</label>
                            <input type="text" name="tituloAcademico" placeholder="Escribe tu Título obtenido">
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="input-group">
                            <label>Año de Graduación:</label>
                            <select name="anoGraduacion">
                                <option value="" disabled selected>Seleccione</option>
                                </select>
                        </div>
                        <div class="input-group">
                            <label>Promedio de Calificaciones:</label>
                            <input type="text" name="promedio" placeholder="Escribe tu Promedio">
                        </div>
                    </div>
                </div>

                <div class="form-step" id="step-4">
                    <h3>Datos Laborales</h3>
                    
                    <div class="form-grid-2">
                        <div class="input-group">
                            <label>Tipo de Institución:</label>
                            <select name="tipoInstitucion">
                                <option value="" disabled selected>Seleccione</option>
                                <option value="Pública">Pública</option>
                                <option value="Privada">Privada</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Nombre de la Institución u Organismo:</label>
                            <input type="text" name="nombreInstitucion" placeholder="Escribe el nombre">
                        </div>
                    </div>

                    <div class="form-grid-3">
                        <div class="input-group">
                            <label>Antigüedad:</label>
                            <input type="text" name="antiguedad" placeholder="Años/Meses">
                        </div>
                        <div class="input-group">
                            <label>Teléfono (Trabajo):</label>
                            <input type="tel" name="telefonoTrabajo" placeholder="Número fijo">
                        </div>
                        <div class="input-group">
                            <label>Cargo:</label>
                            <input type="text" name="cargo" placeholder="Escribe tu Cargo">
                        </div>
                    </div>

                    <div class="form-grid-3">
                        <div class="input-group">
                            <label>Trabajas en la UNEFA:</label>
                            <select name="trabajaUnefa">
                                <option value="" disabled selected>Seleccione</option>
                                <option value="Sí">Sí</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Área:</label>
                            <select name="areaTrabajo">
                                <option value="" disabled selected>Seleccione</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Dedicación:</label>
                            <select name="dedicacion">
                                <option value="" disabled selected>Seleccione</option>
                                <option value="Tiempo Completo">Tiempo Completo</option>
                                <option value="Medio Tiempo">Medio Tiempo</option>
                                <option value="Por Horas">Por Horas</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-step" id="step-5">
                    <h3>Aspectos para la Entrevista</h3>
                    <h4>Aspectos a Evaluar </h4>
                    
                    <div class="interview-table-container">
                        <table class="interview-table">
                            <thead>
                                <tr>
                                    <th>Academico</th>
                                    <th>Sí</th>
                                    <th>No</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1. Participación en eventos científicos nacionales e internacionales.</td>
                                    <td><input type="radio" name="aspecto1" value="si"></td>
                                    <td><input type="radio" name="aspecto1" value="no"></td>
                                </tr>
                                <tr>
                                    <td>2. Participación como jurado o tutor en trabajos de investigación.</td>
                                    <td><input type="radio" name="aspecto2" value="si"></td>
                                    <td><input type="radio" name="aspecto2" value="no"></td>
                                </tr>
                                <tr>
                                    <td>3. Disposición a participar en actividad académicas, investigación e institucionales (Escribe para algún boletín, revista o periódico regularmente. Indica experiencia en la escritura de articulo arbitrados)</td>
                                    <td><input type="radio" name="aspecto3" value="si"></td>
                                    <td><input type="radio" name="aspecto3" value="no"></td>
                                </tr>

                            </tbody>
                            <thead>
                                <tr>
                                    <th>Investigación</th>
                                    <th>Sí</th>
                                    <th>No</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>4. Tema de interés específica para su investigación vinculadas a las áreas prioritarias de desarrollo de la nación. Explique por favor</td>
                                    <td><input type="radio" name="aspecto4" value="si"></td>
                                    <td><input type="radio" name="aspecto4" value="no"></td>
                                </tr>
                                <tr>
                                    <td>5.Vinculación entre el área profesional, laboral con los estudios de postgrado a cursar.</td>
                                    <td><input type="radio" name="aspecto5" value="si"></td>
                                    <td><input type="radio" name="aspecto5" value="no"></td>
                                </tr>
                                <tr>
                                    <td>6.Afiliación a grupo o red de investigadores nacionales o internacionales.</td>
                                    <td><input type="radio" name="aspecto6" value="si"></td>
                                    <td><input type="radio" name="aspecto6" value="no"></td>
                                </tr>
                                <tr>
                                    <td>7. Participación como evaluador en artículos científicos.</td>
                                    <td><input type="radio" name="aspecto7" value="si"></td>
                                    <td><input type="radio" name="aspecto7" value="no"></td>
                                </tr>
                                                                <tr>
                                    <td>8. Ha escrito o publicado artículos científicos.</td>
                                    <td><input type="radio" name="aspecto8" value="si"></td>
                                    <td><input type="radio" name="aspecto8" value="no"></td>
                                </tr>
                                                                <tr>
                                    <td>9. Ha escrito o publicado artículos científicos.</td>
                                    <td><input type="radio" name="aspecto9" value="si"></td>
                                    <td><input type="radio" name="aspecto9" value="no"></td>
                                </tr>
                                                                <tr>
                                    <td>10. Familiarización con las líneas de investigación de la Universidad.</td>
                                    <td><input type="radio" name="aspecto10" value="si"></td>
                                    <td><input type="radio" name="aspecto10" value="no"></td>
                                </tr>
                                                                <tr>
                                    <td>11. La investigación a desarrollar satisface fines personales, personales o institucionales.</td>
                                    <td><input type="radio" name="aspecto11" value="si"></td>
                                    <td><input type="radio" name="aspecto11" value="no"></td>
                                </tr>
                            </tbody>
                            <thead>
                                <tr>
                                    <th>Otros</th>
                                    <th>Sí</th>
                                    <th>No</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>12. Acceso y disponibilidad al manejo de equipos de tecnología y estrategias multimodales.</td>
                                    <td><input type="radio" name="aspecto12" value="si"></td>
                                    <td><input type="radio" name="aspecto12" value="no"></td>
                                </tr>
                                <tr>
                                    <td>13. Disponibilidad personal para financiar los estudios de postgrado.</td>
                                    <td><input type="radio" name="aspecto13" value="si"></td>
                                    <td><input type="radio" name="aspecto13" value="no"></td>
                                </tr>
                                <tr>
                                    <td>14. Disponibilidad personal para financiar los estudios de postgrado.</td>
                                    <td><input type="radio" name="aspecto14" value="si"></td>
                                    <td><input type="radio" name="aspecto14" value="no"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h3>Otros Datos</h3>
                    
                    <div class="form-grid-2">
                        <div class="input-group">
                            <label>Tipo de Beca:</label>
                            <select name="tipoBeca">
                                <option value="" disabled selected>Seleccione</option>
                                <option value="Sí">Sí</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Fecha de ingreso a la UNEFA:</label>
                            <input type="date" name="fechaIngresoUnefa">
                        </div>
                    </div>

                    <h3>Adjuntar Documentos</h3>
                    
                    <div class="upload-instructions">
                        <p><strong>Instrucciones:</strong></p>
                        <ul>
                            <li>Solo se aceptan archivos de imagen en formato JPG o PNG.</li>
                            <li>Las imágenes deben ser en <strong>Color, Claras y Legibles</strong></li>
                            <li>La resolución recomendada es de 1400 x 1400</li>
                        </ul>
                    </div>

                    <div class="form-grid-2">
                        <div class="input-group file-input-group">
                            <label>Documento de Identidad:</label>
                            <div class="file-input-wrapper">
                                <input type="file" name="docIdentidad" accept=".jpg, .jpeg, .png">
                                <div class="file-input-overlay">
                                    <span class="file-input-button">Seleccionar archivo</span>
                                    <span class="file-input-name">Sin archivos seleccionados</span>
                                </div>
                            </div>
                            <span class="file-help-text">Solo se aceptan formatos .JPG y .PNG</span>
                        </div>
                        <div class="input-group file-input-group">
                            <label>Título:</label>
                            <div class="file-input-wrapper">
                                <input type="file" name="tituloAdjunto" accept=".jpg, .jpeg, .png">
                                <div class="file-input-overlay">
                                    <span class="file-input-button">Seleccionar archivo</span>
                                    <span class="file-input-name">Sin archivos seleccionados</span>
                                </div>
                            </div>
                            <span class="file-help-text">Solo se aceptan formatos .JPG y .PNG</span>
                        </div>
                    </div>
                </div>

                <div class="form-navigation">
                    <button type="button" class="btn-step-prev" id="prev-btn" style="display: none;">Atrás</button>
                    <button type="button" class="btn-step-next" id="next-btn">Siguiente</button>
                    <button type="submit" class="btn-step-submit" id="submit-btn" style="display: none;">Finalizar Registro</button>
                </div>

            </form>
        </section>
    </main>

    <footer class="main-footer">
        <div class="footer-content">
            <div class="footer-divider"></div>
            <div class="footer-text">
                <p>UNEFA | Excelencia Educativa Abierta al Pueblo</p>
                <p>Vicerrectorado de Investigación, Postgrado y Recreación</p>
            </div>
        </div>
    </footer>

<script>
function toggleRegDocType() {
    const tipo = document.getElementById('tipoDocumento') || document.querySelector('select[name="tipoDocumento"]');
    const cedulaInput = document.getElementById('reg-cedula') || document.querySelector('input[name="cedula"]');
    const label = document.getElementById('doc-label');
    if (!tipo || !cedulaInput) return;
    if (tipo.value === 'P') {
        cedulaInput.placeholder = 'Ej: FR98765432 (alfanumérico)';
        cedulaInput.pattern = '[A-Za-z0-9]{4,20}';
        if (label) label.textContent = 'Pasaporte:';
    } else if (tipo.value) {
        cedulaInput.placeholder = 'Solo números, 7-10 dígitos';
        cedulaInput.pattern = '\\d{7,10}';
        if (label) label.textContent = 'Cédula de Identidad:';
    } else {
        cedulaInput.placeholder = 'Número de cédula o pasaporte';
        cedulaInput.removeAttribute('pattern');
        if (label) label.textContent = 'Cédula / Pasaporte:';
    }
}
</script>
<script src="multi-step-form.js"></script>

</body>
</html>