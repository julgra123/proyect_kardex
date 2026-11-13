<?php
require_once 'includes/funciones.php';
if (!empty($_SESSION['id_identidad'])) { auditar('logout', 'ok'); cerrar_sesion('logout'); }
ir('login.php?m=logout');
