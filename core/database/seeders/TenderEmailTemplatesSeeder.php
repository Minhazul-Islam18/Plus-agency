<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenderEmailTemplatesSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            [
                'email_type'    => 'tender_purchase',
                'email_subject' => 'Order Received — Tender Purchase',
                'email_body'    => $this->tenderPurchaseBody(),
            ],
            [
                'email_type'    => 'tender_download_link',
                'email_subject' => 'Your Secure Download Link - Order {order_number}',
                'email_body'    => $this->tenderDownloadLinkBody(),
            ],
        ];

        foreach ($templates as $template) {
            $exists = DB::table('email_templates')
                ->where('email_type', $template['email_type'])
                ->exists();

            if (!$exists) {
                DB::table('email_templates')->insert($template);
            }
        }
    }

    private function tenderPurchaseBody(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>Order Received</title>
</head>
<body style="margin:0; padding:0; background-color:#f0f2f5; font-family:Arial,Helvetica,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0f2f5;">
  <tr>
    <td align="center" style="padding:40px 16px 48px;">
      <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; width:100%;">
        <tr>
          <td style="background-color:#1a2a4a; border-radius:10px 10px 0 0; padding:22px 36px;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td><span style="font-size:20px; font-weight:700; color:#ffffff; letter-spacing:0.04em;">{website_title}</span></td>
                <td align="right"><span style="font-size:12px; color:#94a3b8; letter-spacing:0.05em; text-transform:uppercase;">Tender Order Notification</span></td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
      <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; width:100%; background:#ffffff; box-shadow:0 4px 32px rgba(0,0,0,0.10);">
        <tr>
          <td style="padding:44px 44px 32px; border-bottom:1px solid #f1f5f9;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td width="56" valign="top" style="padding-right:18px;">
                  <table cellpadding="0" cellspacing="0" border="0">
                    <tr>
                      <td width="56" height="56" align="center" valign="middle" style="width:56px; height:56px; background-color:#d97706; border-radius:14px; text-align:center; vertical-align:middle;">
                        <span style="font-size:26px; color:#ffffff; line-height:56px; display:block;">&#128337;</span>
                      </td>
                    </tr>
                  </table>
                </td>
                <td valign="middle">
                  <p style="margin:0 0 4px 0; font-size:11px; font-weight:700; color:#d97706; letter-spacing:0.09em; text-transform:uppercase;">Order Received</p>
                  <p style="margin:0; font-size:22px; font-weight:700; color:#0f172a; line-height:1.3;">We have received your order.</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td style="padding:32px 44px 0;">
            <p style="margin:0 0 6px 0; font-size:15px; color:#334155; line-height:1.7;">Hello, <strong style="color:#0f172a;">{customer_name}</strong></p>
            <p style="margin:0 0 28px 0; font-size:15px; color:#334155; line-height:1.7;">Thank you for your order. We have received your purchase request for <strong style="color:#0f172a;">{tender_name}</strong>. Your payment is currently <strong style="color:#d97706;">pending verification</strong>. Once your payment is confirmed, we will send you a secure download link.</p>
          </td>
        </tr>
        <tr>
          <td style="padding:0 44px 32px;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e2e8f0; border-radius:8px; overflow:hidden;">
              <tr>
                <td colspan="2" style="background-color:#f8fafc; padding:12px 16px; border-bottom:1px solid #e2e8f0;">
                  <p style="margin:0; font-size:12px; font-weight:700; color:#64748b; letter-spacing:0.07em; text-transform:uppercase;">Order Summary</p>
                </td>
              </tr>
              <tr>
                <td style="padding:12px 16px; font-size:13px; color:#64748b; border-bottom:1px solid #f1f5f9; width:40%;">Order Number</td>
                <td style="padding:12px 16px; font-size:13px; font-weight:700; color:#0f172a; border-bottom:1px solid #f1f5f9;">{order_number}</td>
              </tr>
              <tr>
                <td style="padding:12px 16px; font-size:13px; color:#64748b; border-bottom:1px solid #f1f5f9;">Tender</td>
                <td style="padding:12px 16px; font-size:13px; color:#334155; border-bottom:1px solid #f1f5f9;">{tender_name}</td>
              </tr>
              <tr>
                <td style="padding:12px 16px; font-size:13px; color:#64748b;">Name</td>
                <td style="padding:12px 16px; font-size:13px; color:#334155;">{customer_name}</td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td style="padding:0 44px;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr><td style="border-top:1px solid #e2e8f0; font-size:0; line-height:0;">&nbsp;</td></tr>
            </table>
          </td>
        </tr>
        <tr>
          <td style="padding:28px 44px 36px;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td style="background-color:#fefce8; border-left:4px solid #facc15; border-radius:0 6px 6px 0; padding:14px 18px;">
                  <p style="margin:0; font-size:13px; color:#713f12; line-height:1.6;"><strong>What&#39;s next?</strong> Our team will verify your payment. Once confirmed, you will receive a <strong>secure download link</strong> at this email address. If you have any questions, please contact us with your order number <strong>{order_number}</strong>.</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
      <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; width:100%; background:#1e293b; border-radius:0 0 10px 10px;">
        <tr>
          <td style="padding:24px 36px;">
            <p style="margin:0 0 2px 0; font-size:14px; font-weight:700; color:#f1f5f9;">{website_title}</p>
            <p style="margin:0; font-size:12px; color:#64748b;">This is an automated message. Please do not reply.</p>
          </td>
        </tr>
      </table>
      <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; width:100%; margin-top:20px;">
        <tr>
          <td align="center">
            <p style="margin:0; font-size:12px; color:#94a3b8; line-height:1.7;">&copy; {website_title}. All rights reserved.<br>You received this email because an order was placed using this email address for order <strong style="color:#64748b;">{order_number}</strong>.</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>
HTML;
    }

    private function tenderDownloadLinkBody(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>Your Secure Download Link</title>
</head>
<body style="margin:0; padding:0; background-color:#f0f2f5; font-family:Arial,Helvetica,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0f2f5;">
  <tr>
    <td align="center" style="padding:40px 16px 48px;">
      <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; width:100%;">
        <tr>
          <td style="background-color:#1a2a4a; border-radius:10px 10px 0 0; padding:22px 36px;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td><span style="font-size:20px; font-weight:700; color:#ffffff; letter-spacing:0.04em;">{website_title}</span></td>
                <td align="right"><span style="font-size:12px; color:#94a3b8; letter-spacing:0.05em; text-transform:uppercase;">Secure Document Delivery</span></td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
      <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; width:100%; background:#ffffff; box-shadow:0 4px 32px rgba(0,0,0,0.10);">
        <tr>
          <td style="padding:44px 44px 32px; border-bottom:1px solid #f1f5f9;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td width="56" valign="top" style="padding-right:18px;">
                  <table cellpadding="0" cellspacing="0" border="0">
                    <tr>
                      <td width="56" height="56" align="center" valign="middle" style="width:56px; height:56px; background-color:#16a34a; border-radius:14px; text-align:center; vertical-align:middle;">
                        <span style="font-size:26px; color:#ffffff; line-height:56px; display:block;">&#10003;</span>
                      </td>
                    </tr>
                  </table>
                </td>
                <td valign="middle">
                  <p style="margin:0 0 4px 0; font-size:11px; font-weight:700; color:#16a34a; letter-spacing:0.09em; text-transform:uppercase;">Order Confirmed</p>
                  <p style="margin:0; font-size:22px; font-weight:700; color:#0f172a; line-height:1.3;">Your files are ready to download.</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td style="padding:32px 44px 0;">
            <p style="margin:0 0 6px 0; font-size:15px; color:#334155; line-height:1.7;">Hello, <strong style="color:#0f172a;">{customer_name}</strong></p>
            <p style="margin:0 0 28px 0; font-size:15px; color:#334155; line-height:1.7;">Your purchase for order <strong style="color:#0f172a;">{order_number}</strong> has been verified. Click the button below to securely download your tender documents.</p>
          </td>
        </tr>
        <tr>
          <td style="padding:0 44px 32px;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td align="center" style="background-color:#2563eb; border-radius:8px;">
                  <a href="{download_url}" target="_blank" style="display:block; padding:17px 32px; font-size:16px; font-weight:700; color:#ffffff; text-decoration:none; border-radius:8px; text-align:center; background-color:#2563eb; letter-spacing:0.01em; line-height:1;">&#8659;&nbsp; Download Secure Files</a>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td style="padding:0 44px 36px;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td width="33%" align="center" style="padding:0 4px;">
                  <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                    <tr>
                      <td align="center" style="background-color:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:12px 8px;">
                        <p style="margin:0 0 3px 0; font-size:18px; color:#16a34a;">&#128274;</p>
                        <p style="margin:0 0 2px 0; font-size:12px; font-weight:700; color:#15803d;">Secured</p>
                        <p style="margin:0; font-size:11px; color:#166534;">End-to-end signed</p>
                      </td>
                    </tr>
                  </table>
                </td>
                <td width="33%" align="center" style="padding:0 4px;">
                  <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                    <tr>
                      <td align="center" style="background-color:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:12px 8px;">
                        <p style="margin:0 0 3px 0; font-size:18px;">&#128337;</p>
                        <p style="margin:0 0 2px 0; font-size:12px; font-weight:700; color:#1d4ed8;">24 Hours</p>
                        <p style="margin:0; font-size:11px; color:#1e40af;">Link expiry window</p>
                      </td>
                    </tr>
                  </table>
                </td>
                <td width="33%" align="center" style="padding:0 4px;">
                  <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                    <tr>
                      <td align="center" style="background-color:#faf5ff; border:1px solid #e9d5ff; border-radius:8px; padding:12px 8px;">
                        <p style="margin:0 0 3px 0; font-size:18px;">&#128190;</p>
                        <p style="margin:0 0 2px 0; font-size:12px; font-weight:700; color:#7c3aed;">{max_downloads} Opens</p>
                        <p style="margin:0; font-size:11px; color:#6d28d9;">Link opens allowed</p>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td style="padding:0 44px;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr><td style="border-top:1px solid #e2e8f0; font-size:0; line-height:0;">&nbsp;</td></tr>
            </table>
          </td>
        </tr>
        <tr>
          <td style="padding:28px 44px 32px;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td style="background-color:#fefce8; border-left:4px solid #facc15; border-radius:0 6px 6px 0; padding:14px 18px;">
                  <p style="margin:0; font-size:13px; color:#713f12; line-height:1.6;"><strong>Important:</strong> This link will automatically expire on <strong>{expires_at}</strong>, or after being opened <strong>{max_downloads} times</strong> — whichever comes first. Each time you open this link counts as one use. If you did not request this link, please ignore this email — no action is needed.</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td style="padding:0 44px 36px;">
            <p style="margin:0 0 6px 0; font-size:12px; color:#94a3b8;">If the button above doesn't work, copy and paste this link into your browser:</p>
            <p style="margin:0; font-size:11px; color:#2563eb; word-break:break-all;">{download_url}</p>
          </td>
        </tr>
      </table>
      <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; width:100%; background:#1e293b; border-radius:0 0 10px 10px;">
        <tr>
          <td style="padding:24px 36px;">
            <p style="margin:0 0 2px 0; font-size:14px; font-weight:700; color:#f1f5f9;">{website_title}</p>
            <p style="margin:0; font-size:12px; color:#64748b;">This is an automated message. Please do not reply.</p>
          </td>
        </tr>
      </table>
      <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; width:100%; margin-top:20px;">
        <tr>
          <td align="center">
            <p style="margin:0; font-size:12px; color:#94a3b8; line-height:1.7;">&copy; {website_title}. All rights reserved.<br>You received this email because a file recovery was requested for order <strong style="color:#64748b;">{order_number}</strong>.</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>
HTML;
    }
}
