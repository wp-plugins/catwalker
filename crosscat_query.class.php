<?php

class crosscat_query{

	/*** define public properties ***/

	/*** the first category to search ***/
	public $cat1;

	/*** the second category to search ***/
	public $cat2;

	/*** the array to use in the query ***/
	public $cc_query = array();

	/*** the constructor ***/
	public function __construct( $cat1 , $cat2 , $taxon ){
//		$this->cat1 = $cat1;
//		$this->cat2 = $cat2;
		$relation = 'AND';
		if ( $cat1 == 0 || $cat2 == 0 ) {
			$relation = 'OR';
		}
		$this->cc_query = array( 
			'tax_query' => array(
				'relation' => $relation,
				array(
					'taxonomy' => $taxon,
					'terms'    => array($cat1),
					'field'    => 'term_id',
				),
				array(
					'taxonomy' => $taxon,
					'terms'    => array($cat2),
					'field'    => 'term_id',
				)
			),
		);

	}

}

?>
