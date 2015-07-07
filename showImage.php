<?php
/**
 * Created by PhpStorm.
 * User: kirillsidorov
 * Date: 07.07.15
 * Time: 15:10
 */

$images = json_decode($_REQUEST['images']);

?>

<html>
<head>
    <title>Photos</title>

    <script src='http://photoswipe.s3-eu-west-1.amazonaws.com/pswp/dist/photoswipe.min.js'></script>
    <script src='http://photoswipe.s3-eu-west-1.amazonaws.com/pswp/dist/photoswipe-ui-default.min.js'></script>
    <script src='http://doska.yugs.ru/js/swipebox.js'></script>

    <link rel='stylesheet prefetch' href='http://photoswipe.s3.amazonaws.com/pswp/dist/photoswipe.css'>
    <link rel='stylesheet prefetch' href='http://photoswipe.s3.amazonaws.com/pswp/dist/default-skin/default-skin.css'>
</head>
<body style="background: black;">



<!-- Root element of PhotoSwipe. Must have class pswp. -->
<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">

    <!-- Background of PhotoSwipe.
         It's a separate element as animating opacity is faster than rgba(). -->
    <div class="pswp__bg"></div>

    <!-- Slides wrapper with overflow:hidden. -->
    <div class="pswp__scroll-wrap">

        <!-- Container that holds slides.
            PhotoSwipe keeps only 3 of them in the DOM to save memory.
            Don't modify these 3 pswp__item elements, data is added later on. -->
        <div class="pswp__container">
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
        </div>

        <!-- Default (PhotoSwipeUI_Default) interface on top of sliding area. Can be changed. -->
        <div class="pswp__ui pswp__ui--hidden">

            <div class="pswp__top-bar">

                <!--  Controls are self-explanatory. Order can be changed. -->

                <div class="pswp__counter"></div>

                <button class="pswp__button pswp__button--close" title="Close (Esc)"></button>

                <button class="pswp__button pswp__button--share" title="Share"></button>

                <button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>

                <button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>

                <!-- Preloader demo http://codepen.io/dimsemenov/pen/yyBWoR -->
                <!-- element will get class pswp__preloader--active when preloader is running -->
                <div class="pswp__preloader">
                    <div class="pswp__preloader__icn">
                        <div class="pswp__preloader__cut">
                            <div class="pswp__preloader__donut"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                <div class="pswp__share-tooltip"></div>
            </div>

            <button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)">
            </button>

            <button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)">
            </button>

            <div class="pswp__caption">
                <div class="pswp__caption__center"></div>
            </div>

        </div>

    </div>

</div>

<script type="text/javascript">
    var pswpElement = document.querySelectorAll('.pswp')[0];

    var items = [
        <? foreach ($images as $image): ?>
        {
            src: "<?=$image?>",
            w: 600,
            h: 400
        },
        <? endforeach; ?>
    ]

    // define options (if needed)
    var options = {
        // optionName: 'option value'
        // for example:
        index: 0 // start at first slide
    };

    document.addEventListener('WebViewJavascriptBridgeReady', function(event) {
        window.ios_bridge = event.bridge;
        bridge.init(function(data, responseCallback) {

        });
    }, false);

    // Initializes and opens PhotoSwipe
    var gallery = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options);
    gallery.init();
    gallery.listen('close', function() {
        if (typeof webViewHandler != "undefined") webViewHandler.closeGallery();
        if (typeof window.ios_bridge != "undefined") {
            window.ios_bridge.send({ mode: "close" });
        }
    });
</script>
</body>
</html>