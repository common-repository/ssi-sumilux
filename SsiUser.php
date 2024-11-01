<?php
define ('SSI_WP_USER_META_IDENTITY', 'ssi_identity');


class SsiUser
{
    public static $errors = 0;

    private static $tran = array(
        'а'=>'a', 'б'=>'b', 'в'=>'v', 'г'=>'g', 'д'=>'d', 'е'=>'e', 'ж'=>'g', 'з'=>'z',
        'и'=>'i', 'й'=>'y', 'к'=>'k', 'л'=>'l', 'м'=>'m', 'н'=>'n', 'о'=>'o', 'п'=>'p',
        'р'=>'r', 'с'=>'s', 'т'=>'t', 'у'=>'u', 'ф'=>'f', 'ы'=>'i', 'э'=>'e', 'А'=>'A',
        'Б'=>'B', 'В'=>'V', 'Г'=>'G', 'Д'=>'D', 'Е'=>'E', 'Ж'=>'G', 'З'=>'Z', 'И'=>'I',
        'Й'=>'Y', 'К'=>'K', 'Л'=>'L', 'М'=>'M', 'Н'=>'N', 'О'=>'O', 'П'=>'P', 'Р'=>'R',
        'С'=>'S', 'Т'=>'T', 'У'=>'U', 'Ф'=>'F', 'Ы'=>'I', 'Э'=>'E', 'ё'=>"yo", 'х'=>"h",
        'ц'=>"ts", 'ч'=>"ch", 'ш'=>"sh", 'щ'=>"shch", 'ъ'=>"", 'ь'=>"", 'ю'=>"yu", 'я'=>"ya",
        'Ё'=>"YO", 'Х'=>"H", 'Ц'=>"TS", 'Ч'=>"CH", 'Ш'=>"SH", 'Щ'=>"SHCH", 'Ъ'=>"", 'Ь'=>"",
        'Ю'=>"YU", 'Я'=>"YA", "?"=>"",
    );

    public function getEmail(){
        return null;
    }


    static function create ($profile_email, $jo, $uid) {
        $user_data = array();


        $firstName =  strtr($jo->user->firstName, self::$tran);
        $lastName = strtr($jo->user->lastName, self::$tran);


        $user_data['user_login'] = "ssi-".utf8_encode(substr(utf8_decode($firstName), 0, 3))."-".utf8_encode(substr(utf8_decode($lastName), 0, 3));


       if ($user_data['user_login'] == "ssi--"){
           $user_data['user_login'] = "ssi-noname";
       }


        //Checking for existing user_login
        global $wpdb;
        $result = $wpdb->get_results( "SELECT COUNT(*) as cnt FROM $wpdb->users WHERE user_login = '".  $user_data['user_login']."' " );

        var_dump($result);

       if ($result[0]->cnt){
             $user_data['user_login'] = $user_data['user_login'] . "-".++$result[0]->cnt;
        }

        $user_data['user_url'] = $jo->user->authIdentifier;
        $user_data['first_name'] = $jo->user->firstName;
        $user_data['last_name'] = $jo->user->lastName;

        $user_data['display_name'] = trim($jo->user->firstName.' '.$jo->user->lastName);


        $user_data['user_pass'] = wp_generate_password();
        $user_data['user_email'] = $profile_email;


        $wp_id = wp_insert_user($user_data);
        add_user_meta( $wp_id, 'ssi_uid', $uid, 1 );

        return $wp_id;
    }


    static function setIdentity ($wp_id, $profile) {
        update_usermeta($wp_id, SSI_WP_USER_META_IDENTITY, $profile->identity);

    }

    static function getUserByMail ($email, &$WpDb) {
        $result = $WpDb->get_var($WpDb->prepare(" SELECT ID
			FROM $WpDb->users
			WHERE  user_email = %s", $email));


        return ($result) ? $result : null;
    }
}
?>