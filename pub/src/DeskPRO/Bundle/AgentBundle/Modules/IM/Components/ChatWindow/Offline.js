import React from 'react';

export default class Offline extends React.Component {
    render() {
        return (
            <div className="active-chat-agent-offline">
                <i className="fa fa-exclamation-triangle"></i>
                <h1>Agent is currently offline.</h1>
                <p>Your messages will be delivered by email.</p>
            </div>
        );
    }
}
