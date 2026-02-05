# Engineering Site Handoff

## Edit content
- Update text directly in `public/index.html`, `public/services.html`, and `public/contact.html`.
- Keep headings in order (`h1` then `h2`) for SEO and readability.

## Update brand colors
- Open `public/assets/styles.css`.
- Replace the color variables under `:root` (`--primary`, `--accent`, etc.) with your brand colors.

## Update the contact email
- Update the email address in `public/contact.html` in the "Reach us directly" section.
- Update the recipient email in `api/send-mail.php` (`'to_email'`).

## Configure SMTP
- Open `api/send-mail.php` and replace the placeholder values:
  - `host`, `port`, `username`, `password`, `secure`, `from_email`, `from_name`.
- `secure` should be `tls` (typical for port 587) or `ssl` (port 465).

## Update sitemap domain
- Replace `https://example.com` in `public/sitemap.xml` with your live domain.

## Deployment notes
- Upload the `public` folder to your site root.
- Upload the `api` folder so `/api/send-mail.php` is reachable.
- Confirm PHP is enabled on your hosting plan.
