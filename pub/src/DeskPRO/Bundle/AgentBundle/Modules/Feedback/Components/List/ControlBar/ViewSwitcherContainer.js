import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { ViewModeSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { viewDataSelector } from '../../../Selectors/list';
import Positioned from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import MenuFooter from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooter';
import { toggleViewMode } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';

@connect(state => ({
  order: state.Feedback.list.get('order'),
  filters: state.Feedback.list.get('filters'),
  viewModeOptions: state.Feedback.list.get('viewModeOptions'),
  tableViewFields: state.Feedback.list.get('tableViewFields'),
  listViewFields: state.Feedback.list.get('listViewFields'),
  currentViewMode: viewDataSelector(state)
}))

export class ViewSwitcherContainer extends Component {

  static propTypes = {
    order: PropTypes.string.isRequired,
    expanded: PropTypes.bool.isRequired,
    currentViewMode: PropTypes.object.isRequired,
    viewModeOptions: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  toggleView(newView) {
    const {dispatch} = this.props;
    dispatch(toggleViewMode(newView));
  }

  renderOptions() {
    const {viewModeOptions, currentViewMode} = this.props;
    return (
      viewModeOptions.map((option, index)=>
          <Item
            key={index}
            isActive={currentViewMode.field === option.get('field')}
            onClick={this.toggleView.bind(this, option.get('field'))}
            icon={option.get('icon')}
            >
            {option.get('label')}
          </Item>
      )
    );
  }

  render() {
    const {expanded} = this.props;
    return (
      <ViewModeSwitcher {...this.props}
        ref="viewModeButton"
        >
        <Positioned isOpen={expanded}
                    positionAt="left bottom"
                    positionTarget={this.refs.viewModeButton}>
          <Menu>
            {this.renderOptions()}
            <MenuFooter>
              <div className="dpw-navigation-dropdown-options-link">
                <a href="#">View Options <i className="fa fa-cog"></i></a>
              </div>
            </MenuFooter>
          </Menu>
        </Positioned>
      </ViewModeSwitcher>
    );
  }
}