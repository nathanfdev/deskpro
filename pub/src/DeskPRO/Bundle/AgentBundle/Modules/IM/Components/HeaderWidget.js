import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Overlay } from './Overlay';
import Chat from './ChatWindow/Chat';
import Recent from './Recent';
import * as actions from '../Actions/imListActions';
import { getRecentAgents } from '../Selectors/list';

@connect(state => ({
  recentAgents: getRecentAgents(state)
}))
export class HeaderWidget extends React.Component {
  static propTypes = {
    recentAgents: PropTypes.array.isRequired
  };

  constructor(props) {
    super(props);
    this.props.dispatch(actions.loadRecentAgents());
    this.state = {
      overlayShown: false,
      chating: false,
      messages: [
        {
          author: {
            gravatar_url: 'http://www.gravatar.com/avatar/85c81137eeb71564a77a337bc44d5173?&d=mm'
          },
          text: 'test'
        },
        {
          author: {
            gravatar_url: 'http://www.gravatar.com/avatar/85c81137eeb71564a77a337bc44d5173?&d=mm'
          },
          text: 'test'
        },
        {
          author: {
            gravatar_url: 'http://www.gravatar.com/avatar/85c81137eeb71564a77a337bc44d5173?&d=mm'
          },
          text: 'test'
        }
      ]
    };
  }

  render() {
    return (
        <div className="agent-ims">
          <a href="#" onClick={this.onClick.bind(this)} className="show-more">
            <span>
                IMs <i className="fa fa-angle-down"></i>
            </span>
          </a>
          { this.props.recentAgents.length > 0
            ? this.props.recentAgents.map(
              (agent, index) =>
                <Recent handleClickParticipant={this.handleClickParticipant.bind(this, agent.id, 'agent')} key={index} agent={agent}/>
            )
            : null
          }
          { this.state.overlayShown ? <Overlay handleClickParticipant={this.handleClickParticipant.bind(this)}/> : null }
          { this.state.chating ? <Chat messages={this.state.messages} handleCloseChat={this.handleCloseChat.bind(this)}/> : null }
        </div>
    );
  }

  handleCloseChat() {
    "use strict";
    let newState = {...this.state};
    newState.chating =false;
    this.setState(newState);
  }

  onClick() {
    let newState = {
      ...this.state,
    };
    newState.overlayShown = !this.state.overlayShown,
    newState.chating = this.state.chating
    this.setState(newState);
  }

  handleClickParticipant(id, type, event) {
    let newState = {
      ...this.state
    };
    newState.chating = true;
    newState.overlayShown = false;
    this.setState(newState);
  }

}