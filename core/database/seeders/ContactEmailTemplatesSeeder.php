<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContactEmailTemplatesSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            // 1) Sent to the site owner (to_mail) when a visitor submits the
            //    Contact page form. Used by Front\FrontendController@sendmail.
            [
                'email_type'    => 'contact_admin_notify',
                'email_subject' => 'New Contact Message — {contact_subject}',
                'email_body'    => $this->contactAdminBody(),
            ],
            // 2) Sent back to the visitor as an auto-reply confirming receipt.
            [
                'email_type'    => 'contact_customer_confirm',
                'email_subject' => 'We\'ve received your message — {website_title}',
                'email_body'    => $this->contactCustomerBody(),
            ],
            // 3) Sent to the visitor when admin replies to their contact message from
            //    the admin panel. Used by Admin\ContactMessageController@reply.
            [
                'email_type'    => 'contact_admin_reply',
                'email_subject' => 'Re: {contact_subject} — {website_title}',
                'email_body'    => $this->contactAdminReplyBody(),
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
     * Shared responsive <style> block — identical to TenderEmailTemplatesSeeder's,
     * kept in sync so every automated email in the app shares the same mobile
     * behaviour and client resets.
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

    /** Opening chrome shared by every template: outer table + header bar + hero. */
    private function shellOpen(string $tag, string $tagColor, string $iconBg, string $icon, string $eyebrow, string $eyebrowColor, string $headline, string $heroBg, string $heroBorder): string
    {
        return <<<HTML
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#eef1f6;">
  <tr>
    <td align="center" style="padding:32px 14px 44px;">
      <!--[if mso]><table role="presentation" width="600" align="center" cellpadding="0" cellspacing="0" border="0"><tr><td><![endif]-->
      <table role="presentation" class="container" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; max-width:600px; margin:0 auto;">
        <tr>
          <td class="px" style="background-color:#0f1b30; border-radius:14px 14px 0 0; padding:22px 40px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td style="font-size:19px; font-weight:700; color:#ffffff; letter-spacing:0.03em;">{website_title}</td>
                <td align="right" class="hide-sm" style="font-size:11px; color:{$tagColor}; letter-spacing:0.14em; text-transform:uppercase; font-weight:700;">{$tag}</td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td class="px" style="background-color:{$heroBg}; padding:32px 40px 26px; border-bottom:1px solid {$heroBorder};">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td width="52" valign="middle" style="padding-right:16px;">
                  <div style="width:52px; height:52px; background-color:{$iconBg}; border-radius:13px; text-align:center; line-height:52px; font-size:26px; color:#ffffff;">{$icon}</div>
                </td>
                <td valign="middle">
                  <p style="margin:0 0 4px 0; font-size:11px; font-weight:700; color:{$eyebrowColor}; letter-spacing:0.1em; text-transform:uppercase;">{$eyebrow}</p>
                  <p class="h1" style="margin:0; font-size:23px; font-weight:800; color:#0f172a; line-height:1.25;">{$headline}</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
HTML;
    }

    /** Closing chrome shared by every template: footer + outer table close. */
    private function shellClose(): string
    {
        return <<<'HTML'
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

    // ── 1. CONTACT — admin notification — blue, paper-plane ────────────────────
    private function contactAdminBody(): string
    {
        $head  = $this->head('New Contact Message', 'A visitor just sent a message through the contact form.');
        $shell = $this->shellOpen(
            'Contact Form', '#60a5fa',
            '#2563eb', '&#9993;',
            'New Message', '#1d4ed8',
            'You have a new contact message.',
            '#eff6ff', '#dbeafe'
        );
        return $head . $shell . <<<'HTML'
        <tr>
          <td class="px" style="background-color:#ffffff; padding:30px 40px 4px;">
            <p style="margin:0 0 26px 0; font-size:15px; color:#475569; line-height:1.7;">Sent from the Contact page. Reply directly to the sender's email address below.</p>
          </td>
        </tr>
        <tr>
          <td class="px" style="background-color:#ffffff; padding:0 40px 28px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e8ecf3; border-radius:10px; overflow:hidden;">
              <tr>
                <td colspan="2" style="background-color:#f8fafc; padding:11px 18px; border-bottom:1px solid #e8ecf3;">
                  <span style="font-size:11px; font-weight:700; color:#64748b; letter-spacing:0.08em; text-transform:uppercase;">Message Details</span>
                </td>
              </tr>
              <tr>
                <td style="padding:12px 18px; font-size:13px; color:#94a3b8; border-bottom:1px solid #f1f5f9; width:32%;">Name</td>
                <td style="padding:12px 18px; font-size:13px; font-weight:700; color:#0f172a; border-bottom:1px solid #f1f5f9; word-break:break-word;">{customer_name}</td>
              </tr>
              <tr>
                <td style="padding:12px 18px; font-size:13px; color:#94a3b8; border-bottom:1px solid #f1f5f9;">Email</td>
                <td style="padding:12px 18px; font-size:13px; color:#334155; border-bottom:1px solid #f1f5f9; word-break:break-word;"><a href="mailto:{contact_email}" style="color:#2563eb;">{contact_email}</a></td>
              </tr>
              <tr>
                <td style="padding:12px 18px; font-size:13px; color:#94a3b8; border-bottom:1px solid #f1f5f9;">Subject</td>
                <td style="padding:12px 18px; font-size:13px; color:#334155; border-bottom:1px solid #f1f5f9; word-break:break-word;">{contact_subject}</td>
              </tr>
              <tr>
                <td colspan="2" style="padding:14px 18px 16px; font-size:13px; color:#334155; line-height:1.7; white-space:pre-line; word-break:break-word;">{contact_message}</td>
              </tr>
            </table>
          </td>
        </tr>
HTML
            . $this->shellClose();
    }

    // ── 2. CONTACT — customer confirmation — blue, checkmark ───────────────────
    private function contactCustomerBody(): string
    {
        $head  = $this->head('We\'ve Received Your Message', 'Thanks for reaching out — we\'ll get back to you shortly.');
        $shell = $this->shellOpen(
            'Contact Form', '#60a5fa',
            '#2563eb', '&#10003;',
            'Message Received', '#1d4ed8',
            'Thanks for getting in touch!',
            '#eff6ff', '#dbeafe'
        );
        return $head . $shell . <<<'HTML'
        <tr>
          <td class="px" style="background-color:#ffffff; padding:30px 40px 4px;">
            <p style="margin:0 0 6px 0; font-size:15px; color:#334155; line-height:1.7;">Hello, <strong style="color:#0f172a;">{customer_name}</strong></p>
            <p style="margin:0 0 26px 0; font-size:15px; color:#475569; line-height:1.7;">Thank you for contacting us. We've received your message and a member of our team will respond as soon as possible.</p>
          </td>
        </tr>
        <tr>
          <td class="px" style="background-color:#ffffff; padding:0 40px 28px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e8ecf3; border-radius:10px; overflow:hidden;">
              <tr>
                <td colspan="2" style="background-color:#f8fafc; padding:11px 18px; border-bottom:1px solid #e8ecf3;">
                  <span style="font-size:11px; font-weight:700; color:#64748b; letter-spacing:0.08em; text-transform:uppercase;">Your Message</span>
                </td>
              </tr>
              <tr>
                <td style="padding:12px 18px; font-size:13px; color:#94a3b8; border-bottom:1px solid #f1f5f9; width:32%;">Subject</td>
                <td style="padding:12px 18px; font-size:13px; font-weight:700; color:#0f172a; border-bottom:1px solid #f1f5f9; word-break:break-word;">{contact_subject}</td>
              </tr>
              <tr>
                <td colspan="2" style="padding:14px 18px 16px; font-size:13px; color:#334155; line-height:1.7; white-space:pre-line; word-break:break-word;">{contact_message}</td>
              </tr>
            </table>
          </td>
        </tr>
HTML
            . $this->shellClose();
    }

    // ── 3. CONTACT — admin reply — blue, reply-arrow ────────────────────────────
    private function contactAdminReplyBody(): string
    {
        $head  = $this->head('Reply To Your Message', 'Our team has replied to the message you sent us.');
        $shell = $this->shellOpen(
            'Contact Form', '#60a5fa',
            '#2563eb', '&#8617;',
            'New Reply', '#1d4ed8',
            'We\'ve replied to your message.',
            '#eff6ff', '#dbeafe'
        );
        return $head . $shell . <<<'HTML'
        <tr>
          <td class="px" style="background-color:#ffffff; padding:30px 40px 4px;">
            <p style="margin:0 0 6px 0; font-size:15px; color:#334155; line-height:1.7;">Hello, <strong style="color:#0f172a;">{customer_name}</strong></p>
            <p style="margin:0 0 26px 0; font-size:15px; color:#475569; line-height:1.7;">Thanks again for reaching out. Here's our reply:</p>
          </td>
        </tr>
        <tr>
          <td class="px" style="background-color:#ffffff; padding:0 40px 24px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e8ecf3; border-radius:10px; overflow:hidden;">
              <tr>
                <td style="background-color:#eff6ff; border-left:4px solid #2563eb; padding:14px 18px; font-size:13px; color:#334155; line-height:1.7; white-space:pre-line; word-break:break-word;">{admin_reply_message}</td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td class="px" style="background-color:#ffffff; padding:0 40px 28px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e8ecf3; border-radius:10px; overflow:hidden;">
              <tr>
                <td colspan="2" style="background-color:#f8fafc; padding:11px 18px; border-bottom:1px solid #e8ecf3;">
                  <span style="font-size:11px; font-weight:700; color:#64748b; letter-spacing:0.08em; text-transform:uppercase;">Your Original Message</span>
                </td>
              </tr>
              <tr>
                <td style="padding:12px 18px; font-size:13px; color:#94a3b8; border-bottom:1px solid #f1f5f9; width:32%;">Subject</td>
                <td style="padding:12px 18px; font-size:13px; font-weight:700; color:#0f172a; border-bottom:1px solid #f1f5f9; word-break:break-word;">{contact_subject}</td>
              </tr>
              <tr>
                <td colspan="2" style="padding:14px 18px 16px; font-size:13px; color:#334155; line-height:1.7; white-space:pre-line; word-break:break-word;">{contact_message}</td>
              </tr>
            </table>
          </td>
        </tr>
HTML
            . $this->shellClose();
    }
}
