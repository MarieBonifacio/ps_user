<?php
/*
  Plugin Name: Portail Sécu Registration Form
  Description: Custom registration form using shortcode and script as well
  Version: 1.x
  Author: Marie Bonifacio
*/

function ps_custom_registration_form($first_name, $last_name, $id_alc, $password, $passwordVerif, $email, $location) {
    global $first_name, $last_name, $id_alc, $password, $passwordVerif, $email, $location;

    echo '
    <h2>Inscription</h2>  
    <form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
        <div>
            <label for="">Nom :</label>
            <input type="text" name="last_name">
        </div>
        <div>
            <label for="">Prénom :</label>            
            <input type="text" name="first_name">
        </div>
        <div>
            <label for="">Matricule :</label>  
            <input type="text" name="id_alc">
        </div>
        <div>
            <label for="">Adresse mail :</label>
            <input type="mail" name="email">
        </div>
        <div>
            <label for="">Mot de passe <small>(au moins 1 caractère spécial, 1 chiffre et 1 majuscule)</small>:</label>
            <input type="password" name="password">
        </div>
        <div>
            <label for="">Vérifiez votre mot de passe :</label>
            <input type="password" name="passwordVerif">
        </div>
        <div>
            <label for="location">Votre site :</label>
            <div class="select">
                <select name="location" id="sites">
                    <option value="">Veuillez choisir votre site</option>';
                    $sites = array("Auxerre", "Bielsko-Biala", "Bordeaux", "Boulogne-Sur-Mer", "Caen", "Calais", "Caldas da Rainha", "Châteauroux", "Cracovie", "Guimarães", "Île de France", "Lisbonne", "Nevers", "Poitiers", "Porto", "Porto Ferreira Dias", "Stalowa Wola", "Tauxigny", "Tunis", "Varsovie", "Villeneuve d\'Ascq");

                    for($i=0; $i<count($sites); $i++){
                        echo '<option value="'.$sites[$i].'">'.$sites[$i].'</option>';
                    }
                echo '
                </select>
                <i class="fas fa-sort-down"></i>
            </div>
        </div>
        <input type="submit" name="submit" value="S\'inscrire">
    </form>
    ';

 
}

function ps_reg_form_valid( $post)  {
    global $customize_error_validation;
    global $wpdb;
    $customize_error_validation = new WP_Error;

    if(empty($post['first_name']) || empty($post['last_name']) || empty($post['id_alc']) || empty($post['password']) || empty($post['passwordVerif']) || 
        empty($post['email']) || empty($post['location'])){
            $customize_error_validation->add('field', 'Veuillez remplir tous les champs.');
    }
    if ( !preg_match('/^([\w]+[.\-+]{0,1})([\w]+[.\-+]{0,1})*@([\w]+[.\-+]{0,1})([\w]+[._\-+]{0,1})*.[\w]{2,}+$/', $post['email'])){
        $customize_error_validation->add('field', 'L\'adresse mail n\'est pas valide.');
    }
    if( !preg_match("/^(?=(?:.*[A-Z]))(?=(?:.*[a-z]))(?=(?:.*[0-9]))(?=(?:.*[\!\#\$\%\'\(\)\*\+\,\-\.\/\:\;\<\=\>\?\@\[\]\^\_\`\{\|\}\~]))([A-Za-z0-9\!\#\$\%\'\(\)\*\+\,\-\.\/\:\;\<\=\>\?\@\[\]\^\_\`\{\|\}\~]{8,})$/", $post['password'])){
        $customize_error_validation->add('field', "Votre mot de passe doit contenir au moins une majuscule, un chiffre et un caractère spécial et 8 caractères.");
    }
    
    if($post['password'] != $post['passwordVerif']){
        $customize_error_validation->add('field', "votre mot de passe et sa vérification sont différents.");
    }
    
    if( !preg_match("#^[0-9]{1,6}$# ", $post['id_alc'])){
        $customize_error_validation->add('field', "Votre identifiant n'est pas correct");
    }

    $mail = $post['mail'];
    $r = $wpdb->get_results("SELECT * FROM wp_users where user_email='".$mail."'");

    if(count($r) !== 0){
        $customize_error_validation->add('field', "Utilisateur déjà existant");
    }
   

    if ( is_wp_error( $customize_error_validation ) ) {
        foreach ( $customize_error_validation->get_error_messages() as $error ) {
         echo '<p class="mess error">'.$error.'</p>';
        }
    }
}
 
function ps_user_registration_form_completion() {
    global $customize_error_validation, $first_name, $last_name, $id_alc, $password, $passwordVerif, $email, $location;
    if ( 1 > count( $customize_error_validation->get_error_messages() ) ) {
        $userdata = array(
         'first_name' =>   $first_name,
         'last_name' =>   $last_name,
         'user_login' =>   $email,
         'user_email' =>   $email,
         'user_pass' =>   $password,
        );
        $user = wp_insert_user( $userdata );


        // set no role
        $userStatus = add_user_meta($user, 'status', "0");
        $locSave = add_user_meta($user, 'location', $location);
        $idALCSave = add_user_meta($user, 'id_alc', $id_alc);
        $avatar = add_user_meta($user, 'avatar', "default.jpg");
        $notif = add_user_meta($user, 'notification', date("Y-m-d H:i:s"));
        echo 'Votre compte est bien enregistré. Veuillez attendre la validation.';
    }
}

function ps_custom_registration_form_function() {
    global $first_name, $last_name, $id_alc, $password, $passwordVerif, $email, $location;
    if ( isset($_POST['submit'] ) ) {
        ps_reg_form_valid($_POST);

        $username   =   'toto';
        $password   =   esc_attr( $_POST['password'] );
        $email   =   sanitize_email( $_POST['email'] );
        $first_name =   sanitize_text_field( $_POST['first_name'] );
        $last_name  =   sanitize_text_field( $_POST['last_name'] );
        $id_alc  =   sanitize_text_field( $_POST['id_alc'] );
        $location  =   sanitize_text_field( $_POST['location'] );
       ps_user_registration_form_completion(
         $username,
         $password,
         $email,
         $first_name,
         $last_name,
         $id_alc,
         $location
        );
    }
    ps_custom_registration_form(
        $first_name, $last_name, $id_alc, $username, $password, $passwordVerif, $email, $location
    );
}
 
function ps_custom_shortcode_registration() {
    ob_start();
    ps_custom_registration_form_function();
    return ob_get_clean();
}
 


function custom_validation_error_method( $errors, $lname, $last_name ) {
 
    if ( empty( $_POST['fname'] ) || ( ! empty( $_POST['fname'] ) && trim( $_POST['fname'] ) == '' ) ) {
        $errors->add( 'fname_error', __( '<strong>Error</strong>: Enter Your First Name.' ) );
    }
 
    if ( empty( $_POST['lname'] ) || ( ! empty( $_POST['lname'] ) && trim( $_POST['lname'] ) == '' ) ) {
        $errors->add( 'lname_error', __( '<strong>Error</strong>: Enter Your Last Name.' ) );
    }
    return $errors;
}

add_shortcode( 'ps_registration_form', 'ps_custom_shortcode_registration' );

add_filter( 'registration_errors', 'custom_validation_error_method', 10, 2 );