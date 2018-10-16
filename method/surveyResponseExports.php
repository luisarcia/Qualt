<?php

/*
Permite manejar los métodos para preparar y descargar los registros de respuestas
 */
class surveyResponseExports
{
	private $token;
	private $surveyId;
	private $format;
	private $dataCenter;
	private $startDate;
	private $endDate;
	private $dir;
	private $fileName;

	public function __construct( array $param )
	{
		$this->token 		= $param['token'];
		$this->surveyId 	= $param['surveyId'];
		$this->format 		= $param['format'];
		$this->dataCenter 	= $param['dataCenter'];
		$this->startDate 	= $param['startDate'];
		$this->endDate 		= $param['endDate'];
		$this->dir 			= $param['dir'];
		$this->fileName 	= $param['fileName'];
	}

	/**
	 * Inicia el proceso de preparación de la data y descargar
	 * @return String Nombre del archivo
	 */
	public function init()
	{
		for ($i=0; $i < count($this->surveyId); $i++) {

			$responseExportId 	= $this->createResponseExport( $this->surveyId[$i] );
			$progress 			= $this->getResponseExportProgress( $responseExportId );

			while ( $progress['percentComplete'] != 100 ) {
				sleep( 1 );
				$progress 			= $this->getResponseExportProgress( $responseExportId );
			}

			if( $progress['status'] == 'complete' ) {
				$fileUrl = $progress['file'];

				$fileDownloaded = $this->getResponseExportFile( $fileUrl );

				if( $fileDownloaded ) {
					$output = Zip::unZip( $this->dir.$this->fileName.'.zip', $this->dir, $this->fileName, $this->format );
					unlink( $this->dir.$this->fileName.'.zip' );

					echo 'Archivo: '.$output;
				}
			}
		}
	}

	/**
	 * Solicita la preparación de la data
	 * @param  String $surveyId ID de la encuesta
	 * @return String           ID de la data preparada
	 */
	private function createResponseExport( $surveyId )
	{
		$param = array(
			'surveyId' 	=> $surveyId,
			'startDate' => formatDate::ISO_8601( $this->startDate ),
			'endDate' 	=> formatDate::ISO_8601( $this->endDate ),
			'format' 	=> $this->format
		);

		$curl = curl_init();

		curl_setopt_array(
			$curl, array(
			  	CURLOPT_URL => 'https://'.$this->dataCenter.'.qualtrics.com/API/v3/responseexports',
			  	CURLOPT_RETURNTRANSFER => true,
			  	CURLOPT_MAXREDIRS => 10,
			  	CURLOPT_TIMEOUT => 60,
			  	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  	CURLOPT_CUSTOMREQUEST => 'POST',
			  	CURLOPT_POSTFIELDS => json_encode( $param ),
			  	CURLOPT_HTTPHEADER => array(
			  		'accept: */*',
					'accept-encoding: gzip, deflate',
				    'cache-control: no-cache',
				    'content-type: application/json',
				    'x-api-token: '.$this->token
				)
			)
		);

		$response = json_decode( curl_exec($curl) );
		curl_close($curl);

		$reponseExportId = $response->result->id;
		return $reponseExportId;
	}

	/**
	 * Valida el porcentaje de la solicitud
	 * @param  String $reponseExportId ID de la solicitud
	 * @return String                  Datos del progreso de la solicitud
	 */
	private function getResponseExportProgress( $reponseExportId )
	{
		$curl = curl_init();

		curl_setopt_array(
			$curl, array(
			  	CURLOPT_URL => 'https://'.$this->dataCenter.'.qualtrics.com/API/v3/responseexports/'.$reponseExportId,
			  	CURLOPT_RETURNTRANSFER => true,
			  	CURLOPT_MAXREDIRS => 10,
			  	CURLOPT_TIMEOUT => 60,
			  	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  	CURLOPT_CUSTOMREQUEST => 'GET',
			  	CURLOPT_HTTPHEADER => array(
			  		'accept: */*',
					'accept-encoding: gzip, deflate',
				    'cache-control: no-cache',
				    'x-api-token: '.$this->token
				)
			)
		);

		$response = json_decode( curl_exec($curl) );
		curl_close($curl);

		$percentComplete 	= (int) $response->result->percentComplete;
		$file 				= (string) $response->result->file;
		$status 			= (string) $response->result->status;

		$progress = array(
			'percentComplete' 	=> $percentComplete,
			'file' 				=> $file,
			'status' 			=> $status
		);

		return $progress;
	}

	/**
	 * Descarga el archivo con los registros
	 * @param  String $fileUrl URL que se generó para descargar el archivo
	 * @return bool          Si se logró descarar el archivo
	 */
	private function getResponseExportFile( $fileUrl )
	{
		$toZip = fopen( $this->dir.basename($this->fileName.'.zip' ), 'w' );

		$curl = curl_init();

		curl_setopt_array(
			$curl, array(
				CURLOPT_URL => $fileUrl,
				CURLOPT_FILE => $toZip,
				CURLOPT_CONNECTTIMEOUT => 60,
				CURLOPT_HTTPHEADER => array(
			  		'accept: */*',
					'accept-encoding: gzip, deflate',
					'cache-control: no-cache',
					'content-type: application/json',
					'x-api-token: '.$this->token
				)
			)
		);

		$res = curl_exec($curl);
		curl_close($curl);
		fclose($toZip);

		return $res;
	}
}

