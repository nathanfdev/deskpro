import PropTypes from 'prop-types';
import React, { Component } from 'react';

export class UrgencyList extends Component {
  static propTypes = {
    items: PropTypes.object.isRequired
  };

  render() {
    const items = this.props.items.toOrderedSet().sort((a, b) => a.get('id') > b.get('id') ? 1 : -1);

    return (
      <div className="sidebar-urgent-sliders">
        {items.map(item => !item.get('count') ? '' : (
          <div key={item.get('id')} className={'slider level-' + item.get('id')}>
            <div className="slider-container">
              <span className="slider-grabber-wrapper">
                <span className="slider-grabber">{item.get('id')}</span>
              </span>
            </div>
            <div className="list-counter-bucket">
              <a className="list-counter active" href="#">{item.get('count')}</a>
            </div>
          </div>
        ))}
      </div>
    );
  }
}
