import DpApi from "../DpApi";

/**
 * @param groupBy
 * @return Promise
 */
export function loadMyChatConversationsCounts(groupBy) {
  return DpApi.sendGet('DP_API/user_chats/counts?agent=me&group_by=' + groupBy);
}

/**
 * @return Promise
 */
export function loadAllChatConversationsCounts(groupBy) {
  return DpApi.sendGet('DP_API/user_chats/counts?group_by=' + groupBy);
}
