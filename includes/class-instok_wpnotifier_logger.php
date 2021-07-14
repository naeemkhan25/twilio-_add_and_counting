<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'InStock_WPNotifier_Logger' ) ) {

	class InStock_WPNotifier_Logger {

		public function __construct( $status = '', $message = '' ) {
			$this->status  = $status;
			$this->message = $message;
		}

		private function InStock_WPNotifier_context_name() {
			$context_name = array( 'source' => INSTOCKWPNOTIFIER_DIRNAME );

			return $context_name;
		}

		public function InStock_WPNotifier_format_message() {
			$replace = str_replace( '#', '', $this->message );
			$arr     = explode( " ", $replace );
			foreach ( $arr as $key => $val ) {
				if ( preg_match( "/^[a-z0-9_\+-]+(\.[a-z0-9_\+-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*\.([a-z]{2,4})$/", $val ) ) {
					$arr_phone  = explode( "@", $val );
					$first_data = $arr_phone[0];
					if ( strlen( $first_data ) > 1 ) {
						$first_character  = $first_data[0];
						$last_character   = substr( $first_data, - 1, '1' );
						$string_length    = strlen( $first_data );
						$hidden_character = substr( $first_data, 1, $string_length - 2 );
						$hidden           = "";
						if ( strlen( $hidden_character ) > 0 ) {
							for ( $i = 1; $i <= strlen( $hidden_character ); $i ++ ) {
								$hidden .= "x";
							}
						}
						$arr_phone[0] = $first_character . $hidden . $last_character;
					} else {
						$arr_phone[0] = 'xxxxx';
					}
					$val_new     = implode( "@", $arr_phone );
					$arr[ $key ] = $val_new;
				}
			}
			$new_msg = implode( " ", $arr );

			return $new_msg;
		}

		public function InStock_WPNotifier_message() {
			return $this->InStock_WPNotifier_format_message();
		}

		public function InStock_WPNotifier_logger() {
			if ( function_exists( 'wc_get_logger' ) ) {
				return wc_get_logger();
			} else {
				return new WC_Logger();
			}
		}

		public function InStock_WPNotifier_record_log() {
			$logger = $this->InStock_WPNotifier_logger();
			$status = $this->status;
			if ( ! function_exists( 'wc_get_logger' ) ) {
				$this->status = '';
			}
			switch ( $this->status ) {
				case 'debug':
					$logger->debug( $this->InStock_WPNotifier_message(), $this->InStock_WPNotifier_context_name() );
					break;
				case 'info':
					$logger->info( $this->InStock_WPNotifier_message(), $this->InStock_WPNotifier_context_name() );
					break;
				case 'notice':
					$logger->notice( $this->InStock_WPNotifier_message(), $this->InStock_WPNotifier_context_name() );
					break;
				case 'warning':
					$logger->warning( $this->InStock_WPNotifier_message(), $this->InStock_WPNotifier_context_name() );
					break;
				case 'error':
					$logger->error( $this->InStock_WPNotifier_message(), $this->InStock_WPNotifier_context_name() );
					break;
				case 'critical':
					$logger->critical( $this->InStock_WPNotifier_message(), $this->InStock_WPNotifier_context_name() );
					break;
				case 'success':
					$logger->log( 'info', $this->InStock_WPNotifier_message(), $this->InStock_WPNotifier_context_name() );
					break;
				case 'alert':
					$logger->alert( $this->InStock_WPNotifier_message(), $this->InStock_WPNotifier_context_name() );
					break;
				case 'emergency':
					$logger->emergency( $this->InStock_WPNotifier_message(), $this->InStock_WPNotifier_context_name() );
					break;
				default:
					if ( function_exists( 'wc_get_logger' ) ) {
						$logger->log( $this->status, $this->InStock_WPNotifier_message(), $this->InStock_WPNotifier_context_name() );
					} else {
						$logger->add( "Instock-WPNotifier", $this->InStock_WPNotifier_message() . " " . $status );
					}
					break;
			}
		}

	}

}
