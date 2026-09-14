import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';

const base = new URL('../wordpress/grafik-publicidad/', import.meta.url);
const [functions, front, carousel, script] = await Promise.all([
  readFile(new URL('functions.php', base), 'utf8'),
  readFile(new URL('front-page.php', base), 'utf8'),
  readFile(new URL('template-parts/product-carousel.php', base), 'utf8'),
  readFile(new URL('assets/js/theme.js', base), 'utf8'),
]);

assert.match(functions, /GRAFIK_SALES_EMAIL', 'ventas@grafikpublicidad\.cl'/);
assert.match(functions, /'From: Grafik Publicidad <' \. GRAFIK_SALES_EMAIL/);
assert.match(functions, /'Reply-To: ' \. \$name \. ' <' \. \$email/);
assert.match(functions, /set_theme_mod\( 'grafik_contact_email', GRAFIK_SALES_EMAIL \)/);
assert.match(functions, /set_theme_mod\( 'grafik_instagram_shortcode', '\[instagram-feed feed=1\]' \)/);
assert.match(front, /shortcode_exists\( 'instagram-feed' \)/);
assert.match(front, /\$instagram_shortcode = '\[instagram-feed feed=1\]'/);
assert.match(front, /do_shortcode\( \$instagram_shortcode \)/);
assert.doesNotMatch(carousel, /data-pause|Pausar/);
assert.doesNotMatch(script, /data-pause|Pausar|Reproducir|stopped/);
assert.match(script, /}, 4000\);/);
assert.match(script, /index = \(index \+ 1\) % slides\.length/);

console.log('PASS: quote email, Smash Balloon feed and 4-second carousel integration.');
