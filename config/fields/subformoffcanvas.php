<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2018 Aleksey A. Morozov. All rights reserved.
 * @license     GNU General Public License version 3 or later; see http://www.gnu.org/licenses/gpl-3.0.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Filesystem\Path;
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass( 'subform' );

class JFormFieldSubformOffcanvas extends \JFormFieldSubform
{
	protected $type = 'subformoffcanvas';
	
	protected $layout = 'joomla.form.field.subform.repeatable-table';

	public function setup( SimpleXMLElement $element, $value, $group = null )
	{
		if ( !parent::setup( $element, $value, $group ) )
		{
			return false;
		}
		
		$fields = $this->getFormFields();
		$sections = $this->getSections();
		$outValues = [];
		foreach ( $sections as $key => $section )
		{
			$originRow = '';
			
			if ( $this->value )
			{
				foreach ( $this->value as $originValue )
				{
					if ( $originValue[ 'form' ][ 'posname' ] == $section )
					{
						$originRow = $originValue[ 'form' ];
					}
				}
			}
			else
			{
				$originRow = [
					'posname' => '',
					'overlay' => 1,
					'mode' => 'slide'
				];
			}
			
			foreach ( $fields as $field )
			{
				$fieldValue = '';
				
				if ( $field === 'posname' )
				{
					$fieldValue = $section;
				}
				elseif ( $originRow && $originRow[ 'posname' ] === $section )
				{
					$fieldValue = isset( $originRow[ $field ] ) ? $originRow[ $field ] : null;
				}
				
				if ( $fieldValue !== null)
				{
					$outValues[ 'offcanvas' . $key ][ 'form' ][ $field ] = $fieldValue;
				}
			}
		}
		
		$this->value = $outValues;

		return true;
	}
	
	protected function getFormFields()
	{
		$xml = simplexml_load_file( $this->formsource );
		$xmlFields = (array) $xml->fields;
		$fields = [];

		foreach ( $xmlFields[ 'fieldset' ] as $field )
		{
			$fields[] = $field->attributes()->name->__toString();
		}

		return $fields;
	}

	protected function getSections()
	{
        $sections = [];

		$filePath = realpath( Path::clean( __DIR__ . '/../../templateDetails.xml' ) );
		
        if ( is_file( $filePath ) )
		{
			$xml = simplexml_load_file( $filePath );

			if ( !$xml )
			{
				return false;
			}

			if ( $xml->getName() != 'extension' && $xml->getName() != 'metafile' )
			{
				unset($xml);
				return false;
			}
            
            foreach ($xml->positions[ 0 ]->position as $position)
            {
                if ( isset( $position[ 'offcanvas' ] ) )
                {
					$sections[] = $position->__toString();
                }
            }
		}

		return array_values( array_unique( $sections ) );
	}
}
