<?php
defined('ABSPATH') or die;

class CFormFields {
    public $fields = array();

    /**
     * Parse fields from publishHtml
     *
     * @param string $form_html
     */
    public function parseFromHtml($form_html) {
        preg_match_all('#<(input|textarea|select)([^>]*)>#', $form_html, $matches);

        $radioButtons = array();
        $checkboxIds = array();
        $checkboxElements = array();
        for ($i = 0; $i < count($matches[0]); $i++) {
            $attrs = $matches[2][$i];
            if (!preg_match('#name="([^"]*)#', $attrs, $m) || strpos($attrs, 'type="hidden"') !== false) {
                continue;
            }
            $name = $m[1];

            if ($name === 'name') { // see detect_unavailable_names
                $name = 'name1';
            }

            if ($matches[1][$i] === 'select') {
                $selectRegExp = '#<select [\s\S]+? name=["|\']' . $name . '["|\']([^>]*)>([\s\S]+?)<\/select>#';
                preg_match_all($selectRegExp, $form_html, $matchesSelect);
                $isCountry = strpos($matchesSelect[0][0], 'id="country') !== false;
                $optionHtml = preg_replace('/data-calc=["\'][\s\S]*?["\'] ?/', '', $matchesSelect[2][0]);
                $optionHtml = preg_replace('/ +?selected="[\s\S]+?"/', '', $optionHtml);
                preg_match_all('#<option value="([^"]+)"#', $optionHtml, $matchesOption);
            }

            $required = strpos($attrs, 'required') !== false;
            $multiple = strpos($attrs, 'multiple') !== false;

            $field = array(
                'required' => $required,
                'name' => $name,
            );
            if ($matches[1][$i] === 'select') {
                $field['option'] = $isCountry ? self::$countries : $matchesOption[1];
                $field['multiple'] = $multiple;
                $field['type'] = 'select';
            }
            if ($matches[1][$i] === 'textarea') {
                $field['type'] = 'textarea';
            }
            if (strpos($attrs, 'type="radio"') !== false) {
                preg_match('#value=["|\']([\s\S]+?)["|\']#', $attrs, $matchesValue);
                $field['value'] = $matchesValue[1];
                if (!array_key_exists($name, $radioButtons)) {
                    $this->fields[] = array();
                    $radioButtons[$name] = array(
                        'type' => 'radio',
                        'name' => $field['name'],
                        'default' => 'default:1',
                        'option' => array($field['value']),
                        'index' => count($this->fields) - 1,

                    );
                } else {
                    array_push($radioButtons[$name]['option'], $field['value']);
                }
            } else if (strpos($attrs, 'type="checkbox"') !== false) {
                preg_match('#value=["|\']([\s\S]+?)["|\']#', $attrs, $matchesValue);
                preg_match('#id=["|\']([\s\S]+?)["|\']#', $attrs, $matchesId);
                $field['value'] = isset($matchesValue[1]) ? $matchesValue[1] : '1';
                $checkboxId = isset($matchesId[1]) ? $matchesId[1] : 0;
                if (!array_key_exists($checkboxId, $checkboxIds)) {
                    //$this->fields[] = array();
                    if (!isset($checkboxElements[$name])) {
                        $checkboxElements[$name] = array();
                    }
                    $checkboxIds[$checkboxId] = $checkboxId;
                    $counter = count($checkboxElements[$name]);
                    $checkboxElements[$name][$counter] = array(
                        'type' => 'checkbox',
                        'name' => str_replace('[]', '', $field['name']),
                        'value' => $field['value'],
                        'required' => $field['required'],
                    );
                }
            } else if (strpos($attrs, 'type="file"') !== false) {
                $field['type'] = "file";
                preg_match('#accept=["|\']([\s\S]*?)["|\']#', $attrs, $matchesValue);
                $field['accept'] = isset($matchesValue[1]) && $matchesValue[1] ? $matchesValue[1] : 'ALL';
                if (strpos($field['name'], '[') !== false) {
                    $field['name'] = str_replace(array('[', ']'), array('',''), $field['name']);
                }
                $this->fields[] = $field;
            } else {
                $this->fields[] = $field;
            }
        }
        foreach ($radioButtons as $key=> $radio) {
            $this->fields[$radio['index']] = $radio;
        }
        foreach ($checkboxElements as $key=> $checkbox) {
            if (count($checkbox) > 0) {
                $checkboxValues = array();
                foreach ($checkbox as $id=> $item) {
                    $checkboxValues[] = $item['value'];
                    if ($id + 1 === count($checkbox)) {
                        //last checkbox group element
                        $item['value'] = $checkboxValues;
                        $this->fields[] = $item;
                    }
                }
            } else {
                //single checkbox element
                $this->fields[] = $checkbox[0];
            }
        }
    }

