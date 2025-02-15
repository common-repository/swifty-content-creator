<?php

// Exit if accessed directly
defined( 'ABSPATH' ) or exit;

/**
 * Class FAIcons Font Awesome names
 */
class FAIcons
{
    /**
     * return a list of font awesome classes, prepend the list with a no icon option
     *
     * @return string
     */
    public static function icons()
    {
        $icons = array();
        foreach( (array) FAIcons::icon_data() as $icon ) {
            if( $icon === 'fw' ) {
                $icons[] = '<i class="fa fa-fw" title="' . __( 'No icon', 'swifty-content-creator' ) . '"></i>';
            } else {
                $icons[] = '<i class="fa fa-' . $icon . '" title="' . $icon . '"></i>';
            }
        }
        return implode( '', $icons );
    }

    /**
     * Font-Awesome icons, 4.2.0
     */
    public static function icon_data()
    {
        // uncomment and get the error log to create a new list of font-awesome icon names
//        $pattern = '/\.fa-((\w+(?:-)?)+):before{\s*content:/';
//        $subject = file_get_contents( 'http://netdna.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css' );
//        preg_match_all( $pattern, $subject, $matches, PREG_SET_ORDER );
//
//        $icons = array();
//        foreach( $matches as $match ) {
//            $icons[] = $match[ 1 ];
//        }
//        sort( $icons );
//        error_log( implode( "', '", $icons ) );

        return array( 'fw', '500px', 'adjust', 'adn', 'align-center', 'align-justify', 'align-left', 'align-right',
            'amazon', 'ambulance', 'anchor', 'android', 'angellist', 'angle-double-down', 'angle-double-left',
            'angle-double-right', 'angle-double-up', 'angle-down', 'angle-left', 'angle-right', 'angle-up', 'apple',
            'archive', 'area-chart', 'arrow-circle-down', 'arrow-circle-left', 'arrow-circle-o-down',
            'arrow-circle-o-left', 'arrow-circle-o-right', 'arrow-circle-o-up', 'arrow-circle-right', 'arrow-circle-up',
            'arrow-down', 'arrow-left', 'arrow-right', 'arrow-up', 'arrows', 'arrows-alt', 'arrows-h', 'arrows-v',
            'asterisk', 'at', 'backward', 'balance-scale', 'ban', 'bar-chart', 'barcode', 'bars', 'battery-empty',
            'battery-full', 'battery-half', 'battery-quarter', 'battery-three-quarters', 'bed', 'beer', 'behance',
            'behance-square', 'bell', 'bell-o', 'bell-slash', 'bell-slash-o', 'bicycle', 'binoculars', 'birthday-cake',
            'bitbucket', 'bitbucket-square', 'black-tie', 'bold', 'bolt', 'bomb', 'book', 'bookmark', 'bookmark-o',
            'briefcase', 'btc', 'bug', 'building', 'building-o', 'bullhorn', 'bullseye', 'bus', 'buysellads',
            'calculator', 'calendar', 'calendar-check-o', 'calendar-minus-o', 'calendar-o', 'calendar-plus-o',
            'calendar-times-o', 'camera', 'camera-retro', 'car', 'caret-down', 'caret-left', 'caret-right',
            'caret-square-o-down', 'caret-square-o-left', 'caret-square-o-right', 'caret-square-o-up', 'caret-up',
            'cart-arrow-down', 'cart-plus', 'cc', 'cc-amex', 'cc-diners-club', 'cc-discover', 'cc-jcb', 'cc-mastercard',
            'cc-paypal', 'cc-stripe', 'cc-visa', 'certificate', 'chain-broken', 'check', 'check-circle',
            'check-circle-o', 'check-square', 'check-square-o', 'chevron-circle-down', 'chevron-circle-left',
            'chevron-circle-right', 'chevron-circle-up', 'chevron-down', 'chevron-left', 'chevron-right', 'chevron-up',
            'child', 'chrome', 'circle', 'circle-o', 'circle-o-notch', 'circle-thin', 'clipboard', 'clock-o', 'clone',
            'cloud', 'cloud-download', 'cloud-upload', 'code', 'code-fork', 'codepen', 'coffee', 'cog', 'cogs',
            'columns', 'comment', 'comment-o', 'commenting', 'commenting-o', 'comments', 'comments-o', 'compass',
            'compress', 'connectdevelop', 'contao', 'copyright', 'creative-commons', 'credit-card', 'crop',
            'crosshairs', 'css3', 'cube', 'cubes', 'cutlery', 'dashcube', 'database', 'delicious', 'desktop',
            'deviantart', 'diamond', 'digg', 'dot-circle-o', 'download', 'dribbble', 'dropbox', 'drupal', 'eject',
            'ellipsis-h', 'ellipsis-v', 'empire', 'envelope', 'envelope-o', 'envelope-square', 'eraser', 'eur',
            'exchange', 'exclamation', 'exclamation-circle', 'exclamation-triangle', 'expand', 'expeditedssl',
            'external-link', 'external-link-square', 'eye', 'eye-slash', 'eyedropper', 'facebook', 'facebook-official',
            'facebook-square', 'fast-backward', 'fast-forward', 'fax', 'female', 'fighter-jet', 'file',
            'file-archive-o', 'file-audio-o', 'file-code-o', 'file-excel-o', 'file-image-o', 'file-o', 'file-pdf-o',
            'file-powerpoint-o', 'file-text', 'file-text-o', 'file-video-o', 'file-word-o', 'files-o', 'film', 'filter',
            'fire', 'fire-extinguisher', 'firefox', 'flag', 'flag-checkered', 'flag-o', 'flask', 'flickr', 'floppy-o',
            'folder', 'folder-o', 'folder-open', 'folder-open-o', 'font', 'fonticons', 'forumbee', 'forward',
            'foursquare', 'frown-o', 'futbol-o', 'gamepad', 'gavel', 'gbp', 'genderless', 'get-pocket', 'gg',
            'gg-circle', 'gift', 'git', 'git-square', 'github', 'github-alt', 'github-square', 'glass', 'globe',
            'google', 'google-plus', 'google-plus-square', 'google-wallet', 'graduation-cap', 'gratipay', 'h-square',
            'hacker-news', 'hand-lizard-o', 'hand-o-down', 'hand-o-left', 'hand-o-right', 'hand-o-up', 'hand-paper-o',
            'hand-peace-o', 'hand-pointer-o', 'hand-rock-o', 'hand-scissors-o', 'hand-spock-o', 'hdd-o', 'header',
            'headphones', 'heart', 'heart-o', 'heartbeat', 'history', 'home', 'hospital-o', 'hourglass',
            'hourglass-end', 'hourglass-half', 'hourglass-o', 'hourglass-start', 'houzz', 'html5', 'i-cursor', 'ils',
            'inbox', 'indent', 'industry', 'info', 'info-circle', 'inr', 'instagram', 'internet-explorer', 'ioxhost',
            'italic', 'joomla', 'jpy', 'jsfiddle', 'key', 'keyboard-o', 'krw', 'language', 'laptop', 'lastfm',
            'lastfm-square', 'leaf', 'leanpub', 'lemon-o', 'level-down', 'level-up', 'life-ring', 'lightbulb-o',
            'line-chart', 'link', 'linkedin', 'linkedin-square', 'linux', 'list', 'list-alt', 'list-ol', 'list-ul',
            'location-arrow', 'lock', 'long-arrow-down', 'long-arrow-left', 'long-arrow-right', 'long-arrow-up',
            'magic', 'magnet', 'male', 'map', 'map-marker', 'map-o', 'map-pin', 'map-signs', 'mars', 'mars-double',
            'mars-stroke', 'mars-stroke-h', 'mars-stroke-v', 'maxcdn', 'meanpath', 'medium', 'medkit', 'meh-o',
            'mercury', 'microphone', 'microphone-slash', 'minus', 'minus-circle', 'minus-square', 'minus-square-o',
            'mobile', 'money', 'moon-o', 'motorcycle', 'mouse-pointer', 'music', 'neuter', 'newspaper-o',
            'object-group', 'object-ungroup', 'odnoklassniki', 'odnoklassniki-square', 'opencart', 'openid', 'opera',
            'optin-monster', 'outdent', 'pagelines', 'paint-brush', 'paper-plane', 'paper-plane-o', 'paperclip',
            'paragraph', 'pause', 'paw', 'paypal', 'pencil', 'pencil-square', 'pencil-square-o', 'phone',
            'phone-square', 'picture-o', 'pie-chart', 'pied-piper', 'pied-piper-alt', 'pinterest', 'pinterest-p',
            'pinterest-square', 'plane', 'play', 'play-circle', 'play-circle-o', 'plug', 'plus', 'plus-circle',
            'plus-square', 'plus-square-o', 'power-off', 'print', 'puzzle-piece', 'qq', 'qrcode', 'question',
            'question-circle', 'quote-left', 'quote-right', 'random', 'rebel', 'recycle', 'reddit', 'reddit-square',
            'refresh', 'registered', 'renren', 'repeat', 'reply', 'reply-all', 'retweet', 'road', 'rocket', 'rss',
            'rss-square', 'rub', 'safari', 'scissors', 'search', 'search-minus', 'search-plus', 'sellsy', 'server',
            'share', 'share-alt', 'share-alt-square', 'share-square', 'share-square-o', 'shield', 'ship',
            'shirtsinbulk', 'shopping-cart', 'sign-in', 'sign-out', 'signal', 'simplybuilt', 'sitemap', 'skyatlas',
            'skype', 'slack', 'sliders', 'slideshare', 'smile-o', 'sort', 'sort-alpha-asc', 'sort-alpha-desc',
            'sort-amount-asc', 'sort-amount-desc', 'sort-asc', 'sort-desc', 'sort-numeric-asc', 'sort-numeric-desc',
            'soundcloud', 'space-shuttle', 'spinner', 'spoon', 'spotify', 'square', 'square-o', 'stack-exchange',
            'stack-overflow', 'star', 'star-half', 'star-half-o', 'star-o', 'steam', 'steam-square', 'step-backward',
            'step-forward', 'stethoscope', 'sticky-note', 'sticky-note-o', 'stop', 'street-view', 'strikethrough',
            'stumbleupon', 'stumbleupon-circle', 'subscript', 'subway', 'suitcase', 'sun-o', 'superscript', 'table',
            'tablet', 'tachometer', 'tag', 'tags', 'tasks', 'taxi', 'television', 'tencent-weibo', 'terminal',
            'text-height', 'text-width', 'th', 'th-large', 'th-list', 'thumb-tack', 'thumbs-down', 'thumbs-o-down',
            'thumbs-o-up', 'thumbs-up', 'ticket', 'times', 'times-circle', 'times-circle-o', 'tint', 'toggle-off',
            'toggle-on', 'trademark', 'train', 'transgender', 'transgender-alt', 'trash', 'trash-o', 'tree', 'trello',
            'tripadvisor', 'trophy', 'truck', 'try', 'tty', 'tumblr', 'tumblr-square', 'twitch', 'twitter',
            'twitter-square', 'umbrella', 'underline', 'undo', 'university', 'unlock', 'unlock-alt', 'upload', 'usd',
            'user', 'user-md', 'user-plus', 'user-secret', 'user-times', 'users', 'venus', 'venus-double', 'venus-mars',
            'viacoin', 'video-camera', 'vimeo', 'vimeo-square', 'vine', 'vk', 'volume-down', 'volume-off', 'volume-up',
            'weibo', 'weixin', 'whatsapp', 'wheelchair', 'wifi', 'wikipedia-w', 'windows', 'wordpress', 'wrench',
            'xing', 'xing-square', 'y-combinator', 'yahoo', 'yelp', 'youtube', 'youtube-play', 'youtube-square' );
    }
}