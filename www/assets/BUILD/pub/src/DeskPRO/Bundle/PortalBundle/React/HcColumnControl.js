import PropTypes from 'prop-types';
import React from 'react';
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
    const { active, label, data } = this.props;

    return (
      <div className="form-group dp-po-form-check">
        <input onChange={this.onClick} type="checkbox" className="form-check-input" id={data} checked={active} />
        <label className="form-check-label" htmlFor={data}>{label}</label>
      </div>
    );
  }
}

export class HcColumnControl extends React.Component {

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
      newActiveIds = this.state.active_ids.filter(col => col !== toggleColId);
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
        <ul className="dp-po-columnfilter-list">
          {this.state.columns.map(col =>
            <li className="dp-po-columnfilter-item" key={col.id}>
              <SimpleCheckbox
                data={col.id}
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
