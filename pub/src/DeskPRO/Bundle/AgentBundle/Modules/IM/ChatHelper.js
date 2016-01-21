export class ChatHelper {
  getChatNode(chat, me) {
    let id = 'chat-with-' + chat.chat_type;

    switch (chat.chat_type) {
      case 'agent':
        const notMe = chat.agents.filter((agent) => agent !== me);
        id += '-' + notMe[0];
        break;
      case 'department':
        id += '-' + chat.departments[0];
        break;
      case 'team':
        id += '-' + chat.agent_teams[0];
        break;
      default:
        break;
    }
    let node = document.getElementById(id);
    if (!node) {
      node = document.getElementById('im-button');
    }
    return node;
  }

  getChatTarget(chat, me) {
    switch (chat.chat_type) {
      case 'agent':
        const notMe = chat.agents.filter((agent) => agent !== me);
        return notMe[0];
      case 'department':
        return chat.departments[0];
      case 'team':
        return chat.agent_teams[0];
      default:
        return 0;
    }
  }
}


