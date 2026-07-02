<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $subjectLine }}</title>
</head>
<body style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #fafafa; margin: 0; padding: 20px; color: #1f2937;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" max-width="600px" style="background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        <!-- Header -->
        <tr>
            <td style="background-color: #fdd835; padding: 24px; text-align: center;">
                <h1 style="margin: 0; color: #000000; font-size: 24px; font-weight: 800; letter-spacing: -0.025em;">{{ \App\Models\SiteSetting::getVal('platform_name', 'X-Buy') }}</h1>
            </td>
        </tr>
        <!-- Content -->
        <tr>
            <td style="padding: 32px 24px;">
                <div style="font-size: 16px; line-height: 1.6; color: #374151;">
                    {!! $htmlBody !!}
                </div>
            </td>
        </tr>
        <!-- Footer -->
        <tr>
            <td style="background-color: #f9fafb; padding: 24px; text-align: center; border-t: 1px solid #e5e7eb;">
                <p style="margin: 0 0 8px; font-size: 12px; color: #9ca3af;">You are receiving this because you signed up for updates on {{ \App\Models\SiteSetting::getVal('platform_name', 'X-Buy') }}.</p>
                <p style="margin: 0; font-size: 12px;"><a href="{{ $unsubscribeUrl }}" style="color: #6b7280; text-decoration: underline; font-weight: 500;">Unsubscribe from these emails</a></p>
            </td>
        </tr>
    </table>
</body>
</html>
