import React from 'react';
import { connect } from 'redux/react';
import * as actions from '../Actions/chatConversationsNavFrameActions'
import DatePeriodConversationsCount from './DatePeriodConversationsCount';
import AgentConversationsCount from './AgentConversationsCount';

@connect(state => ({
  myChats: state.ChatConversationsNavFrame.myChats,
  allChats: state.ChatConversationsNavFrame.allChats,
}))
export default class ChatConversationsNavFrame extends React.Component {

  constructor(props) {
    super(props);
    this.props.dispatch(actions.loadMyChatConversationsCounts());
    this.props.dispatch(actions.loadAllChatConversationsCounts());
  }

  render() {
    const {myChats, allChats} = this.props;

    console.log("myChats: ", myChats.nested);
    console.log("allChats: ", allChats.nested);

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
                    <a className="list-counter" href="#">{myChats.count}</a>
                  </div>
                </div>
                
                <ul>
                  {myChats.nested.counts.map(count => (
                      <DatePeriodConversationsCount key={'period-' + count.group} count={count.count} period={count.group} />
                  ))}
                </ul>
              
              </section>
              
              <section className="sidebar-list tasks-nav-people">
                <div className="list-sidebar-title">
                  All Chats
                  <div className="list-counter-bucket">
                    <a className="list-counter" href="#">{allChats.count}</a>
                  </div>
                </div>
                
                <ul>
                  {allChats.nested.counts.map(count => (
                      <AgentConversationsCount key={'agent-' + count.group} count={count.count} agent={count.group} />
                  ))}
                </ul>
              
              </section>
            
            </div>
          </aside>
        </div>
      </section>
    );
  }
}
