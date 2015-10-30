import React, { Component, PropTypes } from 'react';

export class UrgencyList extends Component {
  static propTypes = {
    items: PropTypes.object.isRequired
  };

  render() {
    const items = this.props.items.toOrderedSet().sort((a, b) => a.urgency > b.urgency ? 1 : -1);

    return (
      <div className="sidebar-urgent-sliders">
        {items.map(item => (
          <div className={'slider level-' + item.get('group')}>
            <div className="slider-container">
              <span className="slider-grabber-wrapper">
                <span className="slider-grabber">{item.get('group')}</span>
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
