import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

class ListHeader extends React.Component {

  static propTypes = {
    onChange:     PropTypes.func.isRequired,
    onLabelClick: PropTypes.func.isRequired,
    labels:       PropTypes.object.isRequired,
    activeLabels: PropTypes.number.isRequired,
    searchText:   PropTypes.string.isRequired,
    onAddClick:   PropTypes.func.isRequired,
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
    const { searchText, activeLabels, labels } = this.props;

    const inputProps = {
      type:        'text',
      placeholder: 'Filter stats by name',
      onChange:    this.onChange,
      value:       searchText
    };

    return (
      <div className="big-list-of-stats-filters">
        <div className="filter-title">
          <div className="box">
            <input {...inputProps} />
          </div>
        </div>

        <div className="filter-label" style={{ position: 'relative' }}>
          <a className="link-pointer select" onClick={this.toggleSelect}>
            { activeLabels > 0 ? `${activeLabels} labels` : 'Labels'}
            <i className={classNames('fa', { 'fa-caret-down': !active, 'fa-caret-up': active })} />
          </a>
          {active ? <div className="select-label-dropdown">
            { labels.map(label => (
              <a
                key={label}
                onClick={() => this.onLabelClick(label.get('label'))}
                className={classNames('link-pointer stat-label', { active: label.get('active') })}
              >
                <i className="fa fa-tag" />{label.get('label')}
              </a>)
            )}
          </div> : null }
        </div>
        <div className="filter-addbtn">
          <button className="ui button green" onClick={this.props.onAddClick}><i className="fa fa-plus" /> ADD</button>
        </div>
      </div>
    );
  }
}
export default ListHeader;
