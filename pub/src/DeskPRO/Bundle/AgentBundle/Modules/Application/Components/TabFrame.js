import React from "react";

export default class TabFrame extends React.Component {
  render() {
      const { dp_window } = this.props;

      const my_classes = "dp-tab-frame" + (dp_window.collapseNav ? ' expanded' : '')
        + (dp_window.taskView !== 'list' ? ' kanban-shifted' : '');

    return (
      <section className={my_classes}>
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
