import React, { Component, PropTypes } from 'react';
import { ListItem }
    from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';

export class CategoryTab extends React.Component {

    render() {
        const { customCategories, onClick } = this.props;

        return (
            <ul>
                {customCategories.map((item, index) =>
                        <div key={index}
                             onClick={onClick.bind(this, {'custom_category':item.group})}>
                            <ListItem count={item.count} label={item.group}/>
                        </div>
                )}
            </ul>
        );
    }
}