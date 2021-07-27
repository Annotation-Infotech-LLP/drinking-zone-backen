<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class ANNOT_Model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Kolkata');
	    $this->db->query("SET sql_mode = '' ");
    }
    protected function sendOTP($mobile,$name,$flag=FALSE)
    {
        $otp=rand(100000,999999);
        if($flag)
            $message=urlencode("Hello $name,\nYour Water Supply Password Reset OTP is : ".$otp." \nThank You.");
        else
            $message=urlencode("Hello $name,\nYour Water Supply Registraion OTP is : ".$otp." \nThank You.");
        $this->load->helper("sms");
        if(sendSMS($mobile,$message))
            return $otp;
        else
            return FALSE;
    }
    protected function sendSMS($mobile,$message)
    {
        $this->load->helper("sms");
        sendSMS($mobile,urlencode($message));
    }
    protected function uploadFile($file,$path,$types)
    {
        $config =   array(
            'upload_path'   => $path,
            'allowed_types' => $types,
            'encrypt_name'  => TRUE,
        );
        $this->load->library('upload',$config);
        
        if($this->upload->do_upload($file))
        {
            $name=$this->upload->data('file_name');
            unset($this->upload);
            return $path.$name;
        }
        unset($this->upload);
        return FALSE;
    }
    protected function resizeImage( $file, $width, $height, $quality = 50, $ratio = FALSE, $thumb = FALSE)
    {
        $config = array(
            'source_image'  => $file,
            'image_library' => 'gd2',
            'width'         => $width,
            'height'        => $height,
            'quality'       => $quality,
            'maintain_ratio'=> $ratio,
            'create_thumb'  => $thumb
        );
        $this->load->library('image_lib',$config);
        $this->image_lib->resize();
        unset($this->image_lib);
    }
}