<?php
final class IVD_Shortcodes{
    public static $name_of_unit='';
    public static $course = null;
    public static $course_float = null;
    public static $bonus_for_referrer=0;
    public static $bonus_for_new_user=0;
    public static $old_referrer_user_units=0;

    public function __construct()
    {
        add_action('init',array($this,'IVD_shortcode_init'));

        self::$name_of_unit=get_option('name_of_unit');
        self::$course= get_option('course_of_units');
        self::$course_float=str_replace(',', '.', self::$course);
        self::$bonus_for_referrer=get_option( 'bonus_for_referrer' );
        self::$bonus_for_new_user=get_option( 'bonus_for_new_user' );
        self::$old_referrer_user_units=get_option( 'bonus_for_new_user' );

        settype(self::$bonus_for_referrer,'int');
        settype(self::$bonus_for_new_user,'int');
        settype(self::$old_referrer_user_units,'int');
        settype(self::$course_float, 'float');

    }

    function IVD_shortcode_init(){
        add_shortcode('view_referr_link', array($this, 'IVD_generate_refer_link'));
        add_shortcode('view_units_current_user', array($this, 'IVD_generate_units_current_user'));
        add_shortcode('view_account_description', array($this, 'IVD_generate_account_description'));
        add_shortcode('view_sms_auth_current_user', array($this, 'IVD_generate_sms_auth_current_user'));
        add_shortcode('view_currency_exchange', array($this, 'IVD_generate_view_currency_exchange'));
        add_shortcode('my_show_user', array($this, 'IVD_get_user'));
        add_shortcode('show_buttons_share', array($this, 'IVD_sharebuttons'));
        add_shortcode('show_social_inputs', array($this, 'IVD_social_inputs'));
  

    }
    /**
     * @return bool|string
     * view refer link to site html
     */
    function IVD_generate_refer_link()
    {

        $url = $_SERVER['REQUEST_URI'];

        strlen($url) == 15 ? $url = substr($url, 3, 15) : $url = false;

        if (wp_get_current_user()->exists() && $url == '/my-account/') {

            $link = home_url() . '?ref=' . get_current_user_id();

            return '<div class="col-sm-12">[:en]Your referral link[:ru]Ваша реферальная ссылка[:de]Ihr Empfehlungslink[:]<input style="min-width:300px;font-weight: 600;" type="text" readonly value="' . $link . '"></div>';

        } else return false;

    }

    /**
     * @param $user_id
     * @return mixed
     */
    function IVD_get_units($user_id){
        return get_user_meta($user_id, 'units', true);
    }

    /**
     * @param $user_id
     * @return mixed
     */
    function IVD_get_counter($user_id){
        return get_user_meta($user_id, 'counter', true);
    }

    /**
     * @param $user_id
     * @return float|int
     */
    function IVD_get_amount_for_course($user_id){
        $amount_for_course = $this->get_units($user_id)* self::$course_float;
        return $amount_for_course;
    }
    /**
     * @return bool|string
     * balance
     */
    function IVD_generate_units_current_user()

    {

        $url = $_SERVER['REQUEST_URI'];

        strlen($url) == 15 ? $url = substr($url, 3, 15) : $url = false;


        if (wp_get_current_user()->exists() && $url == '/my-account/') {

            $units = $this->get_units(get_current_user_id());

            $counter_referrals = $this->get_counter(get_current_user_id());

            $amount_for_course = $this->get_amount_for_course(get_current_user_id());

            return '<div class="col-sm-12">[:en]Your account balance[:ru]Ваш баланс[:de]Ihre Guthaben[:]: <label style="min-width:300px;font-size: 23px;font-weight: 600;" >' . $units .' '. self::$name_of_unit. ' &asymp; ' . $amount_for_course . ' $</div>

                <div class="col-sm-12">[:en]Count of referrals[:ru]Количество активных приглашений[:de]Die Anzahl der aktiven Einladungen[:]: <label style="min-width:300px;font-weight: 600;" >' . $counter_referrals . '</label></div>';

        } else return false;

    }


    /**
     * @return string
     */
    function IVD_generate_account_description()

