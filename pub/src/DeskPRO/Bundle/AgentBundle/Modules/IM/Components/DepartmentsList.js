import React from 'react';

export default class DeparmentsList extends React.Component {
    render() {
        return (
            <ul className="im-list short">
                <li>
                    <a href="#">
                        <span className="chat-avatar" style={{"background-image": "url(./img/avatar-team.png)"}}></span>
                        <span className="agent">Research &amp; Development &amp; Long Name</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <span className="chat-avatar" style={{"background-image": "url(./img/avatar-team.png)"}}></span>
                        <span className="agent">Area 51</span>
                    </a>
                </li>
            </ul>
        );
    }
}