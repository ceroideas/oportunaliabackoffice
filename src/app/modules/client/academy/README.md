# Módulo de Academia

Este módulo contiene toda la funcionalidad relacionada con la Academia de Oportunalia.

## Estructura del Módulo

- **Estudiantes**: Gestión de estudiantes registrados en la academia
- **Cursos**: Gestión de cursos y formaciones
- **Pagos**: Gestión de pagos realizados por estudiantes

## Cómo Desactivar el Módulo

Para desactivar completamente el módulo de Academia, sigue estos pasos:

### 1. Comentar las rutas en `api.routes.ts`

En `src/app/routes/api.routes.ts`, comenta estas líneas:

```typescript
// import { academyRoutes } from './academy.routes';  // ← Comentar esta línea

// Y en el array de rutas:
// ...academyRoutes,  // ← Comentar esta línea
```

### 2. Comentar las importaciones y declaraciones en `client.module.ts`

En `src/app/modules/client/client.module.ts`, comenta:

```typescript
// Todo el bloque marcado como "ACADEMY MODULE"
// Desde las importaciones hasta las declaraciones
```

### 3. Comentar los endpoints en `common.ts`

En `src/environments/common.ts`, comenta:

```typescript
// Todo el bloque marcado como "ACADEMY MODULE ENDPOINTS"
```

### 4. (Opcional) Eliminar carpetas

Si deseas eliminar completamente el código del módulo:

- `src/app/modules/client/academy-students/`
- `src/app/modules/client/academy-courses/`
- `src/app/modules/client/academy-payments/`
- `src/app/routes/academy.routes.ts`
- `src/app/modules/client/academy/` (este README)

## Re-activación

Para reactivar el módulo, simplemente descomenta todas las líneas comentadas en los pasos anteriores.

