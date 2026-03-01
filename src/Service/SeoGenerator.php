<?php

namespace App\Service;

final class SeoGenerator
{
    /**
     * @return array{title:string, meta_description:string, warnings:string[]}
     */
    public function generate(string $keyword): array
    {
        $warnings = [];

        $keyword = trim(preg_replace('/\s+/', ' ', $keyword) ?? $keyword);

        // Liste minimale (à enrichir)
        $forbidden = [
            'meilleur', 'meilleure', 'meilleurs', 'meilleures',
            'incroyable', 'exceptionnel', 'exceptionnelle', 'exceptionnels', 'exceptionnelles',
            'ultime', 'parfait', 'parfaite', 'parfaits', 'parfaites',
            'n°1', 'numéro 1', 'top'
        ];

        // Génération "simple" (MVP) : on part du keyword
        $title = $this->limitWords($keyword, 3, $warnings, 'title');
        $meta = $this->buildMeta($keyword);
        $meta = $this->limitWords($meta, 50, $warnings, 'meta_description');

        // Suppression des interdits + warning
        [$title, $titleWarn] = $this->removeForbidden($title, $forbidden);
        if ($titleWarn) $warnings[] = 'forbidden_words_removed_in_title';

        [$meta, $metaWarn] = $this->removeForbidden($meta, $forbidden);
        if ($metaWarn) $warnings[] = 'forbidden_words_removed_in_meta_description';

        // Nettoyage final
        $title = trim(preg_replace('/\s+/', ' ', $title) ?? $title);
        $meta  = trim(preg_replace('/\s+/', ' ', $meta) ?? $meta);

        return [
            'title' => $title,
            'meta_description' => $meta,
            'warnings' => array_values(array_unique($warnings)),
        ];
    }

    private function buildMeta(string $keyword): string
    {
        // Meta "neutre" volontaire (sans superlatifs)
        return $keyword . '. Informations et points clés, description claire et utile.';
    }

    /**
     * @param string[] $warnings
     */
    private function limitWords(string $text, int $maxWords, array &$warnings, string $field): string
    {
        $words = preg_split('/\s+/', trim($text)) ?: [];
        if (count($words) <= $maxWords) {
            return $text;
        }

        $warnings[] = $field . '_truncated';
        return implode(' ', array_slice($words, 0, $maxWords));
    }

    /**
     * @param string[] $forbidden
     * @return array{0:string,1:bool} [cleanedText, changed?]
     */
    private function removeForbidden(string $text, array $forbidden): array
    {
        $original = $text;

        foreach ($forbidden as $term) {
            // Remplacement "case-insensitive"
            $text = preg_replace('/\b' . preg_quote($term, '/') . '\b/iu', '', $text) ?? $text;
        }

        // Nettoyage des espaces
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? $text);

        return [$text, $text !== $original];
    }
}