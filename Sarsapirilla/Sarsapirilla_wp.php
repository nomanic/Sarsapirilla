<?php
/**
 * Plugin Name: Sarsapirilla
 * Plugin URI: http://www.nomanic.biz/Sarsapirilla/
 * Description: A Lorem Picsum Package
 * Version: 2.14
 * Author: Neil Oman
 * Author URI: http://www.nomanic.biz/
 * License: GNU GPL (see text file)
 *
 * Copyright 2020  Nomanic  (email : nomanic99@gmail.com)
 *
 */

function Sarsapirilla_adding_scripts() {
wp_register_script('Sarsapirilla', plugins_url('Sarsapirilla.js', __FILE__));
wp_enqueue_script('Sarsapirilla');
}
  
add_action( 'wp_enqueue_scripts', 'Sarsapirilla_adding_scripts' );  
?>