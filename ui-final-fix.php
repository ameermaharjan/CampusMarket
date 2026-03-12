<?php
$file = 'wp-content/themes/campusmarket/assets/css/main.css';
$content = file_get_contents($file);

function cm_replace($pattern, $replacement, $subject)
{
    return preg_replace($pattern, $replacement, $subject);
}

// 1. Fix the Sidebar & Grid Responsiveness
// Remove the broken @media 992px block that redundantly set grid-template-columns: 1fr 380px
$content = preg_replace(
    '/@media\s*\(max-width:\s*992px\)\s*\{\s*\.cm-listing-detail__grid\s*\{[^}]*\}\s*\.cm-listing-detail__sidebar\s*\{[^}]*\}\s*/s',
    "@media (max-width: 992px) {\n    .cm-listing-detail__grid {\n        grid-template-columns: 1fr 340px;\n        gap: var(--cm-space-6);\n    }\n",
    $content
);

// 2. Add explicit Stacking Context for Cards
// Booking form should be above seller card if they ever overlap
$content = str_replace(
    '.cm-booking-form h3 {',
    ".cm-booking-form {\n    position: relative;\n    z-index: 10;\n    background: var(--cm-white);\n    border-radius: var(--cm-radius-lg);\n}\n\n.cm-booking-form h3 {",
    $content
);

$content = str_replace(
    '.cm-seller-card h3 {',
    ".cm-seller-card {\n    position: relative;\n    z-index: 5;\n}\n\n.cm-seller-card h3 {",
    $content
);

// 3. Ensure the Sidebar doesn't have internal overlap
$content = str_replace(
    '.cm-listing-detail__sidebar {',
    ".cm-listing-detail__sidebar {\n    position: sticky;\n    top: calc(var(--cm-header-height) + var(--cm-space-6));\n    display: flex;\n    flex-direction: column;\n    gap: var(--cm-space-6);\n    align-self: start;\n    height: fit-content;\n}\n\n/* Removed redundant sidebar selector */\n.cm_ignore_sidebar {",
    $content
);

file_put_contents($file, $content);
echo "SUCCESS: Comprehensive UI Fix Applied";
