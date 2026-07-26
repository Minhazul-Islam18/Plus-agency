<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminEmailTemplatesSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            // Sent when the super admin creates a new admin account. Contains a
            // one-time activation link (no password is set until the recipient
            // clicks through), per client spec — link, not a mailed password.
            [
                'email_type'    => 'admin_account_activation',
                'email_subject' => 'Welcome to the Administration Panel',
                'email_body'    => $this->activationBody(),
            ],
            // Sent from the admin "forgot password" flow. Contains a temporary
            // password valid for a limited window; the admin is forced to set a
            // new password on login before reaching the dashboard.
            [
                'email_type'    => 'admin_temp_password',
                'email_subject' => 'Your Temporary Administrator Password',
                'email_body'    => $this->tempPasswordBody(),
            ],
        ];

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

    private function emailStyle(): string
    {
        return <<<'CSS'
<style type="text/css">
  body,table,td,a { -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; }
  table,td { mso-table-lspace:0pt; mso-table-rspace:0pt; }
  a { text-decoration:none; }
  .container { width:600px; max-width:600px; }
  @media only screen and (max-width:620px) {
    .container { width:100% !important; }
    .px { padding-left:22px !important; padding-right:22px !important; }
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
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="color-scheme" content="light only">
<title>{$title}</title>
{$style}
</head>
<body style="margin:0; padding:0; background-color:#eef1f6; font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif; -webkit-font-smoothing:antialiased;">
<div style="display:none; max-height:0; overflow:hidden; opacity:0; mso-hide:all;">{$preheader}</div>
HTML;
    }

    private function activationBody(): string
    {
        $head = $this->head('Welcome to the Administration Panel', 'Activate your administrator account.');
        return $head . <<<'HTML'
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#eef1f6;">
  <tr>
    <td align="center" style="padding:32px 14px 44px;">
      <!--[if mso]><table role="presentation" width="600" align="center" cellpadding="0" cellspacing="0" border="0"><tr><td><![endif]-->
      <table role="presentation" class="container" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; max-width:600px; margin:0 auto;">
        <!-- Header: logo + badge -->
        <tr>
          <td class="px" style="background-color:#0f1b30; border-radius:14px 14px 0 0; padding:20px 40px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td width="1" valign="middle"><img src="cid:applogo" alt="{website_title}" width="72" height="28" style="height:28px; max-height:28px; width:72px; display:block; border:0;"></td>
                <td align="right" valign="middle" width="100%" style="font-size:16px; font-weight:700; color:#ffffff; white-space:nowrap;">{website_title}</td>
              </tr>
            </table>
          </td>
        </tr>
        <!-- Indigo hero -->
        <tr>
          <td class="px" style="background-color:#eef2ff; padding:32px 40px 26px; border-bottom:1px solid #e0e7ff;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td width="52" valign="middle" style="padding-right:16px;">
                  <div style="width:52px; height:52px; background-color:#4f46e5; border-radius:13px; text-align:center; line-height:52px; font-size:26px; color:#ffffff;">&#128273;</div>
                </td>
                <td valign="middle">
                  <p style="margin:0 0 4px 0; font-size:11px; font-weight:700; color:#4338ca; letter-spacing:0.1em; text-transform:uppercase;">Account Created</p>
                  <p class="h1" style="margin:0; font-size:23px; font-weight:800; color:#0f172a; line-height:1.25;">Welcome to the team, {admin_name}!</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <!-- Body -->
        <tr>
          <td class="px" style="background-color:#ffffff; padding:30px 40px 4px;">
            <p style="margin:0 0 26px 0; font-size:15px; color:#475569; line-height:1.7;">An administrator account on <strong style="color:#0f172a;">{website_title}</strong> has been created for you. For security, no password has been set yet — click the button below to choose your own password and activate your account.</p>
          </td>
        </tr>
        <!-- CTA -->
        <tr>
          <td class="px" style="background-color:#ffffff; padding:0 40px 30px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td align="center" style="background-color:#4f46e5; border-radius:9px;">
                  <a href="{activation_link}" target="_blank" style="display:block; padding:17px 30px; font-size:16px; font-weight:700; color:#ffffff; text-decoration:none; border-radius:9px; text-align:center; letter-spacing:0.01em;">&#128273;&nbsp; Activate my account</a>
                </td>
              </tr>
            </table>
            <p style="margin:16px 0 0 0; font-size:12px; color:#94a3b8; line-height:1.6;">Button not working? Copy this link:<br><span style="color:#4f46e5; word-break:break-all;">{activation_link}</span></p>
          </td>
        </tr>
        <!-- Security note -->
        <tr>
          <td class="px" style="background-color:#ffffff; padding:0 40px 34px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td style="background-color:#eef2ff; border-left:4px solid #4f46e5; border-radius:0 6px 6px 0; padding:14px 18px;">
                  <p style="margin:0; font-size:13px; color:#3730a3; line-height:1.6;"><strong>This link expires in 24 hours</strong> and can only be used once. If you did not expect this invitation, you can safely ignore this email.</p>
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

    private function tempPasswordBody(): string
    {
        $head = $this->head('Your Temporary Administrator Password', 'A temporary password has been issued for your administrator account.');
        return $head . <<<'HTML'
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#eef1f6;">
  <tr>
    <td align="center" style="padding:32px 14px 44px;">
      <table role="presentation" class="container" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%; max-width:600px; margin:0 auto;">
        <tr>
          <td class="px" style="background-color:#0f1b30; border-radius:14px 14px 0 0; padding:20px 40px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td width="1" valign="middle"><img src="cid:applogo" alt="{website_title}" width="72" height="28" style="height:28px; max-height:28px; width:72px; display:block; border:0;"></td>
                <td align="right" valign="middle" width="100%" style="font-size:16px; font-weight:700; color:#ffffff; white-space:nowrap;">{website_title}</td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td class="px" style="background-color:#ffffff; padding:36px 40px 8px;">
            <p style="margin:0 0 6px 0; font-size:15px; color:#334155; line-height:1.7;">Dear <strong style="color:#0f172a;">{admin_name}</strong>,</p>
            <p style="margin:0 0 20px 0; font-size:15px; color:#475569; line-height:1.7;">A password reset request has been processed for your administrator account.</p>
            <p style="margin:0 0 6px 0; font-size:13px; color:#94a3b8;">Your temporary login credentials are:</p>
          </td>
        </tr>
        <tr>
          <td class="px" style="background-color:#ffffff; padding:0 40px 22px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e8ecf3; border-radius:10px; overflow:hidden;">
              <tr>
                <td style="padding:12px 18px; font-size:13px; color:#94a3b8; border-bottom:1px solid #f1f5f9; width:38%;">Username / Email</td>
                <td style="padding:12px 18px; font-size:13px; font-weight:700; color:#0f172a; border-bottom:1px solid #f1f5f9; word-break:break-word;">{admin_email}</td>
              </tr>
              <tr>
                <td style="padding:12px 18px; font-size:13px; color:#94a3b8;">Temporary Password</td>
                <td style="padding:12px 18px; font-size:15px; font-weight:800; color:#0f172a; font-family:monospace; letter-spacing:0.04em;">{temporary_password}</td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td class="px" style="background-color:#ffffff; padding:0 40px 20px;">
            <p style="margin:0 0 10px 0; font-size:14px; color:#334155; line-height:1.7;">For your security, this temporary password is valid until <strong>{expiry_date_time}</strong> and can only be used once.</p>
            <p style="margin:0 0 10px 0; font-size:14px; color:#334155; line-height:1.7;">After signing in, you will be required to create a new password before accessing the Admin Panel.</p>
            <p style="margin:0; font-size:14px; color:#334155; line-height:1.7;">Login Page: <a href="{login_url}" style="color:#1572E8;">{login_url}</a></p>
          </td>
        </tr>
        <tr>
          <td class="px" style="background-color:#ffffff; padding:0 40px 34px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td style="background-color:#fffbeb; border-left:4px solid #f59e0b; border-radius:0 6px 6px 0; padding:14px 18px;">
                  <p style="margin:0 0 6px 0; font-size:13px; color:#713f12; line-height:1.6;">If you did not request this password reset, please contact the Super Administrator immediately or ignore this email if your account has not been accessed.</p>
                  <p style="margin:0; font-size:13px; color:#713f12; line-height:1.6;">For security reasons, never share your password with anyone.</p>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td style="background-color:#0f1b30; border-radius:0 0 14px 14px; padding:22px 40px;">
            <p style="margin:0 0 2px 0; font-size:14px; font-weight:700; color:#f1f5f9;">{website_title} System Security Team</p>
            <p style="margin:0; font-size:12px; color:#64748b;">This is an automated message. Please do not reply.</p>
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