    /**
     * Convert to contact7 format
     *
     * @return string
     */
    public function toString() {
        if (!function_exists('_arr')) {
            /**
             * Get array value by specified key
             *
             * @param array      $array
             * @param string|int $key
             * @param mixed      $default
             *
             * @return mixed
             */
            function _arr(&$array, $key, $default = false) {
                if (isset($array[$key])) {
                    return $array[$key];
                }
                return $default;
            }
        }
        $result = '';
        foreach ($this->fields as $field) {
            $type = isset($field['type']) ? $field['type'] : 'text';
            if (isset($field['option'])) {
                $optionStr = '';
                foreach ($field['option'] as $option) {
                    $optionStr .= ' "' . $option . '"';
                }

                $tagName = _arr(self::$_nameTags, $field['name'], $type);
                $required = isset($field['required']) && $field['required'] ? '*' : '';
                $multiple = isset($field['multiple']) && $field['multiple'] ? ' multiple' : '';
                $default = isset($field['default']) ? (' ' . $field['default']) : '';
                $result .= sprintf("[%s%s %s%s%s%s]\n", $tagName, $required, $field['name'], $multiple, $default, $optionStr);
            } else if ($type === 'file') {
                if (isset(self::$formats[$field['accept']])) {
                    $allowed_formats = self::$formats[$field['accept']] ? ' filetypes:' . self::$formats[$field['accept']] : '';
                } else {
                    // custom formats
                    $allowed_formats = ' filetypes:' . str_replace(array(',', '.'), array('|', ''), $field['accept']);
                }
                $max_file_size = ' limit:10485760'; // 10mb
                $result .= sprintf("[%s%s %s%s%s]\n", _arr(self::$_nameTags, $field['name'], $field['type']), $field['required'] ? '*' : '', $field['name'], $max_file_size, $allowed_formats);
            } else if ($type === 'checkbox') {
                $valuesStr = '';
                if (isset($field['value'])) {
                    foreach ($field['value'] as $value) {
                        $valuesStr .= ' "' . $value . '"';
                    }
                }
                $result .= sprintf("[%s%s %s%s]\n", _arr(self::$_nameTags, $field['name'], $field['type']), $field['required'] ? '*' : '', $field['name'], $valuesStr);
            } else {
                $result .= sprintf("[%s%s %s]\n", _arr(self::$_nameTags, $field['name'], $type), $field['required'] ? '*' : '', $field['name']);
            }
        }
        $result .= "[submit]\n";
        return $result;
    }

