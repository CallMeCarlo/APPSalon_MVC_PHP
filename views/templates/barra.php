<div class="barra">
    <p> <?php echo "Bienvenido " . $nombre ?> </p>

    <a href="/logout" class="boton">Cerrar Sesion</a>
</div>

<?php if(isset($_SESSION["admin"])) { ?>

<div class="barra-servicios">
    <a href="/admin" class="boton">Ver Citas</a>
    <a href="/servicios" class="boton">Ver Servicio</a>
    <a href="/servicios/crear" class="boton">Nuevo Servicio</a>
</div>


<?php } ?>
