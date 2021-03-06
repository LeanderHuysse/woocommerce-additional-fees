<?php
/**
 * Static function for extending WooCommerce Core
 *
 * @author Schoenmann Guenter
 * @version 1.0.0.0
 */
if ( ! defined( 'ABSPATH' ) )  {  exit;  }   // Exit if accessed directly

class WC_Addons_Add_Fees
{
	const TAG_OPEN = 'woocommerce_addons_tag_open_addfee';
	const TAG_CLOSE = 'woocommerce_addons_tag_close_addfee';
	const TAG_STANDALONE = 'woocommerce_addons_tag_standalone_addfee';
	const TAG_COMPLETE = 'woocommerce_addons_tag_complete_addfee';
	const TEXT = 'woocommerce_addons_text_addfee';
	const HIDDEN_INPUT = 'woocommerce_addons_hidden_input_addfee';

	const ERR_MSG_ERROR = 'error';
	const ERR_MSG_INFO = 'info';

	/**
	 * Error messages:
	 * 'field id' => array ( 'message' => ' ... ',
	 *					    'status' =>   type of error (enumeration ERR_MSG_ERROR, ERR_MSG_INFO
	 *				)
	 * @var array
	 */
	protected $errors;

	/**
	 * Saves the ID of the active tab from a hidden field to be able to open it after save
	 * 
	 * @var string
	 */
	public $last_tab_active;
	
	
	/**
	 * Pointer to this object
	 *
	 * @var woocommerce_addons
	 */
	static public $object;

	public function __construct()
	{
		$this->errors = array();
		$this->last_tab_active = '';
	}

	public function __destruct()
	{
		unset( $this->errors );
	}

	/**
	 * attach to WooCommerce hooks
	 */
	public function attach_fields()
	{
		if( ! isset( self::$object ) )
		{
			self::$object = $this;
		}

				// Attach hooks to special form elements
		add_action( 'woocommerce_admin_field_' . self::TAG_OPEN, array( $this, 'form_tag_open' ), 10 );
		add_action( 'woocommerce_admin_field_' . self::TAG_CLOSE, array( $this, 'form_tag_close' ), 10 );
		add_action( 'woocommerce_admin_field_' . self::TAG_STANDALONE, array( $this, 'form_tag_standalone' ), 10 );
		add_action( 'woocommerce_admin_field_' . self::TAG_COMPLETE, array( $this, 'form_tag_complete' ), 10 );
		add_action( 'woocommerce_admin_field_' . self::TEXT, array( $this, 'form_text' ), 10 );
		add_action( 'woocommerce_admin_field_' . self::HIDDEN_INPUT, array( $this, 'form_hidden_input_field' ), 10, 1 );
	}

	/**
	 * Echos a form element
	 *
	 * @param array $element
	 */
	public function echo_html_string( array $element )
    {
		if( ! isset( $element['type'] ) ) 
		{
			return;
		}

		do_action( 'woocommerce_admin_field_' . $element['type'], $element );
		return;
	}

    /**
	 * Outputs a starting element tag
	 *
	 * @param array $element
	 */
	public function form_tag_open( array $element )
	{
		$e = $this->init_element( $element );
		if( empty( $e['tag'] ) )
			return;

		echo '<' . $e['tag'];
		echo $this->get_attribute_string( 'id', $e['id'] );
		echo $this->get_attribute_string( 'class', $e['class'] );
		echo $this->get_attribute_string( 'href', $e['href'] );
		echo $this->get_attribute_string( 'attributes', $e['attributes'], true );
		echo '>';
	}

	/**
	 * Outputs a ending element tag
	 *
	 * @param array $element
	 */
	public function form_tag_close( array $element )
	{
		$e = $this->init_element( $element );
		if( empty( $e['tag'] ) )
		{
			return;
		}

		echo '</' . $e['tag']. '>';
	}

	/**
	 * Outputs a standalone element tag
	 * e.g. <br />
	 *
	 * @param array $element
	 */
	public function form_tag_standalone( array $element )
	{
		$e = $this->init_element( $element );
		if( empty( $e['tag'] ) )
		{
			return;
		}

		echo '<' . $e['tag'];
		echo $this->get_attribute_string( 'id', $e['id'] );
		echo $this->get_attribute_string( 'class', $e['class'] );
		echo $this->get_attribute_string( 'href', $e['href'] );
		echo $this->get_attribute_string( 'attributes', $e['attributes'], true );
		echo ' />';
	}

