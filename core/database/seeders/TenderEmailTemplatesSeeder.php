<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenderEmailTemplatesSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            // 1) Sent when an order is placed, before payment is confirmed. Amber /
            //    "pending" identity with a next-steps timeline.
            [
                'email_type'    => 'tender_purchase',
                'email_subject' => 'Order Received — Tender Purchase',
                'email_body'    => $this->tenderPurchaseBody(),
            ],
            // 2) Sent automatically after payment is confirmed (order confirmation +
            //    single download button + receipt). Emerald / "confirmed" identity.
            //    Used by TenderPaymentHelper.
            [
                'email_type'    => 'tender_download_link',
                'email_subject' => 'Your Secure Download Link - Order {order_number}',
                'email_body'    => $this->tenderDownloadLinkBody(),
            ],
            // 3) Sent from the "Find My Files" recovery flow. Indigo / "vault" identity.
            //    Lists every recovered download link.
            [
                'email_type'    => 'tender_recovery_link',
                'email_subject' => 'Your Tender Download Links — File Recovery',
                'email_body'    => $this->tenderRecoveryLinkBody(),
            ],
            // 5) Sent when an online gateway payment fails/is cancelled/abandoned.
            //    Red / "incomplete" identity with a resume CTA. Used by
            //    TenderPaymentHelper::handleFailedPayment.
            [
                'email_type'    => 'tender_payment_incomplete',
                'email_subject' => 'Complete Your Payment — Order {order_number}',
                'email_body'    => $this->tenderPaymentIncompleteBody(),
            ],
            // 4) Sent from "Find My Files" Method 4 (Expired Link / Regenerate) —
            //    the verification code gating that method's email-only recovery
            //    before any link is issued. Same indigo identity as #3.
            [
                'email_type'    => 'tender_recovery_otp',
                'email_subject' => 'Your Verification Code — File Recovery',
                'email_body'    => $this->tenderRecoveryOtpBody(),
            ],
        ];

        // updateOrInsert so re-running repairs existing rows (subject + body).
        foreach ($templates as $template) {
            DB::table('email_templates')->updateOrInsert(
                ['email_type' => $template['email_type']],
                [
                    'email_subject' => $template['email_subject'],
                    'email_body'    => $template['email_body'],
                ]
            );
        }
    }

    /**
     * Shared responsive <style> block (media queries + client resets). Injected
     * into every template's <head> via the <!--STYLE--> marker. Kept in one place
     * so all three emails share identical mobile behaviour.
     */
    private function emailStyle(): string
    {
        return <<<'CSS'
<style type="text/css">
  body,table,td,a { -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; }
  table,td { mso-table-lspace:0pt; mso-table-rspace:0pt; }
  img { -ms-interpolation-mode:bicubic; border:0; height:auto; line-height:100%; outline:none; text-decoration:none; }
  a { text-decoration:none; }
  .container { width:600px; max-width:600px; }
  @media only screen and (max-width:620px) {
    .container { width:100% !important; }
    .px { padding-left:22px !important; padding-right:22px !important; }
    .pt { padding-top:26px !important; }
    .stack { display:block !important; width:100% !important; box-sizing:border-box !important; padding:5px 0 !important; }
    .h1 { font-size:20px !important; line-height:1.3 !important; }
    .hide-sm { display:none !important; }
  }
</style>
CSS;
    }

    private function head(string $title, string $preheader): string
    {
        $style = $this->emailStyle();
        return <<<HTML
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="color-scheme" content="light only">
<meta name="supported-color-schemes" content="light only">
<title>{$title}</title>
{$style}
</head>
<body style="margin:0; padding:0; background-color:#eef1f6; font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif; -webkit-font-smoothing:antialiased;">
<div style="display:none; max-height:0; overflow:hidden; opacity:0; mso-hide:all;">{$preheader}</div>
HTML;
    }

    // ── 1. ORDER RECEIVED (payment pending) — amber, timeline ──────────────────
    private function tenderPurchaseBody(): string
    {
        $head = $this->head('Order Received', 'We have received your order — payment is being verified.');
        return $head . <<<'HTML'
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#eef1f6;">
  <tr>
    <td align="center" style="padding:32px 14px 44px;">
      <!--[if mso]><table role="presentation" width="600" align="center" cellpadding="0" cellspacing="0" border="0"><tr><td><![endif]-->
      <table role="presentation" class="container" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; max-width:600px; margin:0 auto;">
        <!-- Header -->
        <tr>
          <td class="px" style="background-color:#0f1b30; border-radius:14px 14px 0 0; padding:22px 40px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td style="font-size:19px; font-weight:700; color:#ffffff; letter-spacing:0.03em;">{website_title}</td>
                <td align="right" class="hide-sm" style="font-size:11px; color:#f59e0b; letter-spacing:0.14em; text-transform:uppercase; font-weight:700;">Tender Order</td>
              </tr>
            </table>
          </td>
        </tr>
        <!-- Amber hero -->
        <tr>
          <td class="px" style="background-color:#fffaf0; padding:32px 40px 26px; border-bottom:1px solid #fdecc8;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td width="52" valign="middle" style="padding-right:16px;">
                  <div style="width:52px; height:52px; background-color:#f59e0b; border-radius:13px; text-align:center; line-height:52px; font-size:26px; color:#ffffff;">&#9203;</div>
                </td>
                <td valign="middle">
                  <p style="margin:0 0 4px 0; font-size:11px; font-weight:700; color:#b45309; letter-spacing:0.1em; text-transform:uppercase;">Payment Pending</p>
                  <p class="h1" style="margin:0; font-size:23px; font-weight:800; color:#0f172a; line-height:1.25;">We&#39;ve received your order.</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <!-- Body -->
        <tr>
          <td class="px" style="background-color:#ffffff; padding:30px 40px 4px;">
            <p style="margin:0 0 6px 0; font-size:15px; color:#334155; line-height:1.7;">Hello, <strong style="color:#0f172a;">{customer_name}</strong></p>
            <p style="margin:0 0 26px 0; font-size:15px; color:#475569; line-height:1.7;">Thank you for your order for <strong style="color:#0f172a;">{tender_name}</strong>. Your payment is currently <strong style="color:#b45309;">being verified</strong>. Once confirmed, we&#39;ll email your secure download link.</p>
          </td>
        </tr>
        <!-- Order summary -->
        <tr>
          <td class="px" style="background-color:#ffffff; padding:0 40px 28px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e8ecf3; border-radius:10px; overflow:hidden;">
              <tr>
                <td colspan="2" style="background-color:#f8fafc; padding:11px 18px; border-bottom:1px solid #e8ecf3;">
                  <span style="font-size:11px; font-weight:700; color:#64748b; letter-spacing:0.08em; text-transform:uppercase;">Order Summary</span>
                </td>
              </tr>
              <tr>
                <td style="padding:12px 18px; font-size:13px; color:#94a3b8; border-bottom:1px solid #f1f5f9; width:38%;">Order Number</td>
                <td style="padding:12px 18px; font-size:13px; font-weight:700; color:#0f172a; border-bottom:1px solid #f1f5f9; word-break:break-word;">{order_number}</td>
              </tr>
              <tr>
                <td style="padding:12px 18px; font-size:13px; color:#94a3b8; border-bottom:1px solid #f1f5f9;">Tender</td>
                <td style="padding:12px 18px; font-size:13px; color:#334155; border-bottom:1px solid #f1f5f9; word-break:break-word;">{tender_name}</td>
              </tr>
              <tr>
                <td style="padding:12px 18px; font-size:13px; color:#94a3b8;">Name</td>
                <td style="padding:12px 18px; font-size:13px; color:#334155; word-break:break-word;">{customer_name}</td>
              </tr>
            </table>
          </td>
        </tr>
        <!-- What happens next — numbered timeline (unique to this email) -->
        <tr>
          <td class="px" style="background-color:#ffffff; padding:4px 40px 8px;">
            <p style="margin:0 0 16px 0; font-size:12px; font-weight:700; color:#94a3b8; letter-spacing:0.08em; text-transform:uppercase;">What happens next</p>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td width="34" valign="top"><div style="width:26px; height:26px; background-color:#fef3c7; color:#b45309; border-radius:50%; text-align:center; line-height:26px; font-size:13px; font-weight:800;">1</div></td>
                <td valign="top" style="padding:0 0 16px 4px;"><p style="margin:0; font-size:14px; color:#334155; line-height:1.6;"><strong style="color:#0f172a;">Payment verification.</strong> Our team confirms your payment.</p></td>
              </tr>
              <tr>
                <td width="34" valign="top"><div style="width:26px; height:26px; background-color:#fef3c7; color:#b45309; border-radius:50%; text-align:center; line-height:26px; font-size:13px; font-weight:800;">2</div></td>
                <td valign="top" style="padding:0 0 16px 4px;"><p style="margin:0; font-size:14px; color:#334155; line-height:1.6;"><strong style="color:#0f172a;">Secure link issued.</strong> You receive a private download link by email.</p></td>
              </tr>
              <tr>
                <td width="34" valign="top"><div style="width:26px; height:26px; background-color:#dcfce7; color:#15803d; border-radius:50%; text-align:center; line-height:26px; font-size:13px; font-weight:800;">3</div></td>
                <td valign="top" style="padding:0 0 4px 4px;"><p style="margin:0; font-size:14px; color:#334155; line-height:1.6;"><strong style="color:#0f172a;">Download your files.</strong> Open the link and get your tender documents.</p></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td class="px" style="background-color:#ffffff; padding:22px 40px 34px; border-radius:0 0 0 0;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td style="background-color:#fffbeb; border-left:4px solid #f59e0b; border-radius:0 6px 6px 0; padding:14px 18px;">
                  <p style="margin:0; font-size:13px; color:#713f12; line-height:1.6;">Questions? Contact us and quote your order number <strong>{order_number}</strong>.</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <!-- Footer -->
        <tr>
          <td style="background-color:#0f1b30; border-radius:0 0 14px 14px; padding:22px 40px;">
            <p style="margin:0 0 2px 0; font-size:14px; font-weight:700; color:#f1f5f9;">{website_title}</p>
            <p style="margin:0; font-size:12px; color:#64748b;">Automated message — please do not reply.</p>
          </td>
        </tr>
      </table>
      <table role="presentation" class="container" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; max-width:600px; margin:14px auto 0;">
        <tr><td align="center"><p style="margin:0; font-size:11px; color:#94a3b8; line-height:1.7;">&copy; {website_title}. All rights reserved.</p></td></tr>
      </table>
      <!--[if mso]></td></tr></table><![endif]-->
    </td>
  </tr>
</table>
</body>
</html>
HTML;
    }

    // ── 2. ORDER CONFIRMED (files ready) — emerald, single CTA + receipt ───────
    private function tenderDownloadLinkBody(): string
    {
        $head = $this->head('Your Files Are Ready', 'Payment confirmed — your secure download link is ready.');
        return $head . <<<'HTML'
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#eef1f6;">
  <tr>
    <td align="center" style="padding:32px 14px 44px;">
      <!--[if mso]><table role="presentation" width="600" align="center" cellpadding="0" cellspacing="0" border="0"><tr><td><![endif]-->
      <table role="presentation" class="container" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; max-width:600px; margin:0 auto;">
        <!-- Emerald header -->
        <tr>
          <td class="px" style="background-color:#0b7a4b; border-radius:14px 14px 0 0; padding:22px 40px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td style="font-size:19px; font-weight:700; color:#ffffff; letter-spacing:0.03em;">{website_title}</td>
                <td align="right" class="hide-sm" style="font-size:11px; color:#a7f3d0; letter-spacing:0.14em; text-transform:uppercase; font-weight:700;">Secure Delivery</td>
              </tr>
            </table>
          </td>
        </tr>
        <!-- Hero -->
        <tr>
          <td class="px" style="background-color:#ffffff; padding:36px 40px 8px; text-align:center;">
            <div style="width:64px; height:64px; background-color:#16a34a; border-radius:16px; text-align:center; line-height:64px; font-size:32px; color:#ffffff; margin:0 auto 16px;">&#10003;</div>
            <p style="margin:0 0 6px 0; font-size:11px; font-weight:700; color:#16a34a; letter-spacing:0.12em; text-transform:uppercase;">Payment Confirmed</p>
            <p class="h1" style="margin:0; font-size:24px; font-weight:800; color:#0f172a; line-height:1.25;">Your files are ready to download.</p>
          </td>
        </tr>
        <tr>
          <td class="px" style="background-color:#ffffff; padding:20px 40px 0; text-align:center;">
            <p style="margin:0 0 24px 0; font-size:15px; color:#475569; line-height:1.7;">Hello <strong style="color:#0f172a;">{customer_name}</strong> — your purchase for order <strong style="color:#0f172a; word-break:break-word;">{order_number}</strong> is verified. Use the button below to securely download your tender documents.</p>
          </td>
        </tr>
        <!-- Single CTA -->
        <tr>
          <td class="px" style="background-color:#ffffff; padding:0 40px 26px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td align="center" style="background-color:#16a34a; border-radius:9px;">
                  <a href="{download_url}" target="_blank" style="display:block; padding:17px 30px; font-size:16px; font-weight:700; color:#ffffff; text-decoration:none; border-radius:9px; text-align:center; letter-spacing:0.01em;">&#8659;&nbsp; Download Secure Files</a>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <!-- Two info tiles (stack on mobile) -->
        <tr>
          <td class="px" style="background-color:#ffffff; padding:0 40px 26px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td class="stack" width="50%" valign="top" style="padding-right:6px;">
                  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
                    <td align="center" style="background-color:#eff6ff; border:1px solid #bfdbfe; border-radius:9px; padding:14px 8px;">
                      <p style="margin:0 0 2px 0; font-size:18px;">&#128337;</p>
                      <p style="margin:0 0 2px 0; font-size:12px; font-weight:700; color:#1d4ed8;">Expires</p>
                      <p style="margin:0; font-size:11px; color:#1e40af;">{expires_at}</p>
                    </td>
                  </tr></table>
                </td>
                <td class="stack" width="50%" valign="top" style="padding-left:6px;">
                  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
                    <td align="center" style="background-color:#faf5ff; border:1px solid #e9d5ff; border-radius:9px; padding:14px 8px;">
                      <p style="margin:0 0 2px 0; font-size:18px;">&#128190;</p>
                      <p style="margin:0 0 2px 0; font-size:12px; font-weight:700; color:#7c3aed;">{max_downloads} Opens</p>
                      <p style="margin:0; font-size:11px; color:#6d28d9;">Link opens allowed</p>
                    </td>
                  </tr></table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <!-- Receipt note (unique to this email) -->
        <tr>
          <td class="px" style="background-color:#ffffff; padding:0 40px 30px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td style="background-color:#f0fdf4; border:1px solid #bbf7d0; border-radius:9px; padding:14px 18px;">
                  <p style="margin:0; font-size:13px; color:#15803d; line-height:1.6;">&#128206; <strong>Your payment receipt (PDF)</strong> is attached to this email for your records.</p>
                </td>
              </tr>
            </table>
            <p style="margin:16px 0 0 0; font-size:12px; color:#94a3b8; line-height:1.6;">Button not working? Copy this link:<br><span style="color:#2563eb; word-break:break-all;">{download_url}</span></p>
          </td>
        </tr>
        <!-- Footer -->
        <tr>
          <td style="background-color:#0f1b30; border-radius:0 0 14px 14px; padding:22px 40px;">
            <p style="margin:0 0 2px 0; font-size:14px; font-weight:700; color:#f1f5f9;">{website_title}</p>
            <p style="margin:0; font-size:12px; color:#64748b;">This link expires on {expires_at} or after {max_downloads} opens. Automated message — please do not reply.</p>
          </td>
        </tr>
      </table>
      <table role="presentation" class="container" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; max-width:600px; margin:14px auto 0;">
        <tr><td align="center"><p style="margin:0; font-size:11px; color:#94a3b8; line-height:1.7;">&copy; {website_title}. All rights reserved. &middot; Order {order_number}</p></td></tr>
      </table>
      <!--[if mso]></td></tr></table><![endif]-->
    </td>
  </tr>
</table>
</body>
</html>
HTML;
    }

    // ── 2b. PAYMENT INCOMPLETE — red, single "Complete Payment" CTA ─────────────
    private function tenderPaymentIncompleteBody(): string
    {
        $head = $this->head('Complete Your Payment', 'Your payment did not go through — pick up right where you left off.');
        return $head . <<<'HTML'
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#eef1f6;">
  <tr>
    <td align="center" style="padding:32px 14px 44px;">
      <!--[if mso]><table role="presentation" width="600" align="center" cellpadding="0" cellspacing="0" border="0"><tr><td><![endif]-->
      <table role="presentation" class="container" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; max-width:600px; margin:0 auto;">
        <!-- Red header -->
        <tr>
          <td class="px" style="background-color:#0f1b30; border-radius:14px 14px 0 0; padding:22px 40px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td style="font-size:19px; font-weight:700; color:#ffffff; letter-spacing:0.03em;">{website_title}</td>
                <td align="right" class="hide-sm" style="font-size:11px; color:#fca5a5; letter-spacing:0.14em; text-transform:uppercase; font-weight:700;">Payment Incomplete</td>
              </tr>
            </table>
          </td>
        </tr>
        <!-- Hero -->
        <tr>
          <td class="px" style="background-color:#fef2f2; padding:32px 40px 26px; border-bottom:1px solid #fecaca;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td width="52" valign="middle" style="padding-right:16px;">
                  <div style="width:52px; height:52px; background-color:#dc2626; border-radius:13px; text-align:center; line-height:52px; font-size:26px; color:#ffffff;">&#33;</div>
                </td>
                <td valign="middle">
                  <p style="margin:0 0 4px 0; font-size:11px; font-weight:700; color:#b91c1c; letter-spacing:0.1em; text-transform:uppercase;">Payment Not Completed</p>
                  <p class="h1" style="margin:0; font-size:23px; font-weight:800; color:#0f172a; line-height:1.25;">Your order is still waiting on payment.</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td class="px" style="background-color:#ffffff; padding:30px 40px 0;">
            <p style="margin:0 0 6px 0; font-size:15px; color:#334155; line-height:1.7;">Hello, <strong style="color:#0f172a;">{customer_name}</strong></p>
            <p style="margin:0 0 24px 0; font-size:15px; color:#475569; line-height:1.7;">We noticed your payment for <strong style="color:#0f172a;">{tender_name}</strong> (order <strong style="color:#0f172a;">{order_number}</strong>) didn&#39;t go through. No charge was made. Use the button below to pick up right where you left off — your details and module selection are already saved.</p>
          </td>
        </tr>
        <!-- Single CTA -->
        <tr>
          <td class="px" style="background-color:#ffffff; padding:0 40px 30px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td align="center" style="background-color:#dc2626; border-radius:9px;">
                  <a href="{resume_url}" target="_blank" style="display:block; padding:17px 30px; font-size:16px; font-weight:700; color:#ffffff; text-decoration:none; border-radius:9px; text-align:center; letter-spacing:0.01em;">Complete Your Payment</a>
                </td>
              </tr>
            </table>
            <p style="margin:16px 0 0 0; font-size:12px; color:#94a3b8; line-height:1.6;">Button not working? Copy this link:<br><span style="color:#2563eb; word-break:break-all;">{resume_url}</span></p>
          </td>
        </tr>
        <tr>
          <td class="px" style="background-color:#ffffff; padding:0 40px 34px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td style="background-color:#fef2f2; border-left:4px solid #dc2626; border-radius:0 6px 6px 0; padding:14px 18px;">
                  <p style="margin:0; font-size:13px; color:#7f1d1d; line-height:1.6;">Already paid another way, or have questions? Contact us and quote your order number <strong>{order_number}</strong>.</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <!-- Footer -->
        <tr>
          <td style="background-color:#0f1b30; border-radius:0 0 14px 14px; padding:22px 40px;">
            <p style="margin:0 0 2px 0; font-size:14px; font-weight:700; color:#f1f5f9;">{website_title}</p>
            <p style="margin:0; font-size:12px; color:#64748b;">Automated message — please do not reply.</p>
          </td>
        </tr>
      </table>
      <table role="presentation" class="container" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; max-width:600px; margin:14px auto 0;">
        <tr><td align="center"><p style="margin:0; font-size:11px; color:#94a3b8; line-height:1.7;">&copy; {website_title}. All rights reserved. &middot; Order {order_number}</p></td></tr>
      </table>
      <!--[if mso]></td></tr></table><![endif]-->
    </td>
  </tr>
</table>
</body>
</html>
HTML;
    }

    // ── 3. FILE RECOVERY — indigo, shield/vault, multi-link + security tiles ───
    private function tenderRecoveryLinkBody(): string
    {
        $head = $this->head('Your Download Links', 'Identity verified — your tender download links are ready.');
        return $head . <<<'HTML'
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#eef1f6;">
  <tr>
    <td align="center" style="padding:32px 14px 44px;">
      <!--[if mso]><table role="presentation" width="600" align="center" cellpadding="0" cellspacing="0" border="0"><tr><td><![endif]-->
      <table role="presentation" class="container" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; max-width:600px; margin:0 auto;">
        <!-- Indigo header -->
        <tr>
          <td class="px" style="background-color:#312e81; border-radius:14px 14px 0 0; padding:22px 40px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td style="font-size:19px; font-weight:700; color:#ffffff; letter-spacing:0.03em;">{website_title}</td>
                <td align="right" class="hide-sm" style="font-size:11px; color:#c7d2fe; letter-spacing:0.14em; text-transform:uppercase; font-weight:700;">File Recovery</td>
              </tr>
            </table>
          </td>
        </tr>
        <!-- Hero: shield -->
        <tr>
          <td class="px" style="background-color:#eef2ff; padding:32px 40px 26px; border-bottom:1px solid #e0e7ff;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td width="52" valign="middle" style="padding-right:16px;">
                  <div style="width:52px; height:52px; background-color:#4f46e5; border-radius:13px; text-align:center; line-height:52px; font-size:26px; color:#ffffff;">&#128737;</div>
                </td>
                <td valign="middle">
                  <p style="margin:0 0 4px 0; font-size:11px; font-weight:700; color:#4338ca; letter-spacing:0.1em; text-transform:uppercase;">Identity Verified</p>
                  <p class="h1" style="margin:0; font-size:23px; font-weight:800; color:#0f172a; line-height:1.25;">Your download links are ready.</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td class="px" style="background-color:#ffffff; padding:30px 40px 6px;">
            <p style="margin:0 0 6px 0; font-size:15px; color:#334155; line-height:1.7;">Hello,</p>
            <p style="margin:0 0 20px 0; font-size:15px; color:#475569; line-height:1.7;">Here are the secure download links for every tender purchased with <strong style="color:#0f172a;">this email address and phone number</strong>. Each link is labelled with the buyer name, company and order number that placed it. These links are private to you — please don&#39;t share them.</p>
          </td>
        </tr>
        <!-- The per-tender cards -->
        <tr>
          <td class="px" style="background-color:#ffffff; padding:0 40px 14px;">
            {download_list}
          </td>
        </tr>
        <!-- Security tiles (stack on mobile) -->
        <tr>
          <td class="px" style="background-color:#ffffff; padding:8px 40px 26px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td class="stack" width="33%" valign="top" style="padding-right:5px;">
                  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
                    <td align="center" style="background-color:#f0fdf4; border:1px solid #bbf7d0; border-radius:9px; padding:13px 6px;">
                      <p style="margin:0 0 2px 0; font-size:17px;">&#128274;</p>
                      <p style="margin:0 0 1px 0; font-size:12px; font-weight:700; color:#15803d;">Secured</p>
                      <p style="margin:0; font-size:11px; color:#166534;">Signed link</p>
                    </td>
                  </tr></table>
                </td>
                <td class="stack" width="33%" valign="top" style="padding:0 5px;">
                  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
                    <td align="center" style="background-color:#eef2ff; border:1px solid #c7d2fe; border-radius:9px; padding:13px 6px;">
                      <p style="margin:0 0 2px 0; font-size:17px;">&#128337;</p>
                      <p style="margin:0 0 1px 0; font-size:12px; font-weight:700; color:#4338ca;">24 Hours</p>
                      <p style="margin:0; font-size:11px; color:#3730a3;">Expiry window</p>
                    </td>
                  </tr></table>
                </td>
                <td class="stack" width="33%" valign="top" style="padding-left:5px;">
                  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
                    <td align="center" style="background-color:#faf5ff; border:1px solid #e9d5ff; border-radius:9px; padding:13px 6px;">
                      <p style="margin:0 0 2px 0; font-size:17px;">&#128190;</p>
                      <p style="margin:0 0 1px 0; font-size:12px; font-weight:700; color:#7c3aed;">{max_downloads} Opens</p>
                      <p style="margin:0; font-size:11px; color:#6d28d9;">Per link</p>
                    </td>
                  </tr></table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td class="px" style="background-color:#ffffff; padding:0 40px 32px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td style="background-color:#eef2ff; border-left:4px solid #4f46e5; border-radius:0 6px 6px 0; padding:14px 18px;">
                  <p style="margin:0; font-size:13px; color:#3730a3; line-height:1.6;"><strong>Important:</strong> Each link expires on <strong>{expires_at}</strong>, or after being opened <strong>{max_downloads} times</strong> — whichever comes first. If you didn&#39;t request this, ignore this email.</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <!-- Footer -->
        <tr>
          <td style="background-color:#1e1b4b; border-radius:0 0 14px 14px; padding:22px 40px;">
            <p style="margin:0 0 2px 0; font-size:14px; font-weight:700; color:#f1f5f9;">{website_title}</p>
            <p style="margin:0; font-size:12px; color:#818cf8;">Automated message — please do not reply.</p>
          </td>
        </tr>
      </table>
      <table role="presentation" class="container" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; max-width:600px; margin:14px auto 0;">
        <tr><td align="center"><p style="margin:0; font-size:11px; color:#94a3b8; line-height:1.7;">&copy; {website_title}. All rights reserved. &middot; File recovery request.</p></td></tr>
      </table>
      <!--[if mso]></td></tr></table><![endif]-->
    </td>
  </tr>
</table>
</body>
</html>
HTML;
    }

    // ── 4. RECOVERY OTP — indigo, verification code ─────────────────────────────
    private function tenderRecoveryOtpBody(): string
    {
        $head = $this->head('Your Verification Code', 'Enter this code to continue recovering your tender files.');
        return $head . <<<'HTML'
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#eef1f6;">
  <tr>
    <td align="center" style="padding:32px 14px 44px;">
      <!--[if mso]><table role="presentation" width="600" align="center" cellpadding="0" cellspacing="0" border="0"><tr><td><![endif]-->
      <table role="presentation" class="container" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; max-width:600px; margin:0 auto;">
        <tr>
          <td class="px" style="background-color:#312e81; border-radius:14px 14px 0 0; padding:22px 40px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td style="font-size:19px; font-weight:700; color:#ffffff; letter-spacing:0.03em;">{website_title}</td>
                <td align="right" class="hide-sm" style="font-size:11px; color:#c7d2fe; letter-spacing:0.14em; text-transform:uppercase; font-weight:700;">File Recovery</td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td class="px" style="background-color:#eef2ff; padding:32px 40px 26px; border-bottom:1px solid #e0e7ff;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td width="52" valign="middle" style="padding-right:16px;">
                  <div style="width:52px; height:52px; background-color:#4f46e5; border-radius:13px; text-align:center; line-height:52px; font-size:26px; color:#ffffff;">&#128274;</div>
                </td>
                <td valign="middle">
                  <p style="margin:0 0 4px 0; font-size:11px; font-weight:700; color:#4338ca; letter-spacing:0.1em; text-transform:uppercase;">Verify It's You</p>
                  <p class="h1" style="margin:0; font-size:23px; font-weight:800; color:#0f172a; line-height:1.25;">Enter this code to continue.</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td class="px" style="background-color:#ffffff; padding:30px 40px 6px;">
            <p style="margin:0 0 6px 0; font-size:15px; color:#334155; line-height:1.7;">Hello,</p>
            <p style="margin:0 0 24px 0; font-size:15px; color:#475569; line-height:1.7;">Someone requested a file-recovery link for this email address. Enter the code below on the Find My Files page to continue. If this wasn&#39;t you, you can safely ignore this email — no link will be issued without this code.</p>
          </td>
        </tr>
        <tr>
          <td class="px" style="background-color:#ffffff; padding:0 40px 30px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e0e7ff; border-radius:10px; overflow:hidden;">
              <tr>
                <td align="center" style="background-color:#eef2ff; padding:26px 18px;">
                  <p style="margin:0 0 8px 0; font-size:11px; font-weight:700; color:#4338ca; letter-spacing:0.14em; text-transform:uppercase;">Verification Code</p>
                  <p style="margin:0; font-size:36px; font-weight:800; color:#312e81; letter-spacing:0.28em; font-family:monospace;">{otp_code}</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td class="px" style="background-color:#ffffff; padding:0 40px 32px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td style="background-color:#eef2ff; border-left:4px solid #4f46e5; border-radius:0 6px 6px 0; padding:14px 18px;">
                  <p style="margin:0; font-size:13px; color:#3730a3; line-height:1.6;">This code expires in <strong>{otp_ttl_minutes} minutes</strong> and can only be used once. Never share it with anyone.</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td style="background-color:#1e1b4b; border-radius:0 0 14px 14px; padding:22px 40px;">
            <p style="margin:0 0 2px 0; font-size:14px; font-weight:700; color:#f1f5f9;">{website_title}</p>
            <p style="margin:0; font-size:12px; color:#818cf8;">Automated message — please do not reply.</p>
          </td>
        </tr>
      </table>
      <table role="presentation" class="container" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; max-width:600px; margin:14px auto 0;">
        <tr><td align="center"><p style="margin:0; font-size:11px; color:#94a3b8; line-height:1.7;">&copy; {website_title}. All rights reserved. &middot; File recovery request.</p></td></tr>
      </table>
      <!--[if mso]></td></tr></table><![endif]-->
    </td>
  </tr>
</table>
</body>
</html>
HTML;
    }
}
