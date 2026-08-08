<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NewsletterEmailTemplatesSeeder extends Seeder
{
    public function run()
    {
        DB::table('email_templates')->updateOrInsert(
            ['email_type' => 'newsletter'],
            [
                'email_subject' => '{newsletter_subject}',
                'email_body'    => $this->body(),
            ]
        );
    }

    /** Shared responsive <style> block — same as the other seeders' templates. */
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
    .h1 { font-size:20px !important; line-height:1.3 !important; }
    .hide-sm { display:none !important; }
  }
</style>
CSS;
    }

    private function head(): string
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
<title>{newsletter_subject}</title>
{$style}
</head>
<body style="margin:0; padding:0; background-color:#eef1f6; font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif; -webkit-font-smoothing:antialiased;">
<div style="display:none; max-height:0; overflow:hidden; opacity:0; mso-hide:all;">{newsletter_subject}</div>
HTML;
    }

    private function body(): string
    {
        $head = $this->head();

        return $head . <<<'HTML'
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
                <td align="right" class="hide-sm" style="font-size:11px; color:#4ade80; letter-spacing:0.14em; text-transform:uppercase; font-weight:700;">Newsletter</td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td class="px" style="background-color:#f0fdf4; padding:32px 40px 26px; border-bottom:1px solid #dcfce7;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td width="52" valign="middle" style="padding-right:16px;">
                  <div style="width:52px; height:52px; background-color:#16a34a; border-radius:13px; text-align:center; line-height:52px; font-size:26px; color:#ffffff;">&#128226;</div>
                </td>
                <td valign="middle">
                  <p style="margin:0 0 4px 0; font-size:11px; font-weight:700; color:#15803d; letter-spacing:0.1em; text-transform:uppercase;">Newsletter</p>
                  <p class="h1" style="margin:0; font-size:23px; font-weight:800; color:#0f172a; line-height:1.25;">{newsletter_subject}</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td class="px" style="background-color:#ffffff; padding:30px 40px 34px;">
            <div style="font-size:15px; color:#334155; line-height:1.7; word-break:break-word;">{newsletter_content}</div>
          </td>
        </tr>
        <tr>
          <td class="px" style="background-color:#ffffff; padding:0 40px 30px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-top:1px solid #e8ecf3;">
              <tr>
                <td style="padding:18px 0 0;">
                  <p style="margin:0; font-size:12px; color:#94a3b8; line-height:1.7;">
                    Don't want these emails? <a href="{unsubscribe_link}" style="color:#16a34a; font-weight:600;">Unsubscribe</a>
                  </p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td style="background-color:#0f1b30; border-radius:0 0 14px 14px; padding:22px 40px;">
            <p style="margin:0 0 2px 0; font-size:14px; font-weight:700; color:#f1f5f9;">{website_title}</p>
            <p style="margin:0; font-size:12px; color:#64748b;">This is an automated newsletter — please do not reply.</p>
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
}
