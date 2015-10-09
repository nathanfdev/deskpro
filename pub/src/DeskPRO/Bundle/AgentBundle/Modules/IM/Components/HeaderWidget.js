import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Overlay } from './Overlay';
import { Chat } from './ChatWindow/Chat';
import { Recent } from './Recent';
import * as listActions from '../Actions/imListActions';
import * as chatActions from '../RecordStores/Actions/imChatsActions';
import { getRecentAgents } from '../Selectors/list';
import { chatsSelector } from '../RecordStores/Selectors/chats';

@connect(state => ({
  recentAgents: getRecentAgents(state),
  recentChats: chatsSelector(state),
  current: state.IM.chats.get('current'),
  me: state.Application.user
}))
export class HeaderWidget extends React.Component {
  static propTypes = {
    recentAgents: PropTypes.array.isRequired,
    current: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    const { dispatch } = this.props;
    dispatch(listActions.loadRecentAgents());
    dispatch(chatActions.loadChats('all'));
    this.state = {
      overlayShown: false,
      chating: false,
    };
  }

  render() {
    return (
      <div className="agent-ims">
        <a href="#" onClick={this.onClick} className="show-more">
            <span>
                IMs <i className="fa fa-angle-down"></i>
            </span>
        </a>
        { this.props.recentChats.size > 0
          ? this.props.recentChats.map(
          (map, index) => {
            "use strict";
              return <Recent handleClickParticipant={this.handleClickParticipant} key={index} chat={chat} me={this.props.me}/>
          }
        )
          : null
        }
        { this.state.overlayShown ? <Overlay handleClickParticipant={this.handleClickParticipant}/> : null }
        { this.state.chating && this.props.current.id ? <Chat current={this.props.current}
                                                              handleCloseChat={this.handleCloseChat}/> : null }
      </div>
    );
  }

  handleCloseChat = () => {
    "use strict";
    const oldState = this.state;
    let newState = {...oldState};
    newState.chating = false;
    this.setState(newState);
  };

  onClick = () => {
    const oldState = this.state;
    let newState = {...oldState};
    newState.overlayShown = !this.state.overlayShown,
      newState.chating = this.state.chating
    this.setState(newState);
  };

  handleClickParticipant = () => {
    const oldState = this.state;
    let newState = {...oldState};
    newState.chating = true;
    newState.overlayShown = false;
    this.setState(newState);
  };

}