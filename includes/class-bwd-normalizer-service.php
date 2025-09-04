<?php
if (!defined('ABSPATH')) exit;

class BWD_Normalizer_Service {

    public function normalize_persian_text($text) {
        if (empty($text)) return '';
        
        // Convert to UTF-8 if needed
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'auto');
        }
        
        // Persian character replacements
        $replacements = array(
            // Arabic characters to Persian
            'ي' => 'ی', // Arabic yeh
            'ى' => 'ی', // Arabic alef maksura
            'ئ' => 'ی', // Arabic yeh with hamza above
            'ك' => 'ک', // Arabic kaf
            'ة' => 'ه', // Arabic teh marbuta
            'ؤ' => 'و', // Arabic waw with hamza above
            'إ' => 'ا', // Arabic alef with hamza below
            'أ' => 'ا', // Arabic alef with hamza above
            'آ' => 'ا', // Arabic alef with madda above -> convert to simple alef
            'ء' => '',  // Arabic hamza (remove)
            
            // Normalize spaces
            '‌' => ' ', // Zero-width non-joiner
            '‎' => ' ', // Left-to-right mark
            '‏' => ' ', // Right-to-left mark
            '　' => ' ', // Full-width space
            ' ' => ' ', // Figure space
            ' ' => ' ', // Thin space
            ' ' => ' ', // Hair space
            ' ' => ' ', // Zero-width space
            
            // Normalize numbers
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        );
        
        // Filter replacements
        $replacements = apply_filters('bwd_character_replacements', $replacements);
        
        $text = str_replace(array_keys($replacements), array_values($replacements), $text);
        
        // Remove extra spaces
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        // Remove Persian stopwords (optional)
        $text = $this->remove_persian_stopwords($text);
        
        return $text;
    }
    
    public function remove_persian_stopwords($text) {
        $stopwords = array(
            'از', 'به', 'در', 'با', 'برای', 'که', 'این', 'آن', 'را', 'و', 'یا',
            'هم', 'نیز', 'همچنین', 'همچنین', 'همچنین', 'همچنین', 'همچنین',
            'اما', 'ولی', 'اگر', 'چون', 'زیرا', 'بنابراین', 'پس', 'قبل',
            'بعد', 'بالا', 'پایین', 'چپ', 'راست', 'وسط', 'کنار', 'روبرو'
        );
        
        // Filter stopwords
        $stopwords = apply_filters('bwd_stopwords', $stopwords);
        
        $words = explode(' ', $text);
        $filtered_words = array();
        
        foreach ($words as $word) {
            $word = trim($word);
            if (!empty($word) && !in_array($word, $stopwords) && mb_strlen($word) > 1) {
                $filtered_words[] = $word;
            }
        }
        
        return implode(' ', $filtered_words);
    }
    
    public function generate_search_keywords($title, $content) {
        $text = $title . ' ' . $content;
        $normalized_text = $this->normalize_persian_text($text);
        
        // Extract keywords
        $words = explode(' ', $normalized_text);
        $keywords = array();
        
        foreach ($words as $word) {
            $word = trim($word);
            if (!empty($word) && mb_strlen($word) > 2) {
                $keywords[] = $word;
            }
        }
        
        // Remove duplicates and limit
        $keywords = array_unique($keywords);
        $keywords = array_slice($keywords, 0, 20); // Limit to 20 keywords
        
        return implode(' ', $keywords);
    }
}
