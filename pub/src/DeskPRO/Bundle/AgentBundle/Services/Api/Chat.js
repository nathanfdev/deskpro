import DpApi from "../DpApi";

/**
 * @return Promise
 */
export function loadMyChatConversationsCounts() {
  return DpApi.sendGet('DP_API/user_chats/counts?agent=me&group_by=date_period');
}

/**
 * @return Promise
 */
export function loadAllChatConversationsCounts() {
  return DpApi.sendGet('DP_API/user_chats/counts?group_by=agent');
}
