<?php
class Cpanel
{
    protected $cpanelUsername;
    protected $cpanelPassword;
    protected $cpanelUrl;
    /**
     * Cpanel constructor.
     * @param $cpanelUsername
     * @param $cpanelPassword
     * @param $cpanelUrl
     */
    public function __construct($cpanelUsername, $cpanelPassword, $cpanelUrl)
    {
        $this->cpanelPassword = $cpanelPassword;
        $this->cpanelUsername = $cpanelUsername;
        $this->cpanelUrl = $cpanelUrl;
    }
    /**
     * @param $filePath
     * @return mixed
     */
    public function uploadFile($filePath)
    {
        $curl = curl_init();
        $upload_file = realpath($filePath);
        $destination_dir = "public_html";
        if (function_exists('curl_file_create')) {
            $cf = curl_file_create($upload_file);
        } else {
            $cf = "@/" . $upload_file;
        }
        $payload = array(
            'dir' => $destination_dir,
            'file-1' => $cf
        );
        $actionUrl = $this->cpanelUrl . "/execute/Fileman/upload_files";
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);       // Allow self-signed certs
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);       // Allow certs that do not match the hostname
        curl_setopt($curl, CURLOPT_HEADER, 0);               // Do not include header in output
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);       // Return contents of transfer on curl_exec
        $header[0] = "Authorization: Basic " . base64_encode($this->cpanelUsername . ":" . $this->cpanelPassword) . "\n\r";
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);    // set the username and password
        curl_setopt($curl, CURLOPT_URL, $actionUrl);        // execute the query
        // Set up a POST request with the payload.
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);
        if ($result == false) {
            error_log("curl_exec threw error \"" . curl_error($curl) . "\" for $actionUrl");
            // log error if curl exec fails
        }
        curl_close($curl);
        return json_decode($result, true);
    }
}