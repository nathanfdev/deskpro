import React, { PropTypes } from 'react';
import { RadioOption } from '../RadioOption';

export class RadioList extends React.Component {

  static propTypes = {
    values:           PropTypes.object.isRequired,
    renderLabel:      PropTypes.func.isRequired,
    getKeyword:       PropTypes.func.isRequired,
    multiple:         PropTypes.bool,
    selected:         PropTypes.number,
    showOnlySelected: PropTypes.bool,
    param:            PropTypes.string.isRequired,
    filter:           PropTypes.string,
    options:          PropTypes.any,
    onClick:          PropTypes.func.isRequired
  };

  renderItem(option, index) {
    const { selected, showOnlySelected, filter = '', renderLabel, getKeyword, onClick, param } = this.props;
    const id = option.get('id');
    const checked = selected === id;
    const keyword = getKeyword(option);
    const label = renderLabel(option);

    if (showOnlySelected && !checked) {
      return null;
    }
    if (filter && keyword && keyword.toLowerCase().indexOf(filter.toLowerCase()) === -1) {
      return null;
    }

    return (
      <RadioOption
        key={index}
        isActive={checked}
        onClick={onClick}
        value={id}
        param={param}
        label={label}
        />
    );
  }

  render() {
    return (
      <ul>
        {this.props.values.map((option, index) => this.renderItem(option, index))}
      </ul>
    );
  }
}
