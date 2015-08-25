import DpApi from "../DpApi";

/**
 * Load a generic API endpoint. Only use when you need to get the address from the action
 * @param address
 * @param params
 * @return Promise
 */
export function loadAddress(address, params = {}) {
    if (params.length > 0) {
        address = address + '?' + compileParams(params);
    }

    return DpApi.sendGet('DP_API/' + address);
}

/**
 * Feedback counts
 * @return Promise
 */
export function toValidate() {
    let query = {
        awaiting_validation: 1
    };

    return DpApi.sendGet('DP_API/feedback/counts?' + compileParams(query));
}

/**
 * Feedback comments to review count
 * @return Promise
 */
export function commentsToReview() {
    let query = {
        awaiting_validation: 1
    };

    return DpApi.sendGet('DP_API/feedback_comments/counts?' + compileParams(query));
}

/**
 * Feedback labels
 * @return Promise
 */
export function getLabels() {
    return DpApi.sendGet('DP_API/feedback_labels');
}

/**
 * Feedback by types with counts
 * @return Promise
 */
export function getTypes() {
    let query = {
        group_by: "category"
    };

    return DpApi.sendGet('DP_API/feedback/counts?' + compileParams(query));
}

/**
 * Feedback by custom categories with counts
 * @return Promise
 */
export function getCustomCategories() {
    let query = {
        group_by: "custom_category"
    };

    return DpApi.sendGet('DP_API/feedback/counts?' + compileParams(query));
}

/**
 * Count of new feedback
 * @return Promise
 */
export function getNew() {
    let query = {
        status: "new"
    };

    return DpApi.sendGet('DP_API/feedback/counts?' + compileParams(query));
}

/**
 * Count of active feedback
 * @return Promise
 */
export function getActive() {
    let query = {
        status: "active",
        group_by: "status_category"
    };

    return DpApi.sendGet('DP_API/feedback/counts?' + compileParams(query));
}

/**
 * Count of closed feedback
 * @return Promise
 */
export function getClosed() {
    let query = {
        status: "closed",
        group_by: "status_category"
    };

    return DpApi.sendGet('DP_API/feedback/counts?' + compileParams(query));
}

/**
 * Count of hidden feedback
 * @return Promise
 */
export function getHidden() {
    let query = {
        status: "hidden",
        group_by: "hidden_status"
    };

    return DpApi.sendGet('DP_API/feedback/counts?' + compileParams(query));
}

/**
 * Get list of filtered feedback
 * @return Promise
 */
export function getList(query) {
    console.log('DP_API/feedback/?' + compileParams(query));
    return DpApi.sendGet('DP_API/feedback/?' + compileParams(query));
}

/**
 * Compile parameters into a URL string
 * @param params
 * @returns {string}
 */
function compileParams(params) {
    let compiled = [];

    for (let key of Object.keys(params)) {
        var str = String(params[key]);
        compiled.push(key + '=' + str.replace(/\s/g, "%20"));
    }

    return compiled.join('&');
}
