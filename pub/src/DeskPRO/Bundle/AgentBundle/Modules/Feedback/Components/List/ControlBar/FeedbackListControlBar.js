import React, {Component, PropTypes} from 'react';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameMenu';
import { FilterContainer } from './FilterContainer';
import { CheckboxContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar/MassAction/CheckboxContainer';
import { SortingMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar/Sorting/SortingMenu';
import { ViewMenuContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar/View/ViewMenuContainer';
import { connect } from 'react-redux';
import { toggleMassAction } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { currentListSortSelector, currentListOrderSelector, isCommentsSelector } from '../../../Selectors/list';
import { setSort, setOrder } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { updateRoutingState } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/routingActions';
import { currentViewModeSelector } from '../../../Selectors/list';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

@connect(state => ({
  // mass action data
  count: state.Feedback.list.get('selected').size,

  // sorting data
  sort: currentListSortSelector(state),
  order: currentListOrderSelector(state),
  isComments: isCommentsSelector(state),

  // view
  viewMode: currentViewModeSelector(state)
}))
export class FeedbackListControlBar extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

    constructor(props) {
      super(props);
      this.state = {
        orderByDropdownIsExpanded: false,
        filterByDropdownIsExpanded: false,
        viewModeDropdownIsExpanded: false,
        viewOptionsIsExpanded: false
      };
    }

  toggleFilterByDropdown = (event) => {
    if (event) {
      event.preventDefault();
    }
    this.setState({
      filterByDropdownIsExpanded: !this.state.filterByDropdownIsExpanded,
      orderByDropdownIsExpanded: false,
      viewModeDropdownIsExpanded: false,
      viewOptionsIsExpanded: false
    });
  };

  toggleOptionsMenu = (event) => {
    if (event) {
      event.preventDefault();
    }
    this.setState({
      viewOptionsIsExpanded: !this.state.viewOptionsIsExpanded,
      viewModeDropdownIsExpanded: false,
      orderByDropdownIsExpanded: false,
      filterByDropdownIsExpanded: false
    });
  };

  render() {
    const config = {
      checkbox: {
        count: this.props.count,
        action: toggleMassAction
      },
      sorting: {
        options: [
          {field: 'date_created', label: 'Date', icon: 'calendar'},
          {field: 'total_rating', label: 'Rating', icon: 'calendar-o'},
          {field: 'num_ratings', label: 'Votes', icon: 'calendar'}
        ],
        sort: this.props.sort,
        order: this.props.order,
        sortAction: setSort,
        orderAction: setOrder
      },
      view: {
        options: [
          {field: constants.VIEW_MODE_CARD, label: 'Card View', icon: 'list'},
          {field: constants.VIEW_MODE_TABLE, label: 'Table View', icon: 'table'}
        ],
        viewMode: this.props.viewMode,
        viewModeAction: (mode) => updateRoutingState('list', 'view', mode),

        tableConfigurableFields: {
          id: 'ID',
          urgency: 'Urgency',
          person: 'Person',
          person_email: 'Person email',
          agent: 'Agent',
          subject: 'Subject',
          status: 'Status',
          date_created: 'Date created',
          labels: 'Labels'
        },
        tableVisibleFields: ['id', 'urgency', 'person'],
        tableToggleFieldVisibility: () => ({}),

        cardConfigurableFields: {
          id: 'ID',
          urgency: 'Urgency',
          person: 'Person',
          date_created: 'Date created',
          labels: 'Labels'
        },
        cardVisibleFields: ['id', 'urgency', 'person'],
        cardToggleFieldVisibility: () => ({})
      }
    };

    return (
      <ListFrameMenu>
        <CheckboxContainer {...config.checkbox} />
        <SortingMenu {...config.sorting} />
        <li>
          <hr/>
        </li>
        <FilterContainer
          expanded={this.state.filterByDropdownIsExpanded}
          toggleDropdown={this.toggleFilterByDropdown}
          />
        <li>
          <hr/>
        </li>
        <ViewMenuContainer {...config.view} />
      </ListFrameMenu>
    );
  }
}