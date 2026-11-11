# homepage
Homepage for the Bez Domain

A small PHP site with no frameworks or build step, made for one.com hosting.

## Structure

```
public/                    Everything inside this folder goes on the web server
  pages/                   All pages; pages/about.php is served as /about
    index.php              Home page (hero, tool tiles, explore cards)
    tools.php              All tools, grouped by category
    tools/                 One page per tool, served as /tools/<name>
      ip-subnet-calculator.php   IPv4 subnet calculator (form + result)
      voltage-drop-calculator.php   Cable voltage drop calculator (form + result)
    recipes.php            Recipe list with search and categories
    recipe.php             One recipe, served as /recipes/<address>
    admin.php              Recipe admin: login and list of recipes (/admin)
    admin/recipe.php       Add or edit a recipe (/admin/recipe)
    guides.php             Guide categories
    projects.php           Projects
    about.php              About BEZ.dk
    contact.php            Contact form
    404.php                "Page not found" page
  .htaccess                HTTPS + www redirect, clean URLs, security headers, caching
  robots.txt
  favicon.ico, apple-touch-icon.png   Browser and phone home-screen icons
  assets/css/style.css     Styling with light and dark theme
  assets/js/main.js        Mobile menu, language dropdown, theme toggle
  assets/js/tools/         The calculations for each tool (run in the browser)
  assets/js/recipe.js      Servings calculator and print button on recipe pages
  assets/js/admin.js       Delete confirmation and unsaved-changes warning in the admin
  uploads/                 Recipe photos (only images can be fetched, see its .htaccess)
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
    db.php                 MySQL connection
    db-config.example.php  Template for db-config.php (database + admin password, not in git)
    auth.php               Admin login, sessions and form protection (CSRF)
    recipes.php            Reading, searching, showing and saving recipes
scripts/make_icons.py      Regenerates the PNG/ICO icons from the logo (not uploaded)
router.php                 Makes clean URLs work when previewing locally (not uploaded)
archive/                   Old site and design ideas, local only (ignored by git)
README.md, LICENSE         Repo files only, not uploaded
```

### Adding a tool or guide

Tools and guides are listed in `public/includes/content.php`. Until an entry has a `'url'`, it is shown with a "Kommer snart / Coming soon" badge. Add `'url' => '/tools/...'` when the page exists and it becomes a link.

A tool page lives in `public/pages/tools/<slug>.php` and its script in `public/assets/js/tools/<slug>.js`. Use `public/pages/tools/ip-subnet-calculator.php` as the template: it takes its title and intro from `content.php` via `find_tool()`, renders the form and empty result rows with texts from the language files, and the script fills in the results.

## Getting started

1. Open `public/includes/config.php` and set your site name and email addresses.
   - `contact_from` must be an address on your own domain (for example `noreply@yourdomain.com`), or one.com may reject the mail.
2. Change the page texts in `public/includes/lang/da.php` (Danish) and `public/includes/lang/en.php` (English).

### Adding a new page

Copy `public/pages/about.php` to e.g. `public/pages/blog.php`, change `$pageTitle` and the content, and add `'/blog' => 'nav.blog'` to `$nav` in `public/includes/config.php`. Then add the new text keys (e.g. `'nav.blog' => 'Blog'`) to **both** language files.

Pages are reached without `.php` in the address, e.g. `https://www.bez.dk/projects`. Old `.php` links are redirected automatically.

## Recipes

Recipes are stored in MySQL and added in the admin at `/admin` (not linked from the menu). Each recipe has a Danish and an English version; fill in one or both. A recipe without a title in the visitor's language is shown in the other language.

### Setting it up

1. In the one.com control panel, create a MySQL database (under *Databases*) and note the host, database name, user and password.
2. Copy `public/includes/db-config.example.php` to `public/includes/db-config.php` and fill in those details.
3. Upload `db-config.php` and open `https://www.bez.dk/admin`. The tables are created automatically.
4. The page asks you to choose a password and shows a line with its hash. Paste that line into `db-config.php`, upload it again and reload `/admin` to log in.

`db-config.php` is ignored by git, so the passwords stay out of the repository. Five wrong passwords in 15 minutes locks the login for that IP address.

### Writing a recipe

- **Ingredients:** one per line, amount first (`500 g mel`, `1½ dl fløde`, `2-3 fed hvidløg`), so the servings calculator on the recipe page can scale it. A line ending with a colon (`Dej:`) starts a group.
- **Method:** one step per line; they are numbered automatically.
- **Photo:** JPEG, PNG or WebP. Photos are turned the right way and resized to 1600 px when the server has the GD image library.
- Untick **Published** to hide a recipe while you work on it. When you are logged in you can still open it to preview it.

Search looks in titles, descriptions and ingredients in both languages, so a search for "kylling" or "chicken" finds the same recipe.

Uploaded photos live in `public/uploads/recipes/` on the server and are not in git. Back them up together with the database (phpMyAdmin → Export).

## Languages

The site is in Danish and English. Visitors get the language their browser prefers (Danish if it's neither), and can switch with the language dropdown (🌐) in the top bar. The choice is remembered in a cookie. Any page can be opened in a specific language by adding `?lang=da` or `?lang=en` to the address.

## Running locally (optional)

If PHP is installed (`winget install PHP.PHP`), run this in the project folder:

```
php -S localhost:8000 -t public router.php
```

Then open http://localhost:8000. The contact form won't actually send mail locally. The recipes need a MySQL database; point `db-config.php` at a local one, or at the one.com database if it allows outside connections.

## Deploying to one.com

1. In the one.com control panel, go to **File Manager**, or use **SFTP** (find the login details under *Hosting settings → SFTP/SSH*).
2. Upload the **contents** of the `public` folder (not the folder itself) to `webroot`.
3. If there is an `index.html` in `webroot`, delete it.
4. Delete the files from the old site in `webroot` (the `js`, `css`, `data` and `image` folders and `index.html`). One exception: if you want to keep the old pictures online, upload the `image` folder again.
5. Visit your domain.

When updating the site later, don't overwrite `includes/db-config.php` or the `uploads/recipes` folder on the server.

Note: `.htaccess` is a hidden file (there is also one in `uploads/`). Make sure your SFTP client or file manager is set to show hidden files so it actually gets uploaded.
