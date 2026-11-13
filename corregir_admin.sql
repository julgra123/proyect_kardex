-- SOLO para la base original si admin no puede ingresar.
-- Convierte únicamente la contraseña original en texto plano al formato que espera el login.
-- No cambia contraseñas que ya hayan sido personalizadas.
UPDATE administradores SET password='$2y$12$yQ3IvaYklUG337Ke19pZ/u9cAW8g/cAmBrTREeg63Etf5qDv3sNG2' WHERE usuario='admin' AND password='123456';
