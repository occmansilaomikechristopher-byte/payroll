<?php

use Dompdf\Dompdf;
use Dompdf\Options;
require_once 'library/dompdf/autoload.inc.php';
ob_start();

?>
<style>
.page-break {
    page-break-after: always;
}
</style>
<h1>Text<h1>
<?php
$html = ob_get_clean();
$dompdf = new DOMPDF();
$dompdf->load_html($html);
$dompdf->render();
//$dompdf->stream("sample.pdf");
$dompdf->stream("codexworld", array("Attachment" => 0));
?>