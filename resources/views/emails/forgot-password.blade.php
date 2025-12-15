<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>Reset your password</title>
</head>
<body style="margin:0; padding:0; background:#2b0b0b; font-family:Arial, Helvetica, sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#2b0b0b; padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="620" cellspacing="0" cellpadding="0" border="0" style="width:620px; max-width:620px;">
                <tr>
                    <td style="padding:10px 0 18px 0; color:#ffffff;">
                        <div style="font-size:22px; font-weight:800; letter-spacing:0.5px;">
                            WELCOME TO <span style="color:#fbcc15;">FACULTIME!</span>
                        </div>
                        <div style="margin-top:6px; font-size:13px; color:rgba(255,255,255,0.85); line-height:1.45;">
                            Password reset request
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="background:#ffffff; border-radius:14px; padding:22px; box-shadow:0 10px 30px rgba(0,0,0,0.35);">
                        <div style="font-size:18px; font-weight:700; color:#111827; margin-bottom:10px;">
                            Reset your password
                        </div>

                        <div style="font-size:14px; color:#374151; line-height:1.6; margin-bottom:16px;">
                            We received a request to reset your FaculTime password. Click the button below to set a new password.
                        </div>

                        <div style="text-align:center; margin:18px 0 18px 0;">
                            <a href="{{ $resetUrl }}"
                               style="display:inline-block; background:#fbcc15; color:#111827; text-decoration:none; font-weight:800; padding:12px 18px; border-radius:10px;">
                                Reset Password →
                            </a>
                        </div>

                        <div style="font-size:12px; color:#6b7280; line-height:1.55;">
                            If the button doesn’t work, copy and paste this link into your browser:
                            <div style="margin-top:8px; word-break:break-all;">
                                <a href="{{ $resetUrl }}" style="color:#5E0B0B; text-decoration:underline;">
                                    {{ $resetUrl }}
                                </a>
                            </div>
                        </div>

                        <div style="margin-top:18px; font-size:12px; color:#6b7280; line-height:1.55;">
                            If you did not request a password reset, you can ignore this email.
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:16px 0 0 0; text-align:center; font-size:11px; color:rgba(255,255,255,0.75);">
                        © {{ date('Y') }} FaculTime | All rights reserved
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
