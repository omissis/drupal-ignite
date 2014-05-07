(function ($) {
    "use strict";

    $.extend({
        /**
         * Check if the given object is undefined or null.
         *
         * @param  {Object} obj
         *
         * @return {Boolean}
         */
        isVoid : function (obj) {
            return typeof obj === 'undefined' || obj === null;
        },

        /**
         * Smooth page scrolling.
         *
         * @param {mixed} DomElement or string containing anchor name of the anchor
         *
         * @return null
         */
        scrollTo : function (obj, offset) {
            var $el = null;
            var offsetTop = 75;

            if ($.type(obj) === 'string') {
                // Make sure to trim the hash at the beginning of the string
                if ('#' === obj.charAt(0)) {
                    obj = obj.slice(1);
                }

                $el = $("a[name='" + obj + "']");
            }

            if ($.type(obj) === 'object') {
                $el = $(obj);
            }

            if ($.isVoid($el) || $.isVoid($el.offset())) {
                return;
            }

            if ($.isVoid(offset)) {
                offset = 0;
            }

            $('html,body').animate({ scrollTop: $el.offset().top + offset - offsetTop }, 'slow');
        },

        /**
         * Very simple page scroll spy.
         *
         * This function is not very efficient as appends on callback per element to spy on,
         * but for very small uses it's fine.
         *
         * @param {jQuery} $el element to set the scrollspy on.
         * @param {function} onEnter callback to execute when entering the area.
         * @param {function} onLeave callback to execute when exiting the area.
         *
         * @return null
         */
        scrollSpy : function ($el, onEnter, onLeave) {
            var offsetTop = 75;

            onEnter = onEnter || function () {};
            onLeave = onLeave || function () {};

            $el.isIn = false;

            $(window).scroll(function (e) {
                // prevent execution when scrolling
                if ($('html,body').is(':animated')) {
                    return;
                }

                // prevent execution for scrollY over the top
                if (window.scrollY < 0) {
                    return;
                }

                if ($el.isIn !== true && window.scrollY >= $el.offset().top - offsetTop) {
                    $el.isIn = true;
                    onEnter($el);
                }

                if ($el.isIn === true && window.scrollY < $el.offset().top - offsetTop) {
                    $el.isIn = false;
                    onLeave($el);
                }
            });
        }
    });
}(jQuery));
