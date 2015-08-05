import React from 'react';
import { connect } from 'redux/react';
import { loadChatRoomsList } from '../Actions/chatRoomsNavFrameActions'

@connect(state => ({
  groups: state.ChatRoomsNavFrame.groups,
}))
export default class ChatRoomsNavFrame extends React.Component {

  constructor(props) {
    super(props);
    this.state = {roomKey: 0};
    this.props.dispatch(loadChatRoomsList());
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
                <div className="list-sidebar-title">My Chats</div>
                
                <ul>
                  {my.rooms.map(room => this.renderRoom(room))}
                </ul>
              
              </section>
              
              <section className="sidebar-list tasks-nav-people">
                <div className="list-sidebar-title">All Chats</div>
                
                <ul>
                  {all.rooms.map(room => this.renderRoom(room))}
                </ul>
              
              </section>
            
            </div>
          </aside>
        </div>
      </section>
    );
  }

  renderRoom({title, messagesNum}) {
    return (
      <li key={this.state.roomKey++}>
        <div className="list-counter-bucket">
          <a className="list-counter" href="#">{messagesNum}</a>
        </div>
        <a href="#" className="item">{title}</a>
      </li>
    );
  }
}
