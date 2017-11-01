import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

class TabStrip extends React.Component {

  static propTypes = {
    tabs: PropTypes.array.isRequired
  };

  renderTab(tab) {
    return (
      <li>
        <strong>{tab.title || 'Untitled'}</strong>
        <cite>{tab.subtitle || ''}</cite>
      </li>
    );
  }

  render() {
    return (
      <div className="dp-tabbar">
        <li className="add-dropdown"><strong><i className="fa fa-plus"></i></strong></li>
        {this.props.tabs.forEach(t => this.renderTab(t))}
      </div>
    );
  }
}

export class TabFrame extends React.Component {

  static propTypes = {
    dpWindow: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
  }

  render() {
    const { dpWindow } = this.props;

    return (
      <section className={classNames('dp-tab-frame', {
        'expanded':       dpWindow.get('expandedSwitcher'),
        'collapsed-nav':  dpWindow.get('collapseNav'),
        'collapsed-list': dpWindow.get('columnMode') === 'focus'
      })}
      >

        <div className="dp-tabbar-container"><TabStrip tabs={[]} /></div>
        <div className="dp-tabbody">
          <div className="legacy-dp-interface legacy-tabbody">
            <div id="dp_content_wrap"></div>
          </div>
        </div>
      </section>
    );
  }
}
