import React from 'react';

export default class Recent extends React.Component {
    render() {
        const style = {
            backgroundImage: 'url("'+this.props.agent.gravatar_url+'")'
        };
        return (
            <a href="#" title={this.props.agent.name} className="chat-avatar" style={style}></a>
        );
    }
}