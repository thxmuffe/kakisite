<?php

/**
 * Theme settings
 *
 * @param array $settings
 * @return array
 */
function theme_app_settings($settings) {
    return json_decode(<<<JSON
    {
    "colorScheme": {
        "bodyColors": [
            "#111111",
            "#ffffff"
        ],
        "bgColor": "#ffffff",
        "colors": [
            "#8e867c",
            "#000000",
            "#000000",
            "#000000",
            "#000000"
        ],
        "customColors": [],
        "shadingContrast": "body-alt-color",
        "whiteContrast": "body-color",
        "bgContrast": "body-color",
        "name": "u14"
    },
    "fontScheme": {
        "name": "custom-font-family-2",
        "default": true,
        "fonts": {
            "heading": "Roboto, sans-serif",
            "text": "Arial, sans-serif",
            "accent": "Arial, sans-serif",
            "headingTitle": "Roboto",
            "textTitle": "Arial"
        }
    },
    "typography": {
        "name": "custom-page-typography-4",
        "title": {
            "font-weight": "400",
            "font-size": 6,
            "line-height": "1_1",
            "margin-top": 20,
            "margin-bottom": 20
        },
        "subtitle": {
            "font-weight": "400",
            "font-size": 1.5,
            "line-height": "1_1",
            "margin-top": 20,
            "margin-bottom": 20,
            "font": "Arial, sans-serif",
            "letter-spacing": 3
        },
        "h1": {
            "font-weight": "400",
            "font-size": 3,
            "line-height": "1_1",
            "margin-top": 20,
            "margin-bottom": 20
        },
        "h2": {
            "font-weight": "400",
            "font-size": 3,
            "line-height": "1_1",
            "margin-top": 20,
            "margin-bottom": 20,
            "font": "Arial, sans-serif"
        },
        "h3": {
            "font-weight": "400",
            "font-size": 1.5,
            "line-height": "1_2",
            "margin-top": 20,
            "margin-bottom": 20,
            "font": "Arial, sans-serif",
            "text-transform": "uppercase",
            "letter-spacing": 3
        },
        "h4": {
            "font-weight": "400",
            "font-size": 1.5,
            "line-height": "1_2",
            "margin-top": 20,
            "margin-bottom": 20
        },
        "h5": {
            "font-weight": "400",
            "font-size": 1.25,
            "line-height": "1_2",
            "margin-top": 20,
            "margin-bottom": 20
        },
        "h6": {
            "font-weight": "400",
            "font-size": 1.125,
            "line-height": "1_2",
            "margin-top": 20,
            "margin-bottom": 20
        },
        "largeText": {
            "font-size": 1.25,
            "margin-top": 20,
            "margin-bottom": 20
        },
        "smallText": {
            "font-size": 0.875,
            "margin-top": 20,
            "margin-bottom": 20
        },
        "text": {
            "margin-top": 20,
            "margin-bottom": 20
        },
        "link": {},
        "button": {
            "color": "palette-1-base",
            "margin-top": 20,
            "margin-bottom": 20
        },
        "blockquote": {
            "font-style": "italic",
            "indent": 20,
            "border": 4,
            "border-color": "palette-1-base",
            "margin-top": 20,
            "margin-bottom": 20
        },
        "metadata": {
            "margin-top": 20,
            "margin-bottom": 20
        },
        "list": {
            "margin-top": 20,
            "margin-bottom": 20
        },
        "orderedlist": {
            "margin-top": 20,
            "margin-bottom": 20
        },
        "postContent": {
            "margin-top": 20,
            "margin-bottom": 20
        },
        "theme": {
            "gradient": "",
            "image": "",
            "sheet-width-xl": 1140,
            "sheet-width-lg": 940,
            "sheet-width-md": 720,
            "sheet-width-sm": 540,
            "sheet-width-xs": 340
        },
        "htmlBaseSize": 16,
        "form-input": {
            "border": 1,
            "border-color": "grey-30",
            "borders": "top right bottom left",
            "color": "white",
            "text-color": "black"
        },
        "hyperlink": {
            "font-size": 1.125,
            "text-color": "palette-1-base",
            "line-height": "1_6",
            "SM": {
                "font-size": 1
            }
        }
    }
}
JSON
, true);
}
add_filter('np_theme_settings', 'theme_app_settings');

function theme_analytics() {
    $pluginSiteSettings = get_plugin_site_settings();
    if (empty($pluginSiteSettings->analyticsCode)) { ?>
        
    <?php }
}
add_action('wp_head', 'theme_analytics', 0);


function theme_intlTelInputMeta() {
    $GLOBALS['meta_tel_input'] = true; ?>
    <meta data-intl-tel-input-cdn-path="<?php echo get_template_directory_uri(); ?>/intlTelInput/" />
    <?php
}
add_action('wp_head', 'theme_intlTelInputMeta', 0);

function theme_favicon() {
    $custom_favicon_id = get_theme_mod('custom_favicon');
    @list($favicon_src, ,) = wp_get_attachment_image_src($custom_favicon_id, 'full');
    if (!$favicon_src) {
        $favicon_src = "";
        if ($favicon_src) {
            $favicon_src = get_template_directory_uri() . '/images/' . $favicon_src;
        }
    }

    if ($favicon_src) {
        echo "<link rel=\"icon\" href=\"$favicon_src\">";
    }
}
add_action('wp_head', 'theme_favicon');

function theme_gtm_header() {
    $pluginSiteSettings = get_plugin_site_settings();
    if (empty($pluginSiteSettings->googleTagManager) || empty($pluginSiteSettings->googleTagManagerCode)) { ?>
        
    <?php }
}
add_action('wp_head', 'theme_gtm_header', 0);

function theme_gtm_body() {
    $pluginSiteSettings = get_plugin_site_settings();
    if (empty($pluginSiteSettings->googleTagManager) || empty($pluginSiteSettings->googleTagManagerCodeNoScript)) {
        ob_start(); ?>
        
        <?php $gtmCodeNoScript = ob_get_clean(); ?>
        <script>
            jQuery(document).ready(function () {
                jQuery(document).find('body').prepend(`<?php echo $gtmCodeNoScript; ?>`)
            });
        </script>
    <?php }
}
add_action('wp_footer', 'theme_gtm_body');