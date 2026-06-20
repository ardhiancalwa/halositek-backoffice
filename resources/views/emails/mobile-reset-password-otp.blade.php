<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password OTP - HaloSitek</title>
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
                                            <span style="font-size: 28px;">🔐</span>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Title -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center" style="padding-bottom: 8px;">
                                        <h1 style="margin: 0; font-size: 24px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px;">
                                            Reset Your Password
                                        </h1>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding-bottom: 32px;">
                                        <p style="margin: 0; font-size: 14px; color: #64748b; line-height: 1.6;">
                                            Hi <strong style="color: #334155;">{{ $userName }}</strong>,
                                            we received a request to reset your password from the HaloSitek mobile app. Use the OTP code below to continue.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- OTP Code -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center" style="padding-bottom: 32px;">
                                        <div style="display: inline-block; background-color: #E8820C; color: #ffffff; font-size: 28px; font-weight: 800; text-decoration: none; padding: 14px 40px; border-radius: 10px; letter-spacing: 8px; box-shadow: 0 4px 14px rgba(232,130,12,0.35);">
                                            {{ $otp }}
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

                            <!-- OTP Fallback -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="padding-bottom: 24px;">
                                        <p style="margin: 0 0 8px; font-size: 12px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">
                                            Your OTP code:
                                        </p>
                                        <p style="margin: 0; font-size: 12px; color: #E8820C; word-break: break-all; line-height: 1.6; background-color: #FFF7ED; padding: 12px 16px; border-radius: 8px; border: 1px solid #FFEDD5;">
                                            {{ $otp }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Expiry Notice -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td>
                                        <p style="margin: 0; font-size: 13px; color: #94a3b8; line-height: 1.6;">
                                            This OTP will expire in <strong style="color: #64748b;">{{ $expiresInMinutes }} minutes</strong>
                                            and can only be used once. If you didn't request this, you can safely ignore this email.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding-top: 32px; padding-bottom: 16px;">
                            <p style="margin: 0; font-size: 12px; color: #94a3b8; line-height: 1.6;">
                                &copy; {{ date('Y') }} HaloSitek. All rights reserved.
                            </p>
                            <p style="margin: 4px 0 0; font-size: 11px; color: #cbd5e1;">
                                This is an automated message, please do not reply.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
