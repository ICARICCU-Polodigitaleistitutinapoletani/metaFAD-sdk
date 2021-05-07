<?php
class metafad_print_controllers_Printpdf extends metafad_common_controllers_Command
{
    public function execute($url, $servePdf = false)
    {
        header('Content-Type: application/pdf');

        $content = file_get_contents(rtrim($url,'/'));

        $folder = __Config::get('metafad.tmp.folder');
        $path = $folder . '/' . uniqid() .'.html';

        $file = file_put_contents($path,$content);

        if($file)
        {
            $pdfFile = '/opt/app/polofi_metafad/wwwRoot/cache/print.pdf';
            $cmd = 'xvfb-run wkhtmltopdf --footer-html "/opt/app/polofi_metafad/wwwRoot/application/classes/metafad/print/html/footer.html" -T 15 -B 15 ' . $path  . ' '. $pdfFile;
            $o = exec($cmd);

            if(!$servePdf)
            {
                return PNX_HOST . '/cache/print.pdf';
            }
            else
            {
                header('Content-Disposition: attachment; filename="print.pdf"');
                readfile(PNX_HOST . '/cache/print.pdf');
            }
        }

        return false;
    }
}