add_filter('the_content', function($content) {
    if (!is_singular('post') || is_admin()) return $content;

    $pattern = '/(<h[234][^>]*>.*?<\/h[234]>)/is';
    $parts = preg_split($pattern, $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

    $new_content = '';
    $open = false;

    foreach ($parts as $part) {
        if (preg_match('/<h[234][^>]*>.*?<\/h[234]>/is', $part)) {

            // Close previous section
            if ($open) {
                $new_content .= '</div>';
            }

            // Start new section
            $new_content .= '<div class="post-section-box">' . $part;
            $open = true;

        } else {
            $new_content .= $part;
        }
    }

    // Close last section
    if ($open) {
        $new_content .= '</div>';
    }

    return $new_content;

}, 30);
