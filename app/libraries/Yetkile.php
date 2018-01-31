<?php if (!defined('BASEPATH')) exit('Direk Erişim Yasak...');
/**
 * M3 için Yetkilendirme Kütüphanesi
 *
 * @author		Mehmet ÖZDEMİR <admin@fsmtek.com>
 *
 * @copyright 2017-2018 Mehmet ÖZDEMİR
 *
 * @version 1.0.0
 *
 * @license LGPL
 * @license http://opensource.org/licenses/LGPL-3.0 Lesser GNU Public License
 *
 */
class Yetkile{
    /**
     * @access public
     * @var object
     */
    public $M;
    /**
     * @access public
     * @var array
     */
    public $config_degerleri;
    /**
     * @access public
     * @var array
     */
    public $hatalar = array();
    /**
     * @access public
     * @var array
     */
    public $bilgiler = array();
    /**
     * @access public
     * var array
     */
    public $flash_hatalar = array();
    /**
     * @access public
     * var array
     */
    public $flash_bilgiler = array();
    /**
     * @access public
     * @var object
     */
    public $yetki_db;
    // Tanımlamalar Burda Biter Kardeşim //
    public function __construct()
    {
        // Ana M Fonksiyonu
        $this->M = & get_instance();
        // Bağımlılıklar
        if(VERSIYON >= 2.2){
            $this->M->load->library('driver');
        }
        $this->M->load->library('session');
        // config/yetki.php
        $this->M->config->load('yetki');
        $this->config_degerleri = $this->M->config->item('yetki');
        // Kütüphanemize Veritabanımız Tanıtalım //
        $this->yetki_db = $this->M->load->database($this->config_degerleri['ana_db'], TRUE);
        // flashdata'dan hata ve bilgi mesajları yükle (ancak flashdata'da tekrar saklamayın)
        $this->hatalar = $this->M->session->flashdata('hatalar') ?: array();
        $this->bilgiler = $this->M->session->flashdata('bilgiler') ?: array();
        // Burada Bağımlılıklar Biter //
    }
    /**
     * @param string $tanitim
     * @param string $sifre
     * @param bool $hatirla
     * @return bool Indicates successful gir.
     */
    public function gir($tanitim, $sifre, $hatirla = FALSE, $gizli_kod = NULL){
        // cookieleri siliyoruz
        $cookie = array(
            'name'	 => 'user',
            'value'	 => '',
            'expire' => -3600,
            'path'	 => '/',
        );
        $this->M->input->set_cookie($cookie);
        if ($this->config_degerleri['ddos_koruma'] && ! $this->guncelle_giris_denemesini()) {

            $this->hata('Giriş denemelerini aştınız, hesaplarınız şimdi kilitlendi.');
            return FALSE;
        }
        if($this->config_degerleri['ddos_koruma'] && $this->config_degerleri['recaptcha_aktif'] && $this->al_giris_denemelerini() > $this->config_degerleri['recaptcha_giris_deneme']){
            $this->M->load->helper('recaptchalib');
            $reCaptcha = new ReCaptcha( $this->config_degerleri['recaptcha_secret']);
            $resp = $reCaptcha->verifyResponse( $this->M->input->server("REMOTE_ADDR"), $this->M->input->post("g-recaptcha-response") );

            if( ! $resp->success){
                $this->hata('Üzgünüz, girilen ReCAPTCHA metni yanlış.');
                return FALSE;
            }
        }
        if( $this->config_degerleri['kullanici_adi_ile_gir'] == TRUE){

            if( !$tanitim OR strlen($sifre) < $this->config_degerleri['kuccuk'] OR strlen($sifre) > $this->config_degerleri['buyuk'] )
            {
                $this->hata('Kullanıcı adı ve şifre eşleşmiyor.');
                return FALSE;
            }
            $db_dogrulama = 'kadi';
        }else{
            $this->M->load->helper('email');
            if( !valid_email($tanitim) OR strlen($sifre) < $this->config_degerleri['kuccuk'] OR strlen($sifre) > $this->config_degerleri['buyuk'] )
            {
                $this->hata('E-Posta ve şifre eşleşmiyor.');
                return FALSE;
            }
            $db_dogrulama = 'email';
        }
        // Sorgularıma Başlayalım
        $sorgu = null;
        $sorgu= $this->yetki_db->where($db_dogrulama, $tanitim);
        $sorgu = $this->yetki_db->where('banlimi', 1);
        $sorgu = $this->yetki_db->where('tanitim_kodu !=', '');
        $sorgu = $this->yetki_db->get($this->config_degerleri['yetkili']);
        if ($sorgu->num_rows() > 0) {
            $this->hata('Hesabınız doğrulanmadı. Lütfen e-postanızı kontrol edin ve hesabınızı doğrulayın.');
            return FALSE;
        }
        $sorgu = $this->yetki_db->where($db_dogrulama, $tanitim);
        $sorgu = $this->yetki_db->get($this->config_degerleri['yetkili']);
        if($sorgu->num_rows() == 0){
            $this->hata('Kullanıcı Sistemde Mevcut Değil...');
            return FALSE;
        }
        if($this->config_degerleri['gizlikod_aktif'] == TRUE AND $this->config_degerleri['gizlikod_sadece_ip_degisince'] == FALSE AND $this->config_degerleri['gizlikod_iki_adimli_giris'] == FALSE){
            if($this->config_degerleri['gizlikod_iki_adimli_giris'] == TRUE){
                $this->M->session->set_userdata('gizlikod_zorunlu', true);
            }

            $sorgu = null;
            $sorgu = $this->yetki_db->where($db_dogrulama, $tanitim);
            $sorgu = $this->yetki_db->get($this->config_degerleri['yetkili']);
            $totp_kodu =  $sorgu->row()->totp_kodu;
            if ($sorgu->num_rows() > 0 AND !$gizli_kod) {
                $this->hata('Kimlik Doğrulama Kodu Gerekli.');
                return FALSE;
            }else {
                if(!empty($totp_kodu)){
                    $this->M->load->helper('googleauthenticator');
                    $ga = new PHPGangsta_GoogleAuthenticator();
                    $checkResult = $ga->verifyCode($totp_kodu, $gizli_kod, 0);
                    if (!$checkResult) {
                        $this->hata('Geçersiz Kimlik Doğrulama Kodu');
                        return FALSE;
                    }
                }
            }
        }
        if($this->config_degerleri['gizlikod_aktif'] == TRUE AND $this->config_degerleri['gizlikod_sadece_ip_degisince'] == TRUE){
            $sorgu = null;
            $sorgu = $this->yetki_db->where($db_dogrulama, $tanitim);
            $sorgu = $this->yetki_db->get($this->config_degerleri['yetkili']);
            $totp_kodu =  $sorgu->row()->totp_kodu;
            $ip_adresi = $sorgu->row()->ip_adres;
            $simdikiip = $this->M->input->ip_address();

            if ($sorgu->num_rows() > 0 AND !$gizli_kod) {
                if($ip_adresi != $simdikiip ){
                    if($this->config_degerleri['gizlikod_iki_adimli_giris'] == FALSE){
                        $this->hata('Kimlik Doğrulama Kodu Gerekli');
                        return FALSE;
                    } else if($this->config_degerleri['gizlikod_iki_adimli_giris'] == TRUE){
                        $this->M->session->set_userdata('gizlikod_zorunlu', true);
                    }
                }
            }else {
                if(!empty($totp_kodu)){
                    if($ip_adresi != $simdikiip){
                        $this->M->load->helper('googleauthenticator');
                        $ga = new PHPGangsta_GoogleAuthenticator();
                        $checkResult = $ga->verifyCode($totp_kodu, $gizli_kod, 0);
                        if (!$checkResult) {
                            $this->hata('Geçersiz Kimlik Doğrulama Kodu');
                            return FALSE;
                        }
                    }
                }
            }
        }
        $sorgu = null;
        $sorgu = $this->yetki_db->where($db_dogrulama, $tanitim);
        $sorgu = $this->yetki_db->where('banlimi', 0);
        $sorgu = $this->yetki_db->get($this->config_degerleri['yetkili']);
        $tek = $sorgu->row();
        $sifre = ($this->config_degerleri['hash_aktif'] ? $sifre : $this->hash_password($sifre, $tek->id));
        if ( $sorgu->num_rows() != 0 && $this->sifreyi_dogrula($sifre, $tek->sifre) ) {

            // Gitişi Cookieye yazdırım
            $data = array(
                'id' => $tek->id,
                'username' => $tek->kadi,
                'email' => $tek->email,
                'loggedin' => TRUE
            );
            $this->M->session->set_userdata($data);
            // Yaw Kendimiz Hayırlatalım yoksa nerden bilecek
            if ( $hatirla ){
                $this->M->load->helper('string');
                $sonlanma = $this->config_degerleri['beni_hatirla'];
                $bugun = date("Y-m-d");
                $hatirlama_suresi = date("Y-m-d", strtotime($bugun . $sonlanma) );
                $kafadan_salla = random_string('alnum', 16);
                $this->guncelle_hatirla($tek->id, $kafadan_salla, $hatirlama_suresi );
                $cookie = array(
                    'name'	 => 'user',
                    'value'	 => $tek->id . "-" . $kafadan_salla,
                    'expire' => 99*999*999,
                    'path'	 => '/',
                );
                $this->M->input->set_cookie($cookie);
            }

            // Son girişi güncelliyoruz
            $this->guncelle_son_girisi($tek->id);
            $this->guncelle_aktivite();

            if($this->config_degerleri['basarili_giriste_sil'] == TRUE){
                $this->giris_deneme_sifirla();
            }
            return TRUE;
        } else {
            $this->hata('E-posta, Kullanıcı Adı veya Parola eşleşmiyor.');
            return FALSE;
        }
    }
    public function giris_varmi() {

        if ( $this->M->session->userdata('loggedin') ){
            return TRUE;
        } else {
            if( ! $this->M->input->cookie('user', TRUE) ){
                return FALSE;
            } else {
                $cookie = explode('-', $this->M->input->cookie('user', TRUE));
                if(!is_numeric( $cookie[0] ) OR strlen($cookie[1]) < 13 ){return FALSE;}
                else{
                    $sorgu = $this->yetki_db->where('id', $cookie[0]);
                    $sorgu = $this->yetki_db->where('hatirlama_kodu', $cookie[1]);
                    $sorgu = $this->yetki_db->get($this->config_degerleri['yetkili']);
                    $tekil = $sorgu->row();
                    if ($sorgu->num_rows() < 1) {
                        $this->guncelle_hatirla($cookie[0]);
                        return FALSE;
                    }else{
                        if(strtotime($tekil->hatirlama_suresi) > strtotime("now") ){
                            $this->gir_hizli($cookie[0]);
                            return TRUE;
                        }
                        else {
                            return FALSE;
                        }
                    }
                }
            }
        }
        return FALSE;
    }
    public function kontrol( $izin_par = FALSE ){
        $this->M->load->helper('url');
        if($this->M->session->userdata('gizlikod_zorunlu')){
            $this->hata('Kimlik Doğrulama Kodu Gerekli.');
            redirect($this->config_degerleri['gizlikod_iki_adimli_giriste_git']);
        }
        $izin_id = $this->al_izin_id($izin_par);
        $this->guncelle_aktivite();
        if($izin_par == FALSE){
            if($this->giris_varmi()){
                return TRUE;
            }else if(!$this->giris_varmi()){
                $this->hata('Üzgünüm, istediğiniz kaynağa erişiminiz yok.');
                if($this->config_degerleri['yetki_yoksa'] !== FALSE){
                    redirect($this->config_degerleri['yetki_yoksa']);
                }
            }

        }else if ( ! $this->izin_verildi($izin_id) OR ! $this->izin_verildi_gruba($izin_id) ){
            if( $this->config_degerleri['yetki_yoksa'] ) {
                $this->hata('Üzgünüm, istediğiniz kaynağa erişiminiz yok.');
                if($this->config_degerleri['yetki_yoksa'] !== FALSE){
                    redirect($this->config_degerleri['yetki_yoksa']);
                }
            }
            else {
                echo 'Üzgünüm, istediğiniz kaynağa erişiminiz yok.';
                die();
            }
        }
    }
    public function cikis() {

        $cookie = array(
            'name'	 => 'user',
            'value'	 => '',
            'expire' => -3600,
            'path'	 => '/',
        );
        $this->M->input->set_cookie($cookie);

        return $this->M->session->sess_destroy();
    }
    public function kul_al($user_id = FALSE) {
        if ($user_id == FALSE)
            $user_id = $this->M->session->userdata('id');
        $query = $this->yetki_db->where('id', $user_id);
        $query = $this->yetki_db->get($this->config_degerleri['yetkili']);
        if ($query->num_rows() <= 0){
            $this->hata('yetki_hata_kull_yok');
            return FALSE;
        }
        return $query->row();
    }

