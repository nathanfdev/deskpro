import React from 'react';
import { ListFrame, ControlBar, ListTableViewSwitcher, TableView }
    from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { FeedbackCard} from './FeedbackCard';
import { TableHeader} from './TableHeader';
import { TableBody} from './TableBody';
import { OrderBy} from './OrderBy';
import { FilterBy} from './FilterBy';

import $ from "jquery";
import * as actions from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/ControlBarActions';
import { connect } from 'redux/react';

@connect(state => state.control_bar)

export class ListContainer extends React.Component {

    render() {
        let itemKey = 0;

        const { feedback, viewMode, sortTable, sort, sortName, filters, query } = this.props;

        const fields = [
            {name: 'id', label: 'ID'},
            {name: 'status', label: 'Status'},
            {name: 'hidden_status', label: 'Hidden status'},
            {name: 'status_category', label: 'Status category'},
            {name: 'title', label: 'Status category'},
            {name: 'author_name', label: 'Submitter'},
            {name: 'language_id', label: 'Lang'},
            {name: 'type', label: 'Type'},
            {name: 'slug', label: 'Slug'},
            {name: 'date_created', label: 'Created'},
            {name: 'date_published', label: 'Published'},
            {name: 'view_count', label: 'Views'},
            {name: 'total_rating', label: 'Rating'},
            {name: 'num_rating', label: 'Votes'},
            {name: 'num_comments', label: 'Comments'},
            {name: 'validating', label: 'Validating'},
            {name: 'popularity', label: 'Popularity'},
            {name: 'content', label: 'Content'},
            {name: 'custom_category', label: 'Category'}
        ];

        return (
            <ListFrame>
                <ControlBar>
                    <OrderBy sort={sort} sortName={sortName}/>
                    <FilterBy filters={filters} query={query}/>
                    <ListTableViewSwitcher fields={fields} {...this.props}/>
                </ControlBar>

                {viewMode === 'list' ?
                    feedback.map(item =>
                            <FeedbackCard key={itemKey++} feedback={item}/>
                    ) :
                    <TableView>
                        <TableHeader sortTable={sortTable.bind(this)}/>
                        <TableBody feedback={feedback}/>
                    </TableView>
                }
            </ListFrame>
        );
    }
}
