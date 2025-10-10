<?php
require_once 'vendor/autoload.php'; // Adjust the path to autoload.php

use Dompdf\Dompdf;
use Dompdf\Options;

// Initialize Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);
$options->set('defaultFont', 'Arial'); // Set your default font if necessary
$dompdf = new Dompdf($options);

// Load HTML content
$html = '
<!DOCTYPE html>
<html>
<head>
    <title>Payslip</title>
    <style>
        .page-break {
            page-break-after: always;
        }
        .top-content {
            text-align: center;
        }
        p {
            line-height: 0.3;
        }
        .middle-content {
            height: 120px;
        }
        .middle-content .row {
            width: 50%;
            float: left;
        }
        .middle-content .row2 {
            width: 50%;
            float: right;
        }
        .border-center {
            border-left: 1px solid #000;
        }
        .table {
            border-collapse: collapse;
            border: 1px solid #000;
            width: 100%;
        }
        .table td {
            padding: 10px;
            border: 1px solid #000;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="top-content">
            <p><b>Meow Cafe</b></p>
            <p>2nd floor Jose Un Building</p>
            <p>Malaybalay City</p>
        </div>
        <br><br>
        <div class="middle-content">
            <table class="table">
                <tbody>
                    <tr>
                        <td colspan="2">
                            <p><b>Employee ID: <?= $employee_no ?></b></p>
                            <p><b>Employee Name: <?= ucwords($ename) ?></b></p>
                            <br>
                            <p>Period:</p>
                            <p>Daily Rate: <b><?= number_format($salary, 2) ?></b></p>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 50%;">
                            <p>Office: Cagayan De Oro</p>
                            <br>
                            <p>Department: <?= $name ?></p>
                            <p>Position: <?= $position_name ?></p>
                        </td>
                        <td style="width: 50%; border-left: 1px solid #000;">
                            <p>Contribution</p>
                            <br>
                            <p>SSS: <b><?= number_format($sss, 2) ?></b></p>
                            <p>PH: <b><?= number_format($ph, 2) ?></b></p>
                            <p>HDMF: <b><?= number_format($hdmf, 2) ?></b></p>
                        </td>
                    </tr>
                </tbody>
            </table>
            <br><br>
            <table class="table">
                <tbody>
                    <tr>
                        <td style="width: 50%;">
                            <table width="100%">
                                <tbody>
                                    <tr>
                                        <td>Days of Present:</td>
                                        <td class="text-right"><b><?= $present ?></b></td>
                                    </tr>
                                    <tr>
                                        <td>Salary:</td>
                                        <td class="text-right"><b><?= number_format($salary_collection, 2) ?></b></td>
                                    </tr>
                                    <tr>
                                        <td>Tardiness:</td>
                                        <td class="text-right"><b><?= number_format($late, 2) ?></b></td>
                                    </tr>
                                    <tr>
                                        <td>Overtime:</td>
                                        <td class="text-right"><b><?= number_format($time_log_amount, 2) ?></b></td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                        <td style="width: 25%; border-left: 1px solid #000;">
                            <p>Deductions:</p>
                            <br>
                            <table width="100%">
                                <tbody>
                                  
                                </tbody>
                            </table>
                        </td>
                        <td style="width: 25%; border-left: 1px solid #000;">
                            <table width="100%">
                                <tbody>
                                    <tr>
                                        <td>Total Earnings:</td>
                                        <td class="text-right"><b><?= number_format($total_earnings, 2) ?></b></td>
                                    </tr>
                                    <tr>
                                        <td>Total Contributions:</td>
                                        <td class="text-right"><b><?= number_format($contribute_amount, 2) ?></b></td>
                                    </tr>
                                    <tr>
                                        <td>Total Deduction:</td>
                                        <td class="text-right"><b><?= number_format($deduction_amount, 2) ?></b></td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="border-top: 1px solid #000; padding-top: 10px;">
                            <b>Net Pay:</b>
                            <b style="float: right"><?= number_format($net, 2) ?></b>
                        </td>
                    </tr>
                </tfoot>
            </table>
            <br><br>
            <table class="table">
                <tbody>
                    <tr>
                        <td>
                            <p>I hereby acknowledge receipt of the net pay amount: <b>₱<?= number_format($net, 2) ?></b></p>
                            <div style="width: 200px;">
                                <div>_________________________________</div>
                                <div style="text-align: center;">Signature</div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center; padding-top: 10px;">Date:</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
';

// Load HTML into Dompdf
$dompdf->loadHtml($html);

// (Optional) Set paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
$dompdf->stream('payslip.pdf', array('Attachment' => 0));
?>
