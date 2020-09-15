<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PDF;

use App\Models\User;

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
        //
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


}
