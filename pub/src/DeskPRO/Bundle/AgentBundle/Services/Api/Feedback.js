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