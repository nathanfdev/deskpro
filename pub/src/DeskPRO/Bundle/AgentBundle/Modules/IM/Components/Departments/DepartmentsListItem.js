import React from 'react';

const DepartmentsListItem = React.createClass(
    {
        render: function()
        {
            return (
                <li>
                    <a href="#">
                        <span className="chat-avatar" style={{"background-image": "url(./img/avatar-team.png)"}}></span>
                        <span className="agent">{this.props.department.title}</span>
                    </a>
                </li>
            );
        },
    });

module.exports = DepartmentsListItem;

