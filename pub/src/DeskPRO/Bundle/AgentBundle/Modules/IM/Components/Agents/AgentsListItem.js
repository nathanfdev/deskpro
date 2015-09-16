import React from 'react';

const AgentsListItem = React.createClass(
{
    render: function()
    {
        const style = {
            backgroundImage: 'url("'+this.props.agent.gravatar_url+'")'
        }
        return (

            <li>
                <a href="#">
                    <span className="chat-avatar" style={style}></span>
                    <span className="agent">{this.props.agent.name} <span className="datestamp">2d ago</span></span>
                </a>
            </li>
        );
    },
});

module.exports = AgentsListItem;