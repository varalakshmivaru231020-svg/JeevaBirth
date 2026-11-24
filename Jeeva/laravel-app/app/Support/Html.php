<?php

namespace App\Support;

class Html
{
    private const ALLOWED_TAGS = '<h1><h2><h3><h4><h5><h6><p><br><span><strong><b><em><i><u><s><sub><sup><a><ul><ol><li><blockquote>';

    private const COLOR = '(?:#[0-9a-f]{3,8}|rgb\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*\))';

    /**
     * Reduce rich-text editor output to a safe allowlist of tags and attributes.
     */
    public static function sanitize(?string $html): string
    {
        $html = strip_tags((string) $html, self::ALLOWED_TAGS);

        $html = preg_replace_callback('/<(\/?)([a-z0-9]+)([^>]*)>/i', function ($m) {
            [, $slash, $tag, $attrs] = $m;
            $tag = strtolower($tag);

            if ($slash === '/') {
                return "</{$tag}>";
            }

            if ($tag === 'br') {
                return '<br>';
            }

            $out = '';

            // Links: safe href only.
            if ($tag === 'a') {
                if (preg_match('/href\s*=\s*(?:"([^"]*)"|\'([^\']*)\')/i', $attrs, $h)) {
                    $href = trim(html_entity_decode($h[1] !== '' ? $h[1] : ($h[2] ?? ''), ENT_QUOTES | ENT_HTML5));
                    if (preg_match('#^(https?://|mailto:|tel:|/|\#)#i', $href)) {
                        return '<a href="' . e($href) . '" target="_blank" rel="noopener noreferrer">';
                    }
                }

                return '<a>';
            }

            // Text alignment class from the editor.
            if (preg_match('/class\s*=\s*"([^"]*)"/i', $attrs, $c)
                && preg_match('/\bql-align-(center|right|justify)\b/', $c[1], $a)) {
                $out .= ' class="ql-align-' . $a[1] . '"';
            }

            // Text colour / highlight only.
            if (preg_match('/style\s*=\s*"([^"]*)"/i', $attrs, $s)) {
                $styles = [];
                foreach (explode(';', $s[1]) as $decl) {
                    if (preg_match('/^\s*(color|background-color)\s*:\s*(' . self::COLOR . ')\s*$/i', $decl, $d)) {
                        $styles[] = strtolower($d[1]) . ':' . strtolower($d[2]);
                    }
                }
                if ($styles) {
                    $out .= ' style="' . implode(';', $styles) . '"';
                }
            }

            return "<{$tag}{$out}>";
        }, $html);

        return trim($html);
    }

    /**
     * Render stored blog content: rich HTML as-is (sanitized), legacy plain text with line breaks.
     */
    public static function render(?string $content): string
    {
        $content = (string) $content;

        if ($content !== strip_tags($content)) {
            return self::sanitize($content);
        }

        return nl2br(e($content));
    }

    public static function isBlank(?string $html): bool
    {
        $text = html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(str_replace("\xC2\xA0", ' ', $text)) === '';
    }
}
