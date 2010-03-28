// This function is called when:
// 1. after calling $.historyInit();
// 2. after calling $.historyLoad();
// 3. after pushing "Go Back" button of a browser
function on_hash_change(hash) {
    if (hash === hash_last) {
        return false;
    }

    hash_last = hash;
    var   page = parseInt(get_url_param('p', window.location.href))
        , group = parseInt(get_url_param('g', window.location.href))
        , type = parseInt(get_url_param('t', window.location.href))
    ;
    if (page != t_page || group != t_group || type != t_type) {
        t_page = page;
        t_group = group;
        t_type = type;
        get_tasklist();
    }
}

/**
 * Gets a URL parameter by name from a given URL
 * Only works with the hash
 * Uses constant HASH_SEPARATOR
 */
function get_url_param(name, url) {
    if (!url || undefined === url) {
        url = window.location.href;
    }
    name = name.replace(/[\[]/, '\\\[').replace(/[\]]/, '\\\]');
    var regexS = '[\\#;]' + name + '=([^' + HASH_SEPARATOR + ']*)';
    var regex = new RegExp(regexS);
    var results = regex.exec(url);
    if (results == null) {
        if (name == 'p') return 1;
        if (name == 'g' || name == 't') return 0;
    }
    return results[1];
}

/**
 * Updates the hash
 */
function url_update_hash(param, val, erase) {
    var   new_hash = ''
        , initial = hash_last
        , params_values = []
        , params = {}
        , param_value
    ;
    if (erase) {
        initial = '';
    }
    // split url into params
    if (initial) {
        params_values = initial.split(';');
        for (var i in params_values) {
            param_value = params_values[i].split('=');
            params[param_value[0]] = param_value[1];
        }
    }
    // update value
    params[param] = val;
    // collapse params to string
    for (var i in params) {
        if (!params[i]) continue;
        new_hash += ';' + i + '=' + params[i];
    }
    window.location.href = INITIAL_URL_NOHASH  + '#' + new_hash.substr(1);
}
