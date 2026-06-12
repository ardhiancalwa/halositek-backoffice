<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConsultationReportRefundApprovedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $userName,
        public readonly string $reportId,
        public readonly string $orderId,
        public readonly int $amount,
        public readonly string $reason,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Report Konsultasi Disetujui - Proses Refund HaloSitek',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.consultation-report-refund-approved',
        );
    }
}
