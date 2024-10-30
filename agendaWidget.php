<?php
class JogginAgenda_Widget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct('JogginAgenda', 'Agenda Joggin', array('description' => 'Liste des prochaines sessions'));
    }
    
	public function widget($args, $instance)
	{
	    date_default_timezone_set('Europe/Paris');
		setlocale(LC_ALL, 'fr_FR');

		echo $args['before_widget'];
	    echo $args['before_title'];
	    echo apply_filters('widget_title', $instance['title']);
	    echo $args['after_title'];

	    $identifiant = $instance['identifiant'];
	    $nb_display = $instance['nb_display'];
	    $affichage = $instance['affichage'];
	    $groupe = $instance['groupe'];

	    echo "<style>".$instance['css']."</style>";	   

	    if(!$affichage && !$groupe)
	    	$affichage = "<strong>{LINK}{TITLE} - {HORAIRE}{/LINK}</strong><br />{DISTANCE} à {ALLURE}";
	    elseif(!$affichage)
	    	$affichage = "{DATE}<br /><strong>{LINK}{TITLE} - {HORAIRE}{/LINK}</strong><br />{DISTANCE} à {ALLURE}";


	    if(!$identifiant){
	    	echo "<div>ERREUR : Paramétrage incomplet du widget (saisir un identifiant)</div>";
	    	echo $args['after_widget'];
	    	return true;
	    }
	    
	    $url = "https://api.jogg.in/api/sessions/user/".$identifiant;
	    $file = plugin_dir_path( __FILE__ )."/".$identifiant.".json";

	    $refresh = time() - @filemtime($file);


	    if(!file_exists($file) || $refresh > 60*30)
	    	copy($url,$file);

		$json = file_get_contents($file);
		$obj = json_decode($json);

		function get_date($time){
			setlocale(LC_TIME, "fr_FR.utf8",'fra');
			return $day = ucwords(strftime("%A %d %B",$time));
		}

		function vitesse($distance, $duration){
			$vitesse = $distance/($duration/60);

			return ceil($vitesse)."km/h";
		}

		function duree($duration){
			if($duration<60)
				return $duration."min";
			elseif($duration==60)
				return "1h";
			else
				return floor($duration/60)."h".($duration%60);
		}

		function get_infos_agenda($affichage, $data){
			$time = strtotime($data->time);
			$day = get_date($time);
			
			$affichage = str_replace("{DATE}",$day, $affichage);
			$affichage = str_replace("{TITLE}",$data->title, $affichage);
			$affichage = str_replace("{DISTANCE}",$data->routes[0]->distance."km", $affichage);
			$affichage = str_replace("{ALLURE}",vitesse($data->routes[0]->distance,$data->routes[0]->duration), $affichage);
			$affichage = str_replace("{TEMPS}",duree($data->routes[0]->duration), $affichage);
			$affichage = str_replace("{LINK}","<a href='https://jogg.in/session/".$data->id."'>", $affichage);
			$affichage = str_replace("{/LINK}","</a>", $affichage);
			$affichage = str_replace("{IMAGE}","<img src='https://api.jogg.in/api/session/avatar/".$data->id."' style='width:100%;' />", $affichage);

			$affichage = str_replace("{TITRE}",$data->title, $affichage);	
			$affichage = str_replace("{SHORTDATE}",date("d/m/Y",$time), $affichage);
			$affichage = str_replace("{JOUR}",date("d",$time), $affichage);
			$affichage = str_replace("{MOIS}",date("m",$time), $affichage);
			$affichage = str_replace("{ANNEE}",date("Y",$time), $affichage);
			$affichage = str_replace("{HORAIRE}",date("H:i",$time), $affichage);
			
			return $affichage;
		}



		$nb_display = min(sizeof($obj),$nb_display);

		echo "<div id='JogginAgenda'>";
		for($i=0;$i<$nb_display; $i++){
			$data = $obj[$i];
			$time = strtotime($data->time);
			$day = get_date($time);

			$html = get_infos_agenda($affichage, $data, $groupe);

			if($new_day!=$day && $groupe=="1"){
				echo "<strong>".$day."</strong><br />";
				$new_day=$day;
			}

			echo "<div class='JogginAgenda'>".$html."</div>";
		}
		echo "</div>";

	    echo $args['after_widget'];
	}

	public function update($new_instance, $old_instance){
	    $instance = $old_instance;
	 
	    /* Récupération des paramètres envoyés */
	    $instance['title'] = strip_tags($new_instance['title']);
	    $instance['identifiant'] = $new_instance['identifiant'];
	    $instance['pseudo'] = $new_instance['pseudo'];
	    $instance['nb_display'] = $new_instance['nb_display'];
	    $instance['affichage'] = $new_instance['affichage'];
	    $instance['css'] = $new_instance['css'];
	    $instance['groupe'] = $new_instance['groupe'];	    
	 
	    return $instance;
	}

	public function form($instance){
	   	$title = esc_attr($instance['title']);
	    $identifiant = esc_attr($instance['identifiant']);
	    $nb_display = esc_attr($instance['nb_display']);
	    $affichage = esc_attr($instance['affichage']);
	    $css = esc_attr($instance['css']);
	    $groupe = esc_attr($instance['groupe']);

	    if(!$nb_display) 
	    	$nb_display = 10;

	    echo '<p>';
	    echo '<label for="'.$this->get_field_name( 'title' ).'">'._e( 'Title:' ).'</label>';
	    echo '<input class="widefat" id="'.$this->get_field_id( 'title' ).'" name="'.$this->get_field_name( 'title' ).'" type="text" value="'.$title.'" />';
	    echo '</p>';
	    echo '<p>';
	    echo '<label for="'.$this->get_field_name( 'identifiant' ).'">'._e( 'Identifiant:' ).'</label>';
	    echo '<input class="widefat" id="'.$this->get_field_id( 'identifiant' ).'" name="'.$this->get_field_name( 'identifiant' ).'" type="text" value="'.$identifiant.'" />';
	    echo '</p>';
	    echo '<p>';
	    echo '<label for="'.$this->get_field_name( 'nb_display' ).'">'._e( 'Nombre de sessions:' ).'</label>';
	    echo '<input class="widefat" id="'.$this->get_field_id( 'nb_display' ).'" name="'.$this->get_field_name( 'nb_display' ).'" type="text" value="'.$nb_display.'" />';
	    echo '</p>';
	    echo '<p>';
	    echo '<label for="'.$this->get_field_name( 'groupe' ).'">'._e( 'Grouper par date:' ).'</label><br />';
	    echo '<input id="'.$this->get_field_id( 'groupe' ).'" name="'.$this->get_field_name( 'groupe' ).'" type="checkbox" '.(($groupe=='1'?"CHECKED":"")).' value="1" />';
	    echo '</p>';
	    echo '<p>';
	    echo '<label for="'.$this->get_field_name( 'affichage' ).'">'._e( 'Affichage spécifique:' ).'</label>';
	    echo '<textarea rowspan="5" class="widefat" id="'.$this->get_field_id( 'affichage' ).'" name="'.$this->get_field_name( 'affichage' ).'">'.$affichage.'</textarea>';
	    echo '</p>';


	    echo '<p>';
	    echo '<label for="'.$this->get_field_name( 'css' ).'">'._e( 'CSS spécifique:' ).'</label>';
	    echo '<textarea rowspan="5" class="widefat" id="'.$this->get_field_id( 'css' ).'" name="'.$this->get_field_name( 'css' ).'">'.$css.'</textarea>';
	    echo '</p>';
	}
}

