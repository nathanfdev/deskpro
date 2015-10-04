import React from "react";

export default class TabFrame extends React.Component {
  render() {
      const { Application } = this.props;

      let classes = ["dp-tab-frame"];

      if (Application.dp_window.get('expandedSwitcher')) {
        classes.push('expanded');
      }

      if (Application.dp_window.get('collapseNav')) {
        classes.push('collapsed-nav');
      }

      if (Application.dp_window.get('taskView') !== 'list') {
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
