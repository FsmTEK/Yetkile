<?php
defined('BASEPATH') OR exit('Direk Erişim Yasak...');

/*
| -------------------------------------------------------------------
| Yetki Ayarları
| -------------------------------------------------------------------
| Bu Kütüphane Codeigniter 3 e yetkilendirme yapması için yazılmıştır.
|
| -------------------------------------------------------------------
| Kod ve Açıklamaları
| -------------------------------------------------------------------
|
|   ['no_permission']                   Kullanıcı sayfayı görme iznine sahip değilse, belirtilen sayfayı yeniden yönlendirir.
|
|   ['admin_group']                     Yönetici grubunun adı
|   ['default_group']                   Varsayılan grubun adı, yeni kullanıcı buraya eklenir
|   ['public_group']                    Herkese açık bir grubun adı, giriş yapmamış olan insanlar
|
|   ['db_profile']                      Yapılandırma veritabanı profili (config/database.php)
|
|   ['users']                           Kullanıcıları içeren tablo
|   ['groups']                          Grupları içeren tablo
|   ['user_to_group']                   Kullanıcıların ve gruplara katıldığı tablo
|   ['perms']                           İzinleri içeren tablo
|   ['perm_to_group']                   Gruplar için izinleri içeren tablo
|   ['perm_to_user']                    Kullanıcılar için izinleri içeren tablo
|   ['pms']                             Özel mesaj içeren tablo
|   ['user_variables']                  Kullanıcı değişkenlerini içeren tablo
|   ['login_attempts']                  Giriş denemeleri içeren tablo
|
|   ['remember']                        Bağlandıktan sonra geçen zamanı (göreceli formatta) ve çerezlerle kullanmak için otomatik çıkış'u hatırla
|                                       Örnek Format (e.g. '+ 1 week', '+ 1 month', '+ first day of next month')
|                                       daha geniş bilgi http://php.net/manual/de/datetime.formats.relative.php
|
|   ['max']                             Maximum Şifre Karakteri
|   ['min']                             Minimum Şifre Karakteri
|
|   ['additional_valid_chars']          Kullanıcı adı için geçerli olan ilave karakterler. Varsayılan olarak izin verilen alfasayısal olmayan karakterler
|
|   ['ddos_protection']                 DDoS Korumasını etkinleştirir, kullanıcının girişini aştığında geçici olarak yasaklanır
|
|   ['recaptcha_active']                reCAPTCHA açar (geniş bilgi www.google.com/recaptcha/admin)
|   ['recaptcha_login_attempts']        Giriş ReCAPTCHA'yı görüntüleme
|   ['recaptcha_siteKey']               reCAPTCHA siteKey
|   ['recaptcha_secret']                reCAPTCHA secretKey
|
|   ['totp_active']                     Zamana Dayalı Bir Zamanlık Şifre Algoritmasını etkinleştirir
|   ['totp_only_on_ip_change']          TOTP yalnızca IP Değişimi'nde
|   ['totp_reset_over_reset_password']  TOTP sıfırlama sıfırlama Şifresi
|   ['totp_two_step_login']             TOTP'ye iki adımlı giriş yapmayı etkinleştirir
|   ['totp_two_step_login_redirect']    Tarafından kullanılan TOTP Doğrulama sayfasına yönlendirme yolu control() & is_allowed()
|
|   ['max_login_attempt']               Giriş girişimleri zaman aralığı(varsayılan Bir saatte 10 defa)
|   ['max_login_attempt_time_period']   Maksimum giriş girişimi için geçen süre (varsayılan "5 dakika")
|   ['remove_successful_attempts']      Başarılı giriş yaptıktan sonra giriş girişimi kaldırmayı etkinleştirir
|
|   ['login_with_name']                 Kullanıcı Adıyla Gir. False ise E Postal adresi ile girer.
|
|   ['email']                           Mail İçin Gönderen E Posta Adresi
|   ['name']                            Mail İçin Gönderen Adı
|   ['email_config']                    E-posta Kitaplığı için Yapılandırma Dizisi
|
|   ['verification']                    TRUE  e-postası ile Kullanıcı Doğrulaması.
|   ['verification_link']               Site_url veya base_url olmadan doğrulama bağlantısı
|   ['reset_password_link']             Site_url veya base_url içermeyen reset_password için bağlantı
|
|   ['hash']                            Seçilen karma algoritma adı(örnek "md5", "sha256", "haval160,4", vb..) Tümü İçin hash_algos()
|   ['use_password_hash']               PHP'nin kendi password_hash () işlevini BCrypt ile kullanabilmenizi sağlar, PHP5.5 veya üstü gerekir.
|   ['password_hash_algo']              password_hash algoritması (PASSWORD_DEFAULT, PASSWORD_BCRYPT)Geniş Bilgi http://php.net/manual/de/password.constants.php
|   ['password_hash_options']           password_hash dizi seçenekleri Geniş Bilgi http://php.net/manual/en/function.password-hash.php
|
|   ['pm_encryption']                   PM Şifrelemesini etkinleştirir, yapılandırılmış CI Şifreleme Sınıfı gerekir. Daha Geniş Bilgi: http://www.codeigniter.com/userguide2/libraries/encryption.html
|   ['pm_cleanup_max_age']              PM Temizleme azami yaş (göreceli biçimde), PM'ler azami yaşın üzerindeyken silindi 'cleanup_pms()'
|                                       Örnek Format (e.g. '2 week', '1 month') Daha Geniş Bilgi http://php.net/manual/de/datetime.formats.relative.php
|
*/
$ayar_yetki=array();                                            //$config_yetki = array();
$ayar_yetki["default"]=array(                                   //$config_yetki["default"] = array(
 'yetki_yoksa'                    => FALSE,                     //'yetki_yoksa'                  => FALSE,

 'admin'                          => 'sa',                      //'admin_group'                    => 'sa',
 'default_group'                  => 'musteri',
 'public_group'                   => 'musteri',

 'ana_db'                         => 'default',
 'db_radius'                      => 'radius',
 'db_yedek'                       => 'issyedek',

 'yetkili'                        => 'yonetim',
 'abone'                          => 'aboneler',
 'gruplar'                        => 'gruplar',
 'group_to_group'                 => 'group_to_group',
 'grup_uye'                       => 'grup_uye',
 'izinler'                        => 'izinler',
 'grup_izin'                      => 'grup_izin',
 'perm_to_user'                   => 'perm_to_user',
 'pms'                            => 'pms',
 'user_variables'                 => 'user_variables',
 'giris_denemesi'                 => 'giris_denemeleri',

 'beni_hatirla'                   => '+3 days',

 'buyuk'                          => 13,
 'kuccuk'                         => 2,

 'kabul_edilen_karakter'          => array(),

 'ddos_koruma'                    => true,

 'recaptcha_aktif'                => false,
 'recaptcha_giris_deneme'         => 4,
 'recaptcha_siteKey'              => '',
 'recaptcha_secret'               => '',

 'gizlikod_aktif'                 => false,
 'gizlikod_sadece_ip_degisince'   => false,
 'gizlikod_sifre_yenile'          => false,
 'gizlikod_iki_adimli_giris'      => false,
 'gizlikod_iki_adimli_giriste_git'=> '/account/twofactor_verification/',

 'giris_denemesi_max'             => 10,
 'giris_hatirlama_suresi'         => "5 minutes",
 'basarili_giriste_sil'           => true,

 'kullanici_adi_ile_gir'          => true,

 'email'                          => 'admin@admin.com',
 'name'                           => 'Mehmet ÖZDEMİR',
 'email_ayar'                     => false,

 'verification'                   => false,
 'dogrulama_linki'                => '/account/verification/',
 'sifre_sifirlama_linki'          => '/account/reset_password/',

 'hash'                           => 'sha256',
 'hash_aktif'                     => false,
 'hash_sifre_teknigi'             => PASSWORD_DEFAULT,
 'hash_sifre_ayari'               => array(),

 'pm_encryption'                  => false,
 'pm_cleanup_max_age'             => "3 months",
);

$config['yetki'] = $ayar_yetki['default'];

/* Yetkilendirme Bittiii... */
/* Dosya Yeri: ./application/config/yetki.php */