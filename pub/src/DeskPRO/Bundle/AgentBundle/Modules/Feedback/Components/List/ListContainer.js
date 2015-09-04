import React from 'react';
import { ListFrame, ControlBar, ListTableViewSwitcher, TableView }
    from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { FeedbackCard} from './FeedbackCard';
import { TableHeader} from './TableHeader';
import { TableBody} from './TableBody';
import { OrderBy} from './OrderBy';
import { FilterBy} from './FilterBy';
import { connect } from 'redux/react';

@connect(state => state.control_bar)

export class ListContainer extends React.Component {

    render() {
        const { feedback, viewMode, sortTable, sort, sortName, filters, query } = this.props;
        let itemKey = 0;

        return (
            <ListFrame>
                <ControlBar>
                    <OrderBy sort={sort} sortName={sortName}/>
                    <FilterBy filters={filters} query={query}/>
                    <ListTableViewSwitcher {...this.props}/>
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
