<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use Log;
use PDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use AESGCM\AESGCM;
use Illuminate\Support\Facades\Queue;
use App\Jobs\ExampleJob;

use App\Models\User;
use App\Models\FulfillmentCenterType;
use App\Models\PasswordReset;
use Mail;
use Illuminate\Support\Str;

class IndexController extends Controller
{
   	
/* 
error status code
	200	success
	404	Not Found (page or other resource doesn’t exist)
	401	Not authorized (not logged in)
	403	Logged in but access to requested area is forbidden
	400	Bad request (something wrong with URL or parameters)
	422	Unprocessable Entity (validation failed)
	500	General server error
*/
    public function __construct()
    {
      $this->frontUrl = "http://localhost:8080/reset-password/";
    }

    public function index()
    {
		return response()
    ->json(['status'=>200 ,'datas' => ['message' => 'API Order Management System'], 'errors' => []])
    ->withHeaders([
      'Content-Type'          => 'application/json',
      ])
		->setStatusCode(200);
    }
	
	
	public function test_queue() {
		for($i=0; $i<=10; $i++){
			Queue::push(new ExampleJob(array('queue' => $i)));
			echo "successfully push";
			echo '<br>';
			echo '<br>';
		}
	}
	
	
    public function pdf()
    {

      /**
       * for save pdf file to server
       */
      // $html = "<h1>Test</h1>";
      // return PDF::loadHTML($html)->setPaper('a4', 'landscape')->setWarnings(false)->save('public/pdf/coba.pdf');
      

      /**
       * for download pdf file
       */
      // $data = ["satu" => 1];
      // return PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('pdf.coba', $data)->download('invoice.pdf');


      /**
       * for view pdf file
       */
      // $data = ["satu" => 1];
      // return PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('pdf.coba', $data)->stream();

    }

    
    public function pdfencode()
    {
      $name         = "coba.pdf";
      $avatar_path  = storage_path('pdf') . '/' . $name;
      $file         = file_get_contents($avatar_path);
      $extension    = pathinfo($avatar_path, PATHINFO_EXTENSION);
      $mimetype     = $this->getFileMimeType($extension);
      $pdf_b64      = chunk_split(base64_encode($file));
      // $pdf_b64      = 'as';

      return response()
        ->json([
            'status'=>200 ,
            'datas' => [
              'document'      => $pdf_b64,
              "mime_type"     => $mimetype,
              "document_type" => "shippingLabel"
            ], 
            'errors' => []
          ])
        ->withHeaders([
          'Content-Type'          => 'application/json',
          ])
        ->setStatusCode(200);

    }

    

    
    public function pdfdencode()
    {
      $array = ["document" => array(
        "file" => "JVBERi0xLjcKMSAwIG9iago8PCAvVHlwZSAvQ2F0YWxvZwovT3V0bGluZXMgMiAwIFIKL1BhZ2Vz IDMgMCBSID4+CmVuZG9iagoyIDAgb2JqCjw8IC9UeXBlIC9PdXRsaW5lcyAvQ291bnQgMCA+Pgpl bmRvYmoKMyAwIG9iago8PCAvVHlwZSAvUGFnZXMKL0tpZHMgWzYgMCBSCl0KL0NvdW50IDEKL1Jl c291cmNlcyA8PAovUHJvY1NldCA0IDAgUgovRm9udCA8PCAKL0YxIDggMCBSCj4+Cj4+Ci9NZWRp YUJveCBbMC4wMDAgMC4wMDAgODQxLjg5MCA1OTUuMjgwXQogPj4KZW5kb2JqCjQgMCBvYmoKWy9Q REYgL1RleHQgXQplbmRvYmoKNSAwIG9iago8PAovUHJvZHVjZXIgKP7/AGQAbwBtAHAAZABmACAA MAAuADgALgA2AAoAIAArACAAQwBQAEQARikKL0NyZWF0aW9uRGF0ZSAoRDoyMDIwMDkxNTE5MDU1 NSswNycwMCcpCi9Nb2REYXRlIChEOjIwMjAwOTE1MTkwNTU1KzA3JzAwJykKPj4KZW5kb2JqCjYg MCBvYmoKPDwgL1R5cGUgL1BhZ2UKL01lZGlhQm94IFswLjAwMCAwLjAwMCA4NDEuODkwIDU5NS4y ODBdCi9QYXJlbnQgMyAwIFIKL0NvbnRlbnRzIDcgMCBSCj4+CmVuZG9iago3IDAgb2JqCjw8IC9G aWx0ZXIgL0ZsYXRlRGVjb2RlCi9MZW5ndGggNjYgPj4Kc3RyZWFtCnic4zLQMzAwUEAmi9K5nEIU jE30DAzNFEyNjPSMzU0VQlIU9N0MFYyAogohaQoK0RohqcUlmrEKIV4KriEA7RAPPAplbmRzdHJl YW0KZW5kb2JqCjggMCBvYmoKPDwgL1R5cGUgL0ZvbnQKL1N1YnR5cGUgL1R5cGUxCi9OYW1lIC9G MQovQmFzZUZvbnQgL1RpbWVzLUJvbGQKL0VuY29kaW5nIC9XaW5BbnNpRW5jb2RpbmcKPj4KZW5k b2JqCnhyZWYKMCA5CjAwMDAwMDAwMDAgNjU1MzUgZiAKMDAwMDAwMDAwOSAwMDAwMCBuIAowMDAw MDAwMDc0IDAwMDAwIG4gCjAwMDAwMDAxMjAgMDAwMDAgbiAKMDAwMDAwMDI3NCAwMDAwMCBuIAow MDAwMDAwMzAzIDAwMDAwIG4gCjAwMDAwMDA0NTQgMDAwMDAgbiAKMDAwMDAwMDU1NyAwMDAwMCBu IAowMDAwMDAwNjk0IDAwMDAwIG4gCnRyYWlsZXIKPDwKL1NpemUgOQovUm9vdCAxIDAgUgovSW5m byA1IDAgUgovSURbPGYwNGQ1NGMxN2RhODdiMmY5ODk3MzY1MGQ3NDMzZTEwPjxmMDRkNTRjMTdk YTg3YjJmOTg5NzM2NTBkNzQzM2UxMD5dCj4+CnN0YXJ0eHJlZgo4MDIKJSVFT0YK " , 
        "mime_type" =>"application/pdf", 
        "document_type" => ""
        )];
        $encode = $array["document"]["file"];
        $decode = base64_decode($encode, true);
        return response($decode, 200)->withHeaders(['Content-Type' => $array["document"]["mime_type"], 'Content-Disposition' => 'attachment', 'filename' => 'aa.pdf']);
    }


    
    public function pdfDownload()
    {
      $name         = "coba.pdf";
      $avatar_path  = storage_path('pdf') . '/' . $name;
      // $getContents= file_get_contents($avatar_path);
      // $extension  = pathinfo($getContents, PATHINFO_EXTENSION);
       
      // $data     = ["document" => array("file" => $imgData , "mime_type" => $mimetype, "document_type" => "")];
      // return response()
      // ->json(['status'=>200 ,'datas' => $extension, 'errors' => []])
      // ->setStatusCode(200);

      $type = 'application/pdf';
      $headers = ['Content-Type' => $type];
      
      if (file_exists($avatar_path)) {
        $file = file_get_contents($avatar_path);
        // for download file;
        return response($file, 200)->withHeaders(['Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment', 'filename' => $name]);

        // for view file
        // return response($file, 200)->header('Content-Type', 'application/pdf')->header('filename', $name);
        
      }else{
        echo $avatar_path;
        // echo storage_path('pdf');
      }
    }


