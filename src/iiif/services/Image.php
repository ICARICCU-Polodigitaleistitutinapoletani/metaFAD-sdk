<?php
class iiif_services_Image extends PinaxObject
{
	public function serve($uid,$region,$size,$rotation,$quality,$format)
	{
        $url = iiif_services_Common::getImageWithSize($uid, $size, true, $region);

        header("Content-Type: image/jpeg");
        try{
            readfile($url);
        }
        catch(Exception $e)
        {
            dd($e);
        }

        exit;
    }

    protected function retrieve_remote_file_size($url)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);

        $data = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        $size = $info['download_content_length'];
        if ($size==-1 && $info['redirect_url']) {
            $size = $this->retrieve_remote_file_size($info['redirect_url']);
        }
        return $size;
   }
}
