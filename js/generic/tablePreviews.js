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
    while (parent.tagName !== 'TABLE' && parent.tagName !== 'BODY') {
        parent = parent.parentNode;
    }
    return parent.tagName === 'TABLE';
};

var getTableForPreview = function (inTableElementId) {
    if (inTableElementId === 'undefined' || !inTableElementId) {
        console.log('Missing ID of an element in a table');
        return '';
    }
    var element = document.getElementById(inTableElementId);
    if (element === 'undefined' || !element) {
        console.log('Element in a table not found by ID ' + inTableElementId);
        return '';
    }
    var searchedTable = element;
    while (searchedTable.tagName !== 'TABLE' && searchedTable.tagName !== 'BODY') {
        searchedTable = searchedTable.parentNode;
    }
    if (searchedTable.tagName !== 'TABLE') {
        console.log('Wrapping table not found for an element with ID ' + inTableElementId);
        return '';
    }
    var table = searchedTable.cloneNode(true);
    removeIdsFromElement(table);
    removeAnchorsFromElement(table);

    return table;
};

var showPreview = function (onElement, pinIt) {
    var tablePreviewWrapped = onElement.getElementsByClassName('preview');
    if (tablePreviewWrapped.length > 0) {
        var tablePreview = tablePreviewWrapped[0];
        tablePreview.className = tablePreview.className.replace('hidden', '').trim(); // reveal if hidden
    } else {
        var tablePreview = document.createElement('div');
        tablePreview.className = 'preview';
        var linkedTable = getTableForPreview(onElement.href.replace(/^.*#/, ''));
        if (!linkedTable) {
            console.log('No linked table found for ' + onElement.href);
            return false;
        }
        tablePreview.appendChild(linkedTable);
        onElement.appendChild(tablePreview); // add newly created
    }
    if (pinIt) {
        tablePreview.className += ' pinned';
    }

    return true;
};

var togglePreview = function (onElement) {
    var tablePreviewWrapped = onElement.getElementsByClassName('preview');
    if (tablePreviewWrapped.length === 0) {
        return showPreview(onElement, true);
    }
    var tablePreview = tablePreviewWrapped[0];
    if (tablePreview.className.includes('hidden') || !tablePreview.className.includes('pinned')) {
        return showPreview(onElement, true);
    }
    if (!tablePreview.className.includes('hidden')) {
        tablePreview.className += ' hidden';
    }
    tablePreview.className = tablePreview.className.replace('pinned', '').trim();

    return true;
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
            if (togglePreview(this)) {
                event.preventDefault();
                return false;
            }
        });
        anchor.addEventListener('touchstart', function (event) {
            if (togglePreview(this)) {
                event.preventDefault();
                return false;
            }
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