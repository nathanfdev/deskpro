import React from 'react';

export default class AgentsListItem extends React.Component
{
    render()
    {
        return (
            <li>
                <a href="#">
                    <span className="chat-avatar chat-user-online" style={{"background-image": "url(./img/avatar.jpg)"}}></span>
                    <span className="agent">Ben Henley <span className="datestamp">2d ago</span></span>
                </a>
            </li>
        );
    }
}