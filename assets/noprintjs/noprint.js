/*
NoPrint.js V1.0
Created by PDFAntiCopy.com
*/
if (noCopy) {
    document.body.oncopy = function () { return false };
    document.body.oncontextmenu = function () { return false };
    document.body.onselectstart = document.body.ondrag = function () {
        return false;
    }
    document.onkeydown = function () {
        if ((event.ctrlKey == true || event.metaKey == true) && event.keyCode == 83) {
            event.preventDefault();
        }
        if ((event.ctrlKey == true || event.metaKey == true) && event.code == 83) {
            event.preventDefault();
        }
    }
}

if (noPrint) {
    var c = document.createElement("span");
    c.style.display = "none";
    c.style.postion = "absolute";
    c.style.background = "#000";
    var first = document.body.firstChild;
    var wraphtml = document.body.insertBefore(c, first);
    c.setAttribute('width', document.body.scrollWidth);
    c.setAttribute('height', document.body.scrollHeight);
    c.style.display = "block";
    var cssNode3 = document.createElement('style');
    cssNode3.type = 'text/css';
    cssNode3.media = 'print';
    cssNode3.innerHTML = 'body{display:none}';
    document.head.appendChild(cssNode3);
}

var cssNode2 = document.createElement('style');
cssNode2.type = 'text/css';
cssNode2.media = 'screen';
cssNode2.innerHTML = 'div{-webkit-touch-callout: none;-webkit-user-select: none;-khtml-user-select: none;-moz-user-select: none;-ms-user-select: none;user-select: none;}';
document.head.appendChild(cssNode2);
document.body.style.cssText = "-webkit-touch-callout: none;-webkit-user-select: none;-khtml-user-select: none;-moz-user-select: none;-ms-user-select: none;user-select: none;";



function toBlur() {
    if (autoBlur) {
        var iframe = document.getElementById('pdf-frame');
        var target = iframe || document.body;

        // Apply blur
        target.style.cssText += "-webkit-filter: blur(5px);-moz-filter: blur(5px);-ms-filter: blur(5px);-o-filter: blur(5px);filter: blur(5px);";

        // If target is the iframe, add an overlay to capture clicks and prevent focus trapping
        if (iframe && iframe.parentElement) {
            // Ensure parent is relative for absolute positioning of overlay
            if (getComputedStyle(iframe.parentElement).position === 'static') {
                iframe.parentElement.style.position = 'relative';
            }

            var overlay = document.getElementById('blur-overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.id = 'blur-overlay';
                overlay.style.position = 'absolute';
                overlay.style.top = '0';
                overlay.style.left = '0';
                overlay.style.width = '100%';
                overlay.style.height = '100%';
                overlay.style.backgroundColor = 'rgba(255, 255, 255, 0)'; // Transparent
                overlay.style.zIndex = '1000';
                overlay.style.cursor = 'pointer';
                overlay.style.display = 'flex';
                overlay.style.justifyContent = 'center';
                overlay.style.alignItems = 'center';
                overlay.title = 'Hover to view content';

                // Optional: Add a subtle text or icon indicating "Click to view"
                // overlay.innerHTML = '<span style="background:rgba(0,0,0,0.5); color:white; padding:5px 10px; border-radius:4px;">Click to view</span>';
                // overlay.onclick = function () {
                // overlay.onmouseleave = function () {
                //     toBlur();
                // };

                overlay.onmouseenter = function () {
                    toClear();
                };

                iframe.parentElement.appendChild(overlay);
            }
            overlay.style.display = 'flex';
        }
    }
}

function toClear() {
    var iframe = document.getElementById('pdf-frame');
    var target = iframe || document.body;

    // Remove blur
    target.style.cssText += "-webkit-filter: blur(0px);-moz-filter: blur(0px);-ms-filter: blur(0px);-o-filter: blur(0px);filter: blur(0px);";

    // Hide overlay
    var overlay = document.getElementById('blur-overlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
}

document.onclick = function (event) {
    // Global click also clears
    toClear();
}

document.onmouseleave = function (event) {
    toBlur();
}

// document.onblur = function (event) {
window.onblur = function () {
    toBlur();
}

// document.addEventListener('keyup', (e) => {
document.addEventListener('keydown', (e) => {
    if (e.key == 'PrintScreen') {
        if (noScreenshot) {
            navigator.clipboard.writeText('');
            toBlur();

            // Temporary hide to ruin screenshot
            document.body.style.visibility = 'hidden';
            setTimeout(function () {
                document.body.style.visibility = 'visible';
            }, 1);

            try {
                e.preventDefault();
            } catch (err) { }
        }
    }
});

document.addEventListener('keydown', (e) => {
    if (e.ctrlKey && e.key == 'p') {
        if (noPrint) {
            e.cancelBubble = false;
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    }
});

document.addEventListener('keyup', (e) => {
    // Allow Escape key to unblur
    if (e.key === 'Escape') {
        toClear();
    }
});