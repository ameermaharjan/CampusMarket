<?php
$file = 'wp-content/themes/campusmarket/assets/css/main.css';
$content = file_get_contents($file);

function cm_replace($pattern, $replacement, $subject)
{
    return preg_replace($pattern, $replacement, $subject);
}

// 1. Remove the double declaration inside @media (max-width: 992px)
// This was re-declaring the 2-column grid and sticky sidebar inside the media query, which is redundant or broken.
$media_block = '/@media\s*\(max-width:\s*992px\)\s*\{[^}]*\.cm-listing-detail__grid\s*\{[^}]*\}\s*\.cm-listing-detail__sidebar\s*\{[^}]*\}\s*/s';
$content = preg_replace($media_block, "@media (max-width: 992px) {\n    .cm-listing-detail__grid {\n        grid-template-columns: 1fr 340px;\n        gap: var(--cm-space-6);\n    }\n", $content);

// 2. Add proper stacking for mobile
$stack_fix = "\n@media (max-width: 860px) {\n    .cm-listing-detail__grid {\n        grid-template-columns: 1fr;\n    }\n    \n    .cm-listing-detail__sidebar {\n        position: static !important;\n        width: 100%;\n    }\n}\n";
// Insert before the last responsive block or at the end
$content = str_replace('/* --- ANIMATIONS', $stack_fix . "\n/* --- ANIMATIONS", $content);

// 3. Ensure cards have no negative margins or weird positioning
$content = str_replace('.cm-detail-card:hover {', ".cm-detail-card {\n    position: relative;\n    z-index: 1;\n}\n\n.cm-detail-card:hover {", $content);

file_put_contents($file, $content);
echo "SUCCESS: Responsive Grid & Stacking Fixed";
