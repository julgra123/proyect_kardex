<?php
require_once 'includes/funciones.php'; verificarSesion(); if(rol()!=='estudiante') ir(inicio());
$miKardex=true;
require 'ver_kardex.php';
