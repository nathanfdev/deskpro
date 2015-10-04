import React from 'react';
import { startChat } from '../../Actions/chatsActions';
import { connect } from 'react-redux';

@connect()
export class AgentsListItem extends React.Component {
    render() {
        const style = {
            backgroundImage: 'url("'+this.props.agent.get('gravatar_url')+'")'
        };

        let name = this.props.agent.get('name');
        if(this.props.highlight) {
            const escape = this.props.highlight.replace(/[-\\^$*+?.()|[\]{}]/g, '\\$&');
            const tagStr = '<span class="search-matched-word">$&</span>';
            name = name.replace(
                new RegExp(escape, 'gi'),
                tagStr
            );
        }

        return (

            <li>
                <a href="#"
                   onClick={this.startChat.bind(null, this.props.agent.get('id'), 'agent', this.props.handleClickParticipant)}
                  >
                    <span className="chat-avatar" style={style}></span>
                    <span className="agent"><span dangerouslySetInnerHTML={{__html: name}}/><span className="datestamp">2d ago</span></span>
                </a>
            </li>
        );
    }

    startChat = (id, type, callback) => {
        "use strict";
        this.props.dispatch(startChat(id, type));
        callback();
    }
}
