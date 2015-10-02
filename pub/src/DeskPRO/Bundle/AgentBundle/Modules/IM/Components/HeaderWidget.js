import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Overlay } from './Overlay';
import Chat from './ChatWindow/Chat';
import Recent from './Recent';
import * as listActions from '../Actions/imListActions';
import { getRecentAgents } from '../Selectors/list';
import { chatsSelector } from '../Selectors/chats';
import * as chatsActions from '../RecordStores/Actions/imChatsActions';

@connect(state => ({
  recentAgents: getRecentAgents(state),
  agentChats: chatsSelector(state),
  messages: []
}))
export class HeaderWidget extends React.Component {
  static propTypes = {
    recentAgents: PropTypes.array.isRequired
  };

  constructor(props) {
    super(props);
    const { dispatch } = this.props;
    dispatch(chatsActions.loadChats([1,2]));
    dispatch(listActions.loadRecentAgents());
    this.state = {
      overlayShown: false,
      chating: false,
      target: {id: 0}
    };
  }

  render() {
    return (
        <div className="agent-ims">
          { console.log(this.props.agentChats) }
          <a href="#" onClick={this.onClick} className="show-more">
            <span>
                IMs <i className="fa fa-angle-down"></i>
            </span>
          </a>
          { this.props.recentAgents.length > 0
            ? this.props.recentAgents.map(
              (agent, index) =>
                <Recent handleClickParticipant={this.handleClickParticipant} key={index} agent={agent} />
            )
            : null
          }
          { this.state.overlayShown ? <Overlay handleClickParticipant={this.handleClickParticipant} /> : null }
          { this.state.chating ? <Chat target={this.state.target} messages={this.props.messages} handleCloseChat={this.handleCloseChat}/> : null }
        </div>
    );
  }

  handleCloseChat = () => {
    "use strict";
    const oldState = this.state;
    let newState = {...oldState};
    newState.chating =false;
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