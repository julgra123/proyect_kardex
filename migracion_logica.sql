-- MIGRACIÓN para una base YA INSTALADA (importar sobre kardex_escolar existente).
-- Refuerza en la base las reglas que antes solo controlaba PHP.
--
-- ANTES de ejecutar, comprueba que no haya repetidos (ambas consultas deben salir vacías):
--   SELECT re_estudiante,id_materia,trimestre,COUNT(*) FROM kardex GROUP BY 1,2,3 HAVING COUNT(*)>1;
--   SELECT nombre_materia,COUNT(*) FROM materias GROUP BY 1 HAVING COUNT(*)>1;
-- Si salen filas, corrígelas desde el sistema (Calificaciones / Materias) y recién ejecuta esto.

ALTER TABLE `kardex`
  ADD UNIQUE KEY `uq_kardex_est_mat_trim` (`re_estudiante`,`id_materia`,`trimestre`),
  ADD CONSTRAINT `chk_kardex_nota` CHECK (`nota` BETWEEN 0 AND 100),
  ADD CONSTRAINT `chk_kardex_trimestre` CHECK (`trimestre` IN (1,2,3));

ALTER TABLE `materias`
  ADD UNIQUE KEY `uq_materias_nombre` (`nombre_materia`);
