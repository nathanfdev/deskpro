import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { DropdownMenu, Option, DropdownMenuFooter } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/GlobalWidgets/DropdownMenu';
import { ViewOptionsSubmenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/ViewOptionsSubmenu';
import { toggleViewMode } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { viewDataSelector } from '../../../Selectors/list';

@connect(state => ({
  viewModeOptions: state.Feedback.nav.get('viewModeOptions'),
  currentViewMode: viewDataSelector(state),
  listViewFields: state.Feedback.nav.get('listViewFields'),
  tableViewFields: state.Feedback.nav.get('tableViewFields')
}))
export class ViewSwitcherDropdownContainer extends Component {

  render() {
    const {viewModeOptions, listViewFields, tableViewFields, currentViewMode} = this.props;

    return (
      <DropdownMenu dropdownClass="view-mode-dropdown">
        {viewModeOptions.map((option, index)=>
            <Option key={index} active={currentViewMode.field === option.field} option={option}
                    callback={this.toggleView.bind(this)}/>
        )}
        <DropdownMenuFooter>
          <div className="dpw-navigation-dropdown-options-link">
            <a href="#" onClick={this.openOptionsSubmenu.bind(this)}>View Options <i className="fa fa-cog"></i></a>
          </div>
        </DropdownMenuFooter>
        <ViewOptionsSubmenu viewModeOptions={viewModeOptions}
                            listViewFields={listViewFields} tableViewFields={tableViewFields}/>
      </DropdownMenu>
    );
  }

  toggleView(newView) {
    const {dispatch} = this.props;
    dispatch(toggleViewMode(newView.field));
  }

  openOptionsSubmenu(event) {
    event.stopPropagation();
    var controlButton = $(event.target).closest('.control-button');
    controlButton.find('.dpw-navigation-dropdown').not('.dpw-navigation-dropdown-secondary').hide();
    controlButton.find('.dpw-navigation-dropdown-secondary').show();
  }

}