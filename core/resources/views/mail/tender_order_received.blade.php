<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="x-apple-disable-message-reformatting">
<title>Order Received</title>
<!--[if mso]>
<noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript>
<![endif]-->
</head>
<body style="margin:0; padding:0; background-color:#f0f2f5; font-family:Arial,Helvetica,sans-serif; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%;">

<!-- Outer wrapper -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" style="background-color:#f0f2f5;">
  <tr>
    <td align="center" style="padding:40px 16px 48px;">

      <!-- ── Header brand bar ── -->
      <table width="600" cellpadding="0" cellspacing="0" border="0" role="presentation" style="max-width:600px; width:100%; margin-bottom:0;">
        <tr>
          <td style="background-color:#1a2a4a; border-radius:10px 10px 0 0; padding:22px 36px;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation">
              <tr>
                <td>
                  <span style="font-size:20px; font-weight:700; color:#ffffff; letter-spacing:0.04em;">{{ $fromName }}</span>
                </td>
                <td align="right">
                  <span style="font-size:12px; color:#94a3b8; letter-spacing:0.05em; text-transform:uppercase;">Tender Order Notification</span>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
      <!-- /header bar -->

      <!-- ── Main card ── -->
      <table width="600" cellpadding="0" cellspacing="0" border="0" role="presentation" style="max-width:600px; width:100%; background:#ffffff; box-shadow:0 4px 32px rgba(0,0,0,0.10);">

        <!-- Hero section -->
        <tr>
          <td style="padding:44px 44px 32px; border-bottom:1px solid #f1f5f9;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation">
              <tr>
                <!-- Orange clock icon -->
                <td width="56" valign="top" style="padding-right:18px;">
                  <table cellpadding="0" cellspacing="0" border="0" role="presentation">
                    <tr>
                      <td width="56" height="56" align="center" valign="middle"
                          style="width:56px; height:56px; background-color:#d97706; border-radius:14px; text-align:center; vertical-align:middle;">
                        <span style="font-size:26px; color:#ffffff; line-height:56px; display:block;">&#128337;</span>
                      </td>
                    </tr>
                  </table>
                </td>
                <!-- Title -->
                <td valign="middle">
                  <p style="margin:0 0 4px 0; font-size:11px; font-weight:700; color:#d97706; letter-spacing:0.09em; text-transform:uppercase;">Order Received</p>
                  <p style="margin:0; font-size:22px; font-weight:700; color:#0f172a; line-height:1.3;">We have received your order.</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Body text -->
        <tr>
          <td style="padding:32px 44px 0;">
            <p style="margin:0 0 6px 0; font-size:15px; color:#334155; line-height:1.7;">
              Hello, <strong style="color:#0f172a;">{{ $purchase->first_name }} {{ $purchase->last_name }}</strong>
            </p>
            <p style="margin:0 0 28px 0; font-size:15px; color:#334155; line-height:1.7;">
              Thank you for your order. We have received your purchase request for
              <strong style="color:#0f172a;">{{ $tenderTitle }}</strong>.
              Your payment is currently <strong style="color:#d97706;">pending verification</strong>.
              Once your payment is confirmed, we will send you a secure download link.
            </p>
          </td>
        </tr>

        <!-- Order summary table -->
        <tr>
          <td style="padding:0 44px 32px;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation" style="border:1px solid #e2e8f0; border-radius:8px; overflow:hidden;">
              <tr>
                <td colspan="2" style="background-color:#f8fafc; padding:12px 16px; border-bottom:1px solid #e2e8f0;">
                  <p style="margin:0; font-size:12px; font-weight:700; color:#64748b; letter-spacing:0.07em; text-transform:uppercase;">Order Summary</p>
                </td>
              </tr>
              <tr>
                <td style="padding:12px 16px; font-size:13px; color:#64748b; border-bottom:1px solid #f1f5f9; width:40%;">Order Number</td>
                <td style="padding:12px 16px; font-size:13px; font-weight:700; color:#0f172a; border-bottom:1px solid #f1f5f9;">{{ $purchase->order_number }}</td>
              </tr>
              <tr>
                <td style="padding:12px 16px; font-size:13px; color:#64748b; border-bottom:1px solid #f1f5f9;">Tender</td>
                <td style="padding:12px 16px; font-size:13px; color:#334155; border-bottom:1px solid #f1f5f9;">{{ $tenderTitle }}</td>
              </tr>
              <tr>
                <td style="padding:12px 16px; font-size:13px; color:#64748b; border-bottom:1px solid #f1f5f9;">Name</td>
                <td style="padding:12px 16px; font-size:13px; color:#334155; border-bottom:1px solid #f1f5f9;">{{ $purchase->first_name }} {{ $purchase->last_name }}</td>
              </tr>
              <tr>
                <td style="padding:12px 16px; font-size:13px; color:#64748b; border-bottom:1px solid #f1f5f9;">Email</td>
                <td style="padding:12px 16px; font-size:13px; color:#334155; border-bottom:1px solid #f1f5f9;">{{ $purchase->email }}</td>
              </tr>
              <tr>
                <td style="padding:12px 16px; font-size:13px; color:#64748b; border-bottom:1px solid #f1f5f9;">Phone</td>
                <td style="padding:12px 16px; font-size:13px; color:#334155; border-bottom:1px solid #f1f5f9;">{{ $purchase->phone_number }}</td>
              </tr>
              <tr>
                <td style="padding:12px 16px; font-size:13px; color:#64748b; border-bottom:1px solid #f1f5f9;">Country</td>
                <td style="padding:12px 16px; font-size:13px; color:#334155; border-bottom:1px solid #f1f5f9;">{{ $purchase->country }}</td>
              </tr>
              <tr>
                <td style="padding:12px 16px; font-size:13px; color:#64748b;">Payment Method</td>
                <td style="padding:12px 16px; font-size:13px; color:#334155;">{{ $purchase->payment_method }}</td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Divider -->
        <tr>
          <td style="padding:0 44px;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation">
              <tr>
                <td style="border-top:1px solid #e2e8f0; font-size:0; line-height:0;">&nbsp;</td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Notice box -->
        <tr>
          <td style="padding:28px 44px 36px;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation">
              <tr>
                <td style="background-color:#fefce8; border-left:4px solid #facc15; border-radius:0 6px 6px 0; padding:14px 18px;">
                  <p style="margin:0; font-size:13px; color:#713f12; line-height:1.6;">
                    <strong>What's next?</strong> Our team will verify your payment. Once confirmed, you will
                    receive a <strong>secure download link</strong> at this email address to access your tender documents.
                    If you have any questions, please contact us with your order number
                    <strong>{{ $purchase->order_number }}</strong>.
                  </p>
                </td>
              </tr>
            </table>
          </td>
        </tr>

      </table>
      <!-- /main card -->

      <!-- ── Footer ── -->
      <table width="600" cellpadding="0" cellspacing="0" border="0" role="presentation" style="max-width:600px; width:100%; background:#1e293b; border-radius:0 0 10px 10px;">
        <tr>
          <td style="padding:24px 36px;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation">
              <tr>
                <td valign="middle">
                  <p style="margin:0 0 2px 0; font-size:14px; font-weight:700; color:#f1f5f9;">{{ $fromName }}</p>
                  <p style="margin:0; font-size:12px; color:#64748b;">This is an automated message. Please do not reply.</p>
                </td>
                <td align="right" valign="middle">
                  <table cellpadding="0" cellspacing="0" border="0" role="presentation">
                    <tr>
                      <td style="padding-left:6px;">
                        <a href="#" style="display:inline-block; width:30px; height:30px; background-color:#1877f2; border-radius:50%; text-align:center; line-height:30px; color:#ffffff; font-size:13px; font-weight:700; text-decoration:none;">f</a>
                      </td>
                      <td style="padding-left:6px;">
                        <a href="#" style="display:inline-block; width:30px; height:30px; background-color:#0ea5e9; border-radius:50%; text-align:center; line-height:30px; color:#ffffff; font-size:12px; font-weight:700; text-decoration:none;">𝕏</a>
                      </td>
                      <td style="padding-left:6px;">
                        <a href="#" style="display:inline-block; width:30px; height:30px; background-color:#25d366; border-radius:50%; text-align:center; line-height:30px; color:#ffffff; font-size:14px; font-weight:700; text-decoration:none;">W</a>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
      <!-- /footer -->

      <!-- Below-card note -->
      <table width="600" cellpadding="0" cellspacing="0" border="0" role="presentation" style="max-width:600px; width:100%; margin-top:20px;">
        <tr>
          <td align="center">
            <p style="margin:0; font-size:12px; color:#94a3b8; line-height:1.7;">
              &copy; {{ date('Y') }} {{ $fromName }}. All rights reserved.<br>
              You received this email because an order was placed using this email address for order <strong style="color:#64748b;">{{ $purchase->order_number }}</strong>.
            </p>
          </td>
        </tr>
      </table>

    </td>
  </tr>
</table>

</body>
</html>
