import React from 'react';
import { ListFrame, ControlBar, ListTableViewSwitcher, TableView }
    from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { FeedbackCard} from './FeedbackCard';
import { TableHeader} from './TableHeader';
import { TableBody} from './TableBody';
import { OrderBy} from './OrderBy';
import { FilterBy} from './FilterBy';
import { Options} from './Options';

import $ from "jquery";
import * as actions from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/ControlBarActions';
import { connect } from 'redux/react';

@connect(state => state.control_bar)

export class ListContainer extends React.Component {

    onChange(e) {
        const {dispatch} = this.props;
        e.stopPropagation();
        dispatch(actions.switchViewMode());
    }

    render() {
        const { feedback, viewMode, sortTable, sort, sortName, filters, query } = this.props;
        let itemKey = 0;

        return (
            <ListFrame>
                <ControlBar>
                    <OrderBy sort={sort} sortName={sortName}/>
                    <FilterBy filters={filters} query={query}/>
                    <ListTableViewSwitcher {...this.props}>
                        <div className="view-mode-choice dropdown-choice">
                            <input name="view-mode" type="radio" defaultChecked={viewMode === 'list'}
                                   onChange={this.onChange.bind(this)}>
                                List View
                            </input>
                            <br/>
                            <fieldset>
                                <legend>Display fields</legend>
                                <Options/>
                            </fieldset>
                            <br/>
                            <input name="view-mode" type="radio" defaultChecked={viewMode === 'table'}
                                   onChange={this.onChange.bind(this)}>
                                Table View
                            </input>
                            <br/>
                            <fieldset>
                                <legend>Display fields</legend>
                                <Options/>
                            </fieldset>
                        </div>
                    </ListTableViewSwitcher>
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
