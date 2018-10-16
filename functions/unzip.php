<?php
class Zip
{
	/**
	 * Descomprime archivo
	 * @param  string $file     Nombre del archivo
	 * @param  string $dir      Directorio del archico
	 * @param  string $fileName Nombre final del archivo
	 * @param  string $format   Formato del archivo
	 * @return Return           Nombre del archivo y directorio final
	 */
	public static function unZip( string $file, string $dir, string $fileName, string $format )
	{
		$zip = new ZipArchive;

		if( $zip->open( $file ) === true ) {
			for ($i=0; $i < $zip->numFiles; $i++) { 
				$fileNameZip = $zip->getNameIndex( $i );
			}

			$zip->extractTo( $dir );
			$zip->close();
		}

		return self::setNameFile( $dir, $fileNameZip, $fileName, $format );
	}

	/**
	 * Cambia el nombre del archivo
	 * @param string $dir         Directorio del archivo
	 * @param string $fileNameZip Nombre del archivo que resultó al descomprimir
	 * @param string $fileName    Nuevo nombre del archivo
	 * @param string $format      Formato del nuevo archivo
	 */
	private static function setNameFile( string $dir, string $fileNameZip, string $fileName, string $format )
	{
		$file = $dir.$fileName.'-'.time().'.'.$format;
		
		if( rename( $dir.$fileNameZip, $file ) ) {
			return  $file;
		}
	}
}