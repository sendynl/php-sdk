<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->append([__FILE__]);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PER-CS' => true,
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true,
        ],
        'no_empty_phpdoc' => true,
        'phpdoc_trim' => true,
        'phpdoc_trim_consecutive_blank_line_separation' => true,
        'not_operator_with_successor_space' => true,
        'trailing_comma_in_multiline' => [
            'after_heredoc' => true,
            // https://cs.symfony.com/doc/rules/control_structure/trailing_comma_in_multiline.html
            // only enable for the elements that are safe to use with PHP 7.4+
            'elements' => ['arguments', 'arrays'],
        ],
    ])
    ->setFinder($finder);
