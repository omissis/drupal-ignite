(function ($) {
    "use strict";

    window._skel_config = {
        preset: 'standard',
        prefix: 'css/style',
        resetCSS: true,
        breakpoints: {
            'desktop': {
                grid: {
                    gutters: 50
                }
            }
        }
    };

    var initSmoothScroll = function () {
        $('a[href^="#"]').each(function (i, el) {
            var $el = $(el);

            $el.on('click', function (e) {
                e.preventDefault();

                $.scrollTo($el.attr('href'));
            });
        });
    };

    var initMenu = function ($menuItems) {
        $menuItems.on('click', function (e) {
            e.preventDefault();

            var $currentItem = $(e.currentTarget);

            $menuItems.removeClass('current_page_item');
            $currentItem.addClass('current_page_item');
        });
    };

    var initScrollSpy = function ($menuItems) {
        $menuItems.each(function (i, el) {
            var $item = $(el);
            var $el = $('a[name="' + $item.find('a').attr('href').substring(1) + '"]');

            $.scrollSpy($el, function ($el) {
                $menuItems.removeClass('current_page_item');
                $item.addClass('current_page_item');
            }, function ($el) {
                $menuItems.removeClass('current_page_item');
                $item.prev('li').addClass('current_page_item');
            });
        });
    };

    var initStickyMenu = function ($menu) {
        $menu.sticky({ topSpacing: 0 });
    };

    $(document).ready(function() {
        var $menu = $('#nav');
        var $menuItems = $menu.find('ul > li');

        initSmoothScroll();
        initMenu($menuItems);
        initStickyMenu($menu);
        initScrollSpy($menuItems);
    });
}(jQuery));
