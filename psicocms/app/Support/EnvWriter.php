<?php

namespace App\Support;

final class EnvWriter
{
    public function __construct(private readonly string $path) {}

    public static function forApp(): self
    {
        return new self(base_path('.env'));
    }

    public function exists(): bool
    {
        return is_file($this->path);
    }

    public function get(string $key): ?string
    {
        if (! $this->exists()) {
            return null;
        }

        if (preg_match('/^'.preg_quote($key, '/').'=(.*)$/m', file_get_contents($this->path), $match) !== 1) {
            return null;
        }

        return trim($match[1], " \t\"'");
    }

    public function set(array $values): void
    {
        $content = $this->exists() ? file_get_contents($this->path) : '';

        foreach ($values as $key => $value) {
            $line = $key.'='.$this->format((string) $value);
            $pattern = '/^'.preg_quote($key, '/').'=.*$/m';

            $content = preg_match($pattern, $content) === 1
                ? preg_replace_callback($pattern, fn () => $line, $content)
                : rtrim($content, "\r\n").PHP_EOL.$line.PHP_EOL;
        }

        file_put_contents($this->path, $content, LOCK_EX);
    }

    private function format(string $value): string
    {
        if ($value === '' || preg_match('/^[A-Za-z0-9_.\-\/:@+=]+$/', $value) === 1) {
            return $value;
        }

        if (! str_contains($value, "'")) {
            return "'".$value."'";
        }

        return '"'.str_replace(['\\', '"', '$'], ['\\\\', '\\"', '\\$'], $value).'"';
    }
}
