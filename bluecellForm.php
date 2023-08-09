<?php
/**
 Plugin Name: BlueCell Form
 Description: Plugin realizado para BlueCell
 Version: 0.0.1
*/

function activar() {
    global $wpdb;

    $tabla_nombre = $wpdb->prefix . 'bluecell'; 
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $tabla_nombre (
        codigo INT UNSIGNED AUTO_INCREMENT,
        nombre VARCHAR(40) NOT NULL,
        email VARCHAR(100) NOT NULL,
        telefono VARCHAR(9) NOT NULL,
        asunto VARCHAR(150) NOT NULL,
        mensaje TEXT NOT NULL,
        privacyPolicy BOOLEAN NOT NULL,
        fecha DATETIME NOT NULL,
        PRIMARY KEY (codigo)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}


function desactivar() {

}

// -------------------------------------------- SCRIPTS Y ESTILOS -------------------------------------------------------------

function add_styles() {
  wp_enqueue_style('plugin-styles', plugins_url('css/bluecell.css', __FILE__), array(), '1.0', 'all');
}
add_action('wp_enqueue_scripts', 'add_styles');

function agregar_datos_js() {
  wp_enqueue_script('jquery'); 
  wp_enqueue_script('jquery-validation', 'https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js', array('jquery'), '1.19.3', true);
  wp_enqueue_script('validation-script', plugins_url('js/validacion.js', __FILE__), array('jquery', 'jquery-validation'), '1.0', true);

  $script_data = array(
      'ajaxurl' => admin_url('admin-ajax.php')
  );
  wp_add_inline_script('validation-script', 'var myScriptData = ' . wp_json_encode($script_data), 'before');
}
add_action('wp_enqueue_scripts', 'agregar_datos_js');


// -------------------------------- FORMULARIO -----------------------------------------------------
function insertar_formulario_despues_contenido($content) {
    
    if (is_single()) {
      
      $formulario = '
        <div id="formulario-contacto">
          <h3>Formulario de Nombre y Apellidos</h3>
          <div class="form_success">Datos enviados correctamente</div>
          <form id="contactForm">
            
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>
    
            <label for="apellidos">Email:</label>
            <input type="text" id="email" name="email" required>
    
            <label for="telefono">Telefono:</label>
            <input type="text" id="telefono" name="telefono" pattern="[0-9]{9}" required>
    
            <label for="asunto">Asunto:</label>
            <input type="text" id="asunto" name="asunto" " required>
    
            <textarea name="mensaje" id="mensaje" form="contactForm">Escriba aquí su mensaje...</textarea>
    
            <div class="policy-container">
              <input type="checkbox" id="policy" name="privacyPolicy" value="1" required>
              <label> Acepto la política de privacidad</label>
            </div>

            <input type="submit" value="Enviar" id="submit">
          </form>
        </div>
      ';
      
      $content .= $formulario;
    }
  
    return $content;
  }

// ----------------------- SERVICIO AJAX ----------------------------------

function guardar_datos() {

    global $wpdb;
    
    $nombre = sanitize_text_field($_POST['nombre']);
    $email = sanitize_email($_POST['email']);
    $telefono = sanitize_text_field($_POST['telefono']);
    $asunto = sanitize_text_field($_POST['asunto']);
    $mensaje = sanitize_textarea_field($_POST['mensaje']);
    $privacyPolicy = isset($_POST['privacyPolicy']) ? 1 : 0;
    $fecha_actual = current_time('mysql');

    $tabla = $wpdb->prefix . 'bluecell';
    $datos = array(
      'nombre' => $nombre,
      'telefono' => $telefono,
      'email' => $email,
      'asunto' => $asunto,
      'mensaje' => $mensaje,
      'privacyPolicy' => $privacyPolicy,
      'fecha' => $fecha_actual 
    );
    
    $formato = array('%s', '%s', '%s', '%s', '%s', '%d', '%s');
    $result = $wpdb->insert($tabla, $datos, $formato);
    
    if ($result) {
        wp_send_json_success('Datos almacenados exitosamente.');
    } else {
        wp_send_json_error('Error al guardar los datos.');
    }

    wp_die(); 
}


// ----------------------- MOSTRAR DATOS EN CMS ----------------------------------

function mostrar_datos_formulario() {
  global $wpdb;
  $tabla = $wpdb->prefix . 'bluecell';
  $datos = $wpdb->get_results("SELECT * FROM $tabla");

  echo '<div class="wrap">';
  echo '<h2>Datos del Formulario Enviados</h2>';
  
  if (!empty($datos)) {
      echo '<table class="widefat">
              <thead>
                  <tr>
                      <th>Nombre</th>
                      <th>Email</th>
                      <th>Teléfono</th>
                      <th>Asunto</th>
                      <th>Mensaje</th>
                      <th>Privacidad</th>
                      <th>Fecha</th>
                  </tr>
              </thead>
              <tbody>';
      
      foreach ($datos as $dato) {
          echo '<tr>
                  <td>' . $dato->nombre . '</td>
                  <td>' . $dato->email . '</td>
                  <td>' . $dato->telefono . '</td>
                  <td>' . $dato->asunto . '</td>
                  <td>' . $dato->mensaje . '</td>
                  <td>' . (($dato->privacyPolicy) ? "Sí" : "No") . '</td>
                  <td>' . $dato->fecha . '</td>
              </tr>';
      }

      echo '</tbody></table>';
  } else {
      echo '<p>No hay datos disponibles.</p>';
  }
  
  echo '</div>';
}

function agregar_pagina_admin() {
  add_menu_page('Datos del Formulario', 'Datos del Formulario', 'manage_options', 'datos-formulario', 'mostrar_datos_formulario');
}
add_action('admin_menu', 'agregar_pagina_admin');


// ----------------------------------- ACTIONS ------------------------------------------------
add_action('wp_ajax_guardar_datos', 'guardar_datos');
add_action('wp_ajax_nopriv_guardar_datos', 'guardar_datos'); // usuarios no logueados

add_filter('the_content', 'insertar_formulario_despues_contenido');

register_activation_hook(__FILE__, 'activar');
register_deactivation_hook(__FILE__, 'desactivar');