# 🎮 Gamebox — Tu Red Social y Gestor de Videojuegos

[![Django Version](https://img.shields.io/badge/Django-5.x-092E20?style=for-the-badge&logo=django)](https://www.djangoproject.com/)
[![Python Version](https://img.shields.io/badge/Python-3.10%2B-3776AB?style=for-the-badge&logo=python)](https://python.org)
[![Database](https://img.shields.io/badge/PostgreSQL-Neon-336791?style=for-the-badge&logo=postgresql)](https://neon.tech)
[![Cloud Storage](https://img.shields.io/badge/Cloudinary-Media-3448C5?style=for-the-badge&logo=cloudinary)](https://cloudinary.com)
[![API Provider](https://img.shields.io/badge/API-IGDB_Twitch-6441a5?style=for-the-badge&logo=twitch)](https://www.igdb.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](https://opensource.org/licenses/MIT)

<img src="./public/gamebox_banner.png" alt="Gamebox Banner">

**Gamebox** es una plataforma web integral orientada a revolucionar la forma en que los gamers gestionan su colección y conectan con otros jugadores. Mediante un potente cruce de datos con bases globales (IGDB) y un ecosistema social integrado, Gamebox permite descubrir nuevos títulos, llevar un registro detallado de los juegos completados, planificar futuras partidas y explorar la actualidad de la industria del videojuego.

---

## Índice

1. [Características Principales](#características-principales)
2. [Arquitectura de Seguridad](#arquitectura-de-seguridad)
3. [Módulos de la Aplicación](#módulos-de-la-aplicación)
4. [Motor de Datos y APIs](#motor-de-datos-y-apis)
5. [Vista Previa de la Aplicación](#vista-previa-de-la-aplicación-capturas-de-pantalla)
6. [Despliegue e Infraestructura](#despliegue-e-infraestructura-de-producción)
7. [Panel de Administración](#panel-de-administración)
8. [Stack Tecnológico](#stack-tecnológico)
9. [Guía de Instalación](#guía-de-instalación)
---

## Características Principales

- **Gestión Inteligente de Colección**: Inventario digital de videojuegos clasificados por estados dinámicos (Jugando, Completado, Backlog, Abandonado) con estadísticas en tiempo real.
- **Búsqueda Multidimensional**: Localiza juegos por nombre, plataforma, género, año de lanzamiento o popularidad, con autocompletado y carga asíncrona.
- **Ecosistema Social y Comunidad**: Muro público (Feed) donde los usuarios pueden compartir capturas, escribir reseñas o guías, interactuar con "Me gusta" y dejar comentarios en la actividad de otros.
- **Listas Temáticas Personalizadas**: Creación de colecciones compartibles (ej. "Mejores RPGs de PS5", "Juegos para jugar en cooperativo").
- **Diario del Jugador**: Sistema de reseñas detalladas y puntuaciones (1-10) integradas directamente en la ficha técnica de cada videojuego.
- **Noticias en Tiempo Real**: Feed de actualidad sobre videojuegos utilizando NewsAPI integrado en el panel principal.

---

## Arquitectura de Seguridad

El proyecto implementa estándares rigurosos de seguridad respaldados por el framework **Django** para garantizar la integridad de los datos y la privacidad:

- **Prevención de Inyección SQL**: Todas las consultas utilizan el ORM de Django, vinculando parámetros de forma segura y neutralizando ataques a la base de datos.
- **Defensa contra XSS (Cross-Site Scripting)**: El motor de plantillas escapa de forma nativa (`{{ variable }}`) cualquier cadena de salida, previniendo la ejecución de scripts maliciosos.
- **Protección CSRF**: Todas las transacciones de estado (POST, edición de perfil, reseñas) exigen la validación de un token de seguridad único insertado en los formularios.
- **Gestión Segura de Media**: Los avatares e imágenes subidas por los usuarios se sanitizan y delegan a Cloudinary, aislando el servidor de posibles cargas maliciosas.
- **Decoradores de Autenticación**: Uso estricto de `@login_required` para blindar rutas sensibles y vistas de modificación de datos.

---

## Módulos de la Aplicación

### 1. Dashboard Principal (Inicio)
Panel de control dinámico que presenta las noticias más relevantes del sector, los lanzamientos más esperados, el juego destacado del día y la actividad reciente de la comunidad.

### 2. Mi Colección
Interfaz analítica para visualizar la distribución de tus juegos. Muestra gráficos/barras de progreso y separa tu biblioteca según el estado de juego actual.

### 3. Explorador IGDB (Catálogo)
Integración directa con la base de datos de Twitch (IGDB) para descubrir miles de títulos. Incluye filtros avanzados (género, plataforma, año, nota) y renderizado de carátulas en alta resolución.

### 4. Perfil Público y Red Social
Espacio personalizable (avatar y banner) donde se agrupan tus reseñas, puntuaciones y listas. Permite seguir a otros usuarios y ser seguido, construyendo una red de afinidad gamer.

---

## Motor de Datos y APIs

Gamebox no requiere poblar manualmente la base de datos con juegos. Utiliza la arquitectura de un "Hub Híbrido":
- **Lectura Global:** Consulta la API de IGDB en tiempo real para búsquedas, exploraciones y datos técnicos (desarrolladores, DLCs, remakes).
- **Escritura Local (`slugify`):** En el instante en que un usuario añade un juego a su colección, Gamebox extrae la información clave de la API y crea un registro ligero en la base de datos local (PostgreSQL) garantizando tiempos de carga ultrarrápidos para el ecosistema social.

---

## Vista Previa de la Aplicación (Capturas de Pantalla)

| Inicio y Novedades | Catálogo y Búsqueda |
| :---: | :---: |
| <img src="./public/images/home.png" width="100%" alt="Inicio"> | <img src="./public/images/explore.png" width="100%" alt="Buscador"> |
| *Feed de noticias, lanzamientos y actividad reciente.* | *Filtros avanzados conectados a la base de datos global.* |

| Colección Personal | Perfil y Social |
| :---: | :---: |
| <img src="./public/images/collection.png" width="100%" alt="Colección"> | <img src="./public/images/profile.png" width="100%" alt="Perfil"> |
| *Estadísticas de progreso y gestión del backlog.* | *Listas personalizadas, reseñas y sistema de seguidores.* |

---

## Despliegue e Infraestructura de Producción

La plataforma está diseñada para un entorno cloud moderno y escalable:
- **Hosting de Aplicación:** **Render** (Despliegue automático vía GitHub, servidor WSGI con **Gunicorn**).
- **Base de Datos Relacional:** **Neon.tech** (PostgreSQL Serverless, alta disponibilidad).
- **Almacenamiento Estático y Media:** **Cloudinary** (Optimización, transformación y entrega de imágenes de usuarios y carátulas).
- **Gestión de Estáticos (CSS/JS):** **WhiteNoise** integrado en el middleware de Django.

---

## Panel de Administración

Aprovechando el panel nativo de Django, los administradores tienen acceso a:
- **Gestión de Usuarios y Roles**: Modificación de perfiles, reseteo de contraseñas y baneos.
- **Moderación de Contenido**: Control total (CRUD) sobre las Reseñas (`UserGame`), Comentarios y Listas públicas (`GameList`).
- **Limpieza de Catálogo**: Posibilidad de unificar o eliminar fichas de juegos cacheadas localmente.

---

## Stack Tecnológico

| Tecnología | Rol en el Proyecto |
| :--- | :--- |
| **Django (Python)** | Framework Backend (Arquitectura MVT, ORM, enrutamiento, seguridad) |
| **PostgreSQL** | Motor de base de datos relacional (producción) |
| **IGDB API (Twitch)** | Proveedor REST de datos y metadatos de videojuegos |
| **Cloudinary** | CDN para almacenamiento y optimización de medios |
| **NewsAPI** | Proveedor de artículos de prensa especializada |
| **Tailwind CSS / HTML5** | Maquetación responsiva, diseño UI/UX y estilos |

---

## Guía de Instalación

Sigue estos pasos para desplegar el proyecto en tu entorno local:

```bash
# 1. Clonar el repositorio
git clone [https://github.com/TU_USUARIO/gamebox.git](https://github.com/TU_USUARIO/gamebox.git)
cd gamebox

# 2. Crear y activar el entorno virtual
python -m venv venv
.\venv\Scripts\activate      # En Windows
source venv/bin/activate    # En macOS/Linux

# 3. Instalar dependencias
python -m pip install -r requirements.txt

# 4. Configurar variables de entorno
# Crea un archivo .env en la raíz y añade:
SECRET_KEY=tu_clave_secreta
DEBUG=True
DATABASE_URL=postgresql://usuario:pass@servidor.neon.tech/db
CLOUDINARY_URL=cloudinary://api_key:api_secret@cloud_name
IGDB_CLIENT_ID=tu_client_id
IGDB_CLIENT_SECRET=tu_client_secret
NEWS_API_KEY=tu_api_noticias

# 5. Migrar la Base de Datos
python manage.py migrate

# 6. Crear cuenta de administrador
python manage.py createsuperuser

# 7. Iniciar el servidor local
python manage.py runserver
