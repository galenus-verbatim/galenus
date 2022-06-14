/**
 * Do something with bâle chartier data
 */
const image = document.getElementById('image');
const div = document.getElementById('viewcont');
if (div) {
    var pageViewer = new Viewer(div, {
        title: function(image) {
            return image.alt;
        },
        transition: false,
        inline: true,
        navbar: 0,
        // minWidth: '100%', 
        toolbar: {
            width: function() {
                let cwidth = div.offsetWidth;
                let iwidth = pageViewer.imageData.naturalWidth;
                let zoom = cwidth / iwidth;
                pageViewer.zoomTo(zoom);
                pageViewer.moveTo(0, pageViewer.imageData.y);
            },
            zoomIn: true,
            zoomOut: true,
            oneToOne: true,
            reset: true,
        },
        viewed() {
            // default zoom on load, image width
            let cwidth = div.offsetWidth;
            let iwidth = pageViewer.imageData.naturalWidth;
            let zoom = cwidth / iwidth;
            pageViewer.zoomTo(zoom);
            pageViewer.moveTo(0, 0);
        },
    });
    // viewer override of resize
    Viewer.prototype.resize = function() {
        var _this3 = this;

        if (!this.isShown || this.hiding) {
            return;
        }

        if (this.fulled) {
            this.close();
            this.initBody();
            this.open();
        }

        this.initContainer();
        this.initViewer();
        this.renderViewer();
        this.renderList();

        if (this.viewed) {
            // do not resize image
            /*
            this.initImage(function() {
                _this3.renderImage();
            });
            _this3.options.viewed();
            */
        }

        if (this.played) {
            if (this.options.fullscreen && this.fulled && !(document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement)) {
                this.stop();
                return;
            }

            forEach(this.player.getElementsByTagName('img'), function(image) {
                addListener(image, EVENT_LOAD, _this3.loadImage.bind(_this3), {
                    once: true
                });
                dispatchEvent(image, EVENT_LOAD);
            });
        }
    };

    Viewer.prototype.wheel = function(event) {
        var _this4 = this;
        if (!this.viewed) {
            return;
        }

        event.preventDefault(); // Limit wheel speed to prevent zoom too fast

        if (this.wheeling) {
            return;
        }

        this.wheeling = true;
        setTimeout(function() {
            _this4.wheeling = false;
        }, 50);
        var ratio = Number(this.options.zoomRatio) || 0.1;
        var delta = 1;

        if (event.deltaY) {
            delta = event.deltaY;
        } else if (event.wheelDelta) {
            delta = -event.wheelDelta;
        } else if (event.detail) {
            delta = event.detail;
        }
        this.move(0, -delta);
    };
}
(function() {
    let first = true;
    // const ed1 set, use data 
    if (typeof imgkuhn !== 'undefined') wear(".pb", imgkuhn);
    if (typeof imgbale !== 'undefined') wear(".ed1page", imgbale);
    if (typeof imgchartier !== 'undefined') wear(".ed2page", imgchartier);

    // https://www.biusante.parisdescartes.fr/iiif/2/bibnum:00039x04:0038/full/max/0/default.jpg
    function wear(css, dat) {
        if (!dat) return;
        let els = document.querySelectorAll(css);
        for (let i = 0; i < els.length; ++i) {

            var span = document.createElement("span");
            span.className = "pageview";
            let p = els[i].dataset.n;


            if (!p) p = els[i].dataset.page;
            let url;
            // has been seen, but no more used
            // const pos = p.indexOf(".");
            let pno;
            let text = '[';
            if (els[i].classList.contains('page1') || els[i].classList.contains('pbde')) {
                text += '…';
            }
            let pdiff = dat['pdiff'];
            if (dat['pholes']) {
                for (const prop in dat['pholes']) {
                    if (p >= prop) {
                        pdiff = dat['pholes'][prop];
                    } else {
                        break;
                    }
                }
            }

            text += dat['vol'] + '.' + p + ' ' + dat['abbr'];
            // pad page number for biusante 
            pno = pad(parseInt(p) + parseInt(pdiff), 4);
            url = dat['url'].replace('%%', pno);
            text += ']';
            span.innerText = text;
            // els[i].parentNode.insertBefore(span, els[i].nextSibling);
            els[i].appendChild(span);


            span.onclick = function() {
                if (pageViewer.spanLast) pageViewer.spanLast.classList.remove("selected");
                this.classList.add("selected");
                pageViewer.spanLast = this;
                image.src = url;
                if (dat.title) image.alt = text + ' source : ' + dat.title.replace('%%', pno);
                pageViewer.update();
                pageViewer.resize();
            }
            if (first) {
                span.click();
                first = false;
            }
        }
    }

    function pad(num, width) {
        var s = "000000000" + num;
        return s.substring(s.length - width);
    }
})();