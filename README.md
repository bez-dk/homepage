# homepage
Homepage for the Bez Domain

A small PHP site with no frameworks or build step, made for one.com hosting.

## Structure

```
public/                    Everything inside this folder goes on the web server
  index.php                Home page (hero, tool tiles, explore cards)
  tools.php                All tools, grouped by category
  guides.php               Guide categories
  projects.php             Projects
  about.php                About BEZ.dk
  contact.php              Contact form
  404.php                  "Page not found" page
  .htaccess                HTTPS + www redirect, clean URLs, security headers, caching
  robots.txt
  favicon.ico, apple-touch-icon.png   Browser and phone home-screen icons
  assets/css/style.css     Styling with light and dark theme
  assets/js/main.js        Mobile menu, language dropdown, theme toggle
  assets/img/logo.svg      The "B" logo (also used as favicon)
  assets/img/flags/        Language flags
  includes/                Shared code, blocked from the web by .htaccess
    config.php             Site name, email addresses, navigation
    content.php            The list of tools and guides (Danish + English)
    i18n.php               Picks Danish or English and provides t() for texts
    icons.php              SVG icons (Lucide)
    lang/da.php, en.php    Page texts in Danish and English
    header.php, footer.php Shared top bar and footer
    contact-handler.php    Validates the contact form and sends the email
scripts/make_icons.py      Regenerates the PNG/ICO icons from the logo (not uploaded)
router.php                 Makes clean URLs work when previewing locally (not uploaded)
archive/                   Old site and design ideas, local only (ignored by git)
README.md, LICENSE         Repo files only, not uploaded
```

### Adding a tool or guide

Tools and guides are listed in `public/includes/content.php`. Until an entry has a `'url'`, it is shown with a "Kommer snart / Coming soon" badge. Add `'url' => '/tools/...'` when the page exists and it becomes a link.

## Getting started

1. Open `public/includes/config.php` and set your site name and email addresses.
   - `contact_from` must be an address on your own domain (for example `noreply@yourdomain.com`), or one.com may reject the mail.
2. Change the page texts in `public/includes/lang/da.php` (Danish) and `public/includes/lang/en.php` (English).

### Adding a new page

Copy `public/about.php` to e.g. `public/blog.php`, change `$pageTitle` and the content, and add `'/blog' => 'nav.blog'` to `$nav` in `public/includes/config.php`. Then add the new text keys (e.g. `'nav.blog' => 'Blog'`) to **both** language files.

Pages are reached without `.php` in the address, e.g. `https://www.bez.dk/projects`. Old `.php` links are redirected automatically.

## Languages

The site is in Danish and English. Visitors get the language their browser prefers (Danish if it's neither), and can switch with the language dropdown (🌐) in the top bar. The choice is remembered in a cookie. Any page can be opened in a specific language by adding `?lang=da` or `?lang=en` to the address.

## Running locally (optional)

If PHP is installed (`winget install PHP.PHP`), run this in the project folder:

```
php -S localhost:8000 -t public router.php
```

Then open http://localhost:8000. The contact form won't actually send mail locally.

## Deploying to one.com

1. In the one.com control panel, go to **File Manager**, or use **SFTP** (find the login details under *Hosting settings → SFTP/SSH*).
2. Upload the **contents** of the `public` folder (not the folder itself) to `webroot`.
3. If there is an `index.html` in `webroot`, delete it.
4. Delete the files from the old site in `webroot` (the `js`, `css`, `data` and `image` folders and `index.html`). One exception: if you want to keep the old pictures online, upload the `image` folder again.
5. Visit your domain.

Note: `.htaccess` is a hidden file. Make sure your SFTP client or file manager is set to show hidden files so it actually gets uploaded.
