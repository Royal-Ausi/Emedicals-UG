<?php
session_start();
require 'db.php';
require 'includes/fpdf.php';

// PHPMailer for email functionality
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';

// Email configuration
require 'includes/email_config.php';

// Ensure logged-in user
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$report_id = intval($_GET['id']);

// Fetch report + related appointment + patient + doctor
$query = "
    SELECT r.*, 
           a.appointment_date,
           a.symptoms,
           a.patient_id,
           a.doctor_id,
           p.fullname AS patient_name,
           d.fullname AS doctor_name
    FROM patient_reports r 
    JOIN appointments a ON r.appointment_id = a.id
    JOIN users p ON a.patient_id = p.id
    JOIN users d ON a.doctor_id = d.id
    WHERE r.id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $report_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    header("Location: dashboard.php");
    exit();
}

$report = $res->fetch_assoc();

// Access control: patient can only view their own reports
// Secure Access Control
if ($user_type === "patient" && $report['patient_id'] != $user_id) {
    header("Location: dashboard.php");
    exit();
}

if ($user_type === "doctor" && $report['doctor_id'] != $user_id) {
    header("Location: dashboard.php");
    exit();
}

// Handle email sending
if (isset($_GET['send']) && $_GET['send'] === 'email') {
    // Ensure only doctor can trigger this action
    if ($user_type !== "doctor") {
        header("Location: dashboard.php");
        exit();
    }

    // Fetch patient email
    $email_query = "SELECT email FROM users WHERE id = ?";
    $email_stmt = $conn->prepare($email_query);
    $email_stmt->bind_param("i", $report['patient_id']);
    $email_stmt->execute();
    $email_result = $email_stmt->get_result();
    $patient_data = $email_result->fetch_assoc();
    $patient_email = $patient_data['email'];
    $email_stmt->close();

    // Generate PDF
    class PDF extends FPDF
    {
        function Footer()
        {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->SetTextColor(128);
            $this->Cell(0, 5, 'Generated on: ' . date('d M Y H:i'), 0, 1, 'L');
            $this->Cell(0, 5, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
        }
    }

    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();

    if (file_exists('logo.png')) {
        $pdf->Image('logo.png', 10, 10, 30);
    }
    $pdf->SetY(20);

    $pdf->SetDrawColor(0, 76, 140);
    $pdf->SetFillColor(0, 76, 140);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Medical Report', 0, 1, 'C', true);
    $pdf->Ln(5);

    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 12);

    $pdf->Cell(50, 8, 'Patient: ', 0, 0);
    $pdf->Cell(0, 8, $report['patient_name'], 0, 1);
    $pdf->Cell(50, 8, 'Doctor: ', 0, 0);
    $pdf->Cell(0, 8, $report['doctor_name'], 0, 1);
    $pdf->Cell(50, 8, 'Date: ', 0, 0);
    $pdf->Cell(0, 8, date("d M Y", strtotime($report['appointment_date'])), 0, 1);
    $pdf->Ln(5);

    function addSection($pdf, $title, $content)
    {
        if (!empty($content)) {
            $pdf->SetFillColor(0, 153, 76);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 8, $title, 0, 1, true);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('Arial', '', 12);
            $pdf->MultiCell(0, 8, $content);
            $pdf->Ln(3);
        }
    }

    addSection($pdf, 'Symptoms', $report['symptoms']);
    addSection($pdf, 'Diagnosis', $report['diagnosis']);
    addSection($pdf, 'Prescription', $report['prescription']);
    addSection($pdf, 'Doctor Notes', $report['notes']);
    addSection($pdf, 'Follow-up Date', $report['follow_up_date']);

    // Save PDF temporarily
    $temp_file = sys_get_temp_dir() . '/Medical_Report_' . $report_id . '_' . time() . '.pdf';
    $pdf->Output('F', $temp_file);

    // Send email using PHPMailer
    $mail = new PHPMailer(true);
    try {
        // Check if email is configured
        if (SMTP_USERNAME === '' || SMTP_PASSWORD === '') {
            throw new Exception('Email configuration not set. Please set SMTP_USERNAME and SMTP_PASSWORD in .env.');
        }

        // SMTP configuration for Gmail
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';

        // Email content
        $mail->setFrom(EMAIL_FROM_ADDRESS, EMAIL_FROM_NAME);
        $mail->addAddress($patient_email, $report['patient_name']);
        $mail->Subject = 'Your Medical Report - ' . date('d M Y');
        $mail->Body = 'Dear ' . $report['patient_name'] . ",\n\nPlease find attached your medical report.\n\nBest regards,\n" . $report['doctor_name'];
        $mail->addAttachment($temp_file, 'Medical_Report_' . $report_id . '.pdf');

        $mail->send();

        // Delete temporary file
        unlink($temp_file);

        // Redirect with success status
        header("Location: view_report.php?id=" . $report_id . "&status=sent");
        exit();
    } catch (Exception $e) {
        // Delete temporary file even on error
        if (file_exists($temp_file)) {
            unlink($temp_file);
        }
        // Log error for debugging (remove in production or use proper logging)
        error_log("Email sending failed: " . $mail->ErrorInfo);
        header("Location: view_report.php?id=" . $report_id . "&error=email_failed");
        exit();
    }
}

