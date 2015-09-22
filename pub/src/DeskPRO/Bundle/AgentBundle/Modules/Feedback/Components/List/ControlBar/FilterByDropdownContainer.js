import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { DropdownMenu, Option, DropdownMenuFooter } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/GlobalWidgets/DropdownMenu';
import { filterDataSelector } from '../../../Selectors/list';

@connect(state => ({
  filterOptions: state.Feedback.list.get('filterOptions').toJS(),
  currentFilterMode: filterDataSelector(state)
}))
export class FilterByDropdownContainer extends Component {

  static propTypes = {
    offset: PropTypes.object.isRequired
  };

  render() {
    const { filterOptions, currentFilterMode, offset } = this.props;

    return (
      <DropdownMenu offset={offset}>
        {filterOptions.map((option, index)=>
            <Option key={index} active={currentFilterMode.field === option.field}
                    callback={this.changeFilter.bind(this)}
                    option={option}/>
        )}
        <DropdownMenuFooter>
          <span>Some footer</span>
        </DropdownMenuFooter>
      </DropdownMenu>
    );
  }

  changeFilter(e){
    console.log('We must implement some functionality');
  }
}