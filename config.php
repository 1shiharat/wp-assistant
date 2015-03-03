<?php
/**
 * =====================================================
 * プラグインの設定をcacheで管理
 * @package   siteSupports
 * @author    Grow Group
 * @license   GPL v2 or later
 * @link      http://grow-group.jp
 * =====================================================
 */
namespace siteSupports;

class config{

	public static $prefix = 'ggs';

	/**
	 * キャッシュを取得
	 * @param $key
	 * @param $data
	 */
	public static function set( $key, $data ){
		wp_cache_set( $key, $data, static::$prefix . '_options' );
	}

	/**
	 * キャッシュから値を取得
	 * @return string
	 */
	public static function get( $key ){
		return wp_cache_get( $key, static::$prefix . '_options' );
	}

	/**
	 * キャッシュから値を削除
	 * @return string
	 */
	public static function delete( $key ){
		return wp_cache_delete( $key, static::$prefix . '_options' );
	}

	/**
	 * オプションを取得
	 */
	public static function get_option( $option_key = '' ){
		$options = static::get( 'options' );
		if ( $option_key ){
			if ( isset( $options[$option_key] )  ){
				return $options[$option_key];
			}
		} else {
			return $options;
		}
		return false;
	}
}