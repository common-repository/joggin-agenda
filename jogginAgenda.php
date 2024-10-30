<?php
/*
Plugin Name: Joggin Agenda
Description: Ce plugin affiche dans un widget la liste des prochaines sessions d'un utilisateur jogg.in
Author: Jean-Marc MALECOT
Author URI: http://www.iazone.fr
Version: 1.3.2
*/
include_once plugin_dir_path( __FILE__ ).'/agendaWidget.php';
class JogginAgenda_Plugin
{
    public function __construct()
    {  
    	add_action('widgets_init', function(){register_widget('JogginAgenda_Widget');});
    	new JogginAgenda_Widget();
    }
}

new JogginAgenda_Plugin();