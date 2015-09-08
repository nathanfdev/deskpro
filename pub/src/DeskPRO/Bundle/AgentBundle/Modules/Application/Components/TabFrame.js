import React from "react";

export default class TabFrame extends React.Component {
  render() {
      const { dp_window } = this.props;

      let classes = ["dp-tab-frame"];

      if (dp_window.expandedSwitcher) {
        classes.push('expanded');
      }

      if (dp_window.collapseNav) {
        classes.push('collapsed-nav');
      }

      if (dp_window.taskView !== 'list') {
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
