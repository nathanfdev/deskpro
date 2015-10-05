import React from 'react';

export const TeamsListItem = React.createClass(
    {
        render: function()
        {
            return (
                <li>
                    <a href="#">
                        <span className="chat-avatar" style={{"backgroundImage": "url(./img/avatar-team.png)"}}></span>
                        <span className="agent">{this.props.team.get('name')}</span>
                    </a>
                </li>
            );
        }
    });

