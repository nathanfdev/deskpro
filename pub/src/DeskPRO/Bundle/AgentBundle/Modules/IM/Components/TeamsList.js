import React from 'react';

export default class TeamsList extends React.Component {
    render() {
        return (
            <ul className="im-list short">
                <li>
                    <a href="#">
                        <span className="chat-avatar" style={{"background-image": "url(./img/avatar-team.png)"}}></span>
                        <span className="agent">No "I" in Team</span>
                    </a>
                </li>

                <li>
                <a href="#">
                    <span className="no-avatar"><i className="fa fa-users"></i></span>
                    <span className="agent">No "U" in Team</span>
                </a>
                </li>
            </ul>
        );
    }
}