    private function getFileMimeType($extension) {
      $mimet = array( 
          'csv' => 'text/csv',
          'txt' => 'text/plain',
          'htm' => 'text/html',
          'html' => 'text/html',
          'php' => 'text/html',
          'css' => 'text/css',
          'js' => 'application/javascript',
          'json' => 'application/json',
          'xml' => 'application/xml',
          'swf' => 'application/x-shockwave-flash',
          'flv' => 'video/x-flv',
  
          // images
          'png' => 'image/png',
          'jpe' => 'image/jpeg',
          'jpeg' => 'image/jpeg',
          'jpg' => 'image/jpeg',
          'gif' => 'image/gif',
          'bmp' => 'image/bmp',
          'ico' => 'image/vnd.microsoft.icon',
          'tiff' => 'image/tiff',
          'tif' => 'image/tiff',
          'svg' => 'image/svg+xml',
          'svgz' => 'image/svg+xml',
  
          // archives
          'zip' => 'application/zip',
          'rar' => 'application/x-rar-compressed',
          'exe' => 'application/x-msdownload',
          'msi' => 'application/x-msdownload',
          'cab' => 'application/vnd.ms-cab-compressed',
  
          // audio/video
          'mp3' => 'audio/mpeg',
          'qt' => 'video/quicktime',
          'mov' => 'video/quicktime',
  
          // adobe
          'pdf' => 'application/pdf',
          'psd' => 'image/vnd.adobe.photoshop',
          'ai' => 'application/postscript',
          'eps' => 'application/postscript',
          'ps' => 'application/postscript',
  
          // ms office
          'doc' => 'application/msword',
          'rtf' => 'application/rtf',
          'xls' => 'application/vnd.ms-excel',
          'ppt' => 'application/vnd.ms-powerpoint',
          'docx' => 'application/msword',
          'xlsx' => 'application/vnd.ms-excel',
          'pptx' => 'application/vnd.ms-powerpoint',
  
  
          // open office
          'odt' => 'application/vnd.oasis.opendocument.text',
          'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
      );
  
      if (isset( $mimet[$extension] )) {
       return $mimet[$extension];
      } else {
       return 'application/octet-stream';
      }
   }



   
   public function downloadExcel()
   {
    $name         = "hello world.xlsx";
    $avatar_path  = storage_path('xlsx') . '/' . $name;
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Hello World !');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save($avatar_path);
    $type = 'application/vnd.ms-excel';
    $headers = ['Content-Type' => $type];
    
    if (file_exists($avatar_path)) {
      $file = file_get_contents($avatar_path);
      // for download file;
      // return response()->download($file, $name, $headers);
      return response($file, 200)->withHeaders(['Content-Type' => $type, 'Content-Disposition' => 'attachment;filename="'.$name.'"']);
    }else{
      echo $avatar_path;
    }
   }


   
   public function testAesGcm()
   {
		// The Key Encryption Key
		$K = hex2bin('079423bc31698dbaf2d6f49973301b97');

		// The data to encrypt (can be null for authentication)
		$P = null;

		// Additional Authenticated Data
		$A = '+oY3RKkVzstJjpaBz523z9960f/KKNfV1hrYW9bUVJ0AMwwQQfa0Zi09VH3WGac8qPpZkRodMxBIMl7suYD8ck95JI4K1mnO84nacf9sh0y8577N0zYWfhaIaapiFi/ZbX3J0aJr3gMjSk+/fIjXw99kTjZEEyDtvqyvc8K8XDaa4uQbAz10Vpf61+6I7cPKfXmdWUD53ZLweoLQZfLhnH1XK3bheX6SKRR33N/NzEwE/qbaANs2f6OL7HhjyiR58BEjg+At9c26pD5n170JoadgKVjsLZk0L2+2zMJKHuxnjtm8KxqimObZB2riIXmobA2tZmQQBx2CQGwDeqeUkxbO+uxxfb4sWKeViU90QpfDNqCTELiTkS0KnVpadzSt908rdx7w3lFuIDbqZXwClutORdASz2s1t856aLnT89UT1Tm25MWcX0kAGIaw8xYwOdoUEyk0g+4d0ZVcGUDFlU2L/8mQ9jG4YTAm/9dQH57o1g==';

		// Initialization Vector
		$IV = hex2bin('z7bBO5GmaW12EBdU');

		$P = AESGCM::decrypt($K, $IV, $P, $A, $T="256");
		echo $P;
   }
   
   public function testLog()
   {
		// 	$send = ["link" => '12121212'];
    // $content = view('emails.password')->with($send);
    // Mail::send('layouts.email', ['contentMessage' => $content], function($message) {
    //     $message->to('nugrohoaditya10@gmail.com')->subject('Demo Account Out Of Stock');
    //     $message->from('info@rajawalikapital.co.id','Website Admin');
    //  });
      // echo $this->frontUrl;
     
   }

}
