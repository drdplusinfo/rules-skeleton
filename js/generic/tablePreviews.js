var removeIdsFromElement = function (element) {
    element.id = '';
    for (var i = 0, childrenLength = element.children.length; i < childrenLength; i++) {
        removeIdsFromElement(element.children[i]);
    }
};

var removeAnchorsFromElement = function (element) {
    if (element.tagName === 'A') {
        element.onclick = function () {
            return false;
        }
    }
    for (var i = 0, childrenLength = element.children.length; i < childrenLength; i++) {
        var child = element.children[i];
        if (child.tagName === 'A') {
            var replacement = document.createElement('span');
            replacement.innerHTML = child.innerHTML;
            element.replaceChild(replacement, child);
        } else {
            removeAnchorsFromElement(element.children[i]);
        }
    }
};

var elementParentIsTable = function (element) {
    var parent = element.parentNode;
    while (parent.tagName !== 'TABLE'
    && (parent.tagName === 'THEAD' || parent.tagName === 'TR' || parent.tagName === 'TH'
    || parent.tagName === 'TBODY' || parent.tagName === 'TD')) {
        parent = parent.parentNode;
    }
    return parent.tagName === 'TABLE';
};

var getTableForPreview = function (tableHeaderId) {
    if (tableHeaderId === 'undefined' || !tableHeaderId) {
        console.log('Missing table ID');
        return '';
    }
    var tableHeader = document.getElementById(tableHeaderId);
    if (tableHeader === 'undefined' || !tableHeader) {
        console.log('Table not found by ID ' + tableHeaderId);
        return '';
    }
    var parent = tableHeader.parentNode;
    while (parent.tagName !== 'TABLE' && (parent.tagName === 'TH' || parent.tagName === 'TR' || parent.tagName === 'THEAD')) {
        parent = parent.parentNode;
    }
    if (parent.tagName !== 'TABLE') {
        return '';
    }
    var table = parent.cloneNode(true);
    removeIdsFromElement(table);
    removeAnchorsFromElement(table);

    return table;
};

var showPreview = function (onElement, pinIt) {
    var tablePreviewWrapped = onElement.getElementsByClassName('preview');
    if (tablePreviewWrapped.length > 0) {
        var tablePreview = tablePreviewWrapped[0];
        tablePreview.className = tablePreview.className.replace(/(\s*hidden|hidden\s*)/, ''); // reveal if hidden
    } else {
        var tablePreview = document.createElement('div');
        tablePreview.className = 'preview';
        var linkedTable = getTableForPreview(onElement.href.replace(/^.*#/, ''));
        if (!linkedTable) {
            return;
        }
        tablePreview.appendChild(linkedTable);
        onElement.appendChild(tablePreview); // add newly created
    }
    if (pinIt) {
        tablePreview.className += ' pinned';
    }
};

var togglePreview = function (onElement) {
    var tablePreviewWrapped = onElement.getElementsByClassName('preview');
    if (tablePreviewWrapped.length === 0) {
        showPreview(onElement);
        return;
    }
    var tablePreview = tablePreviewWrapped[0];
    if (tablePreview.className.includes('hidden') || !tablePreview.className.includes('pinned')) {
        showPreview(onElement, true);
        return;
    }
    tablePreview.className += ' hidden';
    tablePreview.className = tablePreview.className.replace(/(\s*pinned|pinned\s*)/, '');
};

var addPreviewToInnerTableLinks = function () {
    var anchors = document.getElementsByTagName('a');
    for (var i = 0, anchorsLength = anchors.length; i < anchorsLength; i++) {
        var anchor = anchors[i];
        if (anchor.href === 'undefined' || !anchor.href || !anchor.href.includes('#tabulka')
            || elementParentIsTable(anchor)
        ) {
            continue;
        }
        anchor.addEventListener('click', function (event) {
            togglePreview(this);
            event.preventDefault();
            return false;
        });
        anchor.addEventListener('touch', function (event) {
            togglePreview(this);
            event.preventDefault();
            return false;
        });
        anchor.addEventListener('mouseover', function () {
            showPreview(this);
        });
        anchor.addEventListener('mouseout', function () { // hide on mouse out
            var tablePreviewWrapped = this.getElementsByClassName('preview');
            if (tablePreviewWrapped.length === 0) {
                console.log('Can not find .preview for anchor ' + this.href);
                return;
            }
            var tablePreview = tablePreviewWrapped[0];
            if (!tablePreview.className.includes('hidden') && !tablePreview.className.includes('pinned')) {
                tablePreview.className += ' hidden';
            }
        });
    }
};

window.addEventListener('load', addPreviewToInnerTableLinks);