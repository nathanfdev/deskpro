import React, { Component, PropTypes } from 'react';
import { ListItem }
    from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';

export class CategoryTab extends React.Component {

    render() {
        const { customCategories, onClick } = this.props;
        let itemKey = 0;

        return (
            <ul>
                {customCategories.map(item =>
                        <div key={itemKey++}
                             onClick={onClick.bind(this, {'custom_category':item.group})}>
                            <ListItem key={itemKey++} count={item.count} label={item.group}/>
                        </div>
                )}
            </ul>
        );
    }
}