// Handle PDF download
if (isset($_GET['download']) && $_GET['download'] === 'pdf') {

    class PDF extends FPDF
    {
        // Page footer
        function Footer()
        {
            $this->SetY(-15);
            $this->SetFont('Arial', 'I', 8);
            $this->SetTextColor(128);
            $this->Cell(0, 5, 'Generated on: ' . date('d M Y H:i'), 0, 1, 'L');
            $this->Cell(0, 5, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
        }
    }

    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->AddPage();

    // Add logo (adjust path as needed)
    if (file_exists('logo.png')) {
        $pdf->Image('logo.png', 10, 10, 30); // x,y,width
    }
    $pdf->SetY(20); // Move below logo

    // Header
    $pdf->SetDrawColor(0, 76, 140);  // Navy border
    $pdf->SetFillColor(0, 76, 140);  // Navy fill
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Medical Report', 0, 1, 'C', true);
    $pdf->Ln(5);

    // Reset text color
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 12);

    // Patient & Doctor Info
    $pdf->Cell(50, 8, 'Patient: ', 0, 0);
    $pdf->Cell(0, 8, $report['patient_name'], 0, 1);
    $pdf->Cell(50, 8, 'Doctor: ', 0, 0);
    $pdf->Cell(0, 8, $report['doctor_name'], 0, 1);
    $pdf->Cell(50, 8, 'Date: ', 0, 0);
    $pdf->Cell(0, 8, date("d M Y", strtotime($report['appointment_date'])), 0, 1);
    $pdf->Ln(5);

    // Sections helper function
    function addSection($pdf, $title, $content)
    {
        if (!empty($content)) {
            $pdf->SetFillColor(0, 153, 76); // Light green header
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 8, $title, 0, 1, true);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('Arial', '', 12);
            $pdf->MultiCell(0, 8, $content);
            $pdf->Ln(3);
        }
    }

    addSection($pdf, 'Symptoms', $report['symptoms']);
    addSection($pdf, 'Diagnosis', $report['diagnosis']);
    addSection($pdf, 'Prescription', $report['prescription']);
    addSection($pdf, 'Doctor Notes', $report['notes']);
    addSection($pdf, 'Follow-up Date', $report['follow_up_date']);

    $pdf->Output('D', 'Medical_Report_' . $report_id . '.pdf'); // Force download
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>View Report</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .box {
            max-width: 700px;
            margin: 30px auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .title {
            color: #004c8c;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
        }

        hr {
            margin: 14px 0;
            border-color: #004c8c;
        }

        .field-title {
            font-weight: bold;
            margin-top: 10px;
            color: #004080;
        }

        .btn {
            display: inline-block;
            background: #28a745;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 15px;
            font-weight: bold;
        }

        .btn:hover {
            background: #218838;
        }
    </style>
</head>

<body>

    <div class="box">
        <h2 class="title">Medical Report</h2>

        <?php if (isset($_GET['status']) && $_GET['status'] === 'sent'): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                Email sent successfully!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'email_failed'): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                Failed to send email. Please check email configuration in includes/email_config.php
            </div>
        <?php endif; ?>

        <p><strong>Patient:</strong> <?= htmlspecialchars($report['patient_name']); ?></p>
        <p><strong>Doctor:</strong> <?= htmlspecialchars($report['doctor_name']); ?></p>
        <p><strong>Date:</strong> <?= $report['appointment_date']; ?></p>

        <hr>

        <p><span class="field-title">Symptoms:</span><br><?= nl2br(htmlspecialchars($report['symptoms'])); ?>
            ; ?></p>

        <?php if (!empty($report['diagnosis'])): ?>
            <p><span class="field-title">Diagnosis:</span><br><?= nl2br($report['diagnosis']); ?></p>
        <?php endif; ?>

        <?php if (!empty($report['prescription'])): ?>
            <p><span class="field-title">Prescription:</span><br><?= nl2br($report['prescription']); ?></p>
        <?php endif; ?>

        <?php if (!empty($report['notes'])): ?>
            <p><span class="field-title">Doctor Notes:</span><br><?= nl2br($report['notes']); ?></p>
        <?php endif; ?>

        <?php if (!empty($report['follow_up_date'])): ?>
            <p><span class="field-title">Follow-up Date:</span> <?= $report['follow_up_date']; ?></p>
        <?php endif; ?>

        <hr>

        <a href="dashboard.php" class="btn">Back</a>
        <a href="view_report.php?id=<?= $report_id; ?>&download=pdf" class="btn">Download PDF</a>
        <?php if ($user_type === "doctor"): ?>
            <a href="view_report.php?id=<?= $report_id; ?>&send=email" class="btn">Email Report</a>
        <?php endif; ?>
    </div>

</body>

</html>