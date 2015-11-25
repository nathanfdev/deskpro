import React, { PropTypes } from 'react';
import * as legacyUtils from 'DeskPRO/Bundle/AgentBundle/Legacy/legacyUtils';
import { debounce } from 'lodash';

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
    const classes = ['dp-tab-frame'];

    if (dpWindow.get('expandedSwitcher')) {
      classes.push('expanded');
    }
    if (dpWindow.get('collapseNav')) {
      classes.push('collapsed-nav');
    }
    if (dpWindow.get('columnMode') === 'focus') {
      classes.push('collapsed-list');
    }

    const tabbarHtml = legacyUtils.getTemplateHtml('tabbar');

    return (
      <section className={classes.join(' ')}>
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
