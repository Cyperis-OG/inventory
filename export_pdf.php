<?php
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="MonthView.pdf"');

require_once __DIR__ . '/fpdf/fpdf.php';

class MyPDF extends FPDF {
    public $title       = 'Monthly Inventory View';
    public $colWidths   = [];
    public $lineHeight  = 6;
    private $tableWidth = 0;
    private $tableStartX= 0;

    function Header() {
        // Title on each page
        $this->SetFont('Arial','B',12);
        $this->Cell(0, 8, $this->title, 0, 1, 'C');
        $this->Ln(2);
        $this->Cell(0,0,'','B',1); // small horizontal line
        $this->Ln(3);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',9);
        $this->Cell(0,10, 'Page '.$this->PageNo().' of {nb}',0,0,'C');
    }

    // MultiCell row logic from prior examples
    function NbLines($w, $txt) {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) {
            $w = $this->w - $this->rMargin - $this->x;
        }
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $txt  = str_replace("\r", '', $txt);
        $nb   = strlen($txt);
        if ($nb > 0 && $txt[$nb-1] == "\n") {
            $nb--;
        }
        $sep = -1; $i = 0; $l = 0; $nl=1;
        while ($i < $nb) {
            $c = $txt[$i];
            if ($c == "\n") {
                $i++;
                $sep=-1; $l=0; $nl++;
                continue;
            }
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $nb) $i++;
                } else {
                    $i = $sep + 1;
                }
                $sep=-1;
                $l=0;
                $nl++;
            } else if ($c == ' ') {
                $sep = $i;
            }
            $i++;
        }
        return $nl;
    }
    function CheckPageBreak($h) {
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
        }
    }

    function MultiCellRow($rowData) {
        // 1) Determine how many lines each cell needs
        $maxLines=0;
        for ($i=0; $i<count($rowData); $i++) {
            $nb = $this->NbLines($this->colWidths[$i], $rowData[$i]);
            if ($nb>$maxLines) {
                $maxLines = $nb;
            }
        }
        $rowHeight = $maxLines*$this->lineHeight;

        // 2) Check for page break
        $this->CheckPageBreak($rowHeight);

        // 3) Move to the table start X so entire row is centered
        $this->SetX($this->tableStartX);

        // 4) Print each cell with border
        for ($i=0; $i<count($rowData); $i++) {
            $x = $this->GetX();
            $y = $this->GetY();
            $this->Rect($x, $y, $this->colWidths[$i], $rowHeight);

            // For left alignment inside the cell:
            $this->MultiCell($this->colWidths[$i], $this->lineHeight, $rowData[$i], 0, 'L');
            $this->SetXY($x+$this->colWidths[$i], $y);
        }

        // next line
        $this->Ln($rowHeight);
    }

    // We'll define a method to compute the table's total width & start X
    function ComputeTableLayout() {
        $this->tableWidth = array_sum($this->colWidths);
        // Page width minus leftMargin minus rightMargin
        $usableWidth = $this->GetPageWidth() - $this->lMargin - $this->rMargin;
        // center offset
        $this->tableStartX = $this->lMargin + ($usableWidth - $this->tableWidth)/2;
        if ($this->tableStartX < $this->lMargin) {
            // In case columns are too wide to center
            $this->tableStartX = $this->lMargin;
        }
    }
}

// --------------------------------------------------------------
// 1) Parse incoming JSON
$postData = file_get_contents('php://input');
$data     = json_decode($postData, true);

// fallback if older php
$monthTitle = isset($data['monthTitle']) ? $data['monthTitle'] : 'Monthly View';
$rows       = isset($data['rows'])       ? $data['rows']       : [];
$grandTotal = isset($data['grandTotal']) ? $data['grandTotal'] : '';

// 2) Create PDF
$pdf = new MyPDF('L','mm','Legal'); // Landscape, mm, Legal
$pdf->SetMargins(5,5,5);           // small margins
$pdf->AliasNbPages();

// set the PDF's title
$pdf->title = $monthTitle;

$pdf->AddPage();

// define column widths
$pdf->colWidths = [28, 45, 45, 40, 40, 50, 40, 27];
$pdf->lineHeight= 7;

// compute the table layout so we can center
$pdf->ComputeTableLayout();

// 3) Table Header
$pdf->SetFont('Arial','B',11);
$headerRow = [
  'Order #',
  'Job Name',
  'Start-of-Month',
  'In (Month)',
  'Out (Month)',
  'End-of-Month',
  'Open Inspect',
  'Total ($)'
];
$pdf->MultiCellRow($headerRow);

// 4) Table Body
$pdf->SetFont('Arial','',11);
foreach($rows as $r) {
    $rowData = [
        $r['orderNumber'],
        $r['jobName'],
        $r['startMonth'],
        $r['inMonth'],
        $r['outMonth'],
        $r['endMonth'],
        $r['openInspect'],
        $r['total']
    ];
    $pdf->MultiCellRow($rowData);
}

// 5) Grand total
$pdf->Ln(4);
$pdf->SetFont('Arial','B',12);
// We'll align this right across entire page, so no need to center specifically
$pdf->Cell(0, 8, "Grand Total: $grandTotal", 0, 1, 'R');

// 6) Output inline
$pdf->Output('I','MonthView.pdf');