    public function guncelle_giris_denemesini() {
        $ip_address = $this->M->input->ip_address();
        $query = $this->yetki_db->where(
            array(
                'ip_adres'=>$ip_address,
                'timestamp >='=>date("Y-m-d H:i:s", strtotime("-".$this->config_degerleri['giris_hatirlama_suresi']))
            )
        );
        $query = $this->yetki_db->get( $this->config_degerleri['giris_denemesi'] );

        if($query->num_rows() == 0){
            $data = array();
            $data['ip_adres'] = $ip_address;
            $data['timestamp']= date("Y-m-d H:i:s");
            $data['login_attempts']= 1;
            $this->yetki_db->insert($this->config_degerleri['giris_denemesi'], $data);
            return TRUE;
        }else{
            $row = $query->row();
            $data = array();
            $data['timestamp'] = date("Y-m-d H:i:s");
            $data['login_attempts'] = $row->login_attempts + 1;
            $this->yetki_db->where('id', $row->id);
            $this->yetki_db->update($this->config_degerleri['giris_denemesi'], $data);

            if ( $data['login_attempts'] > $this->config_degerleri['giris_denemesi_max'] ) {
                return FALSE;
            } else {
                return TRUE;
            }
        }

    }
    public function al_giris_denemelerini() {
        $ip_address = $this->M->input->ip_address();
        $query = $this->yetki_db->where(
            array(
                'ip_adres'=>$ip_address,
                'timestamp >='=>date("Y-m-d H:i:s", strtotime("-".$this->config_degerleri['giris_hatirlama_suresi']))
            )
        );
        $query = $this->yetki_db->get( $this->config_degerleri['giris_denemesi'] );

        if($query->num_rows() != 0){
            $row = $query->row();
            return $row->login_attempts;
        }

        return 0;
    }
    function hash_password($sifre, $userid) {
        if($this->config_degerleri['hash_aktif']){
            return password_hash($sifre, $this->config_degerleri['hash_sifre_teknigi'], $this->config_degerleri['hash_sifre_ayari']);
        }else{
            $salt = md5($userid);
            return hash($this->config_degerleri['hash'], $salt.$sifre);
        }
    }
    function sifreyi_dogrula($sifre, $hash) {
        if($this->config_degerleri['hash_aktif']){
            return password_verify($sifre, $hash);
        }else{
            return ($sifre == $hash ? TRUE : FALSE);
        }
    }
    public function guncelle_hatirla($id, $expression=null, $expire=null) {

        $data['hatirlama_suresi'] = $expire;
        $data['hatirlama_kodu'] = $expression;

        $sorgu = $this->yetki_db->where('id',$id);
        return $this->yetki_db->update($this->config_degerleri['yetkili'], $data);
    }
    public function guncelle_son_girisi($id = FALSE) {

        if ($id == FALSE)
            $id = $this->M->session->userdata('id');

        $data['son_giris'] = date("Y-m-d H:i:s");
        $data['ip_adres'] = $this->M->input->ip_address();

        $this->yetki_db->where('id', $id);
        return $this->yetki_db->update($this->config_degerleri['yetkili'], $data);
    }
    public function guncelle_aktivite($id = FALSE) {

        if ($id == FALSE)
            $id = $this->M->session->userdata('id');

        if($id==FALSE){return FALSE;}

        $data['son_aktif'] = date("Y-m-d H:i:s");

        $sorgu = $this->yetki_db->where('id',$id);
        return $this->yetki_db->update($this->config_degerleri['yetkili'], $data);
    }
    public function giris_deneme_sifirla() {
        $ip_adres = $this->M->input->ip_address();
        $this->yetki_db->where(
            array(
                'ip_adres'=>$ip_adres,
                'timestamp >='=>date("Y-m-d H:i:s", strtotime("-".$this->config_degerleri['giris_hatirlama_suresi']))
            )
        );
        return $this->yetki_db->delete($this->config_degerleri['giris_denemesi']);
    }
    public function gir_hizli($id){
        $query = $this->yetki_db->where('id', $id);
        $query = $this->yetki_db->where('banlimi', 0);
        $query = $this->yetki_db->get($this->config_degerleri['yetkili']);
        $row = $query->row();
        if ($query->num_rows() > 0) {
            $data = array(
                'id' => $row->id,
                'username' => $row->kadi,
                'email' => $row->email,
                'loggedin' => TRUE
            );
            $this->M->session->set_userdata($data);
            return TRUE;
        }
        return FALSE;
    }
    public function al_izin_id($izin_par) {
        if( is_numeric($izin_par) ) { return $izin_par; }
        $sorgu = $this->yetki_db->where('name', $izin_par);
        $sorgu = $this->yetki_db->get($this->config_degerleri['izinler']);

        if ($sorgu->num_rows() == 0)
            return FALSE;

        $row = $sorgu->row();
        return $row->id;
    }
    public function izin_verildi($izin_par, $user_id=FALSE){
        $this->M->load->helper('url');
        if($this->M->session->userdata('gizlikod_zorunlu')){
            redirect($this->config_degerleri['gizlikod_iki_adimli_giriste_git']);
        }
        if( $user_id == FALSE){
            $user_id = $this->M->session->userdata('id');
        }
        if($this->grup_adminmi($user_id))
        {
            return true;
        }

    }
    public function izin_verildi_gruba($izin_par, $grup_par=FALSE){
        $izin_id = $this->al_izin_id($izin_par);
        if($grup_par != FALSE){
            if (strcasecmp($grup_par, $this->config_degerleri['admin']) == 0)
            {return TRUE;}
            $grup_par = $this->grup_id_al($grup_par);
            $query = $this->yetki_db->where('izin_id', $izin_id);
            $query = $this->yetki_db->where('grup_id', $grup_par);
            $query = $this->yetki_db->get( $this->config_degerleri['grup_izin'] );
            $g_allowed=FALSE;
            if( $query->num_rows() > 0){
                $g_allowed=TRUE;
            } return $g_allowed;
        } else {
            if ( $this->grup_adminmi( $this->M->session->userdata('id')) )
            {return TRUE;}
            if (!$this->giris_varmi()){return FALSE;}

            $group_pars = $this->kul_grup_al();
            foreach ($group_pars as $g ){
                if($this->izin_verildi_gruba($izin_id, $g->id)){
                    return TRUE;
                }
            }
            return FALSE;
        }
    }
    public function grup_adminmi( $user_id = FALSE ) {
        return $this->grup_uyeyisimi($this->config_degerleri['admin'], $user_id);
    }
    public function grup_uyeyisimi( $grup_par, $user_id = FALSE ) {


        if( ! $user_id){
            $user_id = $this->M->session->userdata('id');
        }

        $group_id = $this->grup_id_al($grup_par);

        $query = $this->yetki_db->where('kul_id', $user_id);
        $query = $this->yetki_db->where('grup_id', $group_id);
        $query = $this->yetki_db->get($this->config_degerleri['grup_uye']);

        $row = $query->row();

        if ($query->num_rows() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    public function grup_id_al ( $grup_par ) {

        if( is_numeric($grup_par) ) { return $grup_par; }

        $query = $this->yetki_db->where('name', $grup_par);
        $query = $this->yetki_db->get($this->config_degerleri['gruplar']);

        if ($query->num_rows() == 0)
            return FALSE;

        $row = $query->row();
        return $row->id;
    }
    public function kul_grup_al($user_id = FALSE){
        if( !$user_id) { $user_id = $this->M->session->userdata('id'); }
        if($user_id){
            $this->yetki_db->join($this->config_degerleri['gruplar'], "id = grup_id");
            $this->yetki_db->where('kul_id', $user_id);
            $query = $this->yetki_db->get($this->config_degerleri['grup_uye']);
        }
        return $query->result();
    }

    /**
     * HATALAR
     * Add message to hata array and set flash data
     * @param string $mesaj Message to add to array
     * @param boolean $flashdata if TRUE add $mesaj to M flashdata (deflault: FALSE)
     */
    public function hata($mesaj = '', $flashdata = FALSE){
        $this->hatalar[] = $mesaj;
        if($flashdata)
        {
            $this->flash_hatalar[] = $mesaj;
            $this->M->session->set_flashdata('hatalar', $this->flash_hatalar);
        }
    }
}
