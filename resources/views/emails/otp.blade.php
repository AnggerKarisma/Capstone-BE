<!DOCTYPE html>
<html>
<head>
    <title>Kode OTP Login</title>
</head>
<body style="font-family: sans-serif; text-align: center; padding: 20px;">
    <h2>Halo!</h2>
    <p>Anda meminta kode OTP untuk login ke aplikasi Klinik.</p>
    <p>Kode OTP Anda adalah:</p>
    
    <h1 style="background-color: #f3f4f6; padding: 10px; display: inline-block; border-radius: 5px; letter-spacing: 5px;">
        {{ $otpCode }}
    </h1>
    
    <p>Kode ini berlaku selama 5 menit.</p>
    <p>Jika Anda tidak meminta kode ini, abaikan saja email ini.</p>
    <hr>
    <p style="font-size: small; color: gray;">Aplikasi Klinik &copy; {{ date('Y') }}</p>
</body>
</html>