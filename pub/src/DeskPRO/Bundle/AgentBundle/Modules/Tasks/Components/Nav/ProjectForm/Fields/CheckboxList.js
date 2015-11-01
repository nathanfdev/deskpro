import React, { PropTypes } from 'react';
import { Scrollable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Scrollable';

export class CheckboxList extends React.Component {

  static propTypes = {
    selected: PropTypes.array,
    showOnlySelected: PropTypes.bool,
    options: PropTypes.any,
    onChange: PropTypes.func.isRequired
  };

  onClick = value => {
    const { selected = [], onChange } = this.props;
    const index = selected.indexOf(value);

    if (index === -1) {
      selected.push(value);
    } else {
      delete selected[index];
    }

    onChange(selected);
  };

  renderItem({label, value}, index) {
    const { selected = [], showOnlySelected = false } = this.props;
    const checked = selected.indexOf(value) !== -1;

    if (showOnlySelected && !checked) {
      return null;
    }

    const classNames = ['checkbox-button'];
    if (checked) {
      classNames.push('checked');
    }

    return (
      <li key={index}>
        <a className={classNames.join(' ')}
           onClick={this.onClick.bind(this, value)}>

          <span className="checkbox"><i className="fa fa-check"></i></span>
          <span className="name">{label}</span>
        </a>
      </li>
    );
  }

  render() {
    return (
      <Scrollable vertical>
        <ul>
          {this.props.options.map((option, index) => this.renderItem(option, index))}
        </ul>
      </Scrollable>
    );
  }
}
