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
 * Load a generic API endpoint. Only use when you need to get the address from the action
 * @return Promise
 */
export function toValidate() {
    let query = {
        awaiting_validation: 1
    };

    return DpApi.sendGet('DP_API/feedback/counts?' + compileParams(query));
}

