import React, { PropTypes } from 'react';
import classNames from 'classnames';

class ListHeader extends React.Component {

  static propTypes = {
    onChange:     PropTypes.func.isRequired,
    onLabelClick: PropTypes.func.isRequired,
    labels:       PropTypes.object.isRequired,
    activeLabels: PropTypes.number.isRequired,
    searchText:   PropTypes.string.isRequired,
  };

  constructor(props) {
    super(props);
    this.state = {
      active: false
    };
    this.onChange     = this.onChange.bind(this);
    this.onLabelClick = this.onLabelClick.bind(this);
    this.toggleSelect = this.toggleSelect.bind(this);
  }

  onLabelClick(index) {
    this.props.onLabelClick(index);
  }

  onChange(event) {
    this.props.onChange(event.target.value);
  }

  toggleSelect() {
    this.setState({ active: !this.state.active });
  }

  render() {
    const { active } = this.state;

    return (
      <div className="big-list-of-stats-filters">
        <div className="bucket filter-title">
          <div className="box">
            <input type="text" placeholder="Filter stats by name" onChange={this.onChange} value={this.props.searchText} />
          </div>
        </div>

        <div className="bucket filter-label" style={{ position: 'relative' }}>
          <a className="link-pointer select" onClick={this.toggleSelect}>
            {this.props.activeLabels > 0 ? `Selected: ${this.props.activeLabels}` : 'Select labels:'}  <i className={classNames('fa', { 'fa-caret-down': !active, 'fa-caret-up': active })} />
          </a>
          {active ? <div className="select-label-dropdown">
            { this.props.labels.map((label, index) => (
              <a
                key={index}
                onClick={() => this.onLabelClick(label.label)}
                className={classNames('link-pointer stat-label', { active: label.active })}
              >
                <i className="fa fa-tag" />{label.label}
              </a>)
            )}
          </div> : null }
        </div>

        <a className="bucket add button"><i className="fa fa-plus" /> ADD</a>
      </div>
    );
  }
}
export default ListHeader;
