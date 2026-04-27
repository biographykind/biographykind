add_filter('the_content', 'bk_refined_toc_logic', 20);
function bk_refined_toc_logic($content) {
    if (!is_singular('post') || is_admin()) return $content;
    if (stripos($content, '<h2') === false) return $content;

    // Handle special characters/Hindi correctly
    $content = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');

    libxml_use_internal_errors(true);
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);
    $h2s = $xpath->query('//h2');
    if ($h2s->length < 2) return $content;

    // Create TOC Dropdown Container
    $toc_container = $dom->createElement('details');
    $toc_container->setAttribute('class', 'bk-toc-wrapper');
    $toc_container->setAttribute('open', 'open'); // Default open

    // Create Summary (Heading Part)
    $summary = $dom->createElement('summary');
    $summary->setAttribute('class', 'bk-toc-summary');
    
    // Title inside Summary (Size 20px via CSS)
    $title_div = $dom->createElement('div', 'Table of Contents');
    $title_div->setAttribute('class', 'bk-toc-heading-text');
    $summary->appendChild($title_div);
    $toc_container->appendChild($summary);

    // Create List
    $ul = $dom->createElement('ul');
    $ul->setAttribute('class', 'bk-toc-list');

    $i = 1;
    foreach ($h2s as $h2) {
        $text = trim(preg_replace('/\s+/', ' ', wp_strip_all_tags($h2->textContent)));
        if ($text === '') continue;

        $id = $h2->hasAttribute('id') ? $h2->getAttribute('id') : 'section-' . $i;
        $h2->setAttribute('id', $id);

        $li = $dom->createElement('li');
        $a = $dom->createElement('a', $text);
        $a->setAttribute('href', '#' . $id);
        $li->appendChild($a);
        $ul->appendChild($li);
        $i++;
    }

    $toc_container->appendChild($ul);

    // Insert before the very first H2
    $firstH2 = $h2s->item(0);
    $firstH2->parentNode->insertBefore($toc_container, $firstH2);

    return html_entity_decode($dom->saveHTML());
}
