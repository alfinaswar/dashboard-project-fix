<?php

$files = glob(__DIR__ . '/../resources/views/**/*.blade.php');
$files = array_merge($files, glob(__DIR__ . '/../resources/views/*/*.blade.php'));
$files = array_unique($files);

foreach ($files as $file) {
    $html = file_get_contents($file);
    $updated = preg_replace_callback('/<(input|textarea)\b([^>]*)(?:\/>|>)/i', function (array $match): string {
        $tag = $match[1];
        $attributes = $match[2];
        if (stripos($attributes, 'placeholder=') !== false) {
            return $match[0];
        }
        preg_match('/\bname=["\']([^"\']+)["\']/i', $attributes, $name);
        preg_match('/\btype=["\']([^"\']+)["\']/i', $attributes, $type);
        $field = $name[1] ?? '';
        $inputType = strtolower($type[1] ?? 'text');
        if ($inputType === 'hidden' || in_array($inputType, ['checkbox', 'radio'], true)) {
            return $match[0];
        }
        $label = ucwords(str_replace(['_', '-', '[]'], [' ', ' ', ''], $field));
        $placeholder = match ($inputType) {
            'email' => 'nama@contoh.com',
            'number' => 'Masukkan ' . strtolower($label),
            'file' => 'Pilih file',
            'date' => 'Pilih tanggal',
            default => 'Masukkan ' . strtolower($label),
        };
        return '<' . $tag . ' placeholder="' . $placeholder . '"' . $attributes . (str_ends_with($match[0], '/>') ? '/>' : '>');
    }, $html);
    file_put_contents($file, $updated);
}