    /**
     * Check for existing field with such name
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasField($name) {
        foreach ($this->fields as $field) {
            if ($field['name'] === $name) {
                return true;
            }
        }
        return false;
    }

    private static $_nameTags = array(
        'email' => 'email',
        'tel' => 'tel',
        'message' => 'textarea',
        'select' => 'select',
        'radio' => 'radio',
        'file' => 'file',
        'checkbox' => 'checkbox',
    );

    public static $formats = array (
        'IMAGES' => 'bmp|dng|eps|gif|jpg|jpeg|png|ps|raw|svg|tga|tif|tiff',
        'DOCUMENTS' => 'ai|cdr|csv|doc|docb|docx|dot|dotx|dwg|eps|epub|fla|gpx|ical|icalendar|ics|ifb|indd|ipynb|key|kml|kmz|mobi|mtf|mtx|numbers|odg|odp|ods|odt|otp|ots|ott|oxps|pages|pdf|pdn|pkg|pot|potx|pps|ppsx|ppt|pptx|psd|pub|rtf|sldx|txt|vcf|xcf|xls|xlsx|xlt|xltx|xlw|xps|zip',
        'VIDEO' => '3gp|avi|divx|flv|m1v|m2ts|m4v|mkv|mov|mp4|mpe|mpeg|mpg|mxf|ogv|vob.webm|wmv|xvid',
        'AUDIO' => 'aac|aif|aiff|flac|m4a|mp3|wav|wma',
        'ALL' => 'bmp|dng|eps|gif|jpg|jpeg|png|ps|raw|svg|tga|tif|tiff|ai|cdr|csv|doc|docb|docx|dot|dotx|dwg|eps|epub|fla|gpx|ical|icalendar|ics|ifb|indd|ipynb|key|kml|kmz|mobi|mtf|mtx|numbers|odg|odp|ods|odt|otp|ots|ott|oxps|pages|pdf|pdn|pkg|pot|potx|pps|ppsx|ppt|pptx|psd|pub|rtf|sldx|txt|vcf|xcf|xls|xlsx|xlt|xltx|xlw|xps|zip|3gp|avi|divx|flv|m1v|m2ts|m4v|mkv|mov|mp4|mpe|mpeg|mpg|mxf|ogv|vob.webm|wmv|xvid|aac|aif|aiff|flac|m4a|mp3|wav|wma',
    );

    public static $countries = array(
        "Åland Islands", "Afghanistan", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda",
        "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus",
        "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia, Plurinational State of", "Bonaire, Sint Eustatius and Saba", "Bosnia and Herzegovina",
        "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia",
        "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island",
        "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", "Congo, the Democratic Republic of the", "Cook Islands", "Costa Rica", "Côte d'Ivoire",
        "Croatia", "Cuba", "Curaçao", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "Ecuador",
        "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland",
        "France", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar",
        "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guernsey", "Guinea", "Guinea-Bissau", "Guyana", "Haiti",
        "Heard Island and McDonald Islands", "Holy See (Vatican City State)", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran, Islamic Republic of",
        "Iraq", "Ireland", "Isle of Man", "Israel", "Italy", "Jamaica", "Japan", "Jersey", "Jordan", "Kazakhstan", "Kenya",
        "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan", "Lao People's Democratic Republic", "Latvia", "Lebanon", "Lesotho",
        "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Macao", "Macedonia, the former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia",
        "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of",
        "Moldova, Republic of", "Monaco", "Mongolia", "Montenegro", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru",
        "Nepal", "Netherlands", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands",
        "Norway", "Oman", "Pakistan", "Palau", "Palestinian Territory, Occupied", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines",
        "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Réunion", "Romania", "Russian Federation", "Rwanda", "Saint Barthélemy",
        "Saint Helena, Ascension and Tristan da Cunha", "Saint Kitts and Nevis", "Saint Lucia", "Saint Martin (French part)", "Saint Pierre and Miquelon",
        "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Serbia", "Seychelles", "Sierra Leone",
        "Singapore", "Sint Maarten (Dutch part)", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and the South Sandwich Islands",
        "South Sudan", "Spain", "Sri Lanka", "Sudan", "Suriname", "Svalbard and Jan Mayen", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic",
        "Taiwan", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Timor-Leste", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia",
        "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "United States Minor Outlying Islands",
        "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela, Bolivarian Republic of", "Viet Nam", "Virgin Islands, British", "Virgin Islands, U.S.", "Wallis and Futuna", "Western Sahara",
        "Yemen", "Zambia", "Zimbabwe"
    );
}

class CForms {
    /**
     * Update forms data sources
     * Create new if needed
     *
     * @param array $forms
     * @param string $template
     *
     * @return array form data sources array
     */
    public static function _updateForms($forms, $template) {
        if (!class_exists('WPCF7_ContactForm')) {
            return array();
        }
        $count = count($forms);
        $defaultLanguage = class_exists('ThemeMultiLanguages') ? ThemeMultiLanguages::get_np_default_lang() : 'en';
        $lang = isset($_GET['lang']) && $_GET['lang'] !== $defaultLanguage ? $_GET['lang'] : '';
        $lang_prefix = $lang ? $lang . '_' : '';
        $optionName = $lang_prefix . $template . '_' . 'forms_theme'; //header/footer/custom
        $prev_data_sources = get_option($optionName);
        $data_sources = array();

        for ($i = 0; $i < $count; $i++) {
            $form_html = $forms[$i];
            $form_id = isset($prev_data_sources[$i]['id']) ? $prev_data_sources[$i]['id'] : 0;
            $contact_form = null;

            if ($form_id) {
                $contact_form = wpcf7_contact_form($form_id);
            }

            if (!$form_id || !$contact_form) {
                $form_id = 0;
                $contact_form = WPCF7_ContactForm::get_template();
                $form_title = "";
                if ($template === "footer") {
                    $form_title = sprintf(__('Form: %s', 'kakiteollisuus'), "Footer");
                }
                if ($template === "header") {
                    $form_title = sprintf(__('Form: %s', 'kakiteollisuus'), "Header");
                }
                if ($template === "custom") {
                    if (is_singular()) {
                        global $post;
                        $id = isset($post->ID) ? $post->ID : '';
                    } else {
                        $id = 'template-' . $i;
                    }
                    $form_title = sprintf(__('Form: %s', 'kakiteollisuus'), "Custom-" . $id);
                }
                if ($count > 1) {
                    $form_title .= ' (' . ($i + 1) . ')';
                }
                if ($lang) {
                    $form_title = trim($form_title) . ' (' . strtoupper($lang) . ')';
                }
                $contact_form->set_title($form_title);
            }

            $fields = new CFormFields();
            $fields->parseFromHtml($form_html);

            $properties = $contact_form->get_properties();
            $properties['form'] = $fields->toString();

            if (!$form_id) {
                $defaultMail = array(
                    '"[your-subject]"',
                    "Subject: [your-subject]\n",
                    '[your-email]',
                    '[your-name]',
                    '[your-message]',
                );

                $actualMail = array(
                    __('feedback', 'kakiteollisuus'),
                    '',
                    '[email]',
                    $fields->hasField('name1') ? '[name1]' : '',
                );

                $customFields = '';
                $attachmentsFields = '';
                foreach ($fields->fields as $field) {
                    if ($field['name'] !== 'email' && $field['name'] !== 'name1') {
                        $customField = $fields->hasField($field['name']) ? '[' . $field['name'] . ']' : '';
                        $customFields .= $field['name'] . ': ' . $customField . ', ';
                    }
                    if (isset($field['type']) && $field['type'] === 'file') {
                        $attachmentsFields .= '[' . $field['name'] . ']';
                    }
                }
                $actualMail[] = $customFields;

                foreach (array('mail', 'mail_2') as $mail_key) {
                    foreach ($properties[$mail_key] as $key => &$prop) {
                        if (is_string($prop)) {
                            if ($key === 'attachments') {
                                $prop = $attachmentsFields;
                            } else {
                                $prop = str_replace(
                                    $defaultMail,
                                    $actualMail,
                                    $prop
                                );
                            }
                        }
                    }
                }
            }
            $contact_form->set_properties($properties);

            $form_id = $contact_form->save();

            $data_sources[] = array(
                'id' => $form_id,
            );
        }
        if ($template === "header" || $template === "footer") {
            update_option($optionName, $data_sources);
        }
        if ($template === "custom") {
            if(count($data_sources) > 0) {
                update_option($optionName, $data_sources);
            }
        }
        return $data_sources;
    }

