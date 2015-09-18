import DpApi from '../DpApi';

/**
 * @return Promise
 */
export function loadLatest() {
    return DpApi.sendGet('DP_API/agent_chats');
}
