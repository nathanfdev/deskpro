import React from 'react';


const TeamsListItem = React.createClass(
    {
        render: function()
        {
            return (
                <li>
                    <a href="#">
                        <span className="chat-avatar" style={{"background-image": "url(./img/avatar-team.png)"}}></span>
                        <span className="agent">{this.props.team.name}</span>
                    </a>
                </li>
            );
        },
    });

module.exports = TeamsListItem;