    public static $_formHtml;
    public static $_formIdx = 0;

    /**
     * Filter on wpcf7_form_elements
     * Replace default contact7 fields with Np fields
     *
     * @param string $html
     *
     * @return string
     */
    public static function _formElementsFilter($html) {
        $fields_html = preg_replace('#<form[^>]*>#', '', self::$_formHtml);
        $fields_html = str_replace('</form>', '', $fields_html);
        preg_match('/type="file"[\s\S]*?name="([\s\S]*?)"/', $fields_html, $matches);
        $file_input_name = isset($matches[1]) ? $matches[1] : '';
        if (strpos($file_input_name, '[') !== false) {
            $file_input_name = str_replace(array('[', ']'), array('',''), $file_input_name);
            $fields_html = str_replace($matches[1], $file_input_name, $fields_html);
        }
        $html = str_replace(
            array(
                'name="name"',
                'u-input ',
                'class="u-agree-checkbox ',
                'u-form-submit',
                'u-form-group',
                'u-file-group',
                'u-btn-submit ',
                'accept="IMAGES"',
                'accept="DOCUMENTS"',
                'accept="VIDEO"',
                'accept="AUDIO"',
                'multiple="multiple"',
            ),
            array(
                'name="name1"',
                'u-input wpcf7-form-control ',
                'value="1" class="u-agree-checkbox wpcf7-form-control ',
                'u-form-submit wpcf7-form-control',
                'u-form-group wpcf7-form-control-wrap',
                'u-file-group wpcf7-form-control-wrap ' . $file_input_name,
                'u-btn-submit wpcf7-submit ',
                'accept=".jpg,.jpeg,.png,.gif"',
                'accept=".pdf,.doc,.docx,.ppt,.pptx,.odt"',
                'accept=".avi,.mov,.mp4,.mpg,.wmv"',
                'accept=".ogg,.m4a,.mp3,.wav"',
                '',
            ),
            $fields_html
        );
        $html .= '<input type="hidden" name="_contact7_backend" value="1">';
        return $html;
    }

