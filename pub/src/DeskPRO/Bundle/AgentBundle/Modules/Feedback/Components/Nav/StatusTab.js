import React, { Component, PropTypes } from 'react';
import { ListItem, NestedList }
    from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';

export class StatusTab extends React.Component {

    render() {
        let itemKey = 0;

        const { statuses, onClick } = this.props;

        return (
                <ul>
                    <div onClick={onClick.bind(this, {'status':'new'})}>
                        <ListItem count={statuses.new} label="New"/>
                    </div>
                    <div onClick={onClick.bind(this, {'status':'active'})}>
                        <ListItem count={statuses.active.count} label="Active">
                            <NestedList items={statuses.active.nested}/>
                        </ListItem>
                    </div>
                    <div onClick={onClick.bind(this, {'status':'closed'})}>
                        <ListItem count={statuses.closed.total} label="Closed">
                            {statuses.closed.statuses.map(item =>
                                    <div key={itemKey++}
                                         onClick={onClick.bind(this, {'status':'closed','status_category':item.group})}>
                                        <ListItem key={itemKey++} count={item.count}
                                                  label={item.group}/>
                                    </div>
                            )}
                        </ListItem>
                    </div>
                    <div onClick={onClick.bind(this, {'status':'hidden'})}>
                        <ListItem count={statuses.hidden.total} label="Hidden">
                            {statuses.hidden.statuses.map(item =>
                                    <div key={itemKey++}>
                                        <ListItem key={itemKey++} count={item.count}
                                                  label={item.group}/>
                                    </div>
                            )}
                        </ListItem>
                    </div>
                </ul>
        );
    }
}