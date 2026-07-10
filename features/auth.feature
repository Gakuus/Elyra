# language: es
# HU-01: Autenticación de usuarios (login/logout)
Característica: Autenticación de usuarios
  Como funcionario del hospital
  Quiero iniciar y cerrar sesión en el sistema
  Para acceder a los módulos protegidos

  Antecedentes:
    Dado estoy en la página de login

  Escenario: Login exitoso con credenciales válidas
    Cuando relleno "username" con "admin"
    Y relleno "password" con "admin"
    Y presiono "Iniciar Sesión"
    Entonces la URL debería contener "dashboard"
    Y debería ver "Panel"

  Escenario: Login falla con credenciales inválidas
    Cuando relleno "username" con "admin"
    Y relleno "password" con "contraseña_incorrecta"
    Y presiono "Iniciar Sesión"
    Entonces debería ver "Credenciales inválidas"

  Escenario: Login falla con usuario inexistente
    Cuando relleno "username" con "usuario_inexistente"
    Y relleno "password" con "cualquiercosa"
    Y presiono "Iniciar Sesión"
    Entonces debería ver "Credenciales inválidas"

  Escenario: Logout cierra sesión correctamente
    Dado inicié sesión con "admin" y contraseña "admin"
    Cuando voy a "/logout"
    Entonces no debería ver "Salir"