    /**
     * Process Np form html
     *
     * @param string|int $form_id
     * @param string     $form_raw_html
     *
     * @return string
     */
    public static function getHtml($form_id, $form_raw_html) {
        if (function_exists('wpcf7_contact_form') && $form_id && ($contact_form = wpcf7_contact_form($form_id))) {
            self::$_formHtml = $form_raw_html;

            add_filter('wpcf7_form_elements', 'CForms::_formElementsFilter', 9);
            add_filter('wpcf7_form_novalidate', '__return_false');

            $form_class = '';
            if (preg_match('#<form.*?class="([^"]*)#', $form_raw_html, $m)) {
                $form_class = $m[1];
            }
            $form_html = $contact_form->form_html(array('html_class' => $form_class . ' u-form-custom-backend'));
            if (strpos($form_raw_html, 'redirect="true"') !== false && preg_match('#redirect-address="([^"]*)"#', $form_raw_html, $m)) {
                $form_html = str_replace('<form', '<form redirect-address="' . $m[1] . '"', $form_html);
            }

            remove_filter('wpcf7_form_elements', 'CForms::_formElementsFilter', 9);
            remove_filter('wpcf7_form_novalidate', '__return_false');
        } else {
            $form_html = preg_replace('#action="[^"]*#', 'action="#', $form_raw_html);
        }
        if (self::$_formIdx === 0) {
            $form_html = CForms::getScriptsAndStyles() . "\n" . $form_html;
        }
        self::$_formIdx++;
        return $form_html;
    }

    /**
     * Common scripts and styles for all forms
     *
     * @return string
     */
    public static function getScriptsAndStyles() {
        ob_start();
        ?>
        <script>
            function onSuccess(event) {
                if (typeof window.serviceRequest !== 'undefined') {
                    window.serviceRequest(jQuery(this).find('form'));
                }
                var msgContainer = jQuery(event.currentTarget).find('.wpcf7-response-output');
                msgContainer.removeClass('u-form-send-error').addClass('u-form-send-message u-form-send-success');
                msgContainer.show();
                var redirectAddress = jQuery(event.currentTarget).find('[redirect-address]').attr('redirect-address');
                if (redirectAddress) {
                    setTimeout(function () {
                        location.replace(redirectAddress);
                    }, 2000);
                }
            }
            function onError(event) {
                var msgContainer = jQuery(event.currentTarget).find('.wpcf7-response-output');
                msgContainer.removeClass('u-form-send-success').addClass('u-form-send-message u-form-send-error');
                msgContainer.show();
            }

            jQuery('body')
                .on('wpcf7mailsent',   '.u-form .wpcf7', onSuccess)
                .on('wpcf7invalid',    '.u-form .wpcf7', onError)
                .on('wpcf7:unaccepted', '.u-form .wpcf7', onError)
                .on('wpcf7spam',       '.u-form .wpcf7', onError)
                .on('wpcf7:aborted',    '.u-form .wpcf7', onError)
                .on('wpcf7mailfailed', '.u-form .wpcf7', onError);
        </script>
        <style>
            .u-form .wpcf7-response-output {
                /*position: relative !important;*/
                margin: 0 !important;
                bottom: -70px!important;
            }
            .u-form .wpcf7 .ajax-loader {
                margin-left: -24px;
                margin-right: 0;
            }
        </style>
        <?php
        return ob_get_clean();
    }

    /**
     * Filter on wpcf7_ajax_json_echo
     * Replace selectors for Np forms
     *
     * @param array $items
     *
     * @return array
     */
    public static function _ajaxJsonEchoFilter($items) {
        if (isset($_POST['_contact7_backend']) && !empty($items['invalids'])) {
            foreach ($items['invalids'] as &$invalid) {
                $invalid['into'] = str_replace('span.wpcf7-form-control-wrap.', 'div.u-form-group.u-form-', $invalid['into']);
            }
        }
        return $items;
    }
}

add_filter('wpcf7_ajax_json_echo', 'CForms::_ajaxJsonEchoFilter');