<?php
// Recipes: stored in MySQL, shown on /recipes and edited on /admin.
// Every text exists in Danish (_da) and English (_en); a recipe without
// a title in the visitor's language is shown in the other language.
require_once __DIR__ . '/db.php';

const RECIPE_CATEGORIES = [
    'dinner'    => ['da' => 'Aftensmad', 'en' => 'Dinner'],
    'soup'      => ['da' => 'Supper', 'en' => 'Soups'],
    'side'      => ['da' => 'Tilbehør & salater', 'en' => 'Sides & salads'],
    'bread'     => ['da' => 'Brød & bagning', 'en' => 'Bread & baking'],
    'dessert'   => ['da' => 'Kager & desserter', 'en' => 'Cakes & desserts'],
    'breakfast' => ['da' => 'Morgenmad', 'en' => 'Breakfast'],
    'snack'     => ['da' => 'Snacks', 'en' => 'Snacks'],
    'drinks'    => ['da' => 'Drikke', 'en' => 'Drinks'],
    'other'     => ['da' => 'Andet', 'en' => 'Other'],
];

// Texts that exist in both languages (columns are e.g. title_da and title_en).
const RECIPE_TEXTS = ['title', 'summary', 'ingredients', 'steps', 'notes'];

// Uploaded photos. The folder has its own .htaccess that only allows images.
const RECIPE_IMAGE_DIR  = __DIR__ . '/../uploads/recipes';
const RECIPE_IMAGE_URL  = '/uploads/recipes/';
const RECIPE_IMAGE_SIZE = 1600;             // longest side in pixels after resizing
const RECIPE_IMAGE_MAX  = 15 * 1024 * 1024; // largest upload in bytes

