# Thor-Nament (PHP + MySQL)

Proyecto de torneos e-sports con panel gamer (RSC-like), autenticación de sesión (PHP nativo), y vistas con Bootstrap + estilos neon/glass.

## Requisitos
- PHP 8.x
- MySQL/MariaDB
- XAMPP/WAMP (en Windows) o entorno LAMP

## Instalación local
1. Clona o copia el proyecto dentro de tu servidor local. En XAMPP, típico:
   ```
   C:\xampp\htdocs\E-SPORTS
   ```
2. Crea la base de datos y tablas: importa `src/db/schema.sql` en MySQL.
3. Configura credenciales en `src/db/connection.php` si difieren de las de XAMPP (root/empty).
4. Abre en el navegador:
   ```
   http://localhost/E-SPORTS/index.php
   ```

## Flujo principal
1) Crear cuenta y login  
2) Crear torneo (VS / BR)  
3) Unir equipos al torneo (Join)  
4) Crear match:  
   - VS: define Team A y Team B + scores  
   - BR: crea el match vacío y luego ingresa resultados por team (placement/kills)  
5) Guardar resultados → recalcula standings  

## Estilos/Secciones
- Landing gamer (hero + features + preview + marketplace)
- Dashboard gamer con “Player Performance Summary”
- Torneos, Matches, Resultados, Standings
- Games (Marketplace) y Players (con seeding)

## Sembrar datos de ejemplo
- Players: `index.php?page=players` → botón “Seed sample players”  

## Subir a GitHub (Windows, CMD)
1. Crea un repo vacío en GitHub (sin README).
2. En el directorio del proyecto:
   ```cmd
   cd C:\xampp\htdocs\E-SPORTS
   git init
   git branch -M main
   git add .
   git commit -m "Initial commit: Thor-Nament"
   git remote add origin https://github.com/TU_USUARIO/thor-nament.git
   git push -u origin main
   ```
   - Si usas PAT (token), el prompt lo pedirá al hacer `git push`.

## Rutas útiles
- Torneos: `index.php?page=tournaments`
- Crear torneo: `index.php?page=tournaments_create`
- Unirse a torneo: `index.php?page=tournaments_join&id=ID`
- Crear match: `index.php?page=matches_create&tournament_id=ID`
- Resultados: `index.php?page=matches_results&match_id=ID`
- Standings (vista): `index.php?page=standings&tournament_id=ID`
- Dashboard: `index.php?page=dashboard`
- Landing forzada (logueado): `index.php?page=home&showLanding=1`
- Games (Marketplace): `index.php?page=games`
- Players: `index.php?page=players`

## Notas
- `src/helpers/utils.php` incluye `redirect()` que genera URLs absolutas respetando el subdirectorio (`/E-SPORTS`) para evitar salidas a `http://localhost/` root de XAMPP.
- Si cambias el nombre de carpeta del proyecto, no olvides revisar enlaces absolutos fuera del `redirect()`.

## Licencia
MIT


