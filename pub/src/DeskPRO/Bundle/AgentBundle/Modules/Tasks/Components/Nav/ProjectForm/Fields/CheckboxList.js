import React, { PropTypes } from 'react';
import { Scrollable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Scrollable';

export class CheckboxList extends React.Component {

  static propTypes = {
    selected: PropTypes.array,
    options: PropTypes.array,
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

  renderItem(label, value) {
    const { selected = [] } = this.props;
    const classNames = ['checkbox-button'];
    if (selected.indexOf(value) !== -1) {
      classNames.push('checked');
    }

    return (
      <li>
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
          {this.renderItem('Agent 1', 'val1')}
          {this.renderItem('Agent 2', 'val2')}
        </ul>
      </Scrollable>
    );
  }
}
