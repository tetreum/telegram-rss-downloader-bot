<?php

return [
    "www.ejercitos.org" => [
        "remove" => [
            ".mh-meta.entry-meta",
            ".mh-author-box",
            ".mh-share-buttons",
            ".yarpp-related",
        ]
    ],
    "www.elconfidencial.com" => [
        "selector" => ".news-container",
        "remove" => [
            ".news-header-pre-tit",
            ".info-box-tags",
            ".mrf-hide",
            ".news-body-right.aside-right",
            ".news-comments",
            ".info-profile-comments",
            ".news-related",
            ".news-share-complete",
            ".news-rel-box-title",
            ".news-share-fixed",
            ".article-related"
        ]
    ],
    "es.gizmodo.com" => [
        "remove" => [
            ".inset--story__content",
            ".meta--pe",
            ".js_sidebar-actual-container",
            ".navwrap--outer",
            ".footer-nav",
            ".js_ad-dynamic",
            ".post__misc",
            ".sharingfooter",
            ".inset--story",
            "#read-only-warning",
            ".splashy-ad-container",
            ".ad-container",
            "#js_discussion-region",
            "#svggroup--crucial",
            ".hide",
        ],
        "custom" => function ($html) {
            // remove weird bottom padding from article images
            foreach ($html->find(".img-permalink-sub-wrapper") as $el) {
                $el->attr("style", "");
            }

            return $html;
        }
    ]
];
