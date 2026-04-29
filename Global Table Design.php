/**
 * BK MASTER LOGIC - FINAL RE-CHECKED VERSION
 */

/* 1. SHORT NAME LOGIC FOR BLOCKS (Class: bk-short-name) */
if (!function_exists('bk_get_clean_name')) {
    function bk_get_clean_name($title) {
        if (empty($title)) return '';
        $parts = preg_split('/\s*(–|-|:|,|\||\(|\))/u', $title);
        $title = trim($parts[0]);
        $keywords = 'biography|bio|wiki|early life|age|career|family|faimly|net worth|height|facts|born|wiki-bio';
        $title = preg_replace('/\s+(' . $keywords . ').*$/i', '', $title);
        $title = preg_replace('/\s+(का|की|के)\s+.*/u', '', $title);
        return trim($title);
    }
}
add_filter('generateblocks_dynamic_content_output', function($content, $attributes, $block) {
    if (!empty($attributes['className']) && strpos($attributes['className'], 'bk-short-name') !== false) {
        return bk_get_clean_name($content);
    }
    return $content;
}, 10, 3);

/* 2. TOC, SECTION WRAPPING, AND TABLE WRAPPING */
add_filter('the_content', function($content) {
    if (!is_singular('post') || !in_the_loop() || is_admin()) return $content;

    // Generate TOC logic
    $toc_html = '';
    if (preg_match_all('/<h2[^>]*>(.*?)<\/h2>/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
        $toc_html = '<details class="bk-toc-section"><summary class="bk-toc-header">Table of Contents</summary><ul class="bk-toc-list-items">';
        foreach ($matches[1] as $i => $match) {
            $id = sanitize_title($match[0]);
            $toc_html .= '<li><a href="#' . $id . '">' . strip_tags($match[0]) . '</a></li>';
            $content = str_replace($matches[0][$i][0], '<h2 id="' . $id . '">' . $match[0] . '</h2>', $content);
        }
        $toc_html .= '</ul></details>';
    }

    // Splitting logic for Boxes and placing TOC before 2nd H2
    $parts = preg_split('/(<h2[^>]*>)/i', $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    $new_content = '<div class="post-section-box intro-section">';
    $h2_count = 0;

    foreach ($parts as $part) {
        if (preg_match('/<h2[^>]*>/i', $part)) {
            $h2_count++;
            if ($h2_count === 2 && !empty($toc_html)) {
                $new_content .= '</div>' . $toc_html . '<div class="post-section-box">';
            } else { $new_content .= '</div><div class="post-section-box">'; }
        }
        $new_content .= $part;
    }
    $content = $new_content . '</div>';

    // Wrap Tables
    $content = preg_replace('/<table(.*?)<\/table>/is', '<div class="custom-table-minimal"><table$1</table></div>', $content);

    return preg_replace('/<div class="post-section-box[^>]*>\s*<\/div>/', '', $content);
}, 30);

/* 3. BREADCRUMBS HOOK */
add_action('generate_before_main_content', function() {
    if ( (is_singular('post') || is_archive()) && !is_admin() ) {
        echo '<div class="bk-breadcrumbs-wrapper">';
        if (function_exists('rank_math_the_breadcrumbs')) rank_math_the_breadcrumbs();
        echo '</div>';
    }
}, 15);

/* 4. IMAGE POPUP SYSTEM - NO BLUR, TRANSPARENT BROWN */
add_action('wp_footer', function() {
    if (!is_singular('post')) return;
    ?>
    <div id="bk-modal" class="bk-pop-overlay"><span class="bk-close-x">&times;</span><div class="bk-flex"><img id="bk-pop-img" src=""></div></div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const m = document.getElementById("bk-modal"), mi = document.getElementById("bk-pop-img");
        document.querySelectorAll('.post-section-box img, .featured-image img, .bk-hero-flex img').forEach(img => {
            img.style.cursor = 'zoom-in';
            img.onclick = function() {
                m.style.display = "block";
                mi.src = this.currentSrc || this.src; 
                document.body.style.overflow = "hidden";
            }
        });
        const cl = () => { m.style.display = "none"; document.body.style.overflow = ""; mi.src = ""; };
        document.querySelector(".bk-close-x").onclick = cl;
        m.onclick = (e) => { if(e.target !== mi) cl(); };
    });
    </script>
    <style>
    .bk-pop-overlay { display: none; position: fixed; z-index: 999999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(40,24,18,0.9); transition: opacity 0.3s; }
    .bk-flex { display: flex; justify-content: center; align-items: center; height: 100%; padding: 20px; }
    #bk-pop-img { max-width: 90vw; max-height: 85vh; border-radius: 12px; box-shadow: 0 10px 50px rgba(0,0,0,0.5); border: none !important; }
    .bk-close-x { position: absolute; top: 15px; right: 25px; color: #fff; font-size: 50px; cursor: pointer; line-height: 1; }
    </style>
    <?php
});
