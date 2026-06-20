<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Konsultasi Disetujui - HaloSitek</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f1f5f9; padding: 40px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellspacing="0" cellpadding="0" style="max-width: 560px; width: 100%;">
                    <!-- Logo Header -->
                    <tr>
                        <td align="center" style="padding-bottom: 32px;">
                            <img src="{{ asset('images/brand.png') }}" alt="HaloSitek" height="48" style="height: 48px; width: auto;">
                        </td>
                    </tr>

                    <!-- Card -->
                    <tr>
                        <td style="background-color: #ffffff; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.06); padding: 48px 40px;">
                            <!-- Icon -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center" style="padding-bottom: 24px;">
                                        <div style="display: inline-block; width: 56px; height: 56px; background-color: #FFF7ED; border-radius: 14px; text-align: center; line-height: 56px;">
                                            <span style="font-size: 28px;">&#128176;</span>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Title -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center" style="padding-bottom: 8px;">
                                        <h1 style="margin: 0; font-size: 24px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px;">
                                            Report Konsultasi Disetujui
                                        </h1>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding-bottom: 32px;">
                                        <p style="margin: 0; font-size: 14px; color: #64748b; line-height: 1.6;">
                                            Halo <strong style="color: #334155;">{{ $userName }}</strong>,
                                            report konsultasi Anda telah disetujui dan berhak diproses untuk pengembalian dana.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Refund Summary -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="padding-bottom: 32px;">
                                        <div style="background-color: #FFF7ED; padding: 16px 18px; border-radius: 10px; border: 1px solid #FFEDD5;">
                                            <p style="margin: 0 0 8px; font-size: 12px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">
                                                Detail Refund
                                            </p>
                                            <p style="margin: 0 0 6px; font-size: 13px; color: #64748b; line-height: 1.6;">
                                                Report ID: <strong style="color: #334155;">{{ $reportId }}</strong>
                                            </p>
                                            <p style="margin: 0 0 6px; font-size: 13px; color: #64748b; line-height: 1.6;">
                                                Order ID: <strong style="color: #334155;">{{ $orderId }}</strong>
                                            </p>
                                            <p style="margin: 0 0 6px; font-size: 13px; color: #64748b; line-height: 1.6;">
                                                Nominal: <strong style="color: #334155;">Rp {{ number_format($amount, 0, ',', '.') }}</strong>
                                            </p>
                                            <p style="margin: 0; font-size: 13px; color: #64748b; line-height: 1.6;">
                                                Alasan report: <strong style="color: #334155;">{{ $reason }}</strong>
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Divider -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="padding-bottom: 24px;">
                                        <div style="height: 1px; background-color: #e2e8f0;"></div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Reply Notice -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td>
                                        <p style="margin: 0; font-size: 13px; color: #94a3b8; line-height: 1.6;">
                                            Untuk memproses pengembalian dana, silakan balas email ini dengan konfirmasi bahwa Anda ingin melanjutkan proses refund. Sertakan Report ID dan Order ID di atas agar tim HaloSitek dapat melakukan verifikasi.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding-top: 32px; padding-bottom: 16px;">
                            <p style="margin: 0; font-size: 12px; color: #94a3b8; line-height: 1.6;">
                                &copy; {{ date('Y') }} HaloSitek. All rights reserved.
                            </p>
                            <p style="margin: 4px 0 0; font-size: 11px; color: #cbd5e1;">
                                Tim HaloSitek akan membantu proses refund setelah menerima balasan Anda.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
