// This function is called when:
// 1. after calling $.historyInit();
// 2. after calling $.historyLoad();
// 3. after pushing "Go Back" button of a browser
function on_hash_change(hash) {
    if (hash === hash_last) {
        return false;
    }

    hash_last = hash;
    var page = parseInt(get_url_param('p', window.location.href));
    if (isNaN(page)) page = 1;
    if (page != t_page) {
        t_page = page;
        get_tasklist();
    }
}

/**
 * Gets a URL parameter by name from a given URL
 * Only works with the hash
 * Uses constant HASH_SEPARATOR
 */
function get_url_param(name, url) {
    name = name.replace(/[\[]/, '\\\[').replace(/[\]]/, '\\\]');
    var regexS = '[\\#;]' + name + '=([^' + HASH_SEPARATOR + ']*)';
    var regex = new RegExp(regexS);
    var results = regex.exec(url);
    if (results == null) {
        return '';
    }
    return results[1];
}

/**
    * Updates the hash
    * TODO: Add handling for multiple params in hash,
    * separated by HASH_SEPARATOR
    * Only param is page, for now
    */
function url_update_hash(param, val) {
    window.location.href = INITIAL_URL_NOHASH  + '#' + param + '=' + val;
}
