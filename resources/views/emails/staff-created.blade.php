<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Account Created</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f8; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#f4f6f8; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="680" style="max-width:680px; width:100%; background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;">
                    <tr>
                        <td style="padding:22px 24px; border-bottom:1px solid #e5e7eb;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td valign="middle" style="width:40%; padding-right:12px;">
                                        @if(!empty($setting?->logo_url))
                                            <img src="{{ $setting->logo_url }}" alt="{{ $setting->name ?? config('app.name') }}" style="max-width:170px; height:auto; display:block;">
                                        @else
                                            <div style="font-size:18px; font-weight:700; color:#111827;">{{ $setting?->name ?? config('app.name') }}</div>
                                        @endif
                                    </td>
                                    <td valign="middle" align="right" style="width:60%; font-size:13px; line-height:1.6; color:#374151;">
                                        <div style="font-size:20px; font-weight:700; line-height:1.2; color:#111827; margin-bottom:4px;">
                                            {{ $setting?->name ?? config('app.name') }}
                                        </div>
                                        @if(!empty($setting?->phone))
                                            <div><strong>Phone:</strong> {{ $setting->phone }}</div>
                                        @endif
                                        @if(!empty($setting?->email))
                                            <div><strong>Email:</strong> {{ $setting->email }}</div>
                                        @endif
                                        @if(!empty($setting?->address))
                                            <div><strong>Address:</strong> {{ $setting->address }}</div>
                                        @endif
                                        @if(!empty($setting?->gst_num))
                                            <div><strong>GST:</strong> {{ $setting->gst_num }}</div>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px 24px 16px 24px;">
                            <div style="font-size:22px; font-weight:700; color:#111827; margin-bottom:8px;">Welcome, {{ $staff->name }}</div>
                            <div style="font-size:15px; color:#4b5563; line-height:1.6;">
                                Your staff account has been created successfully. Please find your login credentials below.
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:8px 24px 8px 24px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px;">
                                <tr>
                                    <td style="padding:18px;">
                                        <div style="font-size:14px; line-height:1.9;">
                                            <div><strong>Email:</strong> {{ $staff->email }}</div>
                                            <div><strong>Password:</strong> {{ $password }}</div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 24px 28px 24px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="background:#111827; border-radius:6px;">
                                        <a href="{{ url('/') }}" style="display:inline-block; padding:11px 18px; color:#ffffff; text-decoration:none; font-size:14px; font-weight:600;">Login Now</a>
                                    </td>
                                </tr>
                            </table>
                            <div style="margin-top:14px; font-size:13px; color:#6b7280; line-height:1.6;">
                                For security, please change your password after your first login.
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="background-color:#f4f4f4; padding:12px 16px; border-top:1px solid #e5e7eb;">
                            <div style="font-size:14px; font-weight:600; color:#111827;">
                                &copy; {{ date('Y') }} Copyright - {{ $setting?->name ?? 'Fablead Developers Technolab' }}
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
