import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import $ from 'jquery';

class SimpleCheckbox extends React.Component {

  static propTypes = {
    active:         PropTypes.bool,
    label:          PropTypes.any,
    data:           PropTypes.any,
    toggleColumnId: PropTypes.func
  };

  onClick = () => {
    const { toggleColumnId, data } = this.props;
    toggleColumnId(data);
  };

  render() {
    const { active, label } = this.props;

    return (
      <div onClick={this.onClick} className="checkbox-container">
        <span className={classNames('checkbox', { 'checked': active })}>
          <i className="fa fa-check"></i>
        </span>
        {label}
      </div>
    );
  }
}

export class ColumnControl extends React.Component {

  static propTypes = {
    columns:          PropTypes.array,
    active_ids:       PropTypes.array,
    updateActiveCols: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      columns:    props.columns,
      active_ids: props.active_ids
    };
  }

  toggleColumnId(toggleColId) {
    let newActiveIds = [];

    if ($.inArray(toggleColId, this.state.active_ids) >= 0) {
      newActiveIds = this.state.active_ids.filter((col) => col !== toggleColId);
    } else {
      newActiveIds = this.state.active_ids;
      newActiveIds.push(toggleColId);
    }

    this.setState({
      active_ids: newActiveIds,
      columns:    this.state.columns
    });

    this.props.updateActiveCols(newActiveIds);
  }

  render() {
    return (
      <div style={{ width: '100%', height: '100%' }}>
        <h1>Show Columns</h1>
        <ul>
          {this.state.columns.map(col =>
            <li key={col.id}>
              <SimpleCheckbox data={col.id}
                label={col.label}
                active={$.inArray(col.id, this.state.active_ids) >= 0}
                toggleColumnId={this.toggleColumnId.bind(this)}
              />
            </li>
          )}
        </ul>
      </div>
    );
  }
}
