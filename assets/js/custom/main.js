var sitemapChecklist = (function ($) {
    var s;
    var sitemapWidth;
    return {
        settings: {
            $toggle: $('.sitemapChecklist__children__toggle'),
            $sitemap: $('.sitemapChecklist'),
            $sitemapWraps: $('.sitemapChecklist > .sitemapChecklist__pagewrap')
        },

        init: function () {
            s = this.settings;

            sitemapChecklist.sitemapWidth();
            if (s.$toggle.length > 0) {
                this.toggleInit();
            }
        },

        toggleInit: function () {

            s.$toggle.click(function () {

                $(this).toggleClass('collapsed');
                $(this).closest('.sitemapChecklist__children').children('.sitemapChecklist__pagewrap ').toggleClass('hidden');
                sitemapChecklist.sitemapWidth();

            });

        },

        sitemapWidth: function () {

            sitemapWidth = 0;
            s.$sitemapWraps.each(function () {
                sitemapWidth = sitemapWidth + $(this).width();
            });
            s.$sitemap.width(sitemapWidth);
        }


    };
})(jQuery);
sitemapChecklist.init();