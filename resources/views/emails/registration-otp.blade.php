<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>BrandX Email Verification</title>
</head>

<body style="
    margin:0;
    padding:0;
    background:#f4f6f8;
    font-family:Arial, Helvetica, sans-serif;
">

<div style="
    width:100%;
    padding:40px 15px;
    box-sizing:border-box;
">

    <div style="
        max-width:600px;
        margin:0 auto;
        background:#ffffff;
        border-radius:12px;
        overflow:hidden;
        box-shadow:0 4px 20px rgba(0,0,0,0.08);
    ">

        <!-- Header -->

        <div style="
            background:#0d6efd;
            color:#ffffff;
            padding:25px;
            text-align:center;
        ">

            <h1 style="
                margin:0;
                font-size:28px;
            ">
                BrandX
            </h1>

            <p style="
                margin:8px 0 0;
                font-size:14px;
            ">
                Email Verification
            </p>

        </div>


        <!-- Content -->

        <div style="
            padding:35px;
        ">

            <h2 style="
                margin-top:0;
                color:#212529;
            ">
                Hello {{ $name }},
            </h2>

            <p style="
                color:#555;
                font-size:16px;
                line-height:1.6;
            ">
                Thank you for creating your BrandX account.
                Please use the verification code below to verify
                your email address.
            </p>


            <!-- OTP -->

            <div style="
                margin:30px 0;
                padding:25px;
                background:#f1f3f5;
                border-radius:10px;
                text-align:center;
            ">

                <div style="
                    color:#6c757d;
                    font-size:13px;
                    margin-bottom:10px;
                ">
                    YOUR VERIFICATION CODE
                </div>

                <div style="
                    font-size:36px;
                    font-weight:bold;
                    letter-spacing:10px;
                    color:#0d6efd;
                ">
                    {{ $otp }}
                </div>

            </div>


            <p style="
                color:#555;
                font-size:14px;
                line-height:1.6;
            ">
                This OTP is valid for <strong>10 minutes</strong>.
            </p>

            <p style="
                color:#555;
                font-size:14px;
                line-height:1.6;
            ">
                If you did not create a BrandX account, you can
                safely ignore this email.
            </p>

        </div>


        <!-- Footer -->

        <div style="
            background:#f8f9fa;
            padding:20px;
            text-align:center;
            color:#777;
            font-size:12px;
        ">

            © {{ date('Y') }} BrandX. All rights reserved.

        </div>

    </div>

</div>

</body>
</html>