import React, { PropTypes } from 'react';
import { Scrollable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Scrollable';
import classNames from 'classnames';

export class CheckboxList extends React.Component {

  static propTypes = {
    multiple: PropTypes.bool,
    selected: PropTypes.array,
    showOnlySelected: PropTypes.bool,
    filter: PropTypes.string,
    options: PropTypes.any,
    onChange: PropTypes.func.isRequired
  };

  onClick(value) {
    const { multiple, selected = [], onChange } = this.props;
    const index = selected.indexOf(value);

    if (multiple) {
      if (index === -1) {
        selected.push(value);
      } else {
        selected.splice(index, 1);
      }

      onChange(selected);
    } else {
      onChange(index > -1 ? [] : [value]);
    }
  };

  renderItem({label, value, keyword}, index) {
    const { selected = [], showOnlySelected = false, filter = '' } = this.props;
    const checked = selected.indexOf(value) !== -1;

    if (showOnlySelected && !checked) {
      return null;
    }
    if (filter && keyword && keyword.toLowerCase().indexOf(filter.toLowerCase()) === -1) {
      return null;
    }

    return (
      <li key={index}>
        <a className={classNames('checkbox-button', {'checked': checked})} onClick={this.onClick.bind(this, value)}>

          <span className="checkbox">
            {checked ? <i className="fa fa-check"></i> : null}
          </span>
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
