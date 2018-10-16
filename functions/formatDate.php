<?php

class formatDate
{
	/**
	 * Formatea la fecha a ISO 8601
	 * @param string $date Fecha formateada
	 */
	static public function ISO_8601( string $date )
	{
		return date('Y-m-d\TH:i:sP', strtotime( $date ));
	}	
}