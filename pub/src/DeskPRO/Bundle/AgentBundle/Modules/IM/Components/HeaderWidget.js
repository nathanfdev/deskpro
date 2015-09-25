import React from 'react';
import { connect } from 'react-redux';
import Overlay from './Overlay';
import Chat from './ChatWindow/Chat';
import Recent from './Recent';
import * as actions from '../Actions/imListActions';

@connect(state => ({
    recentAgents: state.IM.list.recentAgents
}))
export default class HeaderWidget extends React.Component {

    constructor(props) {
        super(props);
        this.props.dispatch(actions.loadRecentAgents());
        this.state = {
            overlayShown: false,
            chating: true,
            messages: [
                {
                    author: {
                        gravatar_url: "http://www.gravatar.com/avatar/85c81137eeb71564a77a337bc44d5173?&d=mm"
                    },
                    text: 'test'
                },
                {
                    author: {
                        gravatar_url: "http://www.gravatar.com/avatar/85c81137eeb71564a77a337bc44d5173?&d=mm"
                    },
                    text: 'test'
                },
                {
                    author: {
                        gravatar_url: "http://www.gravatar.com/avatar/85c81137eeb71564a77a337bc44d5173?&d=mm"
                    },
                    text: 'test'
                }
            ]
        };
    }

    onClick() {
        const newState = {
            overlayShown: !this.state.overlayShown,
            chating: this.state.chating,
            messages: [
                {
                    author: {
                        gravatar_url: "http://www.gravatar.com/avatar/85c81137eeb71564a77a337bc44d5173?&d=mm"
                    },
                    text: 'test'
                },
                {
                    author: {
                        gravatar_url: "http://www.gravatar.com/avatar/85c81137eeb71564a77a337bc44d5173?&d=mm"
                    },
                    text: 'test'
                },
                {
                    author: {
                        gravatar_url: "http://www.gravatar.com/avatar/85c81137eeb71564a77a337bc44d5173?&d=mm"
                    },
                    text: 'test'
                }
            ]
        };
        this.setState(newState);
    }

    agentClickHandler() {
        const newState = {
            overlayShown: false,
            chating: true,
            messages: [
                {
                    author: {
                        gravatar_url: "http://www.gravatar.com/avatar/85c81137eeb71564a77a337bc44d5173?&d=mm"
                    },
                    text: 'test'
                },
                {
                    author: {
                        gravatar_url: "http://www.gravatar.com/avatar/85c81137eeb71564a77a337bc44d5173?&d=mm"
                    },
                    text: 'test'
                },
                {
                    author: {
                        gravatar_url: "http://www.gravatar.com/avatar/85c81137eeb71564a77a337bc44d5173?&d=mm"
                    },
                    text: 'test'
                }
            ]
        };
        this.setState(newState);
    }

    render() {
        return (
            <div className="agent-ims">
                <a href="#" onClick={this.onClick.bind(this)} className="show-more">
                  <span>
                      IMs <i className="fa fa-angle-down"></i>
                  </span>
                </a>
                { this.props.recentAgents.map((agent, index) => <Recent agentClickHandler={this.agentClickHandler} key={index} agent={agent} />)}
                { this.state.overlayShown ? <Overlay/> : null }
                { this.state.chating ? <Chat messages={this.state.messages} /> : null }
            </div>
        );
    }
}