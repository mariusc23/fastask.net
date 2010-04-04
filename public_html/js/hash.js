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
        , pl_page = parseInt(get_url_param('u', window.location.href))
        , group = parseInt(get_url_param('g', window.location.href))
        , type = parseInt(get_url_param('t', window.location.href))
        , tr_page = parseInt(get_url_param('v', window.location.href))
    ;
    if (page != t_page || pl_page != t_pl_page || tr_page != t_tr_page
        || group != t_group || type != t_type) {
        if (page != t_page || group != t_group || type != t_type) {
            list_handler.expect(0);
        }
        if (pl_page != t_pl_page) {
            list_handler.expect(1);
        }
        if (tr_page != t_tr_page) {
            list_handler.expect(2);
        }
        t_page = page;
        t_pl_page = pl_page;
        t_tr_page = tr_page;
        t_group = group;
        t_type = type;
        list_handler.get_tasklist();
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
        if (name == 'p' || name == 'u' || name == 'v') return 1;
        if (name == 'g' || name == 't') return 0;
        return '';
    }
    return results[1];
}

/**
 * Updates the hash
 */
function url_update_hash(param, val, erase_page) {
    var   new_hash = ''
        , initial = hash_last
        , params_values = []
        , params = {}
        , param_value
    ;
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
    if (erase_page && params.p) {
        delete params.p;
    }
    if (param == 't') {
        delete params.g;
    }
    if ((undefined !== params.g && params.g != t_group) || 
        (undefined !== params.t && params.t != t_type)) {
        params.p = 1;
    }
    // collapse params to string
    for (var i in params) {
        if (!params[i]) continue;
        new_hash += ';' + i + '=' + params[i];
    }
    window.location.href = INITIAL_URL_NOHASH  + '#' + new_hash.substr(1);
}
