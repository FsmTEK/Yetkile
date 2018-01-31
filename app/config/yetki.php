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
|
*/
$ayar_yetki=array();
$ayar_yetki["default"]=array(
 'yetki_yoksa'                    => FALSE,

 'admin'                          => 'sa',
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