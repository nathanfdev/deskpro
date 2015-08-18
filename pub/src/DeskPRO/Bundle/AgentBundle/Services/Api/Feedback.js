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
 * Feedback labels with counts
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
 * Compile parameters into a URL string
 * @param params
 * @returns {string}
 */
function compileParams(params) {
    let compiled = [];

    for (let key of Object.keys(params)) {
        compiled.push(key + '=' + String(params[key]));
    }

    return compiled.join('&');
}