    {

        $link = home_url() . '?ref=' . get_current_user_id();

        return '<div class="col-sm-12">

            <div style="visibility: hidden">' . $link . '</div>

            <div>[:en]Register and you will receive additional points in your account in the amount of ' . self::$bonus_new_user . ' tokens '.self::$name_of_unit.' (with accept your phone number)[:ru]Зарегистрируйтесь и вы получите дополнительные токены МСЕ на свой счет в размере ' . self::$bonus_new_user . ' МСЕ (при подтверждении своего номера телефона)[:] </div>

            <div>[:en]Invite a friend or share your referral link with anyone and you will receive additional points in your account in the amount of ' . self::$bonus_referrer . ' tokens '.self::$name_of_unit.'[:ru]Пригласите друга или поделитесь своей реферальной ссылкой с кем либо и вы получите дополнительные токены МСЕ на свой счет в размере ' . self::$bonus_for_referrer . ' '. self::$name_of_unit.'[:] </div>


            </div>';

    }


    /**
     * Collect list of including scripts for add_action next
     */
    function IVD_include_plugin_scripts(){
        wp_enqueue_script('ajax_async_handler', IVD_PLUGIN_URL_REFERRER . '/js/ajax_async.js', array('jquery'), '', true);

        wp_enqueue_script('check_script', IVD_PLUGIN_URL_REFERRER . '/js/check.js', array('jquery'), '', true);

        wp_localize_script('ajax_async_handler', 'myajax', array('url' => admin_url('admin-ajax.php')));
    }
    /*
     * SMS Authentication view
     */
    function IVD_generate_sms_auth_current_user()

    {

        $phone_num = get_user_meta(get_current_user_id(), 'user_registration_number_box_1529446202', true);

        if (wp_get_current_user()->exists() && !empty($phone_num)) {

            add_action('wp_enqueue_scripts',array($this,'IVD_include_plugin_scripts'));

            $sms_password = get_user_meta(get_current_user_id(), 'sms_secret_key', true);

            // update_user_meta(get_current_user_id(),'Verified_phone_by_sms','NO');//remove verify

            $verify_number_status = get_user_meta(get_current_user_id(), 'Verified_phone_by_sms', true);

            if ($verify_number_status == 'OK') {

                echo '<div class="verify-view verify-container" style="color:green" data="OK">[:en]PHONE NUMBER IS VERIFY[:ru]ВАШ НОМЕР ВЕРИФИЦИРОВАН[:]</div>';

            } else {

                echo '<div class="verify-view not-verify-container" style="color:red" data="NO">[:en]PHONE NUMBER IS NOT VERIFY[:ru]ВАШ НОМЕР НЕ ВЕРИФИЦИРОВАН[:]</div>';

                echo '<div class="col-sm-12 not-verify-container">

                        <label>[:en]Confirm your phone number by sms and catch additional tokens '.self::$name_of_unit.' to you account[:ru]

                        Подтвердите свой номер мобильного телефона по SMS и получите дополнительные токены '.self::$name_of_unit.' на свой счет[:]</label>

                        <br><small>[:en]By sending SMS you agree to receive it[:ru]Отправляя SMS вы даете свое согласие на его получение[:]</small><br>

                            <button style="padding: 10px;margin: 5px;" id="btn_sms_generate" type="button" class="btn btn-outline-dark" >[:en]Get SMS code[:ru]Получить SMS код[:]</button>

                            <input id="btn_sms_input_hidden" style="display:none; visibility:hidden;" type="hidden" value="' . get_current_user_id() . '">

                            <input id="input_sms_check_pass" type="number" value="" placeholder="[:en]set SMS code[:ru]укажите SMS код[:]">

                            <button style="padding: 10px;margin: 5px;" id="btn_sms_check_pass" type="button" class="btn btn-outline-secondary">[:en]Check[:ru]Подтвердить[:]</button>

               </div>';

            }

        } else return;

    }

    /*
     * Shortcode currency view
     */

    function IVD_generate_view_currency_exchange()

