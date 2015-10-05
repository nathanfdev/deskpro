import React from 'react';

export const DepartmentsListItem = React.createClass(
    {
        render: function()
        {
            return (
                <li>
                    <a href="#">
                        <span className="chat-avatar" style={{"backgroundImage": "url(./img/avatar-team.png)"}}></span>
                        <span className="agent">{this.props.department.get('title')}</span>
                    </a>
                </li>
            );
        }
    });

