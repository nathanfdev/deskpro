import React, { PropTypes } from 'react';
import classNames from 'classnames';

export default class TypeSlider extends React.Component {

  static propTypes = {
    id: PropTypes.number,
    label: PropTypes.string,
    active: PropTypes.bool,
    toggleType: PropTypes.func
  };

  onClick = event => {
    event.preventDefault();

    const { toggleType, id } = this.props;
    toggleType(id);
  };

  render() {
    const { id, active, label } = this.props;

    return (
      <li>
        <div className="slider-panel">
          <a href={'/feedback/browse/type-' + id}
             className={classNames('slider', {'off': !active})}
             onClick={this.onClick}>

            <span className="slider-status">{active ? 'on' : 'off'}</span>
            <span className="slider-icon">
              <i className={classNames('fa', active ? 'fa-check' : 'fa-times')} />
            </span>
          </a>
          <span className="slider-label" onClick={this.onClick}>
            {label}
          </span>
        </div>
      </li>
    );
  }
}
