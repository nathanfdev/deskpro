import React from 'react';
import { connect } from 'redux/react';
import { loadChatsList } from '../Actions/chatsNavFrameActions'

@connect(state => ({
  chats: state.chatsList,
}))
export default class ChatsNavFrame extends React.Component {
  constructor(props) {
    super(props);
    this.props.dispatch(loadChatsList());
  }

  render() {
    console.log(this.props);

    return (
      <section className="task-nav-frame dp-nav-frame">
        <div className="sidebar-wrapper" id="sidebar-wrapper">
          <h1>Chats</h1>
        </div>
      </section>
    );
  }
}
