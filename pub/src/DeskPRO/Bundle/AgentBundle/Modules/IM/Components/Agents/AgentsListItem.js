import React from 'react';

const AgentsListItem = React.createClass(
{
    render: function()
    {
        const style = {
            backgroundImage: 'url("'+this.props.agent.gravatar_url+'")'
        };

        let name = this.props.agent.name;
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
                <a href="#">
                    <span className="chat-avatar" style={style}></span>
                    <span className="agent"><span dangerouslySetInnerHTML={{__html: name}}/><span className="datestamp">2d ago</span></span>
                </a>
            </li>
        );
    },
});

module.exports = AgentsListItem;

