import React, { PropTypes } from 'react';

export default class TabFrame extends React.Component {
  static propTypes = {
    dpWindow: PropTypes.object.isRequired
  };

  render() {
    const { dpWindow } = this.props;

    let classes = ['dp-tab-frame'];

    if (dpWindow.get('expandedSwitcher')) {
      classes.push('expanded');
    }
    if (dpWindow.get('collapseNav')) {
      classes.push('collapsed-nav');
    }
    if (dpWindow.get('taskView') !== 'list') {
      classes.push('kanban-shifted');
    }

    classes = classes.join(' ');

    return (
      <section className={classes}>
        <div className="blank-text">
          <p className="hero-icon">
            <i className="fa fa-file-o"></i>
          </p>
          <p>
            No tabs open yet; select an item to display the details here.
          </p>
        </div>
      </section>
    );
  }
}
