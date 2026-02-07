<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reset Your Password</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #991b1b; margin: 0;">Santa Rosa City</h1>
        <p style="color: #666; margin: 5px 0 0 0;">Veterinary Office</p>
    </div>

    <h2 style="color: #333;">Hello, {{ $user->First_Name }}!</h2>
    
    <p>You requested to reset your password for your account. Click the button below to proceed:</p>
    
    <p style="text-align: center; margin: 30px 0;">
        <a href="{{ $url }}" style="display: inline-block; background-color: #991b1b; color: white; padding: 14px 35px; text-decoration: none; border-radius: 8px; font-weight: bold;">
            Reset Password
        </a>
    </p>
    
    <p style="font-size: 12px; color: #666; word-break: break-all;">
        Or copy and paste this link into your browser:<br>
        {{ $url }}
    </p>
    
    <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">
    
    <p style="color: #666;"><strong>This link will expire in 60 minutes.</strong></p>
    
    <p style="font-size: 12px; color: #999; margin-top: 30px;">
        If you did not request a password reset, please ignore this email. Your password will remain unchanged.
    </p>
</body>
</html>