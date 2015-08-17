import React from 'react';
import { connect } from 'react-redux';
import * as actions from '../Actions/chatConversationsNavFrameActions'
import { DatePeriodCountItem } from './CountItems/DatePeriodCountItem';
import { AgentCountItem } from './CountItems/AgentCountItem';
import { DepartmentCountItem } from './CountItems/DepartmentCountItem';

@connect(state => ({
  myChats: state.ChatConversationsNavFrame.myChats,
  myChatsGroupBy: state.ChatConversationsNavFrame.myChatsGroupBy,
  allChats: state.ChatConversationsNavFrame.allChats,
  allChatsGroupBy: state.ChatConversationsNavFrame.allChatsGroupBy,
}))
export class ChatConversationsNavFrame extends React.Component {

  constructor(props) {
    super(props);
    this.props.dispatch(actions.loadMyChatConversationsCounts(this.props.myChatsGroupBy));
    this.props.dispatch(actions.loadAllChatConversationsCounts(this.props.allChatsGroupBy));
  }

  render() {
    const {myChats, allChats, myChatsGroupBy, allChatsGroupBy} = this.props;

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
                    <a className="list-counter-dropdown active" href="#" onClick={this.toggleMyChatsGroupingControls.bind(this)}>
                      <span>&nbsp;</span>
                      <i className="fa fa-angle-down"></i>
                    </a>
                    <a className="list-counter active" href="#">{myChats.count}</a>
                  </div>
                </div>
                
                <ul>
                  {this.renderNestedCounts.call(this, myChats.nested, myChatsGroupBy)}
                </ul>
              
              </section>
              
              <section className="sidebar-list tasks-nav-people">
                <div className="list-sidebar-title">
                  All Chats
                  <div className="list-counter-bucket">
                    <a className="list-counter-dropdown active" href="#" onClick={this.toggleAllChatsGroupingControls.bind(this)}>
                      <span>&nbsp;</span>
                      <i className="fa fa-angle-down"></i>
                    </a>
                    <a className="list-counter active" href="#">{allChats.count}</a>
                  </div>
                </div>
                
                <ul>
                  {this.renderNestedCounts.call(this, allChats.nested, allChatsGroupBy)}
                </ul>
              
              </section>
            
            </div>
          </aside>
        </div>
      </section>
    );
  }

  renderNestedCounts(nested, groupBy) {
    let result = [];

    nested.map(count => {
      switch (groupBy) {
        case 'agent':
          result.push((
              <AgentCountItem key={count.group} count={count.count} group={count.group} />
          ));
          break;

        case 'department':
          result.push((
              <DepartmentCountItem key={count.group} count={count.count} group={count.group} />
          ));
          break;

        case 'date_period':
          result.push((
              <DatePeriodCountItem key={count.group} count={count.count} group={count.group} />
          ));
          break;
      }
    });

    return result;
  }

  toggleMyChatsGroupingControls(e) {
    e.preventDefault();
    this.props.dispatch(actions.toggleMyChatsGroupingControls());
  }

  toggleAllChatsGroupingControls(e) {
    e.preventDefault();
    this.props.dispatch(actions.toggleAllChatsGroupingControls());
  }
}
