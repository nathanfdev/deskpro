import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { DropdownMenu, Option, DropdownMenuFooter } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/GlobalWidgets/DropdownMenu';
import { ViewOptionsSubmenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/ViewOptionsSubmenu';
import { toggleViewMode } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { viewDataSelector } from '../../../Selectors/list';

@connect(state => ({
  viewModeOptions: state.Feedback.list.get('viewModeOptions').toJS(),
  listViewFields: state.Feedback.list.get('listViewFields'),
  tableViewFields: state.Feedback.list.get('tableViewFields'),
  currentViewMode: viewDataSelector(state)
}))
export class ViewSwitcherDropdownContainer extends Component {

  static propTypes = {
    offset: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      optionsIsExpanded: false
    };
  }

  render() {
    const { viewModeOptions, currentViewMode, offset } = this.props;

    return (
      <DropdownMenu offset={offset}>
        {viewModeOptions.map((option, index)=>
            <Option key={index} active={currentViewMode.field === option.field} option={option}
                    callback={this.toggleView.bind(this)}/>
        )}
        <DropdownMenuFooter>
          <div className="dpw-navigation-dropdown-options-link">
            <a href="#" onClick={this.toggleOptionsSubmenu}>View Options <i className="fa fa-cog"></i></a>
          </div>
        </DropdownMenuFooter>
        {this.renderOptionsSubmenu()}
      </DropdownMenu>
    );
  }

  renderOptionsSubmenu() {
    const {viewModeOptions, listViewFields, tableViewFields, currentViewMode} = this.props;
    if (this.state.optionsIsExpanded) {
      return (
        <ViewOptionsSubmenu viewModeOptions={viewModeOptions} currentViewMode={currentViewMode}
                            listViewFields={listViewFields} tableViewFields={tableViewFields}/>
      );
    }
  }

  toggleOptionsSubmenu = (e) => {
    e.preventDefault();
    this.setState({
      optionsIsExpanded: !this.state.optionsIsExpanded
    });
  };


  toggleView(newView) {
    const {dispatch} = this.props;
    dispatch(toggleViewMode(newView.field));
  }

}