    {

        if (wp_get_current_user()->exists()) {

            $course = !empty(get_option('course_of_units')) ? get_option('course_of_units') : 'not course now';

            return '<div class="col-sm-12"><label>[:en]Course currency exchange[:ru]Текущий курс обмена[:]: </label><input style="min-width:200px;" type="text" readonly value="' . $course . '"><label>[:en]for 1 token '.self::$name_of_unit.'[:ru]за 1 токен МСЕ[:]</label></div>';

        } else return;

    }

    /*
     * ADD ACTION BY SHOW USER ON FRONT-END HEADER
     */

    function IVD_get_user()
    {

        $user_id = get_current_user_id();

        if (!empty($user_id)) {

            $user = get_user_by('ID', $user_id);

            $username = $user->display_name;

            if (strlen($username) > 8) {

                $username = substr($username, 0, 6);

                $username .= '...';

            }

            //$all_meta_for_user = get_user_meta($user_id);

            echo '<a style="font-size:17px;font-weight:400;" class="user-front-name" href=' . get_home_url() . '/my-account/" title="LogIn">' . $username . '</a>';

        } else echo '<a style="font-weight:400;" class="user-front-name" href=' . get_home_url() . '/my-account/" title="LogIn">LogIn</a>';

    }

    function IVD_sharebuttons()
    {

        $url = $_SERVER['REQUEST_URI'];

        strlen($url) == 15 ? $url = substr($url, 3, 15) : $url = false;


        if (wp_get_current_user()->exists() && $url == '/my-account/') {

            $user_id = (get_current_user_id());

            $reflink = esc_url(home_url() . '?ref=' . get_current_user_id());


            echo '<label>[:ru]Поделиться через социальную сеть и получи '.self::$bonus_for_new_user.' '.self::$name_of_unit.' за каждого приглашенного партнера[:en]Share via social and get '.self::$bonus_for_new_user.' '.self::$name_of_unit.' for each invited partner[:de]Teilen Sie über das soziale Netzwerk und erhalten Sie '.self::$bonus_for_new_user.' '.self::$name_of_unit.' für jeden eingeladenen Partner[:]</label><br>

                    <a class="elementor-icon elementor-social-icon elementor-social-icon-facebook elementor-animation-pop" href="https://www.facebook.com/sharer/sharer.php?u=' . $reflink . '" title="Share on Facebook" target="_blank"> <i class="fa fa-facebook"></i></a>

                    <a class="elementor-icon elementor-social-icon elementor-social-icon-twitter elementor-animation-pop" href="https://twitter.com/home?status=' . $reflink . '" title="Share on Twitter " target="_blank"><i class="fa fa-twitter"></i></a>';

            //<a class="elementor-icon elementor-social-icon elementor-social-icon-telegram elementor-animation-pop" href="https://telegram.me/share/url?url=' . $reflink . '" title="Share on Telegram" target="_blank"> <i class="fa fa-telegram"></i></a>';


        } else return;

    }

    /*
    * Function for generate inputs for social accounts
    */

    function IVD_social_inputs()
    {

        if (wp_get_current_user()->exists()) {

            $user_id = (get_current_user_id());

            $facebook_account = get_user_meta($user_id, 'user_registration_facebook_account', true);

            $twitter_account = get_user_meta($user_id, 'user_registration_twitter_account', true);

            $telegram_account = get_user_meta($user_id, 'user_registration_telegram_account', true);


            echo '  <i class="fa fa-facebook" ><input style="margin-left: 5px ;" class="social_link_facebook" data="facebook" value="' . $facebook_account . '" placeholder="[:en]entry your account[:ru]введите свой аккаунт[:de][:]"></i>

                     <i class="fa fa-twitter" ><input style="margin-left: 5px ;" class="social_link_twitter" data="twitter" value="' . $twitter_account . '" placeholder="[:en]entry your account[:ru]введите свой аккаунт[:de][:]"></i>

                     <i class="fa fa-telegram" ><input style="margin-left: 5px ;" class="social_link_telegram" data="telegram" value="' . $telegram_account . '" placeholder="[:en]entry your account[:ru]введите свой аккаунт[:de][:]"></i>

                    <br><button type="submit" class="btn btn-submit" id="save-social-accounts" style="padding: 10px;margin: 5px;">[:en]Save social accounts[:ru]Сохранить социальные аккаунты[:de][:]</button>';

        } else return;

    }
}



