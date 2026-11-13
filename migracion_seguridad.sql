-- =====================================================================
-- MIGRACIÓN DE SEGURIDAD (Partes 1-6). Importar sobre la base kardex_escolar
-- YA instalada (después de kardex_escolar.sql y, si quieres, datos_demo.sql).
-- Después ejecutar:  C:\xampp\php\php.exe tools\migrar_identidades.php
-- =====================================================================
SET NAMES utf8mb4;

-- PARTE 1: identidades únicas con estado y ciclo de vida
CREATE TABLE IF NOT EXISTS identidades (
  id_identidad INT NOT NULL AUTO_INCREMENT,
  usuario VARCHAR(50) NOT NULL,
  nombre_completo VARCHAR(200) NOT NULL,
  rol ENUM('admin','auditor','docente','coordinador','estudiante') NOT NULL,
  ref_id VARCHAR(20) NOT NULL COMMENT 'id_admin / id_docente / re del perfil',
  password_hash VARCHAR(255) NOT NULL,
  estado ENUM('activa','suspendida','revocada') NOT NULL DEFAULT 'activa',
  debe_cambiar_password TINYINT(1) NOT NULL DEFAULT 0,
  es_emergencia TINYINT(1) NOT NULL DEFAULT 0,
  vigente_hasta DATETIME NULL,
  mfa_secreto VARCHAR(255) NULL,
  mfa_activo TINYINT(1) NOT NULL DEFAULT 0,
  mfa_ultimo_paso BIGINT NOT NULL DEFAULT 0,
  creada_en DATETIME NOT NULL,
  estado_cambiado_en DATETIME NULL,
  ultimo_login DATETIME NULL,
  PRIMARY KEY (id_identidad),
  UNIQUE KEY uq_ident_usuario (usuario),
  KEY idx_ident_ref (rol, ref_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cambios_rol (
  id INT NOT NULL AUTO_INCREMENT,
  id_identidad INT NOT NULL,
  rol_anterior VARCHAR(20) NOT NULL,
  rol_nuevo VARCHAR(20) NOT NULL,
  autorizado_por INT NOT NULL,
  motivo VARCHAR(255) NOT NULL,
  momento DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY (id_identidad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PARTE 2: sesiones en servidor (expiración y revocación) y control de intentos
CREATE TABLE IF NOT EXISTS sesiones (
  id_sesion CHAR(64) NOT NULL,
  id_identidad INT NOT NULL,
  creada_en DATETIME NOT NULL,
  ultima_actividad DATETIME NOT NULL,
  expira_en DATETIME NOT NULL,
  ip VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  revocada TINYINT(1) NOT NULL DEFAULT 0,
  motivo_revocacion VARCHAR(100) NULL,
  PRIMARY KEY (id_sesion),
  KEY (id_identidad),
  CONSTRAINT fk_ses_ident FOREIGN KEY (id_identidad) REFERENCES identidades(id_identidad) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS intentos_login (
  id BIGINT NOT NULL AUTO_INCREMENT,
  usuario VARCHAR(80) NOT NULL,
  ip VARCHAR(45) NOT NULL,
  momento DATETIME NOT NULL,
  exito TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_int_usuario (usuario, momento),
  KEY idx_int_ip (ip, momento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Relación contextual para autorización docente: materia asignada a uno o varios cursos.
-- Permite decidir qué estudiantes puede consultar/evaluar cada docente sin depender
-- de que ya exista una calificación previa en el kardex.
CREATE TABLE IF NOT EXISTS materia_cursos (
  id_materia INT NOT NULL,
  curso VARCHAR(50) NOT NULL,
  PRIMARY KEY (id_materia, curso),
  KEY idx_materia_cursos_curso (curso),
  CONSTRAINT fk_materia_cursos_materia FOREIGN KEY (id_materia)
    REFERENCES materias(id_materia) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migra automáticamente las relaciones que pueden deducirse de las calificaciones existentes.
INSERT IGNORE INTO materia_cursos (id_materia, curso)
SELECT DISTINCT k.id_materia, e.curso
FROM kardex k INNER JOIN estudiantes e ON e.re=k.re_estudiante;

-- PARTE 3 y 6: matriz de mínimo privilegio (rol x operación) aplicada por el servidor
CREATE TABLE IF NOT EXISTS permisos (
  rol VARCHAR(20) NOT NULL,
  operacion VARCHAR(40) NOT NULL,
  PRIMARY KEY (rol, operacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELETE FROM permisos;
INSERT INTO permisos (rol, operacion) VALUES
-- administrador: gestión completa
('admin','panel.ver'),('admin','estudiante.ver'),('admin','estudiante.crear'),('admin','estudiante.editar'),('admin','estudiante.baja'),
('admin','docente.ver'),('admin','docente.crear'),('admin','docente.editar'),('admin','docente.baja'),
('admin','materia.ver'),('admin','materia.crear'),('admin','materia.editar'),('admin','materia.eliminar'),
('admin','nota.ver'),('admin','nota.crear'),('admin','nota.editar'),('admin','nota.eliminar'),('admin','kardex.ver'),
('admin','identidad.gestionar'),('admin','identidad.cambiar_rol'),('admin','auditoria.ver'),
('admin','integridad.verificar'),('admin','integridad.restaurar'),('admin','config.cambiar'),('admin','mfa.configurar'),
-- auditor: solo lectura y verificación
('auditor','estudiante.ver'),('auditor','nota.ver'),('auditor','kardex.ver'),('auditor','auditoria.ver'),('auditor','integridad.verificar'),
-- coordinador: supervisión académica de solo lectura
('coordinador','estudiante.ver'),('coordinador','nota.ver'),('coordinador','kardex.ver'),
-- docente: sus materias (el contexto se valida en el servidor)
('docente','estudiante.ver'),('docente','materia.propias'),('docente','nota.ver'),('docente','nota.crear'),('docente','nota.editar'),('docente','nota.eliminar'),('docente','kardex.ver'),('docente','mfa.configurar'),
-- estudiante: solo su propio kardex (contexto validado en el servidor)
('estudiante','kardex.ver');

-- PARTE 4: auditoría con cadena de hashes (evidencia de alteración)
CREATE TABLE IF NOT EXISTS auditoria (
  id BIGINT NOT NULL AUTO_INCREMENT,
  momento DATETIME NOT NULL,
  id_identidad INT NULL,
  usuario VARCHAR(80) NULL,
  rol VARCHAR(20) NULL,
  evento VARCHAR(50) NOT NULL,
  resultado ENUM('ok','fallo','denegado') NOT NULL,
  recurso VARCHAR(120) NULL,
  detalle TEXT NULL,
  ip VARCHAR(45) NULL,
  severidad ENUM('info','alerta','critica') NOT NULL DEFAULT 'info',
  hash_prev CHAR(64) NOT NULL,
  hash CHAR(64) NOT NULL,
  PRIMARY KEY (id),
  KEY idx_aud_evento (evento, momento),
  KEY idx_aud_ident (id_identidad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cambios de configuración
CREATE TABLE IF NOT EXISTS configuracion (
  clave VARCHAR(40) NOT NULL,
  valor VARCHAR(100) NOT NULL,
  PRIMARY KEY (clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO configuracion (clave, valor) VALUES
('max_intentos','5'),('bloqueo_minutos','15'),('inactividad_minutos','15'),('sesion_maxima_horas','8'),('modo_sistema','normal');

-- PARTE 5: integridad y recuperación de expedientes (kardex)
ALTER TABLE kardex ADD COLUMN IF NOT EXISTS sello CHAR(64) NULL;

CREATE TABLE IF NOT EXISTS kardex_historial (
  id BIGINT NOT NULL AUTO_INCREMENT,
  id_kardex INT NOT NULL,
  accion ENUM('crear','editar','eliminar','restaurar','migracion') NOT NULL,
  datos_antes TEXT NULL,
  datos_despues TEXT NULL,
  id_identidad INT NULL,
  momento DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY (id_kardex)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