// Creates the tables the first time. Safe to run again.
function recipes_install(PDO $db): void
{
    $db->exec("CREATE TABLE IF NOT EXISTS recipes (
        id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        slug           VARCHAR(120) NOT NULL,
        category       VARCHAR(30) NOT NULL DEFAULT 'other',
        title_da       VARCHAR(200) NOT NULL DEFAULT '',
        title_en       VARCHAR(200) NOT NULL DEFAULT '',
        summary_da     TEXT NOT NULL,
        summary_en     TEXT NOT NULL,
        ingredients_da TEXT NOT NULL,
        ingredients_en TEXT NOT NULL,
        steps_da       TEXT NOT NULL,
        steps_en       TEXT NOT NULL,
        notes_da       TEXT NOT NULL,
        notes_en       TEXT NOT NULL,
        servings       SMALLINT UNSIGNED NULL,
        prep_minutes   SMALLINT UNSIGNED NULL,
        cook_minutes   SMALLINT UNSIGNED NULL,
        image          VARCHAR(100) NULL,
        published      TINYINT(1) NOT NULL DEFAULT 1,
        created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY slug (slug),
        KEY category (category)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci");

    $db->exec("CREATE TABLE IF NOT EXISTS login_failures (
        id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        ip         VARCHAR(45) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY ip (ip, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

/* ---------------------------------------------------------------
   Reading recipes
   --------------------------------------------------------------- */

// Recipes matching the search words and category, sorted by title.
// Returns null when the database isn't available.
function search_recipes(string $query = '', string $category = '', bool $includeHidden = false): ?array
{
    global $lang;
    $db = db();
    if (!$db) {
        return null;
    }

    $where  = [];
    $params = [];
    if (!$includeHidden) {
        $where[] = 'published = 1';
    }
    if ($category !== '') {
        $where[]  = 'category = ?';
        $params[] = $category;
    }

    // Every word must be found somewhere: in a title, summary or the ingredients.
    $columns = ['title_da', 'title_en', 'summary_da', 'summary_en', 'ingredients_da', 'ingredients_en'];
    $words   = array_slice(preg_split('/\s+/u', $query, -1, PREG_SPLIT_NO_EMPTY), 0, 8);
    foreach ($words as $word) {
        $like    = '%' . addcslashes($word, '%_\\') . '%';
        $where[] = '(' . implode(' OR ', array_map(fn ($column) => "$column LIKE ?", $columns)) . ')';
        array_push($params, ...array_fill(0, count($columns), $like));
    }

    $other = $lang === 'da' ? 'en' : 'da';
    $sql   = 'SELECT * FROM recipes'
        . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
        . " ORDER BY CASE WHEN title_$lang = '' THEN title_$other ELSE title_$lang END";

    try {
        $statement = $db->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    } catch (PDOException $exception) {
        // Most likely the table doesn't exist yet (it's made the first time /admin is opened).
        error_log('Recipe search failed: ' . $exception->getMessage());
        return [];
    }
}

// How many published recipes each category has, e.g. ['dinner' => 4].
function recipe_category_counts(): array
{
    try {
        $rows = db()?->query('SELECT category, COUNT(*) FROM recipes WHERE published = 1 GROUP BY category');
        return $rows ? $rows->fetchAll(PDO::FETCH_KEY_PAIR) : [];
    } catch (PDOException) {
        return [];
    }
}

function find_recipe(string $column, string|int $value): ?array
{
    try {
        $statement = db()?->prepare("SELECT * FROM recipes WHERE $column = ?");
        $statement?->execute([$value]);
        return $statement?->fetch() ?: null;
    } catch (PDOException) {
        return null;
    }
}

function find_recipe_by_slug(string $slug): ?array
{
    return find_recipe('slug', $slug);
}

function find_recipe_by_id(int $id): ?array
{
    return find_recipe('id', $id);
}

/* ---------------------------------------------------------------
   Showing recipes
   --------------------------------------------------------------- */

// The language a recipe is shown in: the visitor's, unless it has no title in that language.
function recipe_lang(array $recipe): string
{
    global $lang;
    $other = $lang === 'da' ? 'en' : 'da';
    return $recipe["title_$lang"] !== '' ? $lang : $other;
}

// A text from the recipe in the language it is shown in, e.g. recipe_text($recipe, 'title').
function recipe_text(array $recipe, string $field): string
{
    return $recipe[$field . '_' . recipe_lang($recipe)];
}

function recipe_url(array $recipe): string
{
    return '/recipes/' . rawurlencode($recipe['slug']);
}

function recipe_image_url(array $recipe): ?string
{
    return $recipe['image'] ? RECIPE_IMAGE_URL . rawurlencode($recipe['image']) : null;
}

function recipe_category_name(string $category): string
{
    return tr(RECIPE_CATEGORIES[$category] ?? RECIPE_CATEGORIES['other']);
}

// 95 -> "1 t 35 min" / "1 h 35 min"
function format_minutes(int $minutes): string
{
    $hours = intdiv($minutes, 60);
    $rest  = $minutes % 60;
    $parts = [];
    if ($hours) {
        $parts[] = $hours . ' ' . t('recipes.hours');
    }
    if ($rest || !$hours) {
        $parts[] = $rest . ' min';
    }
    return implode(' ', $parts);
}

// The non-empty lines of a text, with list markers and numbering ("- ", "1. ") removed.
function text_lines(string $text): array
{
    $lines = [];
    foreach (preg_split('/\R/u', $text) as $line) {
        $line = trim(preg_replace('/^\s*(?:[-*•]|\d+[.)])\s+/u', '', $line));
        if ($line !== '') {
            $lines[] = $line;
        }
    }
    return $lines;
}

// Ingredients in groups. A line ending with ":" starts a new group, e.g. "Dough:".
function ingredient_groups(string $text): array
{
    $groups = [['heading' => null, 'items' => []]];
    foreach (text_lines($text) as $line) {
        if (str_ends_with($line, ':')) {
            $groups[] = ['heading' => rtrim($line, ':'), 'items' => []];
        } else {
            $groups[array_key_last($groups)]['items'][] = $line;
        }
    }
    return array_values(array_filter($groups, fn ($group) => $group['items'] || $group['heading']));
}

// Amounts the servings calculator can scale, e.g. "2", "1,5", "1/2", "1½", "1 1/2", "2-3".
const QUANTITY = '(?:\d+\s+\d+\/\d+|\d+\/\d+|\d+(?:[.,]\d+)?(?:\s?[½¼¾⅓⅔])?|[½¼¾⅓⅔])';

// "1½" -> 1.5, "1 1/2" -> 1.5, "2,5" -> 2.5
function quantity_value(string $text): float
{
    $fractions = ['½' => 0.5, '¼' => 0.25, '¾' => 0.75, '⅓' => 1 / 3, '⅔' => 2 / 3];
    $value     = 0.0;
    foreach ($fractions as $char => $fraction) {
        if (str_contains($text, $char)) {
            $value += $fraction;
            $text   = str_replace($char, '', $text);
        }
    }
    if (preg_match('/^(?:(\d+)\s+)?(\d+)\/(\d+)$/', trim($text), $match)) {
        return $value + (int) $match[1] + ($match[3] > 0 ? $match[2] / $match[3] : 0);
    }
    return $value + (float) str_replace(',', '.', trim($text));
}

// An ingredient line as HTML, with the amount at the start marked so it can be scaled.
function ingredient_html(string $line): string
{
    if (!preg_match('/^(' . QUANTITY . ')(?:\s?[-–]\s?(' . QUANTITY . '))?(?![\d.,\/])/u', $line, $match)) {
        return e($line);
    }
    $attributes = ' data-qty="' . quantity_value($match[1]) . '"';
    if (!empty($match[2])) {
        $attributes .= ' data-qty-max="' . quantity_value($match[2]) . '"';
    }
    return '<span class="qty"' . $attributes . '>' . e($match[0]) . '</span>' . e(substr($line, strlen($match[0])));
}

/* ---------------------------------------------------------------
   Saving recipes (admin)
   --------------------------------------------------------------- */

// An empty recipe for the "new recipe" form.
function empty_recipe(): array
{
    $recipe = ['id' => null, 'slug' => '', 'category' => 'dinner', 'servings' => 4, 'prep_minutes' => null, 'cook_minutes' => null, 'image' => null, 'published' => 1];
    foreach (RECIPE_TEXTS as $field) {
        $recipe[$field . '_da'] = '';
        $recipe[$field . '_en'] = '';
    }
    return $recipe;
}

// "Æblekage med flødeskum" -> "aeblekage-med-floedeskum"
function make_slug(string $text): string
{
    $text = mb_strtolower($text, 'UTF-8');
    $text = strtr($text, ['æ' => 'ae', 'ø' => 'oe', 'å' => 'aa', 'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'é' => 'e', 'è' => 'e', 'ß' => 'ss']);
    if (function_exists('iconv')) {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    }
    $text = trim(preg_replace('/[^a-z0-9]+/', '-', $text), '-');
    return substr($text, 0, 100);
}

// Reads the admin form into a recipe. Returns [recipe, list of error text keys].
function recipe_from_form(array $input, array $current): array
{
    $recipe = $current;
    $errors = [];

    foreach (RECIPE_TEXTS as $field) {
        foreach (['da', 'en'] as $code) {
            $value = $input[$field . '_' . $code] ?? '';
            $recipe[$field . '_' . $code] = is_string($value) ? trim(str_replace("\r\n", "\n", $value)) : '';
        }
    }
    $recipe['title_da'] = mb_substr($recipe['title_da'], 0, 200);
    $recipe['title_en'] = mb_substr($recipe['title_en'], 0, 200);
    if ($recipe['title_da'] === '' && $recipe['title_en'] === '') {
        $errors[] = 'admin.error.title';
    }

    $category = $input['category'] ?? '';
    $recipe['category'] = is_string($category) && isset(RECIPE_CATEGORIES[$category]) ? $category : 'other';

    foreach (['servings', 'prep_minutes', 'cook_minutes'] as $field) {
        $value = trim((string) ($input[$field] ?? ''));
        $number = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 9999]]);
        if ($value !== '' && $number === false) {
            $errors[] = 'admin.error.number';
        }
        $recipe[$field] = $number === false ? null : $number;
    }

    $recipe['published'] = empty($input['published']) ? 0 : 1;

    // The address: typed in, or made from the title. It must not be used by another recipe.
    $slugInput      = is_string($input['slug'] ?? null) ? $input['slug'] : '';
    $recipe['slug'] = make_slug($slugInput !== '' ? $slugInput : ($recipe['title_da'] ?: $recipe['title_en']));
    if ($recipe['slug'] === '') {
        $errors[] = 'admin.error.slug';
    } else {
        $existing = find_recipe_by_slug($recipe['slug']);
        if ($existing && (int) $existing['id'] !== (int) $recipe['id']) {
            $errors[] = 'admin.error.slug_taken';
        }
    }

    return [$recipe, array_values(array_unique($errors))];
}

// Inserts or updates the recipe and returns its id.
function save_recipe(array $recipe): int
{
    $db      = db();
    $columns = ['slug', 'category', 'servings', 'prep_minutes', 'cook_minutes', 'image', 'published'];
    foreach (RECIPE_TEXTS as $field) {
        array_push($columns, $field . '_da', $field . '_en');
    }
    $values = array_map(fn ($column) => $recipe[$column], $columns);

    if ($recipe['id']) {
        $set = implode(', ', array_map(fn ($column) => "$column = ?", $columns));
        $db->prepare("UPDATE recipes SET $set WHERE id = ?")->execute([...$values, $recipe['id']]);
        return (int) $recipe['id'];
    }

    $placeholders = implode(', ', array_fill(0, count($columns), '?'));
    $db->prepare('INSERT INTO recipes (' . implode(', ', $columns) . ") VALUES ($placeholders)")->execute($values);
    return (int) $db->lastInsertId();
}

function delete_recipe(array $recipe): void
{
    db()->prepare('DELETE FROM recipes WHERE id = ?')->execute([$recipe['id']]);
    delete_recipe_image($recipe['image']);
}

/* ---------------------------------------------------------------
   Photos
   --------------------------------------------------------------- */

// Saves an uploaded photo and returns its file name, or null if no file was chosen.
// Throws RuntimeException with a text key if the file can't be used.
function store_recipe_image(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > RECIPE_IMAGE_MAX) {
        throw new RuntimeException('admin.error.image_size');
    }

    $types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    $info  = @getimagesize($file['tmp_name']);
    if (!$info || !isset($types[$info[2]])) {
        throw new RuntimeException('admin.error.image_type');
    }

    if (!is_dir(RECIPE_IMAGE_DIR) && !mkdir(RECIPE_IMAGE_DIR, 0755, true)) {
        throw new RuntimeException('admin.error.image_save');
    }

    // A random name, so uploads can't overwrite each other or be guessed.
    $name    = bin2hex(random_bytes(10));
    $resized = resize_image($file['tmp_name'], $info[2]);
    if ($resized) {
        $name .= '.jpg';
        $saved = imagejpeg($resized, RECIPE_IMAGE_DIR . '/' . $name, 82);
        imagedestroy($resized);
    } else {
        // No image library on the server: keep the photo as it is.
        $name .= '.' . $types[$info[2]];
        $saved = move_uploaded_file($file['tmp_name'], RECIPE_IMAGE_DIR . '/' . $name);
    }
    if (!$saved) {
        throw new RuntimeException('admin.error.image_save');
    }
    return $name;
}

// Turns the photo the right way and makes it at most RECIPE_IMAGE_SIZE pixels.
// Returns null if the server has no GD image library (or it can't read the file).
function resize_image(string $path, int $type): ?GdImage
{
    if (!extension_loaded('gd')) {
        return null;
    }
    $image = match ($type) {
        IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
        IMAGETYPE_PNG  => @imagecreatefrompng($path),
        IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
        default        => false,
    };
    if (!$image) {
        return null;
    }

    // Phone photos are often stored sideways with a note (EXIF) saying how to turn them.
    if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
        $orientation = (@exif_read_data($path) ?: [])['Orientation'] ?? 1;
        $angle       = [3 => 180, 6 => -90, 8 => 90][$orientation] ?? 0;
        if ($angle) {
            $image = imagerotate($image, $angle, 0);
        }
    }

    $width  = imagesx($image);
    $height = imagesy($image);
    $scale  = min(1, RECIPE_IMAGE_SIZE / max($width, $height));
    $newWidth  = max(1, (int) round($width * $scale));
    $newHeight = max(1, (int) round($height * $scale));

    // White background, so transparent PNGs don't turn black as JPEG.
    $resized = imagecreatetruecolor($newWidth, $newHeight);
    imagefill($resized, 0, 0, imagecolorallocate($resized, 255, 255, 255));
    imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    imagedestroy($image);
    return $resized;
}

function delete_recipe_image(?string $name): void
{
    // basename() keeps a tampered name from pointing outside the folder.
    if ($name && is_file(RECIPE_IMAGE_DIR . '/' . basename($name))) {
        unlink(RECIPE_IMAGE_DIR . '/' . basename($name));
    }
}
