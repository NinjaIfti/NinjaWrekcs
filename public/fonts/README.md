

# Fonts Directory

Place your custom font file (.tf, .ttf, .otf, or .woff) in this directory.

## Instructions:

1. Copy your font file to this directory (public/fonts/)
2. Update the font file name in `resources/views/home/styles.blade.php`
   - Replace `your-font-file.tf` with your actual font file name
   - Update the format if needed:
     - `.ttf` or `.tf` → `format('truetype')`
     - `.otf` → `format('opentype')`
     - `.woff` → `format('woff')`
     - `.woff2` → `format('woff2')`
3. Update the font-family name if you want to use a different name

## Example:

If your font file is named `valorant-font.ttf`:
- Place it here: `public/fonts/valorant-font.ttf`
- Update the CSS to: `url('/fonts/valorant-font.ttf')`

