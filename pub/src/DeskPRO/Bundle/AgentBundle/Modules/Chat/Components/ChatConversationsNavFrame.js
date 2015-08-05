import React from 'react';
import { connect } from 'redux/react';
import { loadChatConversationsList } from '../Actions/chatConversationsNavFrameActions'

@connect(state => ({
  groups: state.ChatConversationsNavFrame.groups,
}))
export default class ChatConversationsNavFrame extends React.Component {

  constructor(props) {
    super(props);
    this.state = {conversationKey: 0};
    this.props.dispatch(loadChatConversationsList());
  }

  render() {
    const {my, all} = this.props.groups;

    return (
      <section className="task-nav-frame dp-nav-frame">
        <div className="sidebar-wrapper" id="sidebar-wrapper">
          <a className="collapse-button" href="#"><i className="fa fa-angle-right"></i></a>
          <span className="collapse-controls">
            <span className="disc"></span>
            <span className="disc"></span>
            <i className="fa fa-caret-right"></i>
            <span className="disc"></span>
            <span className="disc"></span>
          </span>
          <aside className="sidebar has-tabs" id="sidebar">
            <div className="sidebar-title">
              <span className="sidebar-type-icon">
                <i className="fa fa-comments-o"></i>
                <span className="help">
                  <i className="fa fa-question"></i>
                </span>
              </span>
              <h1>Chat</h1>
              <hr />
              <a href="#" className="slider-control"></a>
            </div>
            <div className="sidebar-list sidebar-list-filters">
              
              <section className="sidebar-list tasks-nav-groups">
                <div className="list-sidebar-title">
                  My Chats
                  <div className="list-counter-bucket">
                    <a className="list-counter" href="#">{my.count}</a>
                  </div>
                </div>
                
                <ul>
                  {my.conversations.map(conversation => this.renderConversation(conversation))}
                </ul>
              
              </section>
              
              <section className="sidebar-list tasks-nav-people">
                <div className="list-sidebar-title">
                  All Chats
                  <div className="list-counter-bucket">
                    <a className="list-counter" href="#">{all.count}</a>
                  </div>
                </div>
                
                <ul>
                  {all.conversations.map(conversation => this.renderConversation(conversation))}
                </ul>
              
              </section>
            
            </div>
          </aside>
        </div>
      </section>
    );
  }

  renderConversation({title, count}) {
    return (
      <li key={this.state.conversationKey++}>
        <div className="list-counter-bucket">
          <a className="list-counter" href="#">{count}</a>
        </div>
        <a href="#" className="item">{title}</a>
      </li>
    );
  }
}
