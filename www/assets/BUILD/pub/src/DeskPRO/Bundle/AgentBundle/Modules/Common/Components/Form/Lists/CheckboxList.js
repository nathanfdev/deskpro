import React, { PropTypes } from 'react';
import { Scrollable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Scrollable';
import classNames from 'classnames';

export class CheckboxList extends React.Component {

  static propTypes = {
    values: PropTypes.object.isRequired,
    renderLabel: PropTypes.func.isRequired,
    getKeyword: PropTypes.func.isRequired,
    multiple: PropTypes.bool,
    selected: PropTypes.array,
    showOnlySelected: PropTypes.bool,
    filter: PropTypes.string,
    options: PropTypes.any,
    onChange: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
  }

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
  }

  renderItem(option, index) {
    const { selected = [], showOnlySelected = false, filter = '', renderLabel, getKeyword } = this.props;
    const id = option.get('id');
    const checked = selected.indexOf(id) !== -1;
    const keyword = getKeyword(option);

    if (showOnlySelected && !checked) {
      return null;
    }
    if (filter && keyword && keyword.toLowerCase().indexOf(filter.toLowerCase()) === -1) {
      return null;
    }

    return (
      <li key={index}>
        <a className={classNames('checkbox-button', {'checked': checked})} onClick={this.onClick.bind(this, id)}>

          <span className="checkbox">
            {checked ? <i className="fa fa-check"></i> : null}
          </span>
          {renderLabel(option)}
        </a>
      </li>
    );
  }

  render() {
    return (
      <Scrollable vertical>
        <ul>
          {this.props.values.map((option, index) => this.renderItem(option, index))}
        </ul>
      </Scrollable>
    );
  }
}