	/**
	 * Outputs a standalone element tag
	 *
	 * @param array $element
	 */
	public function form_text( array $element )
	{
		$e = $this->init_element( $element );
		if(strlen( $e['innerhtml'] ) == 0 )
		{
			return;
		}

		echo esc_html( $e['innerhtml'] );
	}

	/**
	 * Outputs a ending element tag
	 *
	 * @param array $element
	 */
	public function form_tag_complete( array $element )
	{
		$e = $this->init_element( $element );
		if( empty( $e['tag'] ) )
		{
			return;
		}

		echo '<' . $element['tag'];
		echo $this->get_attribute_string( 'id', $e['id'] );
		echo $this->get_attribute_string( 'class', $e['class'] );
		echo $this->get_attribute_string( 'href', $e['href'] );
		echo $this->get_attribute_string( 'attributes', $e['attributes'], true );
		echo '>';
		echo esc_html( $e['innerhtml'] );
		echo '</' . $e['tag']. '>';
	}
	
	/**
	 * Outputs a hidden field
	 * 
	 * @param array $element
	 */
	public function form_hidden_input_field( array $element )
	{
		$e = $this->init_element( $element );
		
		echo '<input type="hidden" ';
		echo $this->get_attribute_string( 'name', $e['id'] );
		echo $this->get_attribute_string( 'value', $e['default'] );
		echo '>';
	}

	/**
	 * Initialises all default values
	 *
	 * @param array $element
	 * @return array
	 */
	protected function &init_element( array $element )
	{
		$default = array(
			'type' => '',
			'tag' => '',
			'id' => '',
			'class' => '',
			'href' => '',
			'default'	=> '',
			'innerhtml' => '',
			'attributes' => array()
			);

		$new = wp_parse_args( $element, $default );
		return $new;
	}

	/**
	 * Returns a string ' key="value"'
	 *
	 * @param string $key
	 * @param string|array $value
	 * @param bool $value_is_key_array		true, if $value is 'key' => 'value' pair, otherwise 'value' is a concatinated string
	 * &return string
	 */
	protected function &get_attribute_string( $key, $value, $value_is_key_array = false )
	{
		$ret = '';
		$k = trim( (string) $key );
		if( strlen( $k ) == 0 )
		{
			return $ret;
		}

			//	return ' key="value"'
		if( ! is_array( $value ) )
		{
			try
			{
				$v = trim( (string) $value );

				if( ( strlen( $v ) == 0 ) )
				{
					return $ret;
				}
				$ret = ' ' . $k . '="' . $v . '"';
			}
			catch( Exception $e )
			{
			}
			return $ret;
		}
			//	concatinate values to string
		if( ! $value_is_key_array )
		{
			$v = implode( ' ', $value );
			if( ( strlen( $v ) == 0) )
			{
				return $ret;
			}
			$ret = ' ' . $k . '="' . $v . '"';
			return $ret;
		}

		foreach ( $value as $k => &$v )
		{
			$r = $this->get_attribute_string( $k, $v );
			$ret .= $r;
		}
		unset ( $v );
		return $ret;
	}

	/**
	 * Summarizes the error messages for input fields in an array
	 *
	 * @param string $id
	 * @param string $message
	 * @param string $status
	 */
	public function add_field_error_message( $id, $message, $status = self::ERR_MSG_ERROR )
	{
		switch ( $status )
		{
			case self::ERR_MSG_ERROR:
			case self::ERR_MSG_INFO:
				break;
			default:
				self::ERR_MSG_ERROR;
				break;
		}

		$this->errors[ $id ] = array(
					'message' => $message,
					'status' => $status
				);
	}

	/**
	 * Returns the error entry for a given field id
	 *
	 * @return array|null
	 */
	public function get_error_message( $id )
	{
		if(isset( $this->errors [ $id ] ) )
		{
			return $this->errors [ $id ];
		}

		return null;
	}

	/**
	 * Number of Error messages stored
	 *
	 * @return int
	 */
	public function count_errors()
	{
		return count( $this->errors );
	}

}
