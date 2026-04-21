<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Feralde</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #44403c; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1c1917; color: #ffffff; padding: 30px; text-align: center; border-radius: 12px 12px 0 0; }
        .content { padding: 40px; border: 1px solid #e7e5e4; border-top: none; border-radius: 0 0 12px 12px; background: #fafaf9; }
        .button { display: inline-block; background: #1c1917; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px; margin-top: 25px; transition: background 0.2s; }
        .button:hover { background: #44403c; }
        .footer { margin-top: 40px; font-size: 12px; color: #a8a29e; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin:0; font-size: 24px;">Welcome to Feralde</h1>
    </div>
    <div class="content">
        <h2 style="color: #1c1917; margin-top: 0;">Hello, {{ $distributorName }}!</h2>
        <p>Great news! Your payment has been confirmed and your distributor account is now fully active.</p>
        <p>You can now log in to the Feralde portal to start managing your network, tracked sales, and commissions.</p>
        <center>
            <a href="{{ config('app.frontend_url') }}" class="button" style="color: #ffffff;">Go to My Dashboard</a>
        </center>
        <p style="margin-top: 25px;">If you have any questions or need assistance, feel free to reply to this email or contact your assigned manager.</p>
        <p>Welcome to the family!</p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} Feralde Marketplace. All rights reserved.
    </div>
</body>
</html>
