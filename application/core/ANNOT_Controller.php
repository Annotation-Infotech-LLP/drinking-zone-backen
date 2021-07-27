<?php
defined('BASEPATH') or exit('No direct script access allowed');
class ANNOT_Controller extends CI_Controller
{
    function __construct()
    {
        if (!isset($_SESSION))
            session_start();
        parent::__construct();
        date_default_timezone_set('Asia/Kolkata');
        $this->db->query("SET sql_mode = '' ");
    }
    protected function uploadFile($file, $path, $types)
    {
        $config =   array(
            'upload_path'   => $path,
            'allowed_types' => $types,
            'encrypt_name'  => TRUE,
        );
        $this->load->library('upload', $config);

        if ($this->upload->do_upload($file)) {
            $name = $this->upload->data('file_name');
            unset($this->upload);
            return $path . $name;
        }
        unset($this->upload);
        return FALSE;
    }
    protected function resizeImage($file, $width, $height, $quality = 50, $ratio = FALSE, $thumb = FALSE)
    {
        $config = array(
            'source_image'  => $file,
            'image_library' => 'gd2',
            'width'         => $width,
            'height'        => $height,
            'quality'       => $quality,
            'maintain_ratio' => $ratio,
            'create_thumb'  => $thumb
        );
        $this->load->library('image_lib', $config);
        $this->image_lib->resize();
        unset($this->image_lib);
    }

    // private $api =   '12345678'; // api key for accessing mobile apis CASE SENSITIVE
    private $api =   '9vmeprm5n2ap1@w52019'; // api key for accessing mobile apis CASE SENSITIVE
    protected function apiCheck()
    {
        $file = fopen("logs/complete_called_api.log", "a+");
        fputs($file, json_encode(["Date" => date("Y-m-d H:i:s"), "API" => $_SERVER["PATH_INFO"], "IP" => $this->input->ip_address()??"self", "Data" => $_POST]) . "\n\n");
        fclose($file);
        $api = $this->input->post('api');
        if ($this->api != $api) {

            echo json_encode(array('status' => FALSE, 'msg' => 'Sorry!!! Invalid API Key....'));
            exit;
        }
    }
}
