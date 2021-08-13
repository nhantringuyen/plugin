var e2pdf = {
    updateViewArea: function (pdfIframe) {
        if (pdfIframe.hasClass('e2pdf-pages-loaded') && pdfIframe.hasClass('e2pdf-responsive')) {
            var pdfIframeContents = pdfIframe.contents();
            var viewerHeight = parseInt(pdfIframeContents.find('#viewer').outerHeight());
            var viewerContainerTop = parseInt(pdfIframeContents.find('#viewerContainer').offset().top);
            pdfIframe.innerHeight(viewerHeight + viewerContainerTop);
            pdfIframeContents.find('#viewerContainer').scrollTop(0);
        }
    },
    viewSinglePageSwitch: function (pdfIframe, page) {
        if (pdfIframe.hasClass('e2pdf-single-page-mode') && pdfIframe.hasClass('e2pdf-responsive')) {
            var page = parseInt(page);
            if (page) {
                var pdfIframeContents = pdfIframe.contents();
                pdfIframeContents.find('.page').not('.page[data-page-number="' + page + '"]').css({'position': 'absolute', 'visibility': 'hidden', 'z-index': '-1'});
                pdfIframeContents.find('.page[data-page-number="' + page + '"]').css({'position': 'relative', 'visibility': '', 'z-index': ''});
            }
        }
    }
};

jQuery(document).ready(function () {

    if (jQuery('.e2pdf-download.e2pdf-auto').not('.e2pdf-iframe-download').length > 0) {
        jQuery('.e2pdf-download.e2pdf-auto').not('.e2pdf-iframe-download').each(function () {
            if (jQuery(this).hasClass('e2pdf-inline')) {
                window.open(jQuery(this).attr('href'), '_blank');
            } else {
                location.href = jQuery(this).attr('href');
            }
        });
    }

    if (jQuery('.e2pdf-view').length > 0) {
        jQuery('.e2pdf-view').each(function () {
            var pdfIframe = jQuery(this);

            pdfIframe.load(function () {
                var pdfIframeContents = jQuery(this).contents();
                pdfIframe.addClass('e2pdf-view-loaded');
                pdfIframeContents.find('html').addClass('e2pdf-view-loaded');

                pdfIframeContents[0].addEventListener('pagesloaded', function (event) {
                    pdfIframe.addClass('e2pdf-pages-loaded');
                    pdfIframeContents.find('html').addClass('e2pdf-pages-loaded');
                    e2pdf.viewSinglePageSwitch(pdfIframe, 1);
                    e2pdf.updateViewArea(pdfIframe);
                });

                pdfIframeContents[0].addEventListener('pagechanging', function (event) {
                    if (event && event.detail.pageNumber) {
                        e2pdf.viewSinglePageSwitch(pdfIframe, event.detail.pageNumber);
                        e2pdf.updateViewArea(pdfIframe);
                    }
                });

                var listeners = [
                    'scalechanging',
                    'scalechanged',
                    'rotationchanging',
                    'updateviewarea',
                    'scrollmodechanged',
                    'spreadmodechanged',
                    'pagechanging',
                    'zoomin',
                    'zoomout',
                    'zoomreset',
                    'nextpage',
                    'previouspage'
                ];
                listeners.forEach(function (listener) {
                    pdfIframeContents[0].addEventListener(listener, function (event) {
                        e2pdf.updateViewArea(pdfIframe);
                    });
                });
            });

        });
    